<?php 

require_once('nuconfig.php');

mb_internal_encoding('UTF-8');

$_POST['RunQuery']			= 0;

if ( strpos($_SERVER['PHP_SELF'], 'wp-content/plugins' ) !== false) {

	require_once('../../../wp-config.php');
	$DBHost         = DB_HOST;
        $DBName         = DB_NAME;
        $DBUser         = DB_USER;
        $DBPassword     = DB_PASSWORD;
	$DBCharset	= DB_CHARSET;

} else {

	$DBHost		= $nuConfigDBHost;
	$DBName		= $nuConfigDBName;
	$DBUser		= $nuConfigDBUser;
	$DBPassword 	= $nuConfigDBPassword;
	$DBCharset      = 'utf8';

}

/*
echo $DBHost.'<br>';
echo $DBName.'<br>';
echo $DBUser.'<br>';
echo $DBPassword.'<br>';
echo $DBCharset.'<br>';
echo die();
*/

$nuDB = new PDO("mysql:host=$DBHost;dbname=$DBName;charset=$DBCharset", $DBUser, $DBPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
$nuDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$nuDB->exec("SET CHARACTER SET utf8");

$GLOBALS['nuSetup']			= db_setup();


function db_setup(){
    
	static $setup;
	
    if (empty($setup)) {                                          			//check if setup has already been called
	
		$s					= "
								SELECT 
									zzzzsys_setup.*, 
									zzzzsys_timezone.stz_timezone AS set_timezone 
								FROM zzzzsys_setup 
								LEFT JOIN zzzzsys_timezone ON zzzzsys_timezone_id = set_zzzzsys_timezone_id
							";
		
		
		$rs					= nuRunQuery($s);						        //get setup info from db
		$setup				= db_fetch_object($rs);
	}
	
	$gcLifetime				= 60 * $setup->set_time_out_minutes;             //setup garbage collect timeouts
	
	ini_set("session.gc_maxlifetime", $gcLifetime);
		
    return $setup;
	
}



function nuRunQuery($s, $a = array(), $isInsert = false){

	global $DBHost;
	global $DBName;
	global $DBUser;
	global $DBPassword;
	global $nuDB;
	if($s == ''){
		$a           = array();
		$a[0]        = $DBHost;
		$a[1]        = $DBName;
		$a[2]        = $DBUser;
		$a[3]        = $DBPassword;
		return $a;
	}

	$object = $nuDB->prepare($s);

	try {
		$object->execute($a);
	}catch(PDOException $ex){
	
		$user        = 'globeadmin';
		$message     = $ex->getMessage();
		$array       = debug_backtrace();
                $trace       = '';
                
                for($i = 0 ; $i < count($array) ; $i ++){
                    $trace  .= $array[$i]['file'] . ' - line ' . $array[$i]['line'] . ' (' . $array[$i]['function'] . ")\n\n";
                }

		$debug       = "
===USER==========

$user

===PDO MESSAGE=== 

$message

===SQL=========== 

$s

===BACK TRACE====

$trace

";

		$_POST['RunQuery']		= 1;
		nuDebug($debug);
		$_POST['RunQuery']		= 0;
	
		$id						= $nuDB->lastInsertId();
		$GLOBALS['ERRORS'][]	= $debug;

		return -1;
		
	}

        if($isInsert){
            
            return $nuDB->lastInsertId();
            
        }else{
            
            return $object;
        
        }
	
}


function db_is_auto_id($table, $pk){

	$s		= "SHOW COLUMNS FROM `$table` WHERE `Field` = '$pk'";
	$t      = nuRunQuery($s);   									//-- mysql's way of checking if its an auto-incrementing id primary key
	$r      = db_fetch_object($t);
	
	return $r->Extra == 'auto_increment';

}

function db_fetch_array($o){

	if (is_object($o)) {
		return $o->fetch(PDO::FETCH_ASSOC);
	} else {
		return array();
	}

}

function db_fetch_object($o){

	if (is_object($o)) {
		return $o->fetch(PDO::FETCH_OBJ);
	} else {
		return false;
	}

}
	
function db_fetch_row($o){

	if (is_object($o)) {
		return $o->fetch(PDO::FETCH_NUM);
	} else {
		return false;
	}

}


function db_field_names($n){
    
    $a       = array();
    $s       = "DESCRIBE $n";
    $t       = nuRunQuery($s);

    while($r = db_fetch_row($t)){
        $a[] = $r[0];
    }
    
    return $a;
    
}


function db_field_types($n){
    
    $a       = array();
    $s       = "dESCRIBE $n";
    $t       = nuRunQuery($s);

    while($r = db_fetch_row($t)){
        $a[] = $r[1];
    }
    
    return $a;
    
}


function db_primary_key($n){
    
    $a       = array();
    $s       = "DESCRIBE $n";
    $t       = nuRunQuery($s);

    while($r = db_fetch_row($t)){
		
		if($r[3] == 'PRI'){
			$a[] = $r[0];
		}
		
    }
    
    return $a;
    
}

function db_num_rows($o) {

	if(!is_object($o)){return 0;}
		
	return $o->rowCount();
	
}


function nuUpdateTables(){
	
	$a	= [];
	$t 	= nuRunQuery("SHOW TABLES");
	
	while($r = db_fetch_row($t)){
		$a[] = $r[0];
	}
		
	nuRunQuery('DELETE FROM zzzzsys_table');
	
	for($i = 0 ; $i < count($a) ; $i ++){
		
		$s	= "INSERT INTO zzzzsys_table (zzzzsys_table_id) VALUES (?)";
		nuRunQuery($s, [$a[$i]]);
		
	}
		
}



?>
