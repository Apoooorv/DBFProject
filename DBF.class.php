<?php
/**
 * This class is used for writing a DBF file. If the record set getting retun contains a MEMO field 
 * then an FPT file is also created with same name as that of the DBF file but with extension FPT.
 * Currently this class supports writing a FPT file containing one Test type MEMO field.
 * Detailed Comments willl come soon......
 */
class DBF {
	private static $isFirstMemo = true;//Dont change this else MEMO file will not be linked to DBF file correctly.	
	private static $blockSize = 64;//Can change this. Optimum and default value is 64.
	private static $DBFFileName = "";//These are variables used within the class
	private static $FPTFileName = "";//These are variables used within the class
	private static $memoDataFileLength = 512;
	private static $lastBlockNo = 8;
	// utility function to ouput a string of binary digits for inspection
	private static function binout($bin) {
		echo "data:", $bin, "<br />";
		foreach(unpack('C*', $bin) as $byte) {
			printf('%b', $byte);
		}
		echo "<br />";
		return $bin;
	}
	
	/**	Writes a DBF file to the provided location {@link $filename}, with a given
	  * {@link $schema} containing the DBF formatted <code>$records</code>
	  * marked with the 'last updated' mark <code>$date</code> or a current timestamp if last 
	  * update is not provided.
	  * @see DBF
	  * @param string $filename a writable path to place the DBF
	  * @param array $schema an array containing DBF field specifications for each 
	  * 	field in the DBF file (see <code>class DBF</code documentation)
	  * @param array $records an array of fields given in the same order as the 
	  * 	field specifications given in the schema
	  * @param $ismemodata A boolean to check if the query executed contains memo field in it.
	  * @param $memoheaders A collection of the details for writing the memo FPT file. Required if $ismemodata is true
	  * @param array $date an array matching the return structure of <code>getdate()</code>
	  * 	or null to use the current timestamp. This is optional.
	  */
	public static function write($filename, array $schema, array $records, $ismemodata=null, $memoheaders=null, $date=null) {
		self::$DBFFileName = $filename;
		if(!$ismemodata){
			file_put_contents(self::$DBFFileName, self::getBinary($schema, $records, $date,$ismemodata));
		}
		else{
			$DBFPathInfo = pathinfo($filename);
			self::$FPTFileName = $DBFPathInfo['dirname'] . '/' . str_ireplace('.dbf','',$DBFPathInfo['basename']) . '.fpt';
			self::writeMemoHeaders($memoheaders);
			file_put_contents(self::$DBFFileName, self::getBinary($schema, $records, $date,$ismemodata));
		}
	}
	
// 	Writes the headers in to memo file.
	/**
	 * This function will be used for wrinting the Header section for the FPT file created if the result set contains 
	 * a memo field.
	 */
	private function writeMemoHeaders($memoheaders){
		$headerstring = '';
		for($i=0;$i<512;$i++){
			$insertvalue = pack('C', '0x00');
			if($i == 7)
				$insertvalue = pack('C', self::$blockSize);
			if($i == 3)
				$insertvalue = pack('C', 0x17);
			$headerstring.= $insertvalue;
		}
		file_put_contents(self::$FPTFileName, $headerstring);		
	}
	
	/** Gets the DBF file as a binary string
	  * @see DBF::write()
	  * @return string a binary string containing the DBF file.
	  */
	public static function getBinary(array $schema, array $records, $pDate,$ismemofile) {
		if (is_numeric($pDate)) {
			$date = getDate($pDate);
		} elseif ($pDate == null) {
			$date = getDate();
		} else {
			$date = $pDate;
		}
		return self::makeHeader($date, $schema, $records,$ismemofile) . self::makeSchema($schema) . self::makeRecords($schema, $records);
	}
	
