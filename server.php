<?php

class server{
	
	var $servername = "localhost";
	var $username = "root";
	var $password = "softcorner";
	var $database = "knuggetlabstest4";
	var $conn;
	
	function makeConnection(){
		$this->conn = mysqli_connect($this->servername, $this->username, $this->password, $this->database);
		if($this->conn->connect_error){
			die("Connection failed : " . $this->conn->connect_error);
		}
		echo "Connection successful\n";
	}
	
	function fireQuery(){
		$sql = "Select id, username, first_name from auth_user limit 5";
		$result = $this->conn->query($sql);
		return $result;
	}
	
	function closeConnection(){
		mysqli_close($this->conn);
	}
}
?>