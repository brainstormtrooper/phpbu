<?php
// config stuff

$baseSrcDir = '/mnt/168/sda/6/7/brainstormtrooper/EN';
$destDir = '/mnt/168/sda/6/7/brainstormtrooper/backup';
$destZipName = $destDir . '/' . date("Y-m-d") . "-backup.zip";
$destSQLName = $destDir . '/' . date("Y-m-d") . "-dbbackup.sql";

$srcDBName = 'brainstormtrooper';

$DBUser = 'brainstormtrooper';
$DBPass = 'b5tr00p3';
$DBHost ='sql.free.fr';


/*
get this ready for $_GET
*/

    if (extension_loaded('zip') ){
      //  die ("no zip extension");


$zip = new ZipArchive();

// open archive)
//if($zip->open($destZipName, ZIPARCHIVE::CREATE) !== TRUE ) 
if($zip->open($destZipName) !== TRUE ) 
{
	die ("Could not open archive");
}
// initialize an iterator
// pass it the directory to be processed
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseSrcDir));
// iterate over the directory
// add each file found to the archive
foreach ($iterator as $key=>$value) {
$zip->addFile(realpath($key), $key) or die ("ERROR: Could not add file: $key");
}
// close and save archive
$zip->close();
echo "Archive created successfully.";


} else {
	$destination = $destDir . '/' . date("Y-m-d");
	copy_directory( $baseSrcDir, $destination );
	}
// get DB

// system("mysqldump -h lsql.free.fr -u YnJhaW5zdG9ybXRyb29wZXI= -pYjV0cjAwcDM= brainstormtrooper > /mnt/168/sda/6/7/brainstormtrooper/pass/BACKUPFILENAME.sql");

backup_tables($DBHost,$DBUser,$DBPass,$srcDBName, $destSQLName, $tables = '*');

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
  $dh = opendir($destDir);
    while (false !== ($file = readdir($dh))) {
    ;
    if ($file == '.' || $file == '..') {
    continue; // skip this file
    }
    $file_parts=explode('.',$file);
    $file_parts_counts=count($file_parts);
    $file_type_location=$file_parts_counts-1;
    $file_type=$file_parts[$file_type_location];
    $count=$count+1;
    }
if(count($files) >= 4){
//delete oldest

	
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