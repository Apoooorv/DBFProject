<?php 

	require "DBFWriter.php";
	ini_set('zend.ze1_compatibility_mode', 0);
	//$variable = new DBFWriter("D:/Projects/PHPRND/PHPDBF/code/GitBindedCode/Sample_512block.dbf","select * from ViewVCHX");
	//$variable = new DBFWriter("D:/Projects/PHPRND/PHPDBF/code/GitBindedCode/JoinQuery.dbf","SELECT * FROM dbfproject.ViewVCDEX V1 join `ViewVCHX` V2 ON V2.YRID = V1.YRID");
	//$variable = new DBFWriter("Sample.dbf","Sample.fpt", "select YRID,VCHNO,VCHDATE,DOCCRTDT,DOCUPDDT from ViewVCHX limit 2");
	//$variable = new DBFWriter("Sample.dbf","Sample.fpt", "select `YRID`,`VCHTPCD`,`VCHSRSCD`,`VCHNO`,`VCHID`,`VCHDATE`,`VCHNOTES`,`LOCCD`,`DOCCRTBY`,`DOCCRTDT`,`DOCCRTTM`,`DOCRMRK`,`DOCUPDBY`,`DOCUPDDT`,`DOCUPDTM`,`DOCFRWDTO`,`DOCAUTHIND`,`EXPIMPID`,`month`,`year` from ViewVCHX limit 1");
	//$variable = new DBFWriter("Sample.dbf","Sample.fpt", "select YRID,VCHNO,VCHDATE,DOCCRTDT,`VCHNOTES`,DOCCRTBY from ViewVCHX limit 3");
	//$variable = new DBFWriter("Sample.dbf","Sample.fpt", "select * from `ViewDEXPLOX`");
	//$variable = new DBFWriter("D:/Projects/PHPRND/PHPDBF/code/GitBindedCode/Sample.dbf", "select * from `ViewVCDEX`");
	//$variable = new DBFWriter("Sample.dbf","Sample.fpt", "select * from `ViewPRTDEX`");
	//$variable = new DBFWriter("D:/Projects/PHPRND/PHPDBF/code/GitBindedCode/TwoMemoFields.dbf","SELECT YRID,LOCCD,VCHTPCD,VCHNO,VCHID,VCHNOTES,DOCCRTBY FROM ViewVCHX");
	$variable = new DBFWriter("D:/Projects/PHPRND/PHPDBF/code/GitBindedCode/NoMemoFields.dbf","SELECT * FROM ViewVCDEX");
	$variable->writedata();
?>