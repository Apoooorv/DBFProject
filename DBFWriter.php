<?php

require "DBF.class.php";
require "SQL.php";

class DBFWriter extends DBF{
	var $schema = array();
	var $result = array();
	var $filename = '';
	var $sqlQuery = '';
	var $memofilename = '';
	var $memodata = false;
	
	function DBFWriter ($filename, $memoname, $sqlQuery){
		$this->filename = $filename;
		$this->sqlQuery = $sqlQuery;
		$this->memofilename = $memoname;
	}
	
	/*var $schema = array(
			array(
					'name' => 'id',
					'type' => 'N',
					'size' => 20,
					'declength' => 2,
					'NOCPTRANS' => TRUE
			),
			array(
					'name' => 'username',
					'type' => 'C',
					'size' => 20, 
					'NOCPTRANS' => TRUE	
			), 
			array(
					'name' => 'first_name', 
					'type' => 'C', 
					'size' => 20, 
					'NOCPTRANS' => TRUE		
			)	
	);
	var $result = array();*/
	
	function getData(){
		$objSQL = new SQL();
		$resultSet =  $objSQL->getQueryResults($this->sqlQuery);
		$this->schema = $resultSet["Header"];
		$this->result = $resultSet["Data"];
		
		if($resultSet["Memo"]){
			$this->memodata = $resultSet["Memo"];
		}
		/*$newconnection = new server();
		$newconnection->makeConnection();
		$result = $newconnection->fireQuery();
		$newconnection->closeConnection();
		$arrayindex = 0;
		while($row = $result->fetch_assoc()){
			$this->result[$arrayindex] = array($row['id'], $row['username'], $row['first_name']);
			$arrayindex+=1;
		}*/
	}
	
	function writedata(){
		try{
			$this->getData();
			WriteLog::writeDebugLog("Start writing the DBF file at location " . $this->filename);
			if($this->memodata){
				$this->write($this->filename, $this->schema, $this->result, $this->memofilename, $this->memodata);
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