<?php
 require "config.php";
 
class WriteLog{
	/**
	 * Write the Debug log. This will be with log level 3 or above
	 */
	static function writeDebugLog($logMessage){
		if ($GLOBALS["configuration"]["loglevel"] >=3 ){
			if(isset($logMessage) && $logMessage!=""){
				$file = fopen(WriteLog::getLogFileName(),"a");
				fwrite($file,"DEBUG\t" . date('d-m-Y H:i:s', time()) . " \t " . $logMessage . PHP_EOL);
				fclose($file);
			}
		}
	}
	/**
	 * Write the Info log. This will be with log level 2 or above
	 */
	static function writeInfoLog($logMessage){
		if ($GLOBALS["configuration"]["loglevel"] >=2 ){
			if(isset($logMessage) && $logMessage!=""){
				$file = fopen(WriteLog::getLogFileName(),"a");
				fwrite($file,"INFO\t" . date('d-m-Y H:i:s', time()) . " \t " . $logMessage . PHP_EOL);
				fclose($file);
			}
		}
	}
	/**
	 * Write the Error log. This will be with log level 1 or above
	 */
	static function writeErrorLog($logMessage){
		if ($GLOBALS["configuration"]["loglevel"] >=1 ){
			if(isset($logMessage) && $logMessage!=""){
				$file = fopen(WriteLog::getLogFileName(),"a");
				fwrite($file,"ERROR\t" . date('d-m-Y H:i:s', time()) . " \t " . $logMessage . PHP_EOL);
				fclose($file);
			}
		}
	}
	
	static function getLogFileName(){
		$logFileNameFromConfig = $GLOBALS["configuration"]["logfile"];
		$fileNameParts = pathinfo($logFileNameFromConfig);
		return $fileNameParts["filename"] . "_" . date("Y-m-d") . "." . $fileNameParts["extension"];
	}
}


?>