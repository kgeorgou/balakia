<?php
session_start();

if (isset($_POST["func"])) {
	
	$con = mysql_connect ( "localhost", "root", "" );

	if (! $con) {
		die ( 'Not connected : ' . mysql_error () );
	}

	mysql_select_db ( "gaia", $con ) or die ( mysql_error () );


	$func =$_POST["func"];

	// schoolArea.js, students.js	
	if ($func == 'readdimous'){
		readDimous($con);
	}
	// menu.js
	else if ($func == 'readschyears'){
		readSchYears($con);
	}
	//schools.js, schoolArea.js, students.js
	else if ($func == 'readschoolsbydimostype'){

		$dimos =$_POST["dimos"];
		$type = $_POST["type"];
		if($type == ""){
			if ($_SESSION["katanomi"] == "ΔΗΜΟΤΙΚΟ σε ΓΥΜΝΑΣΙΟ"){
				$type="ΔΗΜΟΤΙΚΟ";
			}else{
				$type="ΓΥΜΝΑΣΙΟ";
			}
		}
		readSchoolsByDimosType($con, $dimos, $type);
	}
	// katanomi.js
	else if ($func == 'readschoolsandstudents'){
	
		$dimos =$_POST["dimos"];
		$type = $_POST["type"];
		readSchoolsAndStudents($con, $dimos, $type);
	}
	//menu.js
	else if ($func == 'readcurrschyearandkatanomi'){
	
		readCurrSchYearAndKatanomi($con);
	}
	else if ($func == 'readschooldetails'){

		$school =$_POST["school"];
		readSchoolDetails($con, $school);
	}
	else if ($func == 'readschdetailsbydimos'){

		$dimos = $_POST["dimos"];
		readSchoolDetailsByDimos($con, $dimos);
	}
	else if ($func == 'readallschoolsarea'){

		readAllSchoolsArea($con);
	}
	// students.js
	else if ($func == 'readbrotherschools'){
		
		if ($_SESSION["katanomi"] == "ΔΗΜΟΤΙΚΟ σε ΓΥΜΝΑΣΙΟ"){
			$type="ΓΥΜΝΑΣΙΟ";
		}else{
			$type="ΛΥΚΕΙΟ";
		}
		readBrotherSchools($con, $type);
	}
	// students.js
	else if ($func == 'readschoolstogo'){
	
		if ($_SESSION["katanomi"] == "ΔΗΜΟΤΙΚΟ σε ΓΥΜΝΑΣΙΟ"){
			$type="ΓΥΜΝΑΣΙΟ";
		}else{
			$type="ΛΥΚΕΙΟ";
		}
		readSchoolsToGo($con, $type);
	}
	else if ($func == 'readdieythaddress'){

		readDieythAddress($con);
	}
	//schoolArea.js
	else if ($func == 'readparametroi'){
	
		readParametroi($con);
	}
	// katanomi.js
	else if ($func == 'readstudentsdetails'){
		$dimos =$_POST["dimos"];
		$type =$_POST["type"];
		/* Αν γίνεται κατανομή από Δημοτικό σε Γυμνάσιο
		 * να διαβάσει τους μαθητές δημοτικού, ενώ 
		 * αν γίνεται κατανομή από Γυμνάσιο σε Λύκειο 
		 * να διαβάσει τους μαθητές γυμνασίου 
		 */
		if ($type=='ΓΥΜΝΑΣΙΟ'){
			$type = 'ΔΗΜΟΤΙΚΟ';
		}
		if ($type=='ΛΥΚΕΙΟ'){
			$type = 'ΓΥΜΝΑΣΙΟ';
		}
		readStudentsDetails($con, $dimos, $type);
	}
	// katanomi.js
	else if ($func == 'readnondistributedstuds'){
		$dimos =$_POST["dimos"];
		$type =$_POST["type"];
		readNondistributedStuds($con, $dimos, $type);
	}
	// students.js 
	else if ($func == 'readstudentsbyschool'){
		$school =$_POST["school"];
		readStudentsBySchool($con,$school);
	}
	else if ($func == 'readstudentsaddresses'){
		$dimos =$_POST["dimos"];
		readStudentsAddresses($con, $dimos);
	}
	else if ($func == 'backupdatabase'){
		backupDatabase();
	}
	else {
		$file = fopen("logs.txt", "a") or die("Unable to open file!");
		$txt =  date("Y-m-d h:i:sa").": Invalid data send to readDB.php: POST[func] not defined\n";
		fwrite($file, $txt);
		fclose($file);

		unset ( $err, $descr);
		$err = "true";
		$descr = "PHP ERROR: POST[func] not defined.";
		$err_mess = array("err"=>$err, "errdescr"=>$descr);
		echo json_encode($err_mess);
	}
}

function backupDatabase(){

	$file = fopen("logs.txt", "a") or die("Unable to open file!");
	$txt =  date("Y-m-d h:i:sa").": backupDatabase\n";
	fwrite($file, $txt);
	fclose($file);

	$host = 'localhost';
	$user = 'root';
	$pass = '';
	$name = 'gaia';
	$tables = '*';

	$link = mysql_connect($host,$user,$pass);
	mysql_select_db($name,$link);

	mysql_query ( "SET NAMES 'utf8'", $link );
	//get all of the tables
	if($tables == '*')
	{
		$tables = array();
		$result = mysql_query('SHOW TABLES');
		while($row = mysql_fetch_row($result))
		{
			$tables[] = $row[0];
		}
	}
	else
	{
		$tables = is_array($tables) ? $tables : explode(',',$tables);
	}

	//cycle through
	foreach($tables as $table)
	{
		$result = mysql_query('SELECT * FROM '.$table);
		$num_fields = mysql_num_fields($result);

		$return.= 'DROP TABLE IF EXISTS '.$table.';';
		$row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
		$return.= "\n\n".$row2[1].";\n\n";

		for ($i = 0; $i < $num_fields; $i++)
		{
			while($row = mysql_fetch_row($result))
			{
				$return.= 'INSERT INTO '.$table.' VALUES(';
				for($j=0; $j < $num_fields; $j++)
				{
					$row[$j] = addslashes($row[$j]);
					$row[$j] = ereg_replace("\n","\\n",$row[$j]);
					if (isset($row[$j])) {if( is_null($row[$j]))  $return.= 'null' ;  else $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
					if ($j < ($num_fields-1)) { $return.= ','; }
				}
				$return.= ");\n";
			}
		}
		$return.="\n\n\n";
	}

	//save file
	$handle = fopen('.\backups\db-backup-'.date("Y-m-d-H-i-s").'-'.(md5(implode(',',$tables))).'.sql','w+');
	fwrite($handle,$return);
	fclose($handle);


	return true;
}
/* $$$OK$$$**********************************************************************************************
 * readCurrSchYearAndKatanomi: Διαβάζει την τρέχουσα σχολική χρονιά όπως έχει οριστεί από τον χρήστη
 * 				Καλείται από
 * 					* την συνάρτηση displayCurrSchYearAndKatanomi στο menu.js
 *
 * Input:
 * Output: 		Επιστέφει την τρέχουσα σχολική χρονιά
 *********************************************************************************************** */
function readCurrSchYearAndKatanomi($con){

	// build query
	$query = "SELECT SCH_YEAR, DISTRIB_TYPE FROM PARAMETROI";

	// check query result
	mysql_query ( "SET NAMES 'utf8'", $con );
	$result = mysql_query ( $query );

	if (! $result) {
		$message = 'Invalid query: ' . mysql_error () . '\n';
		$message .= 'Whole query: ' . $query;

		$file = fopen("logs.txt", "a") or die("Unable to open file!");
		$txt =  date("Y-m-d h:i:sa").": readCurrSchYear: Query failed = ".$query."\n";
		fwrite($file, $txt);
		fclose($file);

		die ( $message );

	} else {
		$row = mysql_fetch_assoc ( $result );
		unset ( $schyear, $katanomi );
		$schyear = $row['SCH_YEAR'];
		$katanomi = $row['DISTRIB_TYPE'];
		$_SESSION["schyear"]= $schyear;
		$_SESSION["katanomi"]= $katanomi;
		$data = array("schyear"=>$schyear, "katanomi"=>$katanomi);
		echo json_encode($data);

	}
}
/* $$$OK$$$**********************************************************************************************
 * readDimous: 	Διαβάζει τους κωδικούς κα τα ονόματα των δήμων της Διεύθυνσης
 * 				Καλείται από 
 * 					* την συνάρτηση loadDimous στο schoolArea.js
 * 					* την συνάρτηση displayDimous στο menu.js
 * 					* την συνάρτηση readAllDimous στο students.js
 * 		
 * Input:
 * Output: 		Επιστέφει τους κωδικούς κα τα ονόματα των δήμων της Διεύθυνσης
 * 				ή λάθος αν δεν βρέθηκαν δήμοι.
 *********************************************************************************************** */
function readDimous($con){

	// build query
	$query = "SELECT CODE, NAME, ADDRESS, LAT, LNG FROM DHMOI WHERE STATUS is null";

	// check query result
	mysql_query ( "SET NAMES 'utf8'", $con );
	$result = mysql_query ( $query );

	if (! $result) {
		$message = 'Invalid query: ' . mysql_error () . '\n';
		$message .= 'Whole query: ' . $query;

		$file = fopen("logs.txt", "a") or die("Unable to open file!");
		$txt =  date("Y-m-d h:i:sa").": readDimous: Query failed = ".$query."\n";
		fwrite($file, $txt);
		fclose($file);

		die ( $message );

	} else {

		if(mysql_num_rows($result)==0) {
			unset ( $err, $descr);
			$err = "true";
			$descr = "SERVER: Δεν βρέθηκαν δήμοι.";
			$err_mess = array("err"=>$err, "errdescr"=>$descr);
			echo json_encode($err_mess);
		}
		else {
			$data = array();
			while ( ($row = mysql_fetch_assoc ( $result )) ) {

				unset ( $code, $name, $address, $lat, $lng );
				$code = $row['CODE'];
				$name = $row['NAME'];
				$address = $row['ADDRESS'];
				if($address==null) {$address="";}
				$lat = $row['LAT'];
				if($lat==null) {$lat="";}
				$lng = $row['LNG'];
				if($lng==null) {$lng="";}
				$dimos = array("code"=>$code, "name"=>$name, "address"=>$address, "lat"=>$lat, "lng"=>$lng);
				array_push($data,$dimos);
			}
			echo json_encode($data);
		}
	}
}
/* $$$OK$$$**********************************************************************************************
 * readSchYears: 	Διαβάζει τις σχολικές χρονιές
 * 					Καλείται από την συνάρτηση loadSchYears() στο menu.js *
 * Input:
 * Output: 		Επιστέφει τις σχολικές χρονιές ή λάθος.
 *********************************************************************************************** */
function readSchYears($con){

	// build query
	$query = "SELECT SCH_YEAR FROM SCHOOL_YEARS";

	// check query result
	mysql_query ( "SET NAMES 'utf8'", $con );
	$result = mysql_query ( $query );

	if (! $result) {
		$message = 'Invalid query: ' . mysql_error () . '\n';
		$message .= 'Whole query: ' . $query;

		$file = fopen("logs.txt", "a") or die("Unable to open file!");
		$txt =  date("Y-m-d h:i:sa").": readSchYears: Query failed = ".$query."\n";
		fwrite($file, $txt);
		fclose($file);

		die ( $message );

	} else {

		if(mysql_num_rows($result)==0) {
			unset ( $err, $descr);
			$err = "true";
			$descr = "SERVER: Δεν βρέθηκαν σχολικές χρονιές.";
			$err_mess = array("err"=>$err, "errdescr"=>$descr);
			echo json_encode($err_mess);
		}
		else {
			$data = array();
			while ( ($row = mysql_fetch_assoc ( $result )) ) {

				unset ($name);
				$name = $row['SCH_YEAR'];
				$year = array("name"=>$name);
				array_push($data,$year);
			}
			echo json_encode($data);
		}
	}
}
/* $$$ OK $$$ **********************************************************************************************
 * readBrotherSchools: 	Διαβάζει από την βάση δεδομένων και επιστρέφει τα πιθανά σχολεία του αδερφού
 * 						βάσει του είδους κατανομής
 * 						Καλείται από την συνάρτηση
 * 								* readBrotherSchools στο students.js $$OK$$
 * Input:				con: η σύνδεση στη ΒΔ
 * Output: 				Επιστέφει τα στοιχεία των σχολείων ή λάθος αν δεν βρέθηκαν σχολεία
 *********************************************************************************************** */
function readBrotherSchools($con, $type){
	
	if ($type =="ΓΥΜΝΑΣΙΟ"){// κατανομή από Δημοτικό σε Γυμνάσιο
		// build query
		$query = "SELECT CODE, NAME FROM SCHOOLS 
				WHERE STATUS IS NULL
				AND TYPE IN ('ΓΥΜΝΑΣΙΟ', 'ΛΥΚΕΙΟ')
				ORDER BY DHMOS, TYPE, NAME";
	}else{// κατανομή από Γυμνάσιο σε Λύκειο
		$query = "SELECT CODE, NAME FROM SCHOOLS
				WHERE STATUS IS NULL
				AND TYPE ='ΛΥΚΕΙΟ'
				ORDER BY DHMOS, TYPE, NAME";
	}
	

	// check query result
	mysql_query ( "SET NAMES 'utf8'", $con );
	$result = mysql_query ( $query );

	if (! $result) {
		$message = 'Invalid query: ' . mysql_error () . '\n';
		$message .= 'Whole query: ' . $query;

		$file = fopen("logs.txt", "a") or die("Unable to open file!");
		$txt =  date("Y-m-d h:i:sa").": readBrotherSchools: Query failed = ".$query."\n";
		fwrite($file, $txt);
		fclose($file);

		die ( $message );

	} else {

		if(mysql_num_rows($result)==0) {
			unset ( $err, $descr);
			$err = "true";
			$descr = "SERVER: Δεν βρέθηκαν σχολεία.";
			$err_mess = array("err"=>$err, "errdescr"=>$descr);
			echo json_encode($err_mess);
		}
		else {
			$data = array();
			while ( ($row = mysql_fetch_assoc ( $result )) ) {

				unset ( $code, $name );
				$code = $row['CODE'];
				$name = $row['NAME'];
				$school = array("code"=>$code, "name"=>$name);
				array_push($data,$school);
			}
			echo json_encode($data);
		}
	}
}
/* $$$ OK $$$ **********************************************************************************************
 * readSchoolsToGo: 	Διαβάζει από την βάση δεδομένων και επιστρέφει τα πιθανά σχολεία κατανομής
 * 						βάσει του είδους κατανομής
 * 						Καλείται από την συνάρτηση
 * 								* readSchoolsToGo στο students.js $$OK$$
 * Input:				con: η σύνδεση στη ΒΔ
 * Output: 				Επιστέφει τα στοιχεία των σχολείων ή λάθος αν δεν βρέθηκαν σχολεία
 *********************************************************************************************** */
function readSchoolsToGo($con, $type){

	$query = "SELECT CODE, NAME FROM SCHOOLS
				WHERE STATUS IS NULL
				AND TYPE ='$type'
				ORDER BY DHMOS, NAME";

	// check query result
	mysql_query ( "SET NAMES 'utf8'", $con );
	$result = mysql_query ( $query );

	if (! $result) {
		$message = 'Invalid query: ' . mysql_error () . '\n';
		$message .= 'Whole query: ' . $query;

		$file = fopen("logs.txt", "a") or die("Unable to open file!");
		$txt =  date("Y-m-d h:i:sa").": readSchoolsToGo: Query failed = ".$query."\n";
		fwrite($file, $txt);
		fclose($file);

		die ( $message );

	} else {

		if(mysql_num_rows($result)==0) {
			unset ( $err, $descr);
			$err = "true";
			$descr = "SERVER: Δεν βρέθηκαν σχολεία.";
			$err_mess = array("err"=>$err, "errdescr"=>$descr);
			echo json_encode($err_mess);
		}
		else {
			$data = array();
			while ( ($row = mysql_fetch_assoc ( $result )) ) {

				unset ( $code, $name );
				$code = $row['CODE'];
				$name = $row['NAME'];
				$school = array("code"=>$code, "name"=>$name);
				array_push($data,$school);
			}
			echo json_encode($data);
		}
	}
}
/* $$$ OK $$$ **********************************************************************************************
 * readSchoolsByDimosType: Διαβάζει τα στοιχεία των σχολείων που ανήκουν σε κάποιο συγκεκριμένο
 * 							δήμο και είναι Δημοτικά ή Γυμνάσια ή  Λύκεια.
 * 							Καλείται από την συνάρτηση
 * 								* loadSchools στο schoolArea.js $$OK$$
 * 								* displaySchools στο schools.js $$OK$$
 * 								* loadDimosSchools() στο students.js  $$OK$$
 * Input:					con: η σύνδεση στη ΒΔ
 * 							dimos: ο κωδικός του δήμου
 * 							type: ο τύπος του σχολείου Γυμνάσιο ή Λύκειο
 * Output: 					Επιστέφει τα στοιχεία των σχολείων ή
 * 							λάθος αν δεν βρέθηκαν σχολεία
 *********************************************************************************************** */
function readSchoolsByDimosType($con, $dimos, $type){

	if ($type==""){
		$query = "SELECT 	
					CODE, 
					NAME, 
					ADDRESS, 
					LAT, 
					LNG, 
					DHMOS, 
					COLOR, 
					AREA
				FROM SCHOOLS 
				WHERE 
					TYPE in ('ΔΗΜΟΤΙΚΟ', 'ΓΥΜΝΑΣΙΟ') 
					and COD_DHMOS='$dimos' 
					and status is null 
				ORDER BY NAME ";
	}else{
		// If status='Deleted' school is deleted else it is null
		$query = "SELECT 
					CODE, 
					NAME, 
					ADDRESS, 
					LAT, 
					LNG, 
					DHMOS, 
					COLOR, 
					AREA 
				FROM SCHOOLS 
				WHERE 
					TYPE='$type' 
					and COD_DHMOS='$dimos' 
					and status is null 
				ORDER BY NAME ";
	}
	/* For testing
	 * $file = fopen("logs.txt", "a") or die("Unable to open file!");
	 $txt =  date("Y-m-d h:i:sa").": readSschoolsByDimosType: query = ".$query."\n";
	 fwrite($file, $txt);
	 fclose($file);*/


	mysql_query ( "SET NAMES 'utf8'", $con );
	$result = mysql_query ( $query );

	if (!$result) {
		$message = 'Invalid query: ' . mysql_error () . '\n';
		$message .= 'Whole query: ' . $query;

		$file = fopen("logs.txt", "a") or die("Unable to open file!");
		$txt =  date("Y-m-d h:i:sa").": readSchoolsByDimosType: Query failed = ".$query."\n";
		fwrite($file, $txt);
		fclose($file);

		die ( $message );
	} else {
		// Data not found
		if(mysql_num_rows($result)==0) {
			unset ( $err, $descr);
			$err = "true";
			$descr = "SERVER: Δεν βρέθηκαν σχολεία.";
			$err_mess = array("err"=>$err, "errdescr"=>$descr);
			echo json_encode($err_mess);
		}
		else {

			$data = array();

			while ( ($row = mysql_fetch_assoc ( $result )) ) {
					
				unset ( $code, $name, $address, $lat, $lan, $dimoss, $color, $area);
				$code = $row['CODE'];
				$name = $row['NAME'];
				$address = $row['ADDRESS'];
				$lat = $row['LAT'];
				if($lat==null) {$lat="";}
				$lng = $row['LNG'];
				if($lng==null) {$lng="";}
				$dimoss = $row['DHMOS'];
				$color = $row['COLOR'];
				$area = $row['AREA'];
					
				$school = array("code"=>$code, "name"=>$name, "address"=>$address, "lat"=>$lat, "lng"=>$lng, "dimos"=>$dimoss, "color"=>$color, "area"=>$area);
				array_push($data,$school);
			}
			echo json_encode($data);
		}
	}
}



function readSchoolDetails($con, $schCode){

	// build query
	$query = "SELECT NAME, ADDRESS, AREA FROM SCHOOLS WHERE CODE='$schCode'";

	// check query result
	mysql_query ( "SET NAMES 'utf8'", $con );
	$result = mysql_query ( $query ) or die ( '' );

	if (!$result) {
		$message = 'Invalid query: ' . mysql_error () . '\n';
		$message .= 'Whole query: ' . $query;

		$file = fopen("logs.txt", "a") or die("Unable to open file!");
		$txt =  date("Y-m-d h:i:sa").": readSchoolAreaFun:No result returned\n";
		fwrite($file, $txt);
		fclose($file);

		die ( $message );
	} else {
		//unset ( $data);
		$data = array();

		$row = mysql_fetch_assoc ( $result );

		unset ( $name, $address, $area);
		$name = $row['NAME'];
		$address = $row['ADDRESS'];
		$area = $row['AREA'];

		/*$file = fopen("logs.txt", "a") or die("Unable to open file!");
		 $txt =  date("Y-m-d h:i:sa").": NAME = ".$name.", ADDRESS = ".$address.", AREA = ".$area."\n";
		 fwrite($file, $txt);
		 fclose($file);*/

		$schDetails = array("name"=>$name ,"address"=>$address, "area"=>$area);
		array_push($data,$schDetails);

		echo json_encode($data);
	}

}

function readSchoolDetailsByDimos($con, $dimos){
	
	$schyear = $_SESSION["schyear"];
	
	// build query
	$query = "SELECT CODE, NAME, ADDRESS, AREA, COLOR
	FROM SCHOOLS
	WHERE  TYPE='ΓΥΜΝΑΣΙΟ' and COD_DHMOS='$dimos' AND STATUS IS NULL
	ORDER BY NAME ";

	// check query result
	mysql_query ( "SET NAMES 'utf8'", $con );
	$result = mysql_query ( $query ) or die ( '' );

	if (!$result) {
		$message = 'Invalid query: ' . mysql_error () . '\n';
		$message .= 'Whole query: ' . $query;

		$file = fopen("logs.txt", "a") or die("Unable to open file!");
		$txt =  date("Y-m-d h:i:sa").": readSchoolDetailsByDimos:No result returned\n";
		fwrite($file, $txt);
		fclose($file);

		die ( $message );
	} else {
		//unset ( $data);
		$data = array();


		while ( ($row = mysql_fetch_assoc ( $result )) ) {

			unset ( $code, $name, $address, $area, $color, $countStud);
			$code = $row['CODE'];
			$name = $row['NAME'];
			$address = $row['ADDRESS'];
			$area = $row['AREA'];
			$color = $row['COLOR'];
				


			$querySum = "SELECT count(*) AS COUNTSTUD
			FROM students
			WHERE  SCHOOL_TO_GO='$code'
			AND YEAR='$schyear'";
				
			mysql_query ( "SET NAMES 'utf8'", $con );
			$resultSum = mysql_query ( $querySum ) or die ( '' );
				
			if (!$resultSum) {
				$message = 'Invalid query: ' . mysql_error () . '\n';
				$message .= 'Whole query: ' . $query;
					
				$file = fopen("logs.txt", "a") or die("Unable to open file!");
				$txt =  date("Y-m-d h:i:sa").": readSchoolDetailsByDimos: Count not returned\n";
				fwrite($file, $txt);
				fclose($file);
					
				die ( $message );
			} else {

				$rowSum = mysql_fetch_assoc ( $resultSum );


				$countStud = $rowSum['COUNTSTUD'];

				/*$file = fopen("logs.txt", "a") or die("Unable to open file!");
				$txt =  date("Y-m-d h:i:sa").": readSchoolDetailsAll: count:".$countStud."\n";
				fwrite($file, $txt);
				fclose($file);*/
			}
				
				
			$schDetails = array("code"=>$code ,"name"=>$name ,"address"=>$address, "area"=>$area, "color"=>$color, "countstud"=>$countStud);
			array_push($data,$schDetails);
		}

		/*$file = fopen("logs.txt", "a") or die("Unable to open file!");
		$txt =  date("Y-m-d h:i:sa").": readSchoolDetailsByDimos:data:".json_encode($data)."\n";
		fwrite($file, $txt);
		fclose($file);*/

		echo json_encode($data);

	}
}

function readAllSchoolsArea($con){
	
	$schyear = $_SESSION["schyear"];

	// build query
	$query = "SELECT CODE, NAME, ADDRESS, AREA, COLOR
	FROM SCHOOLS
	WHERE  TYPE='ΓΥΜΝΑΣΙΟ' AND STATUS IS NULL
			AND AREA IS NOT NULL
	ORDER BY NAME ";

	// check query result
	mysql_query ( "SET NAMES 'utf8'", $con );
	$result = mysql_query ( $query ) or die ( '' );

	if (!$result) {
		$message = 'Invalid query: ' . mysql_error () . '\n';
		$message .= 'Whole query: ' . $query;

		$file = fopen("logs.txt", "a") or die("Unable to open file!");
		$txt =  date("Y-m-d h:i:sa").": readAllSchoolsArea:No result returned\n";
		fwrite($file, $txt);
		fclose($file);

		die ( $message );
	} else {
		//unset ( $data);
		$data = array();


		while ( ($row = mysql_fetch_assoc ( $result )) ) {

			unset ( $code, $name, $address, $area, $color, $countStud);
			$code = $row['CODE'];
			$name = $row['NAME'];
			$address = $row['ADDRESS'];
			$area = $row['AREA'];
			$color = $row['COLOR'];



			$querySum = "SELECT count(*) AS COUNTSTUD
			FROM students
			WHERE  SCHOOL_TO_GO='$code'
			AND YEAR='$schyear'";

			mysql_query ( "SET NAMES 'utf8'", $con );
			$resultSum = mysql_query ( $querySum ) or die ( '' );

			if (!$resultSum) {
				$message = 'Invalid query: ' . mysql_error () . '\n';
				$message .= 'Whole query: ' . $query;
					
				$file = fopen("logs.txt", "a") or die("Unable to open file!");
				$txt =  date("Y-m-d h:i:sa").": readAllSchoolsArea: Count not returned\n";
				fwrite($file, $txt);
				fclose($file);
					
				die ( $message );
			} else {

				$rowSum = mysql_fetch_assoc ( $resultSum );


				$countStud = $rowSum['COUNTSTUD'];

				/*$file = fopen("logs.txt", "a") or die("Unable to open file!");
				 $txt =  date("Y-m-d h:i:sa").": readAllSchoolsArea: count:".$countStud."\n";
				 fwrite($file, $txt);
				 fclose($file);*/
			}


			$schDetails = array("code"=>$code ,"name"=>$name ,"address"=>$address, "area"=>$area, "color"=>$color, "countstud"=>$countStud);
			array_push($data,$schDetails);
		}

		$file = fopen("logs.txt", "a") or die("Unable to open file!");
		$txt =  date("Y-m-d h:i:sa").": readAllSchoolsArea:data".json_encode($data)."\n";
		fwrite($file, $txt);
		fclose($file);

		echo json_encode($data);

	}
}



function readDieythAddress($con){

	// build query
	$query = "SELECT DIEYTH_NAME, DIEYTH_ADDRESS FROM PARAMETROI";

	// check query result
	mysql_query ( "SET NAMES 'utf8'", $con );
	$result = mysql_query ( $query ) or die ( '' );

	if (! $result) {
		$message = 'Invalid query: ' . mysql_error () . '\n';
		$message .= 'Whole query: ' . $query;

		$file = fopen("logs.txt", "a") or die("Unable to open file!");
		$txt =  date("Y-m-d h:i:sa").": readDieythAddress:No result returned\n";
		fwrite($file, $txt);
		fclose($file);

		die ( $message );
	} else {

		$data = array();
		$row = mysql_fetch_assoc ( $result );
		unset ( $name, $address );
		$name = $row['DIEYTH_NAME'];
		$address = $row['DIEYTH_ADDRESS'];
			
		/* For testing*/
		/*$file = fopen("logs.txt", "a") or die("Unable to open file!");
		 $txt =  date("Y-m-d h:i:sa").": readDieythAddress:".$name.", ".$address. " \n";
		 fwrite($file, $txt);
		 fclose($file);*/
			
			
		$dieyth = array("name"=>$name, "address"=>$address);
		array_push($data,$dieyth);

		echo json_encode($data);
	}
}
/* $$$OK$$$**********************************************************************************************
 * readParametroi: 	Διαβάζει τις παραμετρους του συστήματος
 * 					Καλείται από την συνάρτηση
 * 					* initMap() και initMapArea() στο schoolArea.js $$$OK$$$
 * Input:			con: η σύνδεση στη ΒΔ
 * Output: 			
 *********************************************************************************************** */

function readParametroi($con){

	// build query
	$query = "SELECT DIEYTH_NAME, DIEYTH_ADDRESS, LAT, LNG, API_KEY FROM PARAMETROI WHERE SID=1;";

	// check query result
	mysql_query ( "SET NAMES 'utf8'", $con );
	$result = mysql_query ( $query ) ;

	if (!$result) {
		$message = 'Invalid query: ' . mysql_error () . '\n';
		$message .= 'Whole query: ' . $query;
	
		$file = fopen("logs.txt", "a") or die("Unable to open file!");
		$txt =  date("Y-m-d h:i:sa").": readParametroi: Query failed = ".$query."\n";
		fwrite($file, $txt);
		fclose($file);
	
		die ( $message );
	} else {

		// Data not found
		if(mysql_num_rows($result)==0) {
			unset ( $err, $descr);
			$err = "true";
			$descr = "SERVER: Δεν βρέθηκαν στοιχεία.";
			$err_mess = array("err"=>$err, "errdescr"=>$descr);
			echo json_encode($err_mess);
		}
		else {
			$data = array();
			$row = mysql_fetch_assoc ( $result );
			unset ( $name, $address, $lat, $lng, $apikey );
			$name = $row['DIEYTH_NAME'];
			$address = $row['DIEYTH_ADDRESS'];
			$lat = $row['LAT'];
			$lng = $row['LNG'];
			$apikey = $row['API_KEY'];
				
			
				
			$dieyth = array("name"=>$name, "address"=>$address, "lat"=>$lat, "lng"=>$lng, "apikey"=>$apikey);
			array_push($data,$dieyth);
			
			echo json_encode($data);
		}
		
	}
}

/* $$$OK $$**********************************************************************************************
 * readStudentsBySchool: 	Διαβάζει τα στοιχεία των μαθητών που ανήκουν σε συγκεκριμένο σχολείο.
 * 							Καλείται από την συνάρτηση
 * 								* displayStudents  στο students.js
 * Input:					con: η σύνδεση στη ΒΔ
 * Output: 					Επιστέφει τα στοιχεία των μαθητών ή
 * 							λάθος αν δεν βρέθηκαν σχολεία
 *********************************************************************************************** */
function readStudentsBySchool($con,$school){

	$schyear = $_SESSION["schyear"];
	
	// build query
	$query = "SELECT 
	SID, 
	AM,
	NAME,
	SURNAME,
	FATHER,
	ADDRESS,
	TK,
	(select NAME FROM DHMOI WHERE CODE = STUDENTS.DHMOS_CODE) AS DHMOS,
	SCHOOL,
	(SELECT NAME FROM SCHOOLS WHERE STUDENTS.BROTHER_SCH = SCHOOLS.CODE) AS BROTHER_SCH,
	BROTHER_GRADE,
	YEAR,
	REASON,
	FIXED,
	(SELECT NAME FROM SCHOOLS WHERE STUDENTS.SCHOOL_TO_GO = SCHOOLS.CODE) AS SCHOOL_TO_GO,
	LAT,
	LNG
	FROM STUDENTS
	WHERE  YEAR = '$schyear'
	AND SCHOOL='$school'
	ORDER BY SID";

	// check query result
	mysql_query ( "SET NAMES 'utf8'", $con );
	$result = mysql_query ( $query );

	if (!$result) {
		$message = 'Invalid query: ' . mysql_error () . '\n';
		$message .= 'Whole query: ' . $query;
		
		$file = fopen("logs.txt", "a") or die("Unable to open file!");
		$txt =  date("Y-m-d h:i:sa").": readStudentsBySchool: Query failed = ".$query."\n";
		fwrite($file, $txt);
		fclose($file);
		
		die ( $message );
	} else {
			// Data not found
			if(mysql_num_rows($result)==0) {
				unset ( $err, $descr);
				$err = "true";
				$descr = "SERVER: Δεν βρέθηκαν μαθητές.";
				$err_mess = array("err"=>$err, "errdescr"=>$descr);
				echo json_encode($err_mess);
			}
			else {
				$data = array();
				
				
				while ( ($row = mysql_fetch_assoc ( $result )) ) {
				
				
					unset ( $sid, $am, $name, $surname,$father, $address, $tk, $dhmos, $school);
					unset ( $brother_sch, $brother_grade, $school_to_go,$year, $reason, $fixed, $lat, $lng);
					$sid = $row['SID'];
					$am = $row['AM'];
					$name = $row['NAME'];
					if($name==null) {$name="";}
					$surname = $row['SURNAME'];
					if($surname==null) {$surname="";}
					$father = $row['FATHER'];
					if($father==null) {$father="";}
					$address = $row['ADDRESS'];
					if($address==null) {$address="";}
					$address = str_replace('"','',$address);
					$address = str_replace("'","",$address);
					$tk = $row['TK'];
					if($tk==null) {$tk="";}
					$dhmos = $row['DHMOS'];
					if($tk==null) {$tk="";}
					$school = $row['SCHOOL'];
					$brother_sch = $row['BROTHER_SCH'];
					if($brother_sch==null) {$brother_sch="";}
					$brother_grade = $row['BROTHER_GRADE'];
					if($brother_grade==null) {$brother_grade="";}
					$school_to_go = $row['SCHOOL_TO_GO'];
					if($school_to_go==null) {$school_to_go="";}
					$year = $row['YEAR'];
					$reason = $row['REASON'];
					if($reason==null) {$reason="";}
					$fixed = $row['FIXED']; 
					if($fixed==null) { 	$fixed=0; }
					$lat = sprintf("%10.6f", $row['LAT']);
					$lng = sprintf("%10.6f", $row['LNG']);
				
					$studDetails = array(
							"sid"=>$sid ,
							"am"=>$am ,
							"name"=>$name,
							"surname"=>$surname,
							"father"=>$father ,
							"address"=>$address,
							"tk"=>$tk,
							"dhmos"=>$dhmos,
							"school"=>$school,
							"brosch"=>$brother_sch,
							"brograde"=>$brother_grade,
							"sch2go"=>$school_to_go,
							"year"=>$year,
							"reason"=>$reason,
							"fixed"=>$fixed,
							"lat"=>$lat,
							"lng"=>$lng
					);
					array_push($data,$studDetails);
				}
				
				/*$file = fopen("logs.txt", "a") or die("Unable to open file!");
				$txt =  date("Y-m-d h:i:sa").": readSchoolDetailsAll:data".json_encode($data)."\n";
				fwrite($file, $txt);
				fclose($file);*/
				
				echo json_encode($data);
			}
	}
}

function readStudentsAddresses($con, $dimos){
	
	$schyear = $_SESSION["schyear"];

	// build query
	$query = "SELECT STUDENTS.SID AS SID,
	STUDENTS.ADDRESS AS ADDRESS,
	STUDENTS.TK AS TK,
	(select NAME FROM DHMOI WHERE CODE = STUDENTS.DHMOS_CODE) AS DHMOS,
	FROM STUDENTS, SCHOOLS
	WHERE  YEAR = '$schyear'
	AND STUDENTS.LAT is null
	AND STUDENTS.SCHOOL = SCHOOLS.CODE
	AND SCHOOLS.COD_DHMOS='$dimos'";

	// check query result
	mysql_query ( "SET NAMES 'utf8'", $con );
	$result = mysql_query ( $query ) or die ( '' );

	if (!$result) {
		$message = 'Invalid query: ' . mysql_error () . '\n';
		$message .= 'Whole query: ' . $query;

		$file = fopen("logs.txt", "a") or die("Unable to open file!");
		$txt =  date("Y-m-d h:i:sa").": readStudentsAddresses result returned\n";
		fwrite($file, $txt);
		fclose($file);

		die ( $message );
	} else {
		//unset ( $data);
		$data = array();


		while ( ($row = mysql_fetch_assoc ( $result )) ) {


			unset ( $sid, $address, $tk, $dhmos);
			$sid = $row['SID'];
			$address = $row['ADDRESS'];
			$tk = $row['TK'];
			$dhmos = $row['DHMOS'];

			$studDetails = array("sid"=>$sid ,
					"address"=>$address,
					"tk"=>$tk,
					"dhmos"=>$dhmos
			);
			array_push($data,$studDetails);
		}

		$file = fopen("logs.txt", "a") or die("Unable to open file!");
		$txt =  date("Y-m-d h:i:sa").": readStudentsAddresses data:".json_encode($data)."\n";
		fwrite($file, $txt);
		fclose($file);

		echo json_encode($data);

	}
}


/* $$$ΟΚ$$$ **********************************************************************************************
 * readSchoolsAndStudents: Διαβάζει τα στοιχεία των σχολείων  και το πλήθος μαθητών που έχουνε
 * 							κατανεμηθεί σε κάθε σχολείο.
 * 							Καλείται από την συνάρτηση
 * 								* loadSchoolsAndStudents στο katanomi.js
 * Input:					con: η σύνδεση στη ΒΔ
 * 							dimos: ο κωδικός του δήμου
 * 							type: ο τύπος του σχολείου Γυμνάσιο ή Λύκειο
 * Output: 					Επιστέφει τα στοιχεία των σχολείων ή
 * 							λάθος αν δεν βρέθηκαν σχολεία
 *********************************************************************************************** */
function readSchoolsAndStudents($con, $dimos, $type){
	
	$schyear = $_SESSION["schyear"];


	$query = "SELECT
	SCHOOLS.CODE,
	SCHOOLS.NAME,
	SCHOOLS.ADDRESS,
	SCHOOLS.LAT,
	SCHOOLS.LNG,
	SCHOOLS.COD_DHMOS,
	SCHOOLS.COLOR,
	SCHOOLS.AREA,
	(SELECT COUNT(STUDENTS.AM) FROM STUDENTS WHERE SCHOOL_TO_GO=SCHOOLS.CODE AND STUDENTS.YEAR='$schyear') AS STUDS
	FROM SCHOOLS
	WHERE
	SCHOOLS.TYPE='$type'
	AND SCHOOLS.status is null
	ORDER BY SCHOOLS.CODE";


	mysql_query ( "SET NAMES 'utf8'", $con );
	$result = mysql_query ( $query );

	if (!$result) {
		$message = 'Invalid query: ' . mysql_error () . '\n';
		$message .= 'Whole query: ' . $query;

		$file = fopen("logs.txt", "a") or die("Unable to open file!");
		$txt =  date("Y-m-d h:i:sa").": readSchoolsAndStudents: Query failed = ".$query."\n";
		fwrite($file, $txt);
		fclose($file);

		die ( $message );
	} else {
		// Data not found
		if(mysql_num_rows($result)==0) {
			unset ( $err, $descr);
			$err = "true";
			$descr = "SERVER: Δεν βρέθηκαν σχολεία.";
			$err_mess = array("err"=>$err, "errdescr"=>$descr);
			echo json_encode($err_mess);
		}
		else {

			$data = array();

			while ( ($row = mysql_fetch_assoc ( $result )) ) {
					
				unset ( $code, $name, $address, $lat, $lan, $dimoss, $color, $area, $studs);
				$code = $row['CODE'];
				$name = $row['NAME'];
				$address = $row['ADDRESS'];
				$lat = $row['LAT'];
				if($lat==null) {$lat="";}
				$lng = $row['LNG'];
				if($lng==null) {$lng="";}
				$dimoss = $row['COD_DHMOS'];
				$color = $row['COLOR'];
				$area = $row['AREA'];
				$studs = $row['STUDS'];
					
				$school = array("code"=>$code, "name"=>$name, "address"=>$address, "lat"=>$lat, "lng"=>$lng, "dimos"=>$dimoss, "color"=>$color, "area"=>$area, "studs"=>$studs);
				array_push($data,$school);
			}
			echo json_encode($data);
		}
	}
}

/* $$$OK$$ **********************************************************************************************
 * readStudentsDetails: 	Διαβάζει τα στοιχεία των μαθητών των σχολείων ενός συγκεκριμένου
 * 							δήμου.
 * 							Καλείται από την συνάρτηση
 * 								* loadSchoolsAndStudents στο katanomi.js
 * Input:					con: η σύνδεση στη ΒΔ
 * 							dimos: ο κωδικός του δήμου
 * 							type: ο τύπος του σχολείου Γυμνάσιο ή Λύκειο
 * Output: 					Επιστέφει τα στοιχεία των μαθητών ή
 * 							λάθος αν δεν βρέθηκαν σχολεία
 *********************************************************************************************** */
function readStudentsDetails($con, $dimos, $type){

	$schyear = $_SESSION["schyear"];
	
	// build query
	$query = "SELECT
	SID,
	AM,
	(select NAME FROM SCHOOLS WHERE CODE = STUDENTS.SCHOOL) AS SCHOOL,
	ADDRESS,
	TK,
	(select NAME FROM DHMOI WHERE CODE = STUDENTS.DHMOS_CODE) AS DHMOS,
	LAT,
	LNG,
	(select NAME FROM SCHOOLS WHERE CODE = STUDENTS.SCHOOL_TO_GO) AS SCHOOL_TO_GO
	FROM STUDENTS
	WHERE  YEAR = '$schyear'
	and SCHOOL in (select code from schools where cod_dhmos='$dimos' and TYPE='$type')";

	// check query result
	mysql_query ( "SET NAMES 'utf8'", $con );
	$result = mysql_query ( $query );

	if (!$result) {
		$message = 'Invalid query: ' . mysql_error () . '\n';
		$message .= 'Whole query: ' . $query;

		$file = fopen("logs.txt", "a") or die("Unable to open file!");
		$txt =  date("Y-m-d h:i:sa").": readStudentsDetails: Query failed = ".$query."\n";
		fwrite($file, $txt);
		fclose($file);

		die ( $message );
	} else {

		// Data not found
		if(mysql_num_rows($result)==0) {
			unset ( $err, $descr);
			$err = "true";
			$descr = "SERVER: Δεν βρέθηκαν μαθητές.";
			$err_mess = array("err"=>$err, "errdescr"=>$descr);
			echo json_encode($err_mess);
		}
		else {
				
			$data = array();

			while ( ($row = mysql_fetch_assoc ( $result )) ) {
					
				unset ( $sid, $am, $school, $address, $tk, $dhmos, $lat, $lng, $school_to_go);
				$sid = $row['SID'];
				$am = $row['AM'];
				$school = $row['SCHOOL'];
				$address = $row['ADDRESS'];
				$tk = $row['TK'];
				if($tk==null){$tk='';}
				$dhmos = $row['DHMOS'];
				if($dhmos==null){$dhmos='';}
				$lat = $row['LAT'];
				if($lat==null){$lat=0;}
				$lng = $row['LNG'];
				if($lng==null){$lng=0;}
				$school_to_go = $row['SCHOOL_TO_GO'];
				if($school_to_go==null){$school_to_go='';}
					
				$studDetails = array("sid"=>$sid , "am"=>$am , "school"=>$school,  "address"=>$address, "tk"=>$tk, "dhmos"=>$dhmos, "lat"=>$lat, "lng"=>$lng, "school_to_go"=>$school_to_go );
				array_push($data,$studDetails);
			}

			echo json_encode($data);
		}
	}
}
/* $$$OK$$$ **********************************************************************************************
 * readNonDistributedStuds: Διαβάζει τα στοιχεία των μαθητών που δεν έχουν κατανεμηθεί
 * 							Καλείται από την συνάρτηση
 * 								* distributeByAddress στο katanomi.js
 * Input:					con: η σύνδεση στη ΒΔ
 * 							dimos: ο κωδικός του δήμου
 * 							type: ο τύπος του σχολείου ΔΗΜΟΤΙΚΟ ή ΓΥΜΝΑΣΙΟ
 * Output: 					Επιστέφει τα στοιχεία των μαθητών ή
 * 							λάθος αν δεν βρέθηκαν σχολεία
 *********************************************************************************************** */
function readNonDistributedStuds($con, $dimos, $type){

	$schyear = $_SESSION["schyear"];
	
	// build query
	$query = "SELECT
	SID,
	LAT,
	LNG
	FROM STUDENTS
	WHERE  YEAR = '$schyear'
	AND SCHOOL_TO_GO IS NULL
	and SCHOOL in (select code from schools where cod_dhmos='$dimos' and type='$type')";

	// check query result
	mysql_query ( "SET NAMES 'utf8'", $con );
	$result = mysql_query ( $query );

	
	if (!$result) {
		$message = 'Invalid query: ' . mysql_error () . '\n';
		$message .= 'Whole query: ' . $query;

		$file = fopen("logs.txt", "a") or die("Unable to open file!");
		$txt =  date("Y-m-d h:i:sa").": readNonDistributedStuds: Query failed = ".$query."\n";
		fwrite($file, $txt);
		fclose($file);

		die ( $message );
	} else {

		// Data not found
		if(mysql_num_rows($result)==0) {
			unset ( $err, $descr);
			$err = "true";
			$descr = "SERVER: Δεν βρέθηκαν μη κατανεμημένοι μαθητές.";
			$err_mess = array("err"=>$err, "errdescr"=>$descr);
			echo json_encode($err_mess);
		}
		else {
			//unset ( $data);
			$data = array();

			while ( ($row = mysql_fetch_assoc ( $result )) ) {
					
				unset ( $sid,  $lat, $lng);
				$sid = $row['SID'];
				$lat = $row['LAT'];
				$lng = $row['LNG'];
					
				$studDetails = array("sid"=>$sid , "lat"=>$lat, "lng"=>$lng );
				array_push($data,$studDetails);
			}

			echo json_encode($data);
		}
	}
}
?>
