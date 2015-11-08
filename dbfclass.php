<?php

require "DBF.class.php";
require "server.php";

class dbfdemo extends DBF{
	
	var $schema = array(
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
	var $result = array();
	var $filename = 'testfile.dbf';
	function getdata(){
		
		$newconnection = new server();
		$newconnection->makeConnection();
		$result = $newconnection->fireQuery();
		$newconnection->closeConnection();
		$arrayindex = 0;
		while($row = $result->fetch_assoc()){
			$this->result[$arrayindex] = array($row['id'], $row['username'], $row['first_name']);
			$arrayindex+=1;
		}
	
	}
	
	function writedata(){
		$this->write($this->filename, $this->schema, $this->result);
	}
}

$variable = new dbfdemo();
$variable->getdata();
$variable->writedata();
?>