	/** Convert a unix timestamp, or the return structure of the <code>getdate()</code>
	  * function into a (binary) DBF timestamp.
	  * @param mixed $date a unix timestamp or the return structure of the <code>getdate()</code>
	  * @param number $milleseconds the number of milleseconds elapsed since 
	  * 	midnight on the day before the date in question. If omitted a second
	  * 	accurate rounding will be constructed from the $date parameter
	  * @return string a binary string containing the DBF formatted timestamp
	  */
	public static function toTimeStamp($date, $milleseconds = null) {
		if (is_array($date)) {
			if (isset($date['jd'])) {
				$jd = $date['jd'];
			}
		
			if (isset($date['js'])) {
				$js = $date['js'];
			}
		}
		
		if (!isset($jd)) {
			$pDate = self::toDate($date);
			$year = substr($pDate, 0, 4);
			$month = substr($pDate, 4, 2);
			$day = substr($pDate, 6, 2);
			$jd = gregoriantojd(intval($month), intval($day), intval($year));
		}
		
		if (!isset($js)) {
			if ($milleseconds === null) {
				if (is_numeric($date) || empty($date)) {
					$utime = getdate(intval($date));
				} else {
					$utime = $date;
				}
				
				$ms = (
					//FIXME: grumble grumble seems to be 9 hours off,
					// no idea where the 9 came from
					$utime['hours'] * 60 * 60 * 1000 + 
					$utime['minutes'] * 60 * 1000 +
					$utime['seconds'] * 1000
				);
				$js = $ms;
			} else {
				$js = $milleseconds;
			}
		}
		return (pack('V', $jd)) . (pack('V', $js));
	}
	
	/** Converts a unix timestamp to the type of date expected by this file writer.
	  * @param integer $timestamp a unix timestamp, or the return format of <code>getdate()</code>
	  * @return string a date formatted to DBF expectations (8 byte string: YYYYMMDD);
	  */
	public static function toDate($timestamp) {
		if (empty($timestamp)) {
			$timestamp = 0;
		}
		if (!is_numeric($timestamp) && !is_array($timestamp)) {
			throw new InvalidArgumentException('$timestamp was not in expected format(s).');
		}
		
		if (is_array($timestamp) && (!isset($timestamp['year']) || !isset($timestamp['mon']) || !isset($timestamp['mday']))) {
			throw new InvalidArgumentException('$timestamp array did not contain expected key(s).');
		}
		
		if (is_string($timestamp) && strlen($timestamp) === 8 && self::validate_date_string($timestamp)) {
			return $timestamp;
		}
		
		if (!is_array($timestamp)) {
			$date = getdate($timestamp);
		} else {
			$date = $timestamp;
		}
		
		return substr(str_pad($date['year'], 4, '0', STR_PAD_LEFT), 0, 4) .
			substr(str_pad($date['mon'], 2, '0', STR_PAD_LEFT), 0, 2) .
			substr(str_pad($date['mday'], 2, '0', STR_PAD_LEFT), 0, 2);
	}
	
	/** Convert a boolean value into DBF equivalent, preserving the meaning of 'T' or 'F'
	  * 	non-booleans will be converted to ' ' (unintialized) with the exception of
	  * 	'T' or 'F', which will be kept as is.
	  * 	booleans will be converted to their respective meanings (true = 'T', false = 'F')
	  * 	
	  * @param mixed $value value to be converted
	  * @return string length 1 string containing 'T', 'F' or uninitialized ' '
	  */
	public static function toLogical($value) {
		if ($value === 'F' || $value === false) {
			return 'F';
		}
		
		if (is_string($value)) {
			if (preg_match("#^\\ +$#", $value)) {
				return ' ';
			}
		}
		
		if ($value === 'T' || $value === true) {
			return 'T';
		}
		
		return ' ';
	}
	
	//calculates the size of a single record
	private static function getRecordSize($schema) {
		$size = 1;//FIXME: I have no idea why this is 1 instead of 0
		$datecount = 0;
		$memocount = 0;
		
		$ismemo = false;
		$isdate = false;
		
		foreach ($schema as $field) {
			if($field['type']=='D'){//if contains a date field
				$isdate = true;
				$datecount++;
			}
			if($field['type']=='M6'){//if contains a Memo field
				$ismemo = true;
				$memocount++;
			}
		}
		
		foreach ($schema as $field) {
			if($field['type']!='M6'){
				if($field['type']=='D'){
					$size += 8;
				}else{
					$size += $field['size'];
				}
			}else{
				$size+=10;
			}
		}
		return $size;
	}
	
	//assembles a string into DBF format truncating and padding, where required
	private static function character($data, $fieldInfo) {
		return substr(str_pad(strval($data), $fieldInfo['size'], " "), 0, $fieldInfo['size']);
	}
	
	private static function validate_date_string($string) {
		$time = mktime (
			0,
			0,
			0,
			intval(substr($string, 4, 2)),
			intval(substr($string, 6, 2)),
			intval(substr($string, 0, 4))
		);
		if ($time === false || $time === -1) {
			return false;
		}
		return true;
	}
	
	//assembles a date into DBF format
	private static function date($data) {
		if (is_int($data)) {
			$tmp = strval($data);
			if (strlen($tmp) == 8 && self::validate_date_string($tmp)) {
				$data = $tmp;
			}
		}else{
			$data = array("year"=>intval(substr($data, 0, 4)),"mon"=>intval(substr($data, 6, 2)),"mday"=>intval(substr($data, 8, 2)));
		}
		return self::toDate($data);
	}
	
	//assembles a number into DBF format, truncating and padding where required
	private static function numeric($data, $fieldInfo) {
		if (isset($fieldInfo['declength']) && $fieldInfo['declength'] > 0) {
			$cleaned = str_pad(number_format(floatval($data), $fieldInfo['declength']), $fieldInfo['size'], ' ', STR_PAD_LEFT);
		} else {
			$cleaned = str_pad(strval(intval($data)), $fieldInfo['size'], ' ', STR_PAD_LEFT);
		}
		return substr($cleaned, 0, $fieldInfo['size']);
	}
	
	//assembles a boolean into DBF format or ' ' for uninitialized
	private static function logical($data) {
		return self::toLogical($data);
	}
	
	//assembles a timestamp into DBF format 
	private static function timeStamp($data) {
		return self::toTimeStamp($data);
	}
	
	//assembles a single field 
	private static function makeField($data, $fieldInfo) {
		//FIXME: support all the types (that make sense)
		switch ($fieldInfo['type']) {
		case 'C':
			return self::character($data, $fieldInfo);
			break;
		case 'D':
			return self::date($data);
			break;
		case 'N':
			return self::numeric($data, $fieldInfo);
			break;
		case 'L':
			return self::logical($data);
			break;
		case 'T':
			return self::timeStamp($data);
			break;
		case 'M6':
			return self::memodata($data, $fieldInfo);
			break;
		default:
			return "";
		}
	}
	
	//insert data in memo field
	private static function memodata($data, $fieldInfo){
		$out = '';
		//Creation of block signature
		$out.= pack('C', 0).pack('C',0).pack('C',0).pack('C',0x01);
		
		/*Create the string of the length of the memo data with fixed string size of 8 chars.If the length is less than 
	 	*	8 characters then provide a left padding of 0*/
		$length = strlen($data);
		for($i=strlen($length);$i<8;$i++)
			$length = '0'.$length;
			
			
		$out.= pack('C', intval($length[0].$length[1])).pack('C', intval($length[2].$length[3])).pack('C', intval($length[4].$length[5])).pack('C', intval($length[6].$length[7]));
		
		//appending data to output
		$out.=$data;
		
		/*Every memo record should be in multiples of the block size. So provide right padding for the memo
		 *  records which have lengts not in the multiple of block size*/
		$lengthOfOut = strlen($out);
		$fraction = ($lengthOfOut/self::$blockSize) - floor($lengthOfOut / self::$blockSize);
		$requiredlength = $lengthOfOut + (self::$blockSize-($fraction*self::$blockSize));
		$out = str_pad($out,$requiredlength,pack('C', 0),STR_PAD_RIGHT);
		self::$memoDataFileLength += strlen($out); 
		$handle = fopen(self::$FPTFileName, 'a');
		fwrite($handle, $out);
		fclose($handle);
		//Clear the cached statics for fetching the current file size 
		clearstatcache();
		//First memo block starts from 8 = 512/blockSize
		//$memoBlockNumber = 512/self::$blockSize;
		//if(!self::$isFirstMemo){
			//$memoBlockNumber = floor(filesize(self::$FPTFileName)/self::$blockSize);
		//	$memoBlockNumber = self::$memoDataFileLength/self::$blockSize;
		//}
		$totakeblockno = self::$lastBlockNo;
		self::$lastBlockNo = self::$lastBlockNo + (strlen($out)/self::$blockSize);
		self::$isFirstMemo = false;
		//self::$lastBlockNo =  $memoBlockNumber;
		return str_pad($totakeblockno, 10, " ", STR_PAD_LEFT);
	}
	/**
	 * Create the string for a single record
	 */
	private static function makeRecord($schema, $record) {
		$out = " ";
		foreach($schema as $singlerow){
			$out.=self::makeField($record[$singlerow['name']], $singlerow);	
		}
		return $out;
	}
	
	/**
	 * Create a string for all the records.
	 */ 
	private static function makeRecords($schema, $records) {
		$out = "";
		foreach ($records as $record) {
			$out .= self::makeRecord($schema, $record);
		}
		return $out . "\x1a"; //FIXME: I have no idea why the end of the file is marked with 0x1a
	}
	
	/**
	 * Make a definition for a single field.
	 */
	private static function makeFieldDef($fieldDef, &$location) {
	//DBF requires date in 8 bytes format, and we receive the date from MYSQL as 10 so adjust it to 8.
	if($fieldDef["type"]=='D'){
		$fieldDef["size"]=8;
	}
	//For memo types of records the type should by M and the size is 10 bytes containing the block size, so adjust it here.
	if($fieldDef["type"]=='M6'){
		$fieldDef["type"]='M';
		$fieldDef["size"]=10;
	}
	//Creating the Field definition for the DBF header, field wise.
		//0+11
		$out = substr(str_pad($fieldDef['name'], 11, "\x00"), 0 , 11);
		//11+1
		$out .= substr($fieldDef['type'], 0, 1);
		//12+4
		$out .= (pack('V', $location));
		//16+1
		$out .= (pack('C', $fieldDef['size']));
		//17+1
		$out .= (pack('C', @$fieldDef['declength']));
		//18+1
		$out .= (pack('C', @$fieldDef['NOCPTRANS'] === true ? 0 : 0));
		//19+13
		$out .= (pack('x13'));
		$location += $fieldDef['size'];
		return $out;
	}
	
	/**
	 * Create the schema to be added in the DBF header.
	 */
	private static function makeSchema($schema) {
		$out = "";
		$location = 1;//FIXME: explain why this is 1 instead of 0
		
		foreach ($schema as $key => $fieldDef) {
			$out .= self::makeFieldDef($fieldDef, $location);
		}
		
		$out .= (pack('C', 13)); // marks the end of the schema portion of the file
		
		//$out .= str_repeat(chr(0), 256); //FIXME: I gues filenames are sometimes stored here
		
		return $out;
	}
	
	/**
	 * Create the header for the DBF file.
	 */
	private static function makeHeader($date, $schema, $records,$ismemofile) {
		//0+1
		if($ismemofile == null){
			$out = (pack('C', 0x02)); // version Foxpro 2.x;
		}else{
			$out = (pack('C', 0xF5)); // version Foxpro 2.x with memo;
		}
		//1+2
		$out .= (pack('C3', substr($date['year'],2,strlen($date['year'])), $date['mon'], $date['mday']));
		//4+4
		$out .= (pack('V', count($records)));//number of records
		//8+2
		$out .= (pack('v', self::getTotalHeaderSize($schema))); //bytes in the header
		//10+2
		$out .= (pack('v', self::getRecordSize($schema))); //bytes in each record
		//12+17
		$out .= (pack('x17')); //reserved for zeros (unused)
		//29+1
		$out .= (pack('C', 0)); //FIXME: language? i have no idea
		//30+2
		$out .= (pack('x2')); //empty
		return $out;
	}
	
	//calculates the total size of the header, given the number of columns
	private static function getTotalHeaderSize($schema) {
		return (count($schema) * 32) + 32 + 1 + 0; 
	}
	
}
?>
