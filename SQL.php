<?php
/*
	Created by SOFTCORNER
*/
require "config.php";
require "WriteLog.php";
/**
 * This class is used for opening the connection then executing the query provided.
 */
class SQL{
	var $connection;
	/*
	 * This collection maps the MYSQL datatype int value to the DBF dataype value
	 * */
	private $mysql_dbf_datatype_mapping = array(
		1=>'N',
		2=>'N',
		3=>'N',
		4=>'N',
		5=>'N',
		7=>'T',
		8=>'N',
		9=>'N',
		10=>'D',
		11=>'T',
		12=>'T',
		13=>'N',
		16=>'L',
		252=>'M6',
		253=>'C',
		254=>'C',
		246=>'N'
	);
	/*
	 * This collection will map the MYSQL data type int value to the corresponding datatype name value
	 * */
	private $mysql_data_type_hash = array(
		1=>'tinyint',
		2=>'smallint',
		3=>'int',
		4=>'float',
		5=>'double',
		7=>'timestamp',
		8=>'bigint',
		9=>'mediumint',
		10=>'date',
		11=>'time',
		12=>'datetime',
		13=>'year',
		16=>'bit',
		253=>'varchar',
		254=>'char',
		246=>'decimal'
	);

	/*
	 * This function will be used for fetching the result set based on the given query.
	 * This will return the collection containing the column headings and the data received
	 * by executing the query.
	 * */
	function getQueryResults($sqlQuery){
	WriteLog::writeDebugLog("Start execution of the query");
	$resultSet = array();
		$fieldsMetaData = array();
		$responseArray = array("Header"=>array(),"Data"=>array());
		try{

			$this->getConnection();
			
			
			
			$resultSet = $this->executeQuery($sqlQuery);

			
			$fieldsMetaData = $resultSet->fetch_fields();
			$responseArray["Memo"] = false;
			//This will be used for creating the header for the table in the DBF
			foreach ($fieldsMetaData as $field) {
				$fieldName = $field->name;
				$fieldType = $field->type;
				$length = $field->length;
				if($fieldType == 252){//For Text type MEMO
					$responseArray["Memo"] = array(
						'name' => $field->name,
						'type' => $field->type,
						'size' => $field->max_length
					);
				}
					$headerArray["name"] = $fieldName;
					$headerArray["type"] = $this->mysql_dbf_datatype_mapping[$fieldType];
					$headerArray["size"] = $length;
					$headerArray["NOCPTRANS"] = true;
					$responseArray["Header"][]= $headerArray;
			}
			WriteLog::writeDebugLog("Created header array");
			WriteLog::writeInfoLog("Column count " . count($responseArray["Header"]));
			
			while($row = $resultSet->fetch_assoc()){
				$responseArray["Data"][] = $row;
			}
			WriteLog::writeDebugLog("Created data array");
			WriteLog::writeInfoLog("Total records received " . count($responseArray["Data"]));
		}catch(Exception $exp){
			WriteLog::writeErrorLog("Error when fetching data from query.Exception: " . $exp->getTraceAsString ());
		}
		return $responseArray;
	}
	/**
	 * This function will execute the given SQL query and then return the result set to be processed on.
	 */
	function executeQuery($sqlQuery){
		$resultSet = array();
		WriteLog::writeInfoLog("Query to be executed:" . $sqlQuery);
		try{
			$resultSet = $this->connection->query($sqlQuery);
		}catch(Exception $exp){
			WriteLog::writeErrorLog("Error during query execution:" . $exp->getTraceAsString ());
		}
		WriteLog::writeDebugLog("Query execution successful");
		return $resultSet;
	}
	/*
	 * This function will open a connection with the DB details as given in the configuration file.
	 * */
	function getConnection(){
		WriteLog::writeDebugLog("Open Connection");
		$this->connection = new mysqli($GLOBALS["configuration"]["server"], $GLOBALS["configuration"]["username"], $GLOBALS["configuration"]["password"],$GLOBALS["configuration"]["databasename"]);
		if (!$this->connection) {
			WriteLog::writeErrorLog("Could not open DB connection. " . mysql_error());
		    die('Could not connect: ' . mysql_error());
		}
	}
	/*This function will be used for closing the connection if it is laready opened.*/
	function closeConnection($connection){
		if(isset($this->connection)){
			WriteLog::writeDebugLog("Close Connection");
			mysql_close($this->connection);
		}
	}
}


?>