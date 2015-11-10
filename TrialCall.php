<?php 
	require "DBFWriter.php";
	
	$variable = new DBFWriter("Sample.dbf","SELECT * FROM `ViewDEXPLOX`");
	$variable->writedata();

?>