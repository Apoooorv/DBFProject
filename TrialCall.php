<?php 
	require "DBFWriter.php";
	
	$variable = new DBFWriter("Sample.dbf","Sample.fpt", "SELECT id, customer_id, type, name, value from `non_medical_customer_general` limit 5");
	$variable->writedata();

?>