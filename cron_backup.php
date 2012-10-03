<?php
// config stuff

$cfg_srcRoot = '/path/to/site/root/';
$cfg_srcDir = 'folder_to_backup';
$cfg_srcDBName = 'db_to_backup';
$cfg_destDir = '/path/to/backup/files';
$cfg_destZipName = $cfg_destDir . '/' . date("Y-m-d") . '/' . $cfg_srcDir . "-backup.zip";
$cfg_destSQLName = $cfg_destDir . '/' . date("Y-m-d") . '/' . $cfg_srcDBName . "-dbbackup.sql";



$cfg_DBUser = 'dbuser';
$cfg_DBPass = 'dbpass';
$cfg_DBHost ='dbhost';


$cfg_keepCount = 5;

// $cfg_defAction = '*'; // not needed
// get the root directory with this...

/*
get this ready for $_GET
*/
if(isset($_GET['bu_srcDir'])) {
	$cfg_srcDir = $_GET['bu_srcDir'];
}


$sn = $cfg_srcDir;
$sd = $cfg_srcRoot . $sn;
$dd = (isset($_GET['bu_destDir']))?$_GET['bu_destDir']:$cfg_destDir;
$dz = (isset($_GET['bu_destZip']))?$dd . '/' . $_GET['bu_destZip']:$cfg_destZipName;
$dq = (isset($_GET['bu_destSql']))?$dd . '/' . $_GET['bu_destSql']:$cfg_destSQLName;
$sb = (isset($_GET['bu_srcDB']))?$_GET['bu_srcDB']:$cfg_srcDBName;


if($_GET['bu_action']) {





	switch($_GET['bu_action']) {
		case 'files':
		backupFileSystem($sd, $dd, $dz, $sn);
		trimdir($dd, $cfg_keepCount);
		break;
		
		case 'db':
		backup_tables($cfg_DBHost,$cfg_DBUser,$cfg_DBPass,$cfg_srcDBName, $dq, $tables = '*');
		break;
		
		default:
		backupFileSystem($sd, $dd, $dz, $sn);
		backup_tables($cfg_DBHost,$cfg_DBUser,$cfg_DBPass,$cfg_srcDBName, $dq, $tables = '*');
		trimdir($dd, $cfg_keepCount);
		
	}
} else {

	backupFileSystem($sd, $dd, $dz, $sn);
	backup_tables($cfg_DBHost,$cfg_DBUser,$cfg_DBPass,$cfg_srcDBName, $dq, $tables = '*');
	trimdir($dd, $cfg_keepCount);

}







function backupFileSystem($sd, $dd, $dz, $sn){

    if (extension_loaded('zip') ){
      //  die ("no zip extension");
		$zip = new ZipArchive();

		//if($zip->open($destZipName, ZIPARCHIVE::CREATE) !== TRUE ) 
		if($zip->open($dz) !== TRUE ) 
		{
			die ("Could not open archive");
		}
		// initialize an iterator
		// pass it the directory to be processed
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sd));
		// iterate over the directory
		// add each file found to the archive
		foreach ($iterator as $key=>$value) {
			$zip->addFile(realpath($key), $key) or die ("ERROR: Could not add file: $key");
		}
		// close and save archive
		$zip->close();
		//echo "Archive created successfully.";


	} else {
		$destination = $dd . '/' . date("Y-m-d") . '/' . $sn . '-bak';
		copy_directory( $sd, $destination );
	}
	
}
// get DB





function backup_tables($host,$user,$pass,$name, $outputfile, $tables = '*')
{
	
	$link = mysql_connect($host,$user,$pass);
	if(!$link){
die("no DB link");
}
	mysql_select_db($name,$link);
	
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
		
		$return.= 'DROP TABLE '.$table.';';
		$row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
		$return.= "\n\n".$row2[1].";\n\n";
		
		for ($i = 0; $i < $num_fields; $i++) 
		{
			while($row = mysql_fetch_row($result))
			{
				$return.= 'INSERT INTO '.$table.' VALUES(';
				for($j=0; $j<$num_fields; $j++) 
				{
					$row[$j] = addslashes($row[$j]);
					$row[$j] = ereg_replace("\n","\\n",$row[$j]);
					if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
					if ($j<($num_fields-1)) { $return.= ','; }
				}
				$return.= ");\n";
			}
		}
		$return.="\n\n\n";
	}
	
	//save file
	$handle = fopen($outputfile, 'w+');
	

	fwrite($handle,$return);

	fclose($handle);
}





// check folders

function trimdir($destDir, $keepCount){


// check folders
  $dh = opendir($destDir);
    while (false !== ($file = readdir($dh))) {
    
    if ($file == '.' || $file == '..') {
    continue; // skip this file
    }
    $file_parts=explode('.',$file);
    $file_parts_counts=count($file_parts);
    $file_type_location=$file_parts_counts-1;
    $file_type=$file_parts[$file_type_location];
    $count=$count+1;
    if(is_dir($destDir . '/' . $file)){
    	   $flist[filectime($destDir . '/' . $file)] = $destDir . '/' . $file; 
    		
    	}

    }

    if($count > $keepCount) {
    	
    ASORT($flist, SORT_NUMERIC); 
    RESET($flist); 
    unlink($flist['path']); 
    //print $oldest; 
    
 }
   closedir($dh); 



}
// copy files
function copy_directory( $source, $destination ) {
	if ( is_dir( $source ) ) {
		@mkdir( $destination );
		$directory = dir( $source );
		while ( FALSE !== ( $readdirectory = $directory->read() ) ) {
			if ( $readdirectory == '.' || $readdirectory == '..' ) {
				continue;
			}
			$PathDir = $source . '/' . $readdirectory; 
			if ( is_dir( $PathDir ) ) {
				copy_directory( $PathDir, $destination . '/' . $readdirectory );
				continue;
			}
			copy( $PathDir, $destination . '/' . $readdirectory );
		}
 
		$directory->close();
	}else {
		copy( $source, $destination );
	}
}

// make zips




?>
