<?php
//echo 'hello';
//exec("./prerunfile.sh 2>&1", $cmd);
move_uploaded_file($_FILES['source']['tmp_name'], 'blastdir/database.fa');
move_uploaded_file($_FILES['query']['tmp_name'], 'blastdir/query.fa');
echo '<br><br>';
exec("./runfile.sh 2>&1", $cmd);
exec("./blastrunfile.sh 2>&1", $result);
//print_r(get_defined_vars());
$blast_result = array();
$blast_key = array();
$blast_pct = array();

$counter = 0;
foreach($result as $i)
	{
		if((substr($i, 0, 1) != '#') && (substr($i, 0, 12) != 'CFastaReader'))
		{
		$exp = preg_split('/\s+/', $i);
		
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

	echo json_encode($blast_result);	
	
	
	
//Begin table formatting	
/*
	echo '<table><tr><td>QueryID</td><td>SubjectID</td><td>PCIdent</td><td>Length</td><td>Mismatch</td></tr>';
	
	foreach(array_keys($pcident_key) as $i)
	{
		
		
		echo '<tr>	
					<td>' . $blast_result[$i]['QueryID'] . '</td>
					<td>' . $blast_result[$i]['SubjectID'] . '</td>
					<td>' . $blast_result[$i]['PCIdent'] . '</td>
					<td>' . $blast_result[$i]['Length'] . '</td>
					<td>' . $blast_result[$i]['Mismatch'] . '</td>
			</tr>';
	}
echo '</table><br><br>';

*/



//phpinfo();
//makeblastdb -in database.fa -dbtype nucl
//blastn -query query.fa -db database.fa -num_threads 16 -culling_limit 2 -outfmt 10

?>