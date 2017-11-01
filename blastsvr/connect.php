<?php
//echo "hello";
exec("./prerunfile.sh 2>&1", $cmd);
		//print_r($_POST);
		//print_r($_FILES);
move_uploaded_file($_FILES["source"]["tmp_name"], "./blastdir/runfile-" . $_FILES["query"]["name"]);
move_uploaded_file($_FILES["query"]["tmp_name"], "./blastdir/" . $_FILES["query"]["name"]);
//print_r($_FILES);
if($_FILES["source"]["size"] == 0 || $_FILES["query"]["size"] == 0)
{
echo '<span class="warning label">A transferred file has failed to contain any data.<br>This error is normally caused when all files have already been blasted.<br>If this problem persists please contact a Whiteflybase Administrator.</span>';
}
else
{	
	echo "<br><br>";
	//Define Filename
	$filename = explode(".", $_FILES["query"]["name"]);
	//Blast Runtime database creation
	$rffile = "#!/bin/bash
	set -x
	makeblastdb -in blastdir/runfile-" . $_FILES["query"]["name"] . " -dbtype nucl
	echo Blastx has run";
	$rffile = str_replace("\r\n", "\n", $rffile);
	file_put_contents("./blastdir/execs/runfile-" . $filename[0] . ".sh", $rffile);	

	chmod("blastdir/execs/runfile-" . $filename[0] . ".sh", 0755);
	exec("./blastdir/execs/runfile-" . $filename[0] . ".sh 2>&1", $cmd);

	//Blast Query
	$tofile = "#!/bin/bash
	#set -x
	blastn -query blastdir/" . $_FILES["query"]["name"] . " -db blastdir/runfile-" . $_FILES["query"]["name"] . " -num_threads 12 -culling_limit 1 -outfmt 7";

	$tofile = str_replace("\r\n", "\n", $tofile);


	file_put_contents("./blastdir/execs/" . $filename[0] . ".sh", $tofile);	

	chmod("./blastdir/execs/" . $filename[0] . ".sh", 0755);


	exec("./blastdir/execs/" . $filename[0] . ".sh" . " 2>&1", $result);
	//print_r($result);

	$blast_result = array();
	$blast_key = array();
	$blast_pct = array();

	$counter = 0;
	foreach($result as $i)
		{
			if((substr($i, 0, 1) != "#") && (substr($i, 0, 12) != "CFastaReader"))
			{
			$exp = preg_split("/\s+/", $i);
			
			//print_r($exp);
			
			$new_i = array("RowID" => $counter, "QueryID" => $exp[0], "SubjectID" => $exp[1], "PCIdent" => $exp[2], "Length" => $exp[3], "Mismatch" => $exp[4], "QueryStart" => $exp[6], "QueryEnd" => $exp[7], "SeqStart" => $exp[8], "SeqEnd" => $exp[9]); 
			
			array_push($blast_result, $new_i);
			array_push($blast_key, $counter);
			array_push($blast_pct, $exp[2]);
			$counter = $counter + 1;
			}
		}

		$pcident_key = array_combine($blast_key, $blast_pct);
		
		arsort($pcident_key);

	//Begin table formatting	

	echo json_encode($blast_result);

	array_map("unlink", glob("blastdir/runfile-" . $filename[0] . "*"));
	unlink("blastdir/" . $_FILES["query"]["name"]);
	unlink("blastdir/execs/runfile-" . $filename[0] . ".sh");
	unlink("blastdir/execs/" . $filename[0] . ".sh");
}
?>