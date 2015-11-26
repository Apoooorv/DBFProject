<?php

require "DBF.class.php";
require "SQL.php";

/**
 * This class is used for executing the given SQL query and then give a call to the class which will write the DBF file.
 */
class DBFWriter extends DBF{
	var $schema = array();
	var $result = array();
	var $filename = '';
	var $sqlQuery = '';
	var $memofilename = '';
	var $memodata = false;
	
	/*function DBFWriter ($filename, $memoname, $sqlQuery){
		$this->filename = $filename;
		$this->sqlQuery = $sqlQuery;
		$this->memofilename = $memoname;
	}*/
	
	function DBFWriter ($filename, $sqlQuery){
		$this->filename = $filename;
		$this->sqlQuery = $sqlQuery;
	}
	/**
	 * Execute the query and fetch data in required format to be written to DBF file.
	 */
	function getData(){
		$objSQL = new SQL();
		$resultSet =  $objSQL->getQueryResults($this->sqlQuery);
		$this->schema = $resultSet["Header"];
		$this->result = $resultSet["Data"];
		
		if($resultSet["Memo"]){
			$this->memodata = $resultSet["Memo"];
		}
	}
	/**
	 * The method which will be called from  outside for creating the DBF and FPT (if required) files.
	 */
	function writedata(){
		try{
			$this->getData();
			WriteLog::writeDebugLog("Start writing the DBF file at location " . $this->filename);
			if($this->memodata){
				$this->write($this->filename, $this->schema, $this->result, $this->memodata, $this->memodata);
			}
			else{
				$this->write($this->filename, $this->schema, $this->result);
			}
			
			WriteLog::writeDebugLog("Completed writing the DBF file at location " . $this->filename);
		}catch(Exception $exp){
			WriteLog::writeErrorLog("Error when writing the DBF file " . $exp->getTraceAsString ());
		}
	}
}

?>