<?php
session_start();
include("sqlconnect.php");
error_reporting(-1);
ini_set('display_errors', 1); 
require 'mailgun/vendor/autoload.php';
include 'email/action.php';
use Mailgun\Mailgun;
$mysqli = new mysqli($SV, $UN, $PW, $DB);
	if($mysqli->connect_errno)
	{
		echo "Failed to connect to SQL Database: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	}
	
//echo "Connected to Database " . $mysqli->server_info . " on ";
//echo gethostname();

if(isset($_POST['command']))
	{
	//Commands are routed via $_POST. Any application with the right format can submit a request	
	call_user_func($_POST['command']);	
	
	}

function DBStats()
	{
	global $mysqli;
	$stmt = $mysqli->prepare("SELECT (SELECT COUNT(*) FROM MT_SEQUENCE WHERE BAYES = 1) AS WBAYES, (SELECT COUNT(*) FROM MT_SEQUENCE WHERE BAYES != 1 AND BLAST = 1 AND SEQ_MATCH_PC >= 1) AS BLASTONLY, (SELECT COUNT(*) FROM MT_SEQUENCE WHERE BAYES != 1 AND BLAST = 1 AND SEQ_MATCH_PC > 0.9 AND SEQ_MATCH_PC < 1) AS VERIFY, ((SELECT COUNT(*) FROM MT_SEQUENCE WHERE BAYES != 1 AND BLAST = 1 AND SEQ_MATCH_PC < 0.9)) AS UNCLASS");
	$stmt->execute();
	$stmt->bind_result($wbayes, $blastonly, $verify, $unclass);
	$stmt->fetch();
	//Changed this because stats are being dumb after I changed the DB
	$stats = array('bayes' => $wbayes, 'blast' => $blastonly, 'verify' => $verify, 'unclass' => $unclass);
	
	echo json_encode($stats);
		
	}
	
function LoadLists()
	{
	echo '<h1>List Management</h1><br><div class="row"><div id="listdiv" class="large-6 columns"><label><select id="listbox">';
	echo '<option value="0">Select a list</option>';
	global $mysqli;
	$stmt = $mysqli->prepare("SELECT UL.ID, UL.LIST_NAME FROM USERS_LIST UL WHERE UL.USERS_ID = ? ORDER BY UL.ID");
	$stmt->bind_param("i", $_SESSION['UserAccID']);
	$stmt->execute();
	$stmt->bind_result($listid, $listname);
	$headername = '';
	while($stmt->fetch())
	{
	echo '<option value="' . $listid . '">' . $listname . '</option>';		
	}
	echo '</select></label></div><div id="listnewbuttondiv" class="large-2 columns"><button type="button" class="button" onclick="newlist();">New List</button></div><div id="listdelbuttondiv" class="large-2 columns end"></div></div>';
	//Separate echo to create the table ready for data loading
	echo '<div class="row"><div class="large-12 columns" id="tablediv"></div><div>';
	}
	
function NewList()
	{

	$listname = $_POST['listname'];	
	$listdesc = $_POST['listdesc'];	
	$userid = $_SESSION['UserAccID'];
		
	global $mysqli;
	$sql = $mysqli;				
	$stmt = $sql->prepare("INSERT INTO USERS_LIST (LIST_NAME, DESCRIPTION, USERS_ID) VALUES (?, ?, ?)");
	$stmt->bind_param("ssi", $listname, $listdesc, $userid);
	$stmt->execute();
	$stmt->store_result();
	//$result = print_r($stmt);
	$stmt->free_result();
				echo 'List created: ' . $listname . '<br>List Description: ' . $listdesc . '<br>Redirecting to list Management';
	$stmt->close();		
	
	}
	
function DelList()
	{

	$listid = $_POST['listid'];	
	global $mysqli;
	$sql = $mysqli;				
	$stmt = $sql->prepare("DELETE FROM USERS_LIST_LIB WHERE USERS_LIST_ID = ?");
	$stmt->bind_param("i", $listid);
	$stmt->execute();
	$stmt->store_result();
	$stmt->free_result();
	echo 'Operation 1 of 2 All list sequences have been deleted<br>';
	$stmt->close();	

	global $mysqli;
	$sql = $mysqli;				
	$stmt = $sql->prepare("DELETE FROM USERS_LIST WHERE ID = ?");
	$stmt->bind_param("i", $listid);
	$stmt->execute();
	$stmt->store_result();
	//$result = print_r($stmt);
	$stmt->free_result();
	echo 'Operation 2 of 2 Parent list been deleted<br>';
	$stmt->close();
	unset($_SESSION['ActiveList'], $_SESSION['ActiveListName']);
	}	
	
function LoadListsContent()
	{
	$_SESSION['ActiveList'] = $_POST['listid'];
	$_SESSION['ActiveListName'] = $_POST['listtitle'];	
	$output_array = array();
	$tableheaders = array('ID', 'Submission Name', 'Genbank ID', 'Match Submission Name', 'Match Species', 'Match Genbank ID', 'Blast Percentage', 'P(AB)');
	$listid = $_POST['listid'];
	global $mysqli;
	$stmt = $mysqli->prepare("SELECT MT.ID, MT.SUB_NAME, MT.GENBANK_ID, MT2.SUB_NAME AS MATCH_NAME, SP.SPECIES, MT2.GENBANK_ID, CAST(((MT.SEQ_MATCH_PC)*100) AS DECIMAL(10,2)) AS SEQ_MATCH_PC, CAST(MT.ROSENBERG AS DECIMAL(14,6)) AS ROSENBERG FROM USERS_LIST_LIB ULL JOIN MT_SEQUENCE MT ON ULL.MT_SEQUENCE_ID = MT.ID LEFT JOIN MT_SEQUENCE MT2 ON MT.SEQ_MATCH = MT2.ID LEFT JOIN SPECIES SP ON SP.ID = MT2.SPECIES_ID WHERE ULL.USERS_LIST_ID = ?");
	$stmt->bind_param("i", $listid);
	$stmt->execute();
	$stmt->bind_result($mtid, $subname, $genbank, $matchname, $matchspecies, $matchgenbank, $blastpc, $rosenberg);
	while($stmt->fetch())
	{
	$temp_array = array("ID" => $mtid, "Submission Name" => $subname, "Genbank ID" => $genbank, "Match Submission Name" => $matchname, "Match Species" => $matchspecies, "Match Genbank ID" => $matchgenbank, "Blast Percentage" => $blastpc . "%", "P(AB)" => $rosenberg);
	array_push($output_array, $temp_array);
	unset($temp_array);
	}
	$finalarray = array("header" => $tableheaders, "data" => $output_array);
	echo json_encode($finalarray);
	}	

function load_align()
	{
		
	echo ">ReferenceSequence_AY521259\n";
	echo "ATGAAAGTTTGAACTTTTTCAACAAATCATAAAGATATTGGTGTTTTATATTTTATTTTTGGCGTTTGGAGAGGATTAATTGGAACTTCTTTTAGTATAGTTATCCGTTCTGAACTTATGACTGGTGGATCTTTTTTATTAAATGATCATTTATATAATGTTGTTGTTACTTCTCATGCTTTCATTATAATTTTTTTTATAACTATACCTTTAGTTATTGGTGGCTTTGGTAATTGACTAGTTCCTTTAATAATTGGTGCTCCTGATATAGCTTTTCCTCGTATAAACAACTTAAGTTTCTGACTACTTGTTCCTTCATTAATTTTTATGTTAGTTAGAATGCTTGTTAGGGTGGGGGCTGGTACTGGTTGGACTGTGTACCCTCCTTTGTCTTTAAGATTAACACACGGGGGATTATCAGTTGATTTATTGATTTTTTCTTTACATATTGCAGGTATTTCATCTATTTTAGGTTCAGTAAACTTTATTGTTACTATCTTTAACATACGAGTTCTTGGTATAAATTTTGAATATGTGAGATTATTTGTTTGGTCAGTATTAATTACGGTGTTTCTATTATTAATTTCACTTCCCGTTCTTGCGGGAGCTATCACGATATTACTGATAGATCGAAATTTTAATAGATCTTTTTATGATCCTCTGGGGGGAGGGGACCCTATCTTATATCAGCACTTGTTCTGATTTTTTGGTCATCCAGAAGTTTATGTACTTATTTTACCAGGTTTTGGTATTATCTCTCATTTAATTAGAAGTGAGGCTGGAAAATTAGAAGTATTTGGTAGCCTGGGTATAATTTATGCCATGATAACTATTGGTATCTTGGGATTTATTGTTTGGGGACATCACATGTTTACTGTTGGAATAGATGTTGACACTCGGGCTTATTTTACTTCAGCTACTATAGTTATTGCTGTTCCGACGGGAATTAAAATCTTTAGGTGGCTTGCTACTCTAGGTGGAATAAAGTCTAATAAGTTTAGACCCCTAGTTCTCTGATTTACAGGATTTCTATTTTTATTTACTATGGGTGGATTAACTGGAATTATTCTTGGTAATTCTTCGGTAGATGTGTGTCTTCATGATACTTATTTTGTTGTTGCTCATTTTCATTATGTTTTATCTATAGGAATTATCTTTGCTATTATAGGCGGAGTAATTTATTGATTTCCTTTAATTTTAGGTCTAACTTTAAACAATTATAACCTGGTGTCTCAATTTTATATAATATTTGTGGGAGTAAACCTGACATTTTTTCCACAGCATTTTCTTGGTTTAAGTGGAATACCTCGTCGGTATTCTGACTACCCTGATTGTTATTTACTATGAAATAAAATTTCCTCTGGGGGGAGGGTCTTAAGTGTTATTTCTGTTATTTATTTTTTATTTATTATTTTAGAGTCTTTTCTTCTCTTACGGCCGGTTAGGTTTAAACTTGGTGTAAGCAGGCATTTAGAGTGGAAAATCAATAAGCCAGTTCTTAATCATAGTTTTAAAGAGGTGTGTTTAATTTTTTTTTTCTAATGTGGCAGAAAGTGCAGTAAATTTAAGATTTATAAAAAAGGCTTATAATTCTTCTTTAGAAATAAGAATGTGGGGTGGAGTTAGATTTCAAAATAGTGCTAGTTTTACAATAGAACAGATAACATTTTTTTACGATTTTTCGTTTTTAACTATTTTATTAATTTTAGTGTTTGTAGTATACATAATGAGCTATTTAGCTTGTGAATCTTTAATTAAATGCTATGATTTAGATAACCAAATTATGGAGTCTTTTTGGACAATTTTACCTTTGGTTATTCTTGTTTTCCTAGCGTTTCCCTCTATCCGAATTTTATATCTAATAGATGAAGTAAAAAATCCCATATTAACATGTAAAGTTTTAGGCCATCAATGGTTTTGAAGTTATGAATATAGAGATTTTAACAGTTTTGAATTTGATTCTTACATAGTTTTTAACTTAATACGGCTATTAGAGGTTGATAACTGCTTTGTTATTCCTCTTGGATTAAAAGTGCGGCTGTTAGTTTCGTCTGTAGATGTTCTTCATTCTTGGACGGTTCCTGCATTTGGGGTAAAGGTTGATTCTGTTCCTGGTCGGCTTAATCAGTTAAATTTTACAGCTAATCGGCTAGGTATTTATTTTGGTCAATGCTCAGAAATTTGTGGAGTTAATCACAGATTTATACCCATCGTGGTGGAAGTTATCTTAAATCAAAGATTTTCTGTTTGGTTGGAAAATTCAAGATCATTAAAAAGCTTAAAGTAAGTGTTGATCTTTTAAATCAATTATGGTAATGTCGTCTATCTTTAATGGAAATTTAGTTAAGTTTTATTCCACAGATGGGGCCGTTATATTGATTTTATTTAATACTTATGTTTTGGTTAAGATGATTTTCTATTTCAGTTAGACTTTTTTACTGATTTAATCCAAGGGTTTTAATAATACACAATTCTTCTTCTTTTTTTTTTTTTTTTTTTTACTATGGTATCAATGATTTATTTACTACTAACATAAATACTACAGTTATTTTGGTTAACTCAAAATTTTTTTATTTCTATGATTTTTAGCCTTTTCGAGATTTATGATCCACACACTTTTTTTTTTAATCTAAGTTTTAACTGATTTCCTATTCTCTTGATTTTATTGTTTAGTATGTCTGATTTTTGGTGTTTAATTCCTTCTGTTGTTTATTTATACACTTCTTTCTTAGGATTTTTAACTAAGGAGTTTGTTAATTTTTATCTTTATAACAAAATAATTATTAGGGTATTTTTATTTATTTCCTTATTTTTGTTTTTATTATTTACTAATATCTTAGGATTAATTCCTTACGTTTTTTCTTGTTCATCACATTTTGTGTTTTCTATTAGATTTGGATTTCCTTTCTGAATCTCTTTTATCTTCTTGGGTTGGTTTAATTTTAATAGAAAGTCTTTTTCTCACTTAGTCCCCCTGGGTACTCCATTAATCTTAATTTCTTTTATGGTTGTAATTGAGACTATTAGAGCTCTGATTCGGCCTTGGTCTCTAAGAATTCGACTAATATCTAACATGATTTCTGGCCATTTATTAATAATTCTTTTAGGAAATTGTGGATTTTATATATTTTTAATTCAGATAGTTCTATTTCTTTTTGAATTTTTTGTCTGTTTTATCCAAGCCTTTGTATTTTCTGTCCTACTAACCCTTTATTCTAGTGAAATTTAATTTATGTCTTAAGCCACTAAATTTTATTTTATTTAGTCCAAAACCTTAGTAAAATTATCTACTATAATATAGTTAGAAGCTATATTTCAGAAGGTTTAATGGTTAAAATTATTAAATTTTTTTTATTTCTATACTGAAAAGATAGTGTTTTTATTAAACTAAATAATTGTTTTAAAATTTTTATAATTATCTTAATAGCTTCAGTATTACGCTCTACAATTTAAGCTATTAAAACAAATCAAAATGATTATTAAAATAAATATGAGTAGAGTTGTAGCCTTTAAGTTTATTTTACCAATCTTAAATATTAACTTTGTTGCTAAATAAATAAATTTAATTAGGGTTTTTACTTGGTTAGTTATAGCTCCCTTTTCATTCGTTTTTAGTATAACAACTCCTTGATTTATAAAGTTTATTTTGGTTATTAAGTTTAAATTTTTTATAAATCATATATATCTTATTATTTGTTTTACGCTTGTGTTGGGTTTATTAATTTTAAATCTTTTATCAACTTTTATTATTTTTATTATGGTTAAAATTATGACTGGGGCAATTAATTTTTCTGTAATAGATAAGTACACAATGTTTATATTTAATTCTATTAACCATGATAATTTATTGCCTAAAAGTATAGATGGGATTAGAAGTAAAACTTTTCTTATTTTTTGGTTATATGTTTCTTTCTTTATCTTTATATTTAAAGATATTTTGTTCAAATTAATAATTATCATCATTTTTAACGAGTAACTTGAAGTTACTATTATTAATGTAATAAAAATTAAAGTTAGAGGTCTATTAATTGATCTAATTATCATCATCTCCATAATGATTTCTTTTGAGTAAAATCTAGATACAAAGGGGAGAGCACATAAAATTATCCTTGCTACATTGAAAGAAATATTTGTTACCATTATATATGATCTAGTTGAAGATAGTTTTCGTAGATCTTGTGTATTACTTACATAAATAAAAGTACTTCTACATAGAAAAATTAGAGCTTTAAAAGTTGCATGTACAATTATATGAAAAAAAGCCAATGAATATAGGTTTGCTGAAATAGAAATAAATATAATTCTGATTTGACTTAGAGTAGATAGGGCTACAAGCTTTTTTATATCTATTTCTATTAACGAATTTAGGCTAGCTATTATTAATGTTATTATGGCTACTGTTAAAATAATGGGATTTATCCCTGTCCTAAGTATGGATGATTTAAATCGAATTGTTAGGTATACCCCTGCTGTAACTAGTGTTGAAGAATGAACAAGGGCTGAAACTGGGGTGGGGGCTGCTATGGCTTCTGTCAATCAGGATGAAAATGGCATTTGAGCTCTTTTAGAAAATGTACTGATTGATAGAAATCTGATTCATACTATTCTATGATTTAAGTATTCTGTTGATAAAAATATTCATGAAGCTGAATCTATTAGGATTATTATTGTTAACAATAAAGTAATATCTCCAACTCGATTTATTATCATAGTATAAAAACTTCTTATCACTGATTTCTTATTTTGGTAGTATATAATTAAGACAAAAGAGGTTATTCCCAGTCCTTCTCATCTTATAACTAATGATATCATATTTGGTCTTAGAATTATTATTAACATAGAAATTATAAATGTAACTAAGATTTTTGCAAATATTTTTTTTTTTTTTTCAATATAAAACCTTGAATATATTAAAATTCTAGTTGTTACTATTATTACTGTGAGAACAAATGCTGAGGATCTTTCATCTAGTAAAAAGAATATGCTTACTTCTCTTCTTTTTATTGATATTATTTTTCACTCAATTATAATGGTTAAGCTTTTTCTAAAAATCATTATATTGACGGGAACTAATATAATGGAAGCTAAAATTATAAATAAAGTATAGTTTTTGATAATCTAAAAACCTATAATTAAAGTTATCTTTAATTTCACATATTAATGTTTTTATAAACTACTTAGATTTAGATAATTAATAAGTCTGGGTTAATTGAAATTATGATTATGGGAATAATCAATATAGAAGTAGTATTTATATTTTTTGAATTGAAAATTTTAACTGTGTTAGTTGATTTTTTTAATATCCCATGGGTAATAATATAAAATATAAAAATCCTATATAATGATCTTGAAAACATGATCGTTGTTATAGTGACAACAACTGTTGATCTTGTTCCAGATCAGTTTATAATTGATTTAAGTGAGAAAAGTTCACCTATAAAATTGATCGAGGGGGGTATTGGGGAATTTCTTATACACATAAAAAATCATATCATTATTGCTGATGGATTTATTGAAAATATCCTCTTATTAATAATGATTCTTCGAGACTTAGAATTTTTATATATAATATTTGCTATATAAAATAATCCTGCTGAACATGTCCCATGTCCAATTATAATATATATACCTCCTAATATACTTTTCGACTTGTCTAATGTAATTGTTGCAAACACTAATGTTATGTGAACCACTGATGAGTAAGCAATTATTGTTTTCAAGTCGGTTTGTATAAAGCAAATTATTCTTAAATAAAGAGCTGTTCATAGACTAAATGATAAAATAATTCATTCTAATTTGTTGTTTATTTCTGAATTGGTAGAGGTTATTCGGATCAATCCATATGATCCTAGTTTAAGAAGGATCGATGCTAAAATTATTGATCCCTGAACTGGTGATTCTACGTGAACTTTGGGTAGTCATATATGTAAAAACATTGTGGGTATTTTTATTATAAAAACCAGTATTAATCTTAGATATTCTCATTTATTCATCTCTATATATTGGATATTTCATAATCTTAAGTTGGTTCTATTATTTATCATAGTAGTGATAAAAGGTAGTGAAGTTATAACTGTTATTATAACTATGAATAAAGCAGCTTCAACTCGTTCTGGCTGGTAACCTCATGTTATAATAATCATGAGTACAACAATTATAGATATTTCAAATATTAAGTAGAATATCATTAAATTTCATCTGTAAAAGGTTATTATCAATAGAATTAACATTATAAATACTGATTTATTGTAAATTTTGGGTTGGTTAATTTTACTAAAAATTAAAACAAAACCAATTCAGAGCGATAAATTAAAAGTTCAAAATGATAGTTCATCTCCTCATATAAAATAACTGATTTTACTTACTTCCTGTTTTTGTATATTTAAATTGAATATTAGTATAACAAGTGAGTAGTTGATAATTAATTGCTTTGTTTTTGGTACCATAATCATTATCATAATAACTATAAAAAATTTTATAATTTTAACACTGTTGAAGATTTTAAAAAATCGTTTCCATGAGTTCGAATTATTCTAACCAAAATAGATAGTCCAAGTACTCTTTCTGTAATTAATAGAATTATAAAGTATAAAACATTGTGATTCTCTAAAGTTATCCTTTTAACAAAAATTATTATCAGAATGATTACTATGATAGAAATATATTCAATAATAATTAATCTATTAATAATGTGTATCTTGGAAATTAGGTAAACTATTACTATTAATATAATAGGTGTAATTATTTCTTCTATTATCATAATTTTAATAGTTTATCAAAAATATTGATTTTGTAATTCAATGAGATCTGGCTAGATTTAAAGTATCAGTAAAAATATAATTATTTCTAATTTCCAAGATTAGTATTTTATTTTTAAATTATTTACTGATTGAAAGCACTGACTTTTTTGATTTTATTTATAATGTTTGATCCATTAATTTTAATTTTGTTTTTCATGGTTTGTCTCGGTTTCTCTAGAATTTTTCTTGTATTTATTATA\n";
		
	global $mysqli;
	if($_POST['dataset'] == 'priv_align')
	{
	$stmt = $mysqli->prepare("SELECT SUB_NAME, MT_SEQUENCE, GLOBAL_POSITION, LENGTH(MT_SEQUENCE) AS LE FROM MT_SEQUENCE_PRIVATE WHERE OWNER = ? AND GLOBAL_POSITION IS NOT NULL");
	$stmt->bind_param("i", $_SESSION['UserAccID']);
	}
	if($_POST['dataset'] == 'list_align')
	{
$stmt = $mysqli->prepare("SELECT MT.ID, MT.MT_SEQUENCE, MT.GLOBAL_POSITION, LENGTH(MT.MT_SEQUENCE) AS LE FROM USERS_LIST_LIB ULL JOIN MT_SEQUENCE MT ON ULL.MT_SEQUENCE_ID = MT.ID WHERE ULL.USERS_LIST_ID = ? ORDER BY ID ASC");
	$stmt->bind_param("i", $_SESSION['ActiveList']);
	}

	if($_POST['dataset'] == 'import_review_align')
	{
	$stmt = $mysqli->prepare("SELECT ID, MT_SEQUENCE, GLOBAL_POSITION, LENGTH(MT_SEQUENCE) AS LE FROM MT_SEQUENCE WHERE `NEW` = 1 AND GLOBAL_POSITION IS NOT NULL LIMIT 100");
	}
	

	$stmt->execute();
	$stmt->bind_result($subname, $sequence, $globalpos, $length);
	
	$position = 751;
	while($stmt->fetch())
	{
	$paddingL = str_pad("", ($globalpos - 1), "-"); 
	$paddingR = str_pad("", (7000 - $globalpos - $length + 1), "-");
	$sequence = $paddingL . $sequence . $paddingR;
	echo ">" . $subname . "\n";
	echo $sequence . "\n";
	}	
	}
	
function LoadSequences()
	{
	echo '<h1>WhiteflyBase Verified Data</h1>';
	if(isset($_SESSION['ActiveListName']))
	{
	echo "Active List: " . $_SESSION['ActiveListName'];
	}
	//Build Table!
	echo "<table width='100%' id='maintable' class='display'>
			<thead>
			<tr>
				<th>Species</th>
				<th>Genbank ID</th>				
				<th>Country</th>
				<th>Location 1</th>
				<th>Location 2</th>
				<th>Host</th>
				<th>Submission Name</th>
				<th>Flags</th>
				";
		
	if(isset($_SESSION['ActiveList']))
	{
	echo "<th>Add to Acive List</th>";
	}		
		echo "
				
			</tr>
			</thead>
			<tfoot>
			<tr>
				<th>Species</th>
				<th>Genbank ID</th>				
				<th>Country</th>
				<th>Location 1</th>
				<th>Location 2</th>
				<th>Host</th>
				<th>Submission Name</th>
				<th>Flags</th>				
		";
		
	if(isset($_SESSION['ActiveList']))
	{
	echo "<th>Add to Acive List</th>";
	}		
		echo "</tr>
			</tfoot>
			<tbody>
			";
	
	global $mysqli;
	if($_SESSION['Auth'] == 1 && isset($_SESSION['ActiveList']))
	{
		//print_r($_SESSION);
		$activelistid = $_SESSION['ActiveList'];
	$stmt = $mysqli->prepare("SELECT MT.ID, IFNULL(SP.SPECIES, 'Unconfirmed') AS SPECIES, MT.LOC1, MT.LOC2, MT.LOC3, MT.GENBANK_ID, UPPER(SUBSTR(MT.MT_SEQUENCE,1,30)) AS MT_SEQUENCE, MT.SUB_NAME, MT.SUBMITTED_ON, IFNULL(MT.HOST, 'UnknownHost') HOST, IFNULL(A.USERS_LIST_ID, '0') AS INLIST,  MT.BLAST, MT.BAYES, MT.`DISCARD`, DR.REASON FROM MT_SEQUENCE MT LEFT JOIN SPECIES SP ON SP.ID = MT.SPECIES_ID LEFT JOIN (SELECT MT_SEQUENCE_ID, USERS_LIST_ID FROM USERS_LIST_LIB ULL WHERE USERS_LIST_ID = ?) A ON A.MT_SEQUENCE_ID = MT.ID LEFT JOIN MT_SEQ_DISCARD_REASON DR ON DR.ID = MT.DISCARD_REASON WHERE MT.BLAST != 0 ORDER BY MT.ID ASC;");
	$stmt->bind_param("i", $activelistid);
	$stmt->execute();
	$stmt->bind_result($id, $species, $LOC1, $LOC2, $LOC3, $genbank_id, $sequence, $subname, $submit, $host, $inlist, $blast, $bayes, $discard, $reason);
	}
	else
		{
		$stmt = $mysqli->prepare("SELECT MT.ID, SP.SPECIES, MT.LOC1, MT.LOC2, MT.LOC3, MT.GENBANK_ID, UPPER(SUBSTR(MT.MT_SEQUENCE,1,30)) AS MT_SEQUENCE, MT.SUB_NAME, MT.SUBMITTED_ON, IFNULL(MT.HOST, 'UnknownHost') HOST, MT.BLAST, MT.BAYES, MT.`DISCARD`, DR.REASON FROM MT_SEQUENCE MT JOIN SPECIES SP ON SP.ID = MT.SPECIES_ID LEFT JOIN MT_SEQ_DISCARD_REASON DR ON DR.ID = MT.DISCARD_REASON WHERE PUBLIC = 1");		
	$stmt->execute();
	$stmt->bind_result($id, $species, $LOC1, $LOC2, $LOC3, $genbank_id, $sequence, $subname, $submit, $host, $blast, $bayes, $discard, $reason);		
		}

	
	while($stmt->fetch())
	{
	
	$flags = '';
	
	if($blast == 1)
	{
	$flags = $flags . '<span class="success label">BLAST</span> ';	
	}
	if($bayes == 1)
	{
	$flags = $flags . '<span class="success label">TREE</span> ';	
	}	
	if($discard == 1)
	{
	$flags = $flags . '<span class="alert label">' . $reason . '</span>';	
	}	

	//$SpeciesDelim = "<td>" .  $species . "_" . $LOC1 . "_" . $genbank_id . "_" . $host . "</td>";
	$SpeciesDelim = "<td>" .  $species . "</td>";
		
	echo "<tr>"
			
			. $SpeciesDelim .
			
			"<td><a target='_blank' href='http://www.ncbi.nlm.nih.gov/nuccore/" . $genbank_id . "'>" .  $genbank_id . "</a></td>
			<td>" . $LOC1 . "</td>
			<td>" . $LOC2 . "</td>
			<td>" . $LOC3 . "</td>
			<td>" . $host . "</td>			
			<td>" . $subname . "</td>
			<td id='flags'>" . $flags . "</td>
			
			";
	if(isset($_SESSION['ActiveList']))
	{
	if($inlist == '0')
		{
		echo "<td><button type='button' id='" . $id . "' class='button' onclick='addtoactivelist(" . $id . ");'>Add</button></td>";
		}
		else
			{
			echo "<td><button type='button' id='" . $id . "' class='button alert' onclick='removefromlist(" . $id . ");'>Remove</button></td>";	
			}
	}			
			
	echo "</tr>";
	}
	echo "</tbody></table>";
	}

function AddToList()
	{
	$listid = $_SESSION['ActiveList'];	
	$itemid = $_POST['itemid'];	
		
	global $mysqli;
	$sql = $mysqli;				
	$stmt = $sql->prepare("INSERT INTO USERS_LIST_LIB (USERS_LIST_ID, MT_SEQUENCE_ID) VALUES (?, ?)");
	$stmt->bind_param("ii", $listid, $itemid);
	$stmt->execute();
	$stmt->store_result();
	//$result = print_r($stmt);
	$stmt->free_result();
				echo '1';
	$stmt->close();					
	}
	
function RemoveFromList()
	{
	$listid = $_SESSION['ActiveList'];	
	$itemid = $_POST['itemid'];	
		
	global $mysqli;
	$sql = $mysqli;				
	$stmt = $sql->prepare("DELETE FROM USERS_LIST_LIB WHERE USERS_LIST_ID = ? AND MT_SEQUENCE_ID = ?");
	$stmt->bind_param("ii", $listid, $itemid);
	$stmt->execute();
	$stmt->store_result();
	//$result = print_r($stmt);
	$stmt->free_result();
				echo '1';
	$stmt->close();					
	}	

	
function LoadMySequences()
	{
	/*	
	echo "<br><div class='callout primary'>
	<h5> Interpreting Results </h5>
	<p><b>Verified</b><br> Verified by Blast with Accuracy (B:%)<br>Not Verified (N) This is quite rare and indicates a sequence that did not match to any database entries.</p>
	<p><b>Diagnostics</b><br>Diagnostics indicate if there is an issue with using your sequence in future analysis. Clicking the table data will open more verbose information.</p>
	<p><b>AP</b><br>AP is Alignment Position it indicates the beginning and end of the sequence in relation to the Reference Sequence.</p>
	</div>*/
	
	if($_POST['hitsonly'] == 1)
		{
			$hitsonly = 1;
			$hitsbutton = 'success';
			$hitsonlychange = 0;
			$showtag = 'Showing'; 
		}
		else
		{
		$hitsonly = 0;
		$hitsbutton = 'alert';
		$hitsonlychange = 1;
		$showtag = 'Show';
		}
	
	echo "<div id='global_functions'>Global Functions<br><button type='button' id='global_delete' class='button' onclick='del_all_seq(0)'>Delete All</button>
	<button type='button' id='show_only_matches' class='button " . $hitsbutton . "' onclick='hitsonlychange(" . $hitsonlychange . ")'>" . $showtag . " Hits Only</button></div>";
	
	echo "<table width='100%' id='maintable' class='display'>
			<thead>
			<tr>
				<th>Submission Name</th>
				<th>Species</th>
				<th>Verified</th>
				<th>Diagnostics</th>
				<th>Date</th>
				<th>Delete</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<th>Submission Name</th>
				<th>Species</th>
				<th>Verified</th>
				<th>Diagnostics</th>
				<th>Date</th>
				<th>Delete</th>		
			</tr>
			</tfoot>
			<tbody>
			";

	$trim_point = 850;
	$trim_length = 600;
			
	global $mysqli;
	if($hitsonly == 1)
	{
	$stmt = $mysqli->prepare("SELECT MT.ID, SP.SPECIES, UPPER(SUBSTR(MT.MT_SEQUENCE,1,30)) AS MT_SEQUENCE, MT.SUB_NAME, MT.SUBMITTED, MT.BLAST, MT.SEQ_MATCH_PC, CAST(MT.ROSENBERG AS DECIMAL(14,6)) AS ROSENBERG, MT.GLOBAL_POSITION, (MT.GLOBAL_POSITION + MT.SEQ_MATCH_END - MT.SEQ_MATCH_START +1) AS ADJ_LEN FROM MT_SEQUENCE_PRIVATE MT LEFT JOIN MT_SEQUENCE MTM ON MT.SEQ_MATCH = MTM.ID LEFT JOIN SPECIES SP ON SP.ID = MTM.SPECIES_ID WHERE MT.OWNER = ? AND MT.BLAST = 1");
	$stmt->bind_param("i", $_SESSION['UserAccID']);		
	}
	else
		{
	$stmt = $mysqli->prepare("SELECT MT.ID, SP.SPECIES, UPPER(SUBSTR(MT.MT_SEQUENCE,1,30)) AS MT_SEQUENCE, MT.SUB_NAME, MT.SUBMITTED, MT.BLAST, MT.SEQ_MATCH_PC, CAST(MT.ROSENBERG AS DECIMAL(14,6)) AS ROSENBERG, MT.GLOBAL_POSITION, (MT.GLOBAL_POSITION + MT.SEQ_MATCH_END - MT.SEQ_MATCH_START +1) AS ADJ_LEN FROM MT_SEQUENCE_PRIVATE MT LEFT JOIN MT_SEQUENCE MTM ON MT.SEQ_MATCH = MTM.ID LEFT JOIN SPECIES SP ON SP.ID = MTM.SPECIES_ID WHERE MT.OWNER = ?");
	$stmt->bind_param("i", $_SESSION['UserAccID']);		
		}
	$stmt->execute();
	$stmt->bind_result($id, $species, $sequence, $subname, $subdate, $blast, $matchpc, $rosenberg, $globalpos, $adjlen);
	
	while($stmt->fetch())
	{
	if($matchpc >= 0.65)
			{
			$matchpc = $matchpc * 100;	
			$verified = $matchpc . "%";
			$tdcolor = '#00cc00';
			}
			else
				{
				$matchpc = $matchpc * 100;	
				$verified = '';
				$tdcolor = 'red';
				$species = "UNKNOWN";
				}
				

	$match_abv_99pc = "<span class='success label' id='fright'>Blast Match</span>";
	$match_bet_99pc80 = "<span class='warning label' id='fright'>Blast Match Low Ident</span>";
	$match_bel_80pc = "<span class='alert label' id='fright'>No Blast Match</span>";
	$ap_correct = "<span class='success label' id='fleft'>AP Correct</span>";
	$ap_incorrect = "<span class='warning label' id='fleft'>AP Incorrect</span>";
	$le_correct = "<span class='success label' id='fright'>Length Correct</span>";
	$le_incorrect = "<span class='warning label' id='fright'>Length Incorrect</span>";	
				

	$checkmark = "<span class='checkmarkspan'>&#10004;</span>";
	$crossmark = "<span class='crossmarkspan'>&#x2717;</span>";
	
	$alert = 0;
	
	if($matchpc > 99)
	{
	$mpcmark = $match_abv_99pc;
		}
		elseif($matchpc > 0.65)
			{
			$mpcmark = $match_bet_99pc80;
			}
			else
				{
				$mpcmark = $match_bel_80pc;
				$alert = $alert + 1;
				}
	if($globalpos <= $trim_point && $globalpos > 0)
	{
	$apmark = $ap_correct;
		}
		else
			{
		$apmark = $ap_incorrect;
		$alert = $alert + 1;	
			}

	if($adjlen - $globalpos >= $trim_length)
	{
	$lemark = $le_correct;
		}
		else
			{
		$lemark = $le_incorrect;
		$alert = $alert + 1;	
			}			
			
	
		$nsubdate = date('M j Y g:i A', strtotime($subdate));	
		
		$onclick_activator =  " data-toggle='data-reveal' onclick='load_ms_sidebar(". '"' . $id . '"' .")'";
			
	echo "<tr id='row-" . $id . "'><td" . $onclick_activator . "><span id='mysequencesubnamespan'>" . $subname . "</span></td><td" . $onclick_activator . "><span id='mysequencespeciesname'>" .  $species . "</span></td><td" . $onclick_activator . ">" . $verified . " " . $mpcmark . "</td><td align='right'" . $onclick_activator . "><span id='mysequencediagnostic'>" . $apmark . " " . $lemark . "</span></td><td" . $onclick_activator . ">" . $nsubdate . "</td><td><button type='button' class='button warning' id='" . $id . "' onclick='del_p_seq(" . $id . ", 0);'>Delete</button></td></tr>";
	}
	echo "</tbody></table>";
	}

function DelPSequence()
	{
	$rowid = $_POST['seqid'];	
	global $mysqli;
	$sql = $mysqli;				
	$stmt = $sql->prepare("DELETE FROM MT_SEQUENCE_PRIVATE WHERE ID = ?");
	$stmt->bind_param("i", $rowid);
	$stmt->execute();
	$stmt->store_result();
	$stmt->free_result();
	echo 'Complete';
	$stmt->close();	
	}
	
function DelAllPSequence()
	{
	$userid = $_SESSION['UserAccID'];	
	global $mysqli;
	$sql = $mysqli;				
	$stmt = $sql->prepare("DELETE FROM MT_SEQUENCE_PRIVATE WHERE OWNER = ?");
	$stmt->bind_param("i", $userid);
	$stmt->execute();
	$stmt->store_result();
	$stmt->free_result();
	echo 'Complete';
	$stmt->close();	
	}	

function LoadMSSidebar()
	{
	$p_seq = $_POST['Seq'];

	$trim_point = 850;
	$trim_length = 600;	
	
	global $mysqli;
	$stmt = $mysqli->prepare("SELECT MTP.SUB_NAME, CAST(((MTP.SEQ_MATCH_PC)*100) AS DECIMAL(10,2)) AS SEQ_MATCH_PC, SP1.SPECIES, (MTP.GLOBAL_POSITION + MTP.SEQ_MATCH_END - MTP.SEQ_MATCH_START +1) AS MATCH_LENGTH, MTP.GLOBAL_POSITION, MTA.SUB_NAME AS M_SUB_NAME, MTA.GENBANK_ID, SP2.SPECIES AS M_SPECIES FROM whiteflydb.MT_SEQUENCE_PRIVATE MTP JOIN MT_SEQUENCE MTA ON MTP.SEQ_MATCH = MTA.ID LEFT JOIN SPECIES SP1 ON MTP.SPECIES_ID = SP1.ID LEFT JOIN SPECIES SP2 ON MTA.SPECIES_ID = SP2.ID WHERE MTP.ID = ?");
	$stmt->bind_param("i", $p_seq);
	$stmt->execute();
	$stmt->bind_result($subname, $matchpc, $species, $glob_end, $glob_start, $matchname, $matchgb, $matchsp);
	while($stmt->fetch())
	{
	$checkmark = "<span class='checkmarkspan'>&#10004;</span>";
	$crossmark = "<span class='crossmarkspan'>&#x2717;</span>";
	
	$alert = 0;
	
	if($matchpc > 99)
	{
	$mpcmark = $checkmark;
		}
		else
			{
		$mpcmark = $crossmark . "<br><span class='crossmarkspan'>Below Threshold</span>";;
		$alert = $alert + 1;	
			}
	if($glob_start <= $trim_point)
	{
	$glomark = $checkmark;
		}
		else
			{
		$glomark = $crossmark . "<br><span class='crossmarkspan'>Wrong Region</span>";
		$alert = $alert + 1;		
			}

	if(($glob_end - $glob_start) >= $trim_length)
	{
	$lemark = $checkmark;
		}
		else
			{
		$lemark = $crossmark . "<br><span class='crossmarkspan'>Length too short</span>";
		$alert = $alert + 1;		
			}			

	if($alert > 0)
	{
	$broad_alert = $crossmark . " " . $alert . " Errors Detected";	
	}
	else
		{
		$broad_alert = $checkmark;
		}
		
	echo "<p>Submitted Name: " . $subname . "<br>Match Percent: " . $matchpc . "% " . $mpcmark . "<br>To Species: " . $species . "<br>AP Start: " . $glob_start . " " . $glomark . "<br>AP End: " . $glob_end . " " . $lemark . "<br>Total Length: " . ($glob_end - $glob_start) . " " . $lemark . "<br>Matched To: " . $matchname . "<br>Genbank Link: " . "<a target='_blank' href='http://www.ncbi.nlm.nih.gov/nuccore/" . $matchgb . "'>" .  $matchgb . "</a>" . "<br>Species: " . $matchsp . '<button class="close-button" data-close aria-label="Close modal" type="button"><span aria-hidden="true">&times;</span></button>';
	}
	
	
	
	}
	
	
function loadLogin()
	{
//	session_start();
	//print_r($_SESSION);
	if(!isset($_SESSION['Auth']))
	{
	$_SESSION['Auth'] = 0;
	}

if($_SESSION['Auth'] != 1)
	{
echo '	

<br><br>
<div class="large-4 columns large-offset-4" align="center">

<!--
<p><img src="" /><br></p>
-->

<p><h4>Login</h4></p></div>
<div class="large-4 columns large-offset-4" id="userform" align="center">
<input class="login_enter_listener" type="text" id="email" name="email" placeholder="Email Address"></input>
<input type="hidden" id="lcommand" name="lcommand" value="Login"></input>
<br>
<input class="login_enter_listener" type="password" class="input-text" id="pwd" name="pwd" placeholder="Password"></input>
<div id="result" class="cross_red"></div>
<br>Don' . "'" . 't have an account? <a onclick="loadRegister()">Register Here</a>
<br>
<br>
<button type="button" class="button default form-control" onclick="submit_function();">Login</button>
</form>
<br>


</div>
 
<script>$(document).foundation();</script>

';

	}
	else
		{
		echo "You are already logged in<br>";
		}
	}
	
function Login()
	{
	//session_start();	
	//print_r($_POST);
	//echo $_POST['email'];
	//echo "Started";
	global $mysqli;
	
	$sql = $mysqli;
	
	$stmt = $sql->prepare("SELECT FIRST_NAME, PASSWD, INSTITUTION_ID, ID, ACTIVE_LIST_ID, ADMIN FROM USERS WHERE EMAIL = ?");
	$stmt->bind_param("s", $_POST['email']);
	$stmt->execute();
	$stmt->bind_result($firstname, $pwhash, $pcomp, $userID, $listid, $admin);
	$stmt->fetch();
	
	//TESTING
	//if(1 == 1)
	if(password_verify($_POST['pwd'], $pwhash))
		{
			//echo "Authenticated";
			$_SESSION['Auth'] = 1;
			$_SESSION['CompanyID'] = $pcomp;
			$_SESSION['UserAccID'] = $userID;
			$_SESSION['FirstName'] = $firstname;
			$_SESSION['email'] = $_POST['email'];
			$_SESSION['ActiveList'] = $listid;	
			$_SESSION['Admin'] = $admin;			
			//print_r($_SESSION);
			echo "1";
				}
				else
					{
						echo "0";
						$_SESSION['Auth'] = 0;
					}
		
	$stmt->free_result();
	$stmt->close();	
    }	
	
function Load_Register()
		{
		
	$sitename = 'Whiteflybase';
		
		echo '

		<div class="large-6 small-centered columns"><p><h1>' . $sitename . '</h1></p><p><h4>Account Creation</h4></p>



		<div id="userform">
			<input type="text" class="form-control input-medium register_enter_listener" id="fname" placeholder="First Name" required></input><span id="fname_validator"></span>
			<br>
			<input type="text" class="form-control register_enter_listener" id="sname" placeholder="Surname" required></input><span id="sname_validator"></span>
			<br>
			<input type="email" class="form-control register_enter_listener" id="email" placeholder="Email Address" required></input><span id="email_validator"></span>
			<br>
			<input type="password" class="form-control register_enter_listener" id="pwd" placeholder="Password" required></input><span id="password_validator"></span>
			<br>
			<button type="button" id="register_button" class="form-control button" onclick="createuser();" disabled>Submit</button>
			<br>
			<div id="createuserresult"></div>
		</div>
				</div>
			';
		}
		
function NewUser()
	{
	global $mysqli;
	$sql = $mysqli;
	$pwhash = password_hash($_POST['pwd'], PASSWORD_DEFAULT);	
	$stmt = $sql->prepare("INSERT INTO USERS (`FIRST_NAME`, `SURNAME`, `EMAIL`, `PASSWD`) VALUES (?, ?, ?, ?)");
	$stmt->bind_param("ssss", $_POST['fname'], $_POST['sname'], $_POST['email'], $pwhash);	
	$stmt->execute();
	$stmt->store_result();
	//print_r($stmt);
	if($stmt->error)
	{
	
	echo '<div class="large-6 large-offset-3 columns"><p>It looks like there was a problem creating your account. The diagnostic text is below. If you require help interpreting it please contact the whiteflybase administrator.</p><p>' . $stmt->error . '</p></div>';
	}
	else
		{
		echo '<div class="large-6 large-offset-3 columns"><p>Your Account has been created. Please check your email for further instructions.<br>Alternatively click the blue login button in the top right corner to get started.</p></div>';
		
	$stmt->free_result();
	$stmt->close();	
	
//Send Welcome Email


$main = "<p>Hi " . $_POST['fname'] . ",<br><br>Welcome to Whiteflybase. <br> To login go to <a href='http://beta.whiteflybase.org/'>beta.whiteflybase.org</a> and click the blue login button. You can find it in the top right of your screen.</p>
<p>Your username is " . $_POST['email'] . " <br> If for some reason you have a problem logging in please email <a mailto='anders.savill@uwa.edu.au'>anders.savill@uwa.edu.au</a>
";
$foot = "- Whiteflybase 2.0";
$text = createTemplate($main, $foot);
# First, instantiate the SDK with your API credentials
$mgClient = new Mailgun('');
$domain = "";
# Now, compose and send your message.
$result = $mgClient->sendMessage($domain, array(
    'from'    => 'Whiteflybase Administrator <admin@mx.whiteflybase.org>',
    'to'      => $_POST['email'],
    'subject' => 'Welcome to Whiteflybase',
    'html'    => $text
)); 	
	}
    }		
	
function Upload_Sequences()
		{
		echo '<div class="row">
	<div class="large-12 columns">
		<p><h3>Upload New Sequence</h3></p>
	</div>
	<div class="large-6 columns">
	<form id="data" enctype="multipart/form-data">
    <p> Choose a fasta file containing your sequence or sequences to upload.</p>
	 <input id="upload_file" name="file" type="file" />
	</div>
	<div class="large-4 large-offset-2 columns">
	<button type="button" class="large button primary button" id="upload_button" onclick="upload_sequence_now();">Upload</button>
</form>
	</div>
</div>

<div class="row">
	<div class="large-12 columns">
	<div id="result"></div>
	</div>
</div>	
			';
		}	
		
function Upload_New_Sequence()
		{
		print_r($_POST);
		print_r($_FILES);
		$file = file_get_contents($_FILES['file']['tmp_name']);
		$seq = file_get_contents($_FILES['file']['tmp_name']);
		move_uploaded_file($_FILES['file']['tmp_name'], '/mnt/storage/html/biocore/blastdir/' . $_COOKIE['PHPSESSID'] . '.fa');
			$seq_pos = strpos($seq, '>');
			
			$seq = substr($seq, $seq_pos);
			if(substr($seq, 0, 1) != '>')
					{
					echo "<p>This file is not a fasta file. </p>";		
					}
					else
					{
			
			$seq = trim($seq, '>');
			print_r($seq);
			$seqparts = explode('>', $seq);

		foreach($seqparts as $sequence)
				{
				$sequence = preg_split("/\r\n|\n|\r/", $sequence, 2);
				$mt_sequence = preg_replace('/\s\s+/', '', $sequence[1]);
				//print_r($sequence);
	global $mysqli;
	$sql = $mysqli;				
	$stmt = $sql->prepare("INSERT IGNORE INTO MT_SEQUENCE_PRIVATE (SUB_NAME, MT_SEQUENCE, OWNER) VALUES (?, ?, ?)");
	$stmt->bind_param("ssi", $sequence[0], $mt_sequence, $_SESSION['UserAccID']);
	$stmt->execute();
	$stmt->store_result();
	//$result = print_r($stmt);
	$stmt->free_result();
				echo $sequence[0] . ' Uploaded Sucessfully <br>';
	$stmt->close();				
				
				}
				echo 'All Operations have completed. Please click My Data > Sequences to view the uploaded files.';
				}

		unlink('/mnt/storage/html/biocore/blastdir/' . $_COOKIE['PHPSESSID'] . '.fa');		
				
			}
		

function Blast_Sequences()
			{
				
	// Generate Blast Files

	$fields = array('id' => '1', 'api_key' => '1234567890');
	
// Create file from database START

	global $mysqli;
	$stmt = $mysqli->prepare("SELECT MT.ID, SP.ID, SP.SPECIES, MT.GENBANK_ID, MT.MT_SEQUENCE, MT.SUB_NAME, MT.GLOBAL_POSITION FROM MT_SEQUENCE MT JOIN SPECIES SP ON SP.ID = MT.SPECIES_ID WHERE PUBLIC = '1' AND BLAST = '1' AND BAYES = '1'");
	$stmt->execute();
	$stmt->bind_result($id, $speciesid, $species, $genbank_id, $sequence, $subname, $global_position);
	$db_array = array();
	$tofile = '';
		
while($stmt->fetch())
	{

	$result = '';
	$result = $id;
	
	$trim_location = 600;	
	//Trim reference dataset.
	if($global_position < $trim_location)
	{
	$sequence = substr($sequence, ($trim_location - $global_position));
	$global_position = $global_position + $trim_location;
	}
	
	$tofile = $tofile . '>' . $result . "\n" . $sequence . "\n";

	$db_array[$id] = array('SpeciesID' => $speciesid, 'SpeciesName' => $species, 'GlobalPosition' => $global_position);
	
	}	
	file_put_contents('blastdir/newfile.fasta', $tofile);
	
	//print_r($db_array);
	
// Create File code END 

// Create file of sequences that need to be blasted START
	//$stmt = $mysqli->prepare("SELECT MT.ID, MT.GENBANK_ID, MT.MT_SEQUENCE, MT.SUB_NAME, SP.SPECIES FROM MT_SEQUENCE MT JOIN MT_SEQUENCE MT2 ON MT.SEQ_MATCH = MT2.ID JOIN SPECIES SP ON SP.ID = MT2.SPECIES_ID WHERE MT.BLAST != '1'");
	$stmt = $mysqli->prepare("SELECT MT.ID, MT.MT_SEQUENCE, MT.SUB_NAME FROM MT_SEQUENCE_PRIVATE MT WHERE MT.OWNER = ? AND (MT.BLAST IS NULL OR MT.BLAST != 1)");
	$stmt->bind_param("i", $_SESSION['UserAccID']);	
	$stmt->execute();
	$stmt->bind_result($id, $sequence, $subname);
	$db_array_toblast = array();
	$toblast = '';
while($stmt->fetch())
	{
	$result = '';
	$result = $id;
	
	$toblast = $toblast . '>' . $result . "\n" . $sequence . "\n";

	$db_data = array('SequenceID' => $result, 'Sequence' => $sequence);
	array_push($db_array_toblast, $db_data);
	unset($db_data);
	}	
	file_put_contents('blastdir/newblast.fasta', $toblast);	
	
	//print_r($db_array_toblast);

// Create File code END 	
	
		
				
/* Depreciated				
			echo '<p><h3>Blast Results</h3></p>';
	$fields = array('id' => '1', 'api_key' => '1234567890');

		$dbsource = curl_file_create('blastdir/wfly_reference.fa');
		$dbquery = curl_file_create('blastdir/' . $_COOKIE['PHPSESSID'] . '.fa', 'text/plain', $_COOKIE['PHPSESSID'] . '.fa');
*/
		$dbsource = curl_file_create('blastdir/newfile.fasta');
		$dbquery = curl_file_create('blastdir/newblast.fasta');
		$data = array('source' => $dbsource, 'query' => $dbquery);
		

		
			$rc = curl_init();
			curl_setopt($rc, CURLOPT_URL, "http://192.168.100.113/connect.php");
			curl_setopt($rc, CURLOPT_POST, 1);
			curl_setopt($rc, CURLOPT_POSTFIELDS, $data);
			curl_setopt($rc, CURLOPT_SAFE_UPLOAD, TRUE);
			curl_setopt($rc, CURLOPT_RETURNTRANSFER, TRUE);
			
			$result = curl_exec($rc);
			$res = curl_error($rc);
			curl_close($rc);
			
			$stuff = json_decode(strstr($result, '['), true);
			if(is_array($stuff))
			{
			$array_sort_key = array();
			
			foreach($stuff as $x)
			{
			$array_sort_key[$x['RowID']] = $x['PCIdent'];
			}
			arsort($array_sort_key);

	echo '<table><tr><td>QueryID</td><td>SubjectID</td><td>PCIdent</td><td>Length</td><td>Mismatch</td><td>Blast Defined Species</td></tr>';
			foreach(array_keys($array_sort_key) as $i)
			{
			$threshold = 97.00;
			
			if($stuff[$i]['PCIdent'] > $threshold)
			{
			$col_header = '<td class="blue_column">';
			}
			else
				{
				$col_header = '<td>';
				}
				
			echo '<tr>	
					' . $col_header . $stuff[$i]['QueryID'] . '</td>
					' . $col_header . $stuff[$i]['SubjectID'] . '</td>
					' . $col_header . $stuff[$i]['PCIdent'] . '%</td>
					' . $col_header . $stuff[$i]['Length'] . '</td>
					' . $col_header . $stuff[$i]['Mismatch'] . '</td>
					' . $col_header . $db_array[$stuff[$i]['SubjectID']]['SpeciesName'] . '</td>					
			</tr>';
			unset($col_header);
			}
	echo '</table><br><br>';				

	//Start SQL

	foreach(array_keys($array_sort_key) as $i)
			{
	global $mysqli;
	$sql = $mysqli;
	
	$query = $stuff[$i]['QueryID'];
	$subject = $stuff[$i]['SubjectID'];
	$percent = ($stuff[$i]['PCIdent'] / 100);
	$spid = $db_array[$stuff[$i]['SubjectID']]['SpeciesID'];
	$qstart = $stuff[$i]['QueryStart'];
	$qend = $stuff[$i]['QueryEnd'];
	$sstart = $stuff[$i]['SeqStart'];
	$send = $stuff[$i]['SeqEnd'];
	$glid = ($db_array[$stuff[$i]['SubjectID']]['GlobalPosition'] - ($stuff[$i]['QueryStart'] - 1)) + ($stuff[$i]['SeqStart'] - 1);	
	if($percent == 1.00)
		{
		$species = $spid;
		$public = 1;
		}
		else
			{
			$species = NULL;
			$public = 0;
			}
			
if($qstart > $qend || $sstart > $send)
{	
	echo "<h2>Rerun required " . $query . "</h2>";
	$stmt = $sql->prepare("UPDATE MT_SEQUENCE_PRIVATE SET MT_SEQUENCE = revcomp(MT_SEQUENCE) WHERE ID = ?");
	$stmt->bind_param("i", $query);
	$stmt->execute();
	$stmt->store_result();
	$stmt->free_result();

	$stmt->close();
}
	$stmt = $sql->prepare("UPDATE MT_SEQUENCE_PRIVATE SET SEQ_MATCH = ?, SEQ_MATCH_PC = ?, SPECIES_ID = ?, QUE_START = ?, QUE_END = ?, SEQ_MATCH_START = ?, SEQ_MATCH_END = ?, GLOBAL_POSITION = ?, BLAST = 1 WHERE ID = ?");
	$stmt->bind_param("isiiiiiii", $subject, $percent, $species, $qstart, $qend, $sstart, $send, $glid, $query);
	$stmt->execute();
	$stmt->store_result();
	$stmt->free_result();

	$stmt->close();	
	
	unset($query, $subject, $percent, $glid);
			}
			}
			else
				{
				echo $result;
				}
			}

function Genbank_Import()
			{
set_time_limit(6000);			
//XML Import file to open, will probably change this to an upload script later.
$xml = file_get_contents('sequence.gbx.xml', true);
$dload = simplexml_load_string($xml);

//Create arrays and load data into them.
$fullarray = array();
$subarray = array();

foreach($dload->{'INSDSeq'} AS $ISeq)
	{
	$locus = $ISeq->{'INSDSeq_feature-table'}->{'INSDFeature'};
		$subarray = array( (string) $ISeq->{'INSDSeq_primary-accession'}, (string) $ISeq->{'INSDSeq_sequence'});
		
		$subarray2 = array( (string) $ISeq->{'INSDSeq_primary-accession'}, (string) $ISeq->{'INSDSeq_sequence'});
		foreach($locus AS $locus2)
		{
		foreach($locus2->{'INSDFeature_quals'}->{'INSDQualifier'} AS $locus3)
			{
			$subarray[ (string) $locus3->{'INSDQualifier_name'}] =  (string) $locus3->{'INSDQualifier_value'};
			}
		}			
		array_push($fullarray, $subarray);
	
	}
print_r($dload);
//Produce file and a table containing the data from the Array. File created is a fasta file, this should create a download request one day.	
	
	$tofile = '';
	foreach($fullarray as $i)
	{
	$result = '';
	
	$seq_split = str_split(strtoupper($i['1']), 71);
	foreach($seq_split as $n)
		{
		$result = $result . $n . "\n";
		}
	$tofile = $tofile . '>' . $i['0'] . "\n" . $result . "\n";
	}	
	file_put_contents('blastdir/newfile.fasta', $tofile);

//We want to generate the table before we add new sequences to the database

	$hide = 0;
	
	if($hide == 0)
	{
	echo '<table width=100%><tr><td>Accession</td><td>Organism</td><td>Host</td><td>Country</td><td>Gene</td><td>Sequence</td></tr>';
	foreach($fullarray as $i)
		{
		if(!isset($i['host']))
		{
		$host = 'NA';
		}
		else
			{
			$host = $i['host'];
			}
		
	if(!isset($i['country']))
	{
		$country = 'NA';
	}
	else
		{
		$country = $i['country'];
		}
	if(!isset($i['gene']))
	{
		$gene = 'NA';
	}
	else
		{
		$gene = $i['gene'];
		}
		
		echo '<tr>	
					<td>' . $i['0'] . '</td>
					<td>' . $i['organism'] . '</td>
					<td>' . $host . '</td>
					<td>' . $country . '</td>
					<td>' . $gene . '</td>
					<td>' . strtoupper($i['1']) . '</td>
			</tr>';
			
			unset($host, $country, $gene);
	}
	echo '</table><br><br>';	

	}
//Using the same table generation code we check the data against the database
	
	$db_data = array();
	
	foreach($fullarray as $i)
		{
		if(!isset($i['host']))
		{
		$host = 'NA';
		}
		else
			{
			$host = $i['host'];
			}
		
	if(!isset($i['country']))
	{
		$country = 'NA';
	}
	else
		{
		$country = $i['country'];
		}
	if(!isset($i['gene']))
	{
		$gene = 'NA';
	}
	else
		{
		$gene = $i['gene'];
		}
	
			
			$toarray = array('accession' => $i['0'], 'organism' => $i['organism'], 'host' => $host, 'country' => $country, 'gene' => $gene, 'sequence' => strtoupper($i['1']), 'owner' => 1, 'public' => 0);
			
			array_push($db_data, $toarray);
			
			unset($host, $country, $gene, $toarray);
			
			
	}


	//NORMAL CODE START	

// *** TEMP COMMENTING FOR TESTING *** START
	
	foreach($db_data as $x)
	{
	
	//Start SQL

	global $mysqli;
	
	$sql = $mysqli;


	
	$stmt = $sql->prepare("INSERT IGNORE INTO MT_SEQUENCE (SUB_NAME, GENBANK_ID, LOC1, MT_SEQUENCE, OWNER, PUBLIC, HOST) VALUES (?, ?, ?, ?, ?, ?, ?)");
	#$stmt = $sql->prepare("UPDATE MT_SEQUENCE SET `HOST` = ? WHERE GENBANK_ID = ?");
		var_dump($sql);
	$stmt->bind_param("ssssiis", $x['accession'], $x['accession'], $x['country'], $x['sequence'], $x['owner'], $x['public'], $x['host']);
	#$stmt->bind_param("ss", $x['host'], $x['accession']);
	/*
		$stmt = $sql->prepare("INSERT IGNORE INTO MT_GLOBAL_QA (SUB_NAME, GENBANK_ID, MT_SEQUENCE, SOURCEDB) VALUES (?, ?, ?, 'GENBANK')");
		
		var_dump($sql);
	$stmt->bind_param("sss", $x['accession'], $x['accession'], $x['sequence']);
	*/
	$stmt->execute();
	$stmt->store_result();
	$result = print_r($stmt);
	$stmt->free_result();

	$stmt->close();	
	}
	
	
	
// *** TEMP COMMENTING FOR TESTING *** END	
	
	
//NORMAL CODE END

/*
//FIXING BROKEN THINGS CODE START
	
	foreach($db_data as $x)
	{
	
	//Start SQL

	global $mysqli;
	
	$sql = $mysqli;

	$stmt = $sql->prepare("UPDATE MT_SEQUENCE SET MT_SEQUENCE = ? WHERE GENBANK_ID = ?");
		var_dump($sql);
	$stmt->bind_param("ss", $x['sequence'], $x['accession']);
	$stmt->execute();
	$stmt->store_result();
	$result = print_r($stmt);
	$stmt->free_result();

	$stmt->close();	
	}	

//FIXING BROKEN THINGS CODE END	
*/
	//End SQL
			}

function Blast_Assay()
	{
	SET_TIME_LIMIT(600);
	echo '<h1>Blast Assay</h1>';
	
				echo '<p><h3>Blast Results</h3></p>';
	$fields = array('id' => '1', 'api_key' => '1234567890');
	
// Create file from database START

	global $mysqli;
	$stmt = $mysqli->prepare("SELECT MT.ID, SP.ID, SP.SPECIES, MT.GENBANK_ID, MT.MT_SEQUENCE, MT.SUB_NAME, MT.GLOBAL_POSITION FROM MT_SEQUENCE MT JOIN SPECIES SP ON SP.ID = MT.SPECIES_ID WHERE BAYES = '1'");
	$stmt->execute();
	$stmt->bind_result($id, $speciesid, $species, $genbank_id, $sequence, $subname, $global_position);
	$db_array = array();
	$tofile = '';
while($stmt->fetch())
	{
	$result = '';
	$result = $id;
	
	
	$trim_location = 600;	
	//Trim reference dataset.
	if($global_position < $trim_location)
	{
	$sequence = substr($sequence, ($trim_location - $global_position));
	$global_position = $global_position + $trim_location;
	}	
	
	
	$tofile = $tofile . '>' . $result . "\n" . $sequence . "\n";

	$db_array[$id] = array('SpeciesID' => $speciesid, 'SpeciesName' => $species, 'GlobalPosition' => $global_position);
	
	}	
	file_put_contents('blastdir/newfile.fasta', $tofile);
	
	//print_r($db_array);
	
// Create File code END 

// Create file of sequences that need to be blasted START
	//$stmt = $mysqli->prepare("SELECT MT.ID, MT.GENBANK_ID, MT.MT_SEQUENCE, MT.SUB_NAME, SP.SPECIES FROM MT_SEQUENCE MT JOIN MT_SEQUENCE MT2 ON MT.SEQ_MATCH = MT2.ID JOIN SPECIES SP ON SP.ID = MT2.SPECIES_ID WHERE MT.BLAST != '1'");
	$stmt = $mysqli->prepare("SELECT MT.ID, MT.GENBANK_ID, MT.MT_SEQUENCE, MT.SUB_NAME FROM MT_SEQUENCE MT WHERE MT.BLAST != '1'");
	$stmt->execute();
	$stmt->bind_result($id, $genbank_id, $sequence, $subname);
	$db_array_toblast = array();
	$toblast = '';
while($stmt->fetch())
	{
	$result = '';
	$result = $id;
	
	$toblast = $toblast . '>' . $result . "\n" . $sequence . "\n";

	$db_data = array('SequenceID' => $result, 'Sequence' => $sequence);
	array_push($db_array_toblast, $db_data);
	unset($db_data);
	}	
	file_put_contents('blastdir/newblast.fasta', $toblast);	
	
	//print_r($db_array_toblast);

// Create File code END 
	
		$dbsource = curl_file_create('blastdir/newfile.fasta');
		$dbquery = curl_file_create('blastdir/newblast.fasta');

		$data = array('source' => $dbsource, 'query' => $dbquery);
		
			$rc = curl_init();
			curl_setopt($rc, CURLOPT_URL, "http://192.168.100.113/connect.php");
			curl_setopt($rc, CURLOPT_POST, 1);
			curl_setopt($rc, CURLOPT_POSTFIELDS, $data);
			curl_setopt($rc, CURLOPT_SAFE_UPLOAD, TRUE);
			curl_setopt($rc, CURLOPT_RETURNTRANSFER, TRUE);
			
			$result = curl_exec($rc);
			$res = curl_error($rc);
			curl_close($rc);
			
			$stuff = json_decode(strstr($result, '['), true);
			
			$array_sort_key = array();
			
			print_r($result);
			
			foreach($stuff as $x)
			{
			$array_sort_key[$x['RowID']] = $x['PCIdent'];
			}
			arsort($array_sort_key);

	echo '<table><tr><td>QueryID</td><td>SubjectID</td><td>PCIdent</td><td>Length</td><td>Mismatch</td><td>Blast Defined Species</td></tr>';
			foreach(array_keys($array_sort_key) as $i)
			{
			$threshold = 98.00;
			
			if($stuff[$i]['PCIdent'] > $threshold)
			{
			$col_header = '<td class="blue_column">';
			}
			else
				{
				$col_header = '<td>';
				}
				
			echo '<tr>	
					' . $col_header . $stuff[$i]['QueryID'] . '</td>
					' . $col_header . $stuff[$i]['SubjectID'] . '</td>
					' . $col_header . $stuff[$i]['PCIdent'] . '%</td>
					' . $col_header . $stuff[$i]['Length'] . '</td>
					' . $col_header . $stuff[$i]['Mismatch'] . '</td>
					' . $col_header . $db_array[$stuff[$i]['SubjectID']]['SpeciesName'] . '</td>					
			</tr>';
			unset($col_header);
			}
	echo '</table><br><br>';				

	//Start SQL

	foreach(array_keys($array_sort_key) as $i)
			{
	global $mysqli;
	$sql = $mysqli;
	
	$query = $stuff[$i]['QueryID'];
	$subject = $stuff[$i]['SubjectID'];
	$percent = ($stuff[$i]['PCIdent'] / 100);
	$spid = $db_array[$stuff[$i]['SubjectID']]['SpeciesID'];
	$qstart = $stuff[$i]['QueryStart'];
	$qend = $stuff[$i]['QueryEnd'];
	$sstart = $stuff[$i]['SeqStart'];
	$send = $stuff[$i]['SeqEnd'];
	$glid = ($db_array[$stuff[$i]['SubjectID']]['GlobalPosition'] - ($stuff[$i]['QueryStart'] - 1)) + ($stuff[$i]['SeqStart'] - 1);	
	if($percent == 1.00)
		{
		$species = $spid;
		$public = 1;
		}
		else
			{
			$species = NULL;
			$public = 0;
			}
			
if($qstart > $qend || $sstart > $send)
{	
	echo "<h2>Rerun required " . $query . "</h2>";
	$stmt = $sql->prepare("UPDATE MT_SEQUENCE SET MT_SEQUENCE = revcomp(MT_SEQUENCE) WHERE ID = ?");
	$stmt->bind_param("i", $query);
	$stmt->execute();
	$stmt->store_result();
	$stmt->free_result();

	$stmt->close();
}
	$stmt = $sql->prepare("UPDATE MT_SEQUENCE SET SEQ_MATCH = ?, SEQ_MATCH_PC = ?, BLAST = '1', SPECIES_ID = ?, PUBLIC = ?, QUE_START = ?, QUE_END = ?, SEQ_MATCH_START = ?, SEQ_MATCH_END = ?, GLOBAL_POSITION = ? WHERE ID = ?");
	$stmt->bind_param("ssiiiiiiii", $subject, $percent, $species, $public, $qstart, $qend, $sstart, $send, $glid, $query);
	$stmt->execute();
	$stmt->store_result();
	$stmt->free_result();

	$stmt->close();	
	
	unset($query, $subject, $percent, $glid);
			}
	}
	
function Statistics_Main()
	{
	echo '<h2>Statistics Manager</h2>
	<br><div class="row"><div class="large-3 columns"> Create New Alignment File </div><div class="large-3 columns"><button type="button" class="large button primary button" onclick="stats_create_alignment();">Create</button></div></div>
	<div class="row"><div class="large-12 columns" id="stats_result">Ready</div></div>
	';	
	
	
	}

function Create_Alignment()
	{
	SET_TIME_LIMIT(600);
	global $mysqli;
	$sql = $mysqli;
	//global $mysqli;
	//$stmt = $mysqli->prepare("SELECT ID, GENBANK_ID, LOC1, UPPER(MT_SEQUENCE) AS MT_SEQUENCE, SUB_NAME FROM whiteflydb.MT_SEQUENCE WHERE SEQ_MATCH_PC < 1 OR SEQ_MATCH_PC IS NULL;");
	
	//Trimming Variables
	
	$trim_point = 850;
	
	$trim_length = 600;
	
	$stmt = $sql->prepare("SELECT MT.ID, MT.GENBANK_ID, MT.LOC1, UPPER(MT.MT_SEQUENCE) AS MT_SEQUENCE, MT.SUB_NAME, MT.SEQ_MATCH_START, MT.SEQ_MATCH_END, LENGTH(MT.MT_SEQUENCE) AS LE, MT.SEQ_MATCH, MT.QUE_START, MT.QUE_END, MT.GLOBAL_POSITION, MT2.SPECIES_ID FROM whiteflydb.MT_SEQUENCE MT JOIN MT_SEQUENCE MT2 ON MT.SEQ_MATCH = MT2.ID JOIN SPECIES SP ON SP.ID = MT2.SPECIES_ID WHERE MT.GLOBAL_POSITION < ? AND (LENGTH(MT.MT_SEQUENCE) - (? - MT.GLOBAL_POSITION)) > ? AND SP.Outgroup != 1;");
	$stmt->bind_param("iii", $trim_point, $trim_point, $trim_length);
	$stmt->execute();
	$stmt->bind_result($id, $genbankID, $loc, $sequence, $subname, $sstart, $send, $length, $smatch, $qstart, $qend, $glid, $speciesid);
	$db_array = array();
	$tofile = '';
		
$spec = array();
		
while($stmt->fetch())
{
	$eval = preg_match('/[^AGCT]/', $sequence);

		if(!isset($spec[$speciesid]))
		{
		$add_array = array($speciesid => 0);
		$spec = $spec + $add_array;
		unset($add_array);
		}
		
	if($eval === 0 && $spec[$speciesid] < 10000)
		{
			$update = $spec[$speciesid] + 1; 
			$spec[$speciesid] = $update;
	//PADDING START
	/* This is used to align, we want to trim and align
	$pad_left = $length + $glid;
	
	$sequence = str_pad($sequence, $pad_left, "-", STR_PAD_LEFT);	
	
	unset($pad_left);
	//$sequence = str_pad($sequence, 7000, "-", STR_PAD_RIGHT);	
	*/
	//PADDING FINISH
	
	//Align and Trim Start
	
	$trim_left = $trim_point - $glid;
	
	$trim_right = $trim_length;
	
	$sequence = substr($sequence, $trim_left, $trim_right);
	
	//Align and Trim Finish
	
	$result = '';
	$result = $id;
	$seq_result = '';
	$seq_split = str_split($sequence, 71);
	
	foreach($seq_split as $n)
	{
	$seq_result = $seq_result . "\n" . $n;
	}
	
	$tofile = $tofile . '>' . $result . "_" . $speciesid . $seq_result . "\n";
	
	$seqlength = strlen($seq_result);

	$db_array[$id] = array('SpeciesID' => $genbankID, 'SubmissionName' => $loc);
	
	unset($seq_result, $seqlength, $eval);
		}
	}
	file_put_contents('blastdir/alignment_altr.fasta', $tofile);
	
	echo "Alignment has been Created";
	}
	
function Send_Alignment()
			{
	global $mysqli;
	$sql = $mysqli;
	$fields = array('id' => '1', 'api_key' => '1234567890');

		$dbquery = curl_file_create('blastdir/alignment_altr.fasta', 'text/plain', 'alignment_altr.fasta');

		$data = array('query' => $dbquery);

			$rc = curl_init();
			curl_setopt($rc, CURLOPT_URL, "http://192.168.100.113/rops/manager.php");
			curl_setopt($rc, CURLOPT_POST, 1);
			curl_setopt($rc, CURLOPT_POSTFIELDS, $data);
			curl_setopt($rc, CURLOPT_SAFE_UPLOAD, TRUE);
			curl_setopt($rc, CURLOPT_RETURNTRANSFER, TRUE);

			$result = curl_exec($rc);
			$res = curl_error($rc);
			curl_close($rc);
			
			$stuff = json_decode($result, TRUE);
			//print_r($stuff);
			//echo $result;
			foreach($stuff as $ros)
			{			
			$stmt = $sql->prepare("UPDATE MT_SEQUENCE SET ROSENBERG = ? WHERE ID = ?");
			$stmt->bind_param("si", $ros['Rosenberg'], $ros['ID']);
			$stmt->execute();
			$stmt->store_result();
			$stmt->free_result();
			$stmt->close();
			}
			echo "Rosenbergs probability of Reciprocal Monophyly has been updated for all entries";
			}	

function export_all_alignments()
	{
	global $mysqli;
	$stmt = $mysqli->prepare("SELECT sp.ID, sp.SPECIES FROM SPECIES sp");
	$stmt->execute();
	$stmt->bind_result($id, $species);
		
	$species_ar = array();
	$species_id_ar = array();
	
		while($stmt->fetch())
		{
		array_push($species_ar, $species);
		array_push($species_id_ar, $id);
		}
	
	if($_POST['conf'] == 1)
		{
		$output_text = '';	
			
		foreach($species_id_ar as $x)
			{
			$res_text = export_sp_alignment($x, '100000');
			if(isset($res_text))
				{
				foreach($res_text as $xyz)
					{
					print_r($xyz);
					echo "<hr>";
					}

				}
			}
		echo 'Operation reported as complete. Please check to ensure data has been created';
		//echo $output_text;
		file_put_contents('blastdir/full_combined.fasta', $output_text);
		}
		else
		{
			echo '<p><h2>Sequence Export</h2></p><p>If you press continue all species alignments will be exported to the blast directory</p>Species to be Exported:<br>';
	
				foreach($species_ar as $x)
				{
				echo $x . '<br>';
				}
	
			echo '<button type="button" class="button default" onclick="export_alignments(' . "'1'" . ');">Confirm</button>';	
		}
	}
			
			
function export_sp_alignment($speciesid, $limit)
	{
		//Trimming Variables
	
	global $mysqli;
	
	$trim_point = 800;
	//Changed from 850
	$trim_length = 600;
	//Changed from 600
	$stmt = $mysqli->prepare("SELECT MT.ID, MT.GENBANK_ID, MT.LOC1, UPPER(MT.MT_SEQUENCE) AS MT_SEQUENCE, MT.SUB_NAME, MT.SEQ_MATCH_START, MT.SEQ_MATCH_END, LENGTH(MT.MT_SEQUENCE) AS LE, MT.SEQ_MATCH, MT.QUE_START, MT.QUE_END, MT.GLOBAL_POSITION, IFNULL(MT.SPECIES_ID, MT2.SPECIES_ID) AS SPECIES_ID, IFNULL(SP.SPECIES, SP2.SPECIES) AS SPECIES, IFNULL(MT.HOST, 'NA') AS HOST FROM whiteflydb.MT_SEQUENCE MT LEFT JOIN MT_SEQUENCE MT2 ON MT.SEQ_MATCH = MT2.ID LEFT JOIN SPECIES SP ON MT2.SPECIES_ID = SP.ID LEFT JOIN SPECIES SP2 ON MT.SPECIES_ID = SP2.ID WHERE (SP.Outgroup != 1 OR SP2.Outgroup != 1) AND MT.DISCARD != 1 AND MT.GLOBAL_POSITION < ? AND (LENGTH(MT.MT_SEQUENCE) - (? - MT.GLOBAL_POSITION)) > ? AND (MT.SPECIES_ID = ? OR MT2.SPECIES_ID = ?) ORDER BY RAND();");
//	$stmt->bind_param("iii", $trim_point, $speciesid, $speciesid);	
	$stmt->bind_param("iiiii", $trim_point, $trim_point, $trim_length, $speciesid, $speciesid);
	$stmt->execute();
	$stmt->bind_result($id, $genbankID, $loc, $sequence, $subname, $sstart, $send, $length, $smatch, $qstart, $qend, $glid, $speciesid, $species, $host);
	$db_array = array();
	$tofile = '';
	$spec = array();
	$species_grouped = array();
	$species_name = array();
while($stmt->fetch())
{
	$eval = preg_match('/[^AGCT]/', $sequence);
	
		

		if(!isset($spec[$speciesid]))
		{
		$add_array = array($speciesid => 0);
		$spadd_array = array($speciesid => $species);
		$species_group = array($speciesid => array());
		$spec = $spec + $add_array;
		$species_name = $species_name + $spadd_array;
		$species_grouped = $species_grouped + $species_group;
		unset($add_array, $species_group, $spadd_array);
		}
		
	if($eval === 0 && $spec[$speciesid] < $limit)
		{
			$update = $spec[$speciesid] + 1; 
			$spec[$speciesid] = $update;

	//PADDING FINISH
	
	//Align and Trim Start
	
	$trim_left = $trim_point - $glid;
	
	$trim_right = $trim_length;
	
	$sequence = substr($sequence, $trim_left, $trim_right);
	
	//Align and Trim Finish
	
	$result = '';
	$result = $id;
	$seq_result = '';
	$seq_split = str_split($sequence, 71);
	
	foreach($seq_split as $n)
	{
	$seq_result = $seq_result . "\n" . $n;
	}
	
	$tofile = $tofile . '>' . $result . "_" . $speciesid . $seq_result . "\n";
	
	$arloc = preg_replace('/[^A-Za-z0-9]/', '', $loc);
	$arhost = preg_replace('/[^A-Za-z0-9]/', '', $host);
	
	array_push($species_grouped[$speciesid], array($result . "_" . $speciesid . "_" . $species . "_" . $arloc . "_" . $genbankID . "_" . $arhost, $sequence));
	
	$seqlength = strlen($seq_result);

	$db_array[$id] = array('SpeciesID' => $genbankID, 'SubmissionName' => $loc);
	
	unset($seq_result, $seqlength, $eval);
		}
	}
	foreach($species_grouped as $k => $x)
	{
	$sptofile = '';
		foreach($x as $y)
		{
		$sptofile = $sptofile . '>' . $y[0] . "\n" . $y[1] . "\n";	
		}
	file_put_contents('blastdir/alignment_species_' . $species_name[$k] . '.fasta', $sptofile);
	unset($sptofile);
	}
	
	//file_put_contents('blastdir/alignment_altr.fasta', $tofile);	
	//print_r($spec);
	//echo "<br><br>";
	//print_r($species_grouped);
	//print_r($tofile);
	if(isset($species_grouped))
		{
		return $species_grouped;
		}
	}
		
function Load_Sequence_Info()
	{
	echo '<button type="button" class="button" onclick="load_sandbox();">Reload</button><br>';	
		
	echo 'Now Editing: ' . $_POST['genbank'] . '<br>';
	
	echo 'Move Sequence: <button type="button" class="button" onclick="move_sequence(' . "'-1'" . ')">LEFT</button> <button type="button" class="button" onclick="move_sequence(' . "'1'" . ')">RIGHT</button><br>';
	
	echo '<div id="control">This will move the sequence: <span id="move_amount" value=0>0</span> Spaces <button type="button" class="button success" id="update_button" onclick="update_reload();">Update and Reload</button></div>';
	
	}
	
function Move_Sequence()
	{
	global $mysqli;
	$sql = $mysqli;
		
	$sequence_id = $_POST['sequenceid'];
	$shift_amount = $_POST['shift'];
	
	$stmt = $sql->prepare("UPDATE MT_SEQUENCE SET GLOBAL_POSITION = (GLOBAL_POSITION + ?), NEW = 0 WHERE ID = ?");
			$stmt->bind_param("ii", $shift_amount, $sequence_id);
			$stmt->execute();
			$stmt->store_result();
			$stmt->free_result();
			$stmt->close();
	
		echo $sequence_id . '<br>' . $shift_amount . '<br>';
		echo 'Sequence has been shifted ' . $shift_amount . ' and has been accepted.';
	}
	
function Sequence_Updater($filename)
	{
	//Load Nexus File
	$nexus = file_get_contents($filename, true);
	
	//Strip #Nexus
	$nexus = preg_replace('/#NEXUS/', '', $nexus, 1);	
		
	//Split into parts
	$nexus = explode('end;', $nexus);
	
	//Read into Arrays with component parts
	foreach($nexus as $x)
	{
	$contains_begin = strpos($x, 'begin');
	
	if($contains_begin > 0)
	{
	$datastring = explode(';', $x);
	
	$current_array = ltrim(preg_replace('/begin /', '', $datastring[0], 1));
	
	//echo $current_array . '<hr>';
	
	$nexus_holder[$current_array] = array();


	}
	$newdatastring = explode(';', $x);
		
		foreach($newdatastring as $y)
			{
	$contains_begin = strpos($y, 'begin');
	if($contains_begin > 0)
		{
		}
		else
			{
			array_push($nexus_holder[$current_array], $y);
			}
		
		}
	}
	
	//Start of Taxa
	
	$taxa = $nexus_holder['taxa'];
	
	//print_r($taxa);
	//echo '<br><hr><br>';
	foreach($taxa as $x)
		{
		$newtaxa = preg_split('/\s/', ltrim($x));
		//print_r(array_filter($newtaxa));
		$current_taxa_group = $newtaxa[0];
		//$taxa_groups[$current_taxa_group] = array();
		unset($newtaxa[0]);
		$taxa_groups[$current_taxa_group] = array_filter($newtaxa);
		//echo '<br>';
		}
		//echo '<br><hr><br>';
	$output_taxa_groups = array_values($taxa_groups['taxlabels']);
	//End of Taxa
	//Start of characters
	
		$char = $nexus_holder['characters'];
	//echo '<br><hr><br>';
	foreach($char as $x)
		{
		$newchar = preg_split('/\s/', ltrim($x), 2);
		$current_char_group = $newchar[0];
		unset($newchar[0]);
		$char_groups[$current_char_group] = array_filter($newchar);
		//print_r($newchar);
		//echo '<br>';
		
		}
			$components = preg_split('/[\r\n]+/', trim($char_groups['matrix'][1]));
			//print_r($components);
			foreach($components as $m)
				{
				$seq_breakdown = preg_split('/[\s]/', ltrim($m));	
				//echo $seq_breakdown[0] . " | " . $seq_breakdown[1] . "<br><br>";
				$output_char_groups[$seq_breakdown[0]] = $seq_breakdown[1];
				
				}
		
		//echo '<h2> Taxa Labels </h2><p>Taxa labels are contained in the array $output_taxa_groups with numerical keys</p><br><br>';
		//print_r($output_taxa_groups);		
		//echo '<h2> Taxa Characters  </h2><p>Taxa labels are contained in the array $output_char_groups with Taxa Labels as <u>KEYS</u></p><br><br>';
		//print_r($output_char_groups);
		//echo '<br><hr><br>'; 
		
	//End of characters

	$data_output['taxalabels'] = $output_taxa_groups;
	$data_output['taxachars'] = $output_char_groups;
	
	return $data_output;
	}
	
function read_nexus_data()
	{

	//echo '<h1>Open ops and reactivate read_nexus_data to make bulk updates</h1>';
	
	global $mysqli;
	$sql = $mysqli;

	$dir = glob('nexdir/*.nex');
	
	foreach($dir as $x)
		{		


		$data = Sequence_Updater($x);
			
			echo 'Setting the following sequence IDs as Not New';
			
			foreach($data['taxalabels'] as $t)
				{
				$u = explode('_', $t);
			
					echo $u[0] . ' | ' . $u[1] . '<br>';
						
					$stmt = $sql->prepare("UPDATE MT_SEQUENCE SET NEW = 0, DISCARD = 0, BLAST = 1, BAYES = 1, APPROVER_ID = 1, PUBLIC = 1, SPECIES_ID = ? WHERE ID = ?");
							$stmt->bind_param("ii", $u[1], $u[0]);
							$stmt->execute();
							$stmt->close();
					
				}
		}
	
	}
	
function sandbox_magic()
	{
	#loading Datasets
	
		$seq = file_get_contents("./reference_datasets/nomenclature.fasta");
			$seq_pos = strpos($seq, '>');
			
			$seq = substr($seq, $seq_pos);
			if(substr($seq, 0, 1) != '>')
					{
					echo "<p>This file is not a fasta file. </p>";		
					}
					else
					{
			
			$seq = trim($seq, '>');
			#print_r($seq);
			$seqparts = explode('>', $seq);

		foreach($seqparts as $sequence)
				{
				$sequence = preg_split("/\r\n|\n|\r/", $sequence, 2);
				$mt_sequence = preg_replace('/\s\s+/', '', $sequence[1]);
				$seq_name_parts = explode(".", $sequence[0]);
				$seq_name_parts = array_reverse(explode("_", $seq_name_parts[0]));
				echo "<br>";
				/*
				#CSIRO Genbank
				if(strtoupper($seq_name_parts[0]) == $seq_name_parts[0])
					{
					$genbank_id = $seq_name_parts[0];
					echo $genbank_id . "<hr>";
					}				
				#CSIRO Genbank End	
				*/
				#WFB Genbank
				if(strtoupper($seq_name_parts[1]) == $seq_name_parts[1])
					{
					$genbank_id = $seq_name_parts[1];
					echo $genbank_id . " - 1 - ";
					}
					elseif(strtoupper($seq_name_parts[2]) == $seq_name_parts[2])
						{
						$genbank_id = $seq_name_parts[2];	
						echo $genbank_id . " - 2 - ";
						}
						else
							{
							print_r($seq_name_parts);
							echo " - 3 - ";
							}
							
				#WFB Genbank End
				
						
	global $mysqli;
	$sql = $mysqli;				
	#$stmt = $sql->prepare("INSERT IGNORE INTO MT_GLOBAL_QA (SUB_NAME, GENBANK_ID, MT_SEQUENCE, SOURCEDB) VALUES (?, ?, ?, 'CSIRO')");
	#$stmt->bind_param("sss", $genbank_id, $genbank_id, $mt_sequence);
	#$stmt->execute();
	#$stmt->store_result();
	#$result = print_r($stmt);
	#$stmt->free_result();
#				echo $sequence[0] . ' Uploaded Sucessfully <br>';
#	$stmt->close();				
				
	
	
	unset($genbank_id);
				}
				echo 'All Operations have completed. Please click My Data > Sequences to view the uploaded files.';
				}
	
	
	
	/*
	###Blasting Datasets
	
		// Generate Blast Files

	$fields = array('id' => '1', 'api_key' => '1234567890');
	
// Create file from database START

	global $mysqli;
	$stmt = $mysqli->prepare("SELECT MT.ID, SP.ID, SP.SPECIES, MT.GENBANK_ID, MT.MT_SEQUENCE, MT.SUB_NAME, MT.GLOBAL_POSITION FROM MT_SEQUENCE MT JOIN SPECIES SP ON SP.ID = MT.SPECIES_ID 
	WHERE GENBANK_ID = 'AY521259' OR GENBANK_ID = 'AM176573' OR GENBANK_ID = 'AM176570' OR GENBANK_ID = 'AY563695' OR GENBANK_ID = 'AJ748390'");
	$stmt->execute();
	$stmt->bind_result($id, $speciesid, $species, $genbank_id, $sequence, $subname, $global_position);
	$db_array = array();
	$tofile = '';
		
while($stmt->fetch())
	{

	$result = '';
	$result = $id;
	
	$tofile = $tofile . '>' . $result . "\n" . $sequence . "\n";

	$db_array[$id] = array('SpeciesID' => $speciesid, 'SpeciesName' => $species, 'GlobalPosition' => $global_position);
	
	}	
	file_put_contents('blastdir/newfile.fasta', $tofile);
	
	//print_r($db_array);
	
// Create File code END 

// Create file of sequences that need to be blasted START
	//$stmt = $mysqli->prepare("SELECT MT.ID, MT.GENBANK_ID, MT.MT_SEQUENCE, MT.SUB_NAME, SP.SPECIES FROM MT_SEQUENCE MT JOIN MT_SEQUENCE MT2 ON MT.SEQ_MATCH = MT2.ID JOIN SPECIES SP ON SP.ID = MT2.SPECIES_ID WHERE MT.BLAST != '1'");
	$stmt = $mysqli->prepare("SELECT MT.ID, MT.MT_SEQUENCE, MT.SUB_NAME FROM MT_GLOBAL_QA MT");
	$stmt->execute();
	$stmt->bind_result($id, $sequence, $subname);
	$db_array_toblast = array();
	$toblast = '';
while($stmt->fetch())
	{
	$result = '';
	$result = $id;
	
	$toblast = $toblast . '>' . $result . "\n" . $sequence . "\n";

	$db_data = array('SequenceID' => $result, 'Sequence' => $sequence);
	array_push($db_array_toblast, $db_data);
	unset($db_data);
	}	
	file_put_contents('blastdir/newblast.fasta', $toblast);	
	
	//print_r($db_array_toblast);

// Create File code END 	
	*/
		
				
/* Depreciated				
			echo '<p><h3>Blast Results</h3></p>';
	$fields = array('id' => '1', 'api_key' => '1234567890');

		$dbsource = curl_file_create('blastdir/wfly_reference.fa');
		$dbquery = curl_file_create('blastdir/' . $_COOKIE['PHPSESSID'] . '.fa', 'text/plain', $_COOKIE['PHPSESSID'] . '.fa');
*/
/*
		$dbsource = curl_file_create('blastdir/newfile.fasta');
		$dbquery = curl_file_create('blastdir/newblast.fasta');
		$data = array('source' => $dbsource, 'query' => $dbquery);
		

		
			$rc = curl_init();
			curl_setopt($rc, CURLOPT_URL, "http://192.168.100.113/connect.php");
			curl_setopt($rc, CURLOPT_POST, 1);
			curl_setopt($rc, CURLOPT_POSTFIELDS, $data);
			curl_setopt($rc, CURLOPT_SAFE_UPLOAD, TRUE);
			curl_setopt($rc, CURLOPT_RETURNTRANSFER, TRUE);
			
			$result = curl_exec($rc);
			$res = curl_error($rc);
			curl_close($rc);
			
			$stuff = json_decode(strstr($result, '['), true);
			if(is_array($stuff))
			{
			$array_sort_key = array();
			
			foreach($stuff as $x)
			{
			$array_sort_key[$x['RowID']] = $x['PCIdent'];
			}
			arsort($array_sort_key);

	echo '<table><tr><td>QueryID</td><td>SubjectID</td><td>PCIdent</td><td>Length</td><td>Mismatch</td><td>Blast Defined Species</td></tr>';
			foreach(array_keys($array_sort_key) as $i)
			{
			$threshold = 70.00;
			
			if($stuff[$i]['PCIdent'] > $threshold)
			{
			$col_header = '<td class="blue_column">';
			}
			else
				{
				$col_header = '<td>';
				}
				
			echo '<tr>	
					' . $col_header . $stuff[$i]['QueryID'] . '</td>
					' . $col_header . $stuff[$i]['SubjectID'] . '</td>
					' . $col_header . $stuff[$i]['PCIdent'] . '%</td>
					' . $col_header . $stuff[$i]['Length'] . '</td>
					' . $col_header . $stuff[$i]['Mismatch'] . '</td>
					' . $col_header . $db_array[$stuff[$i]['SubjectID']]['SpeciesName'] . '</td>					
			</tr>';
			unset($col_header);
			}
	echo '</table><br><br>';				

	//Start SQL

	foreach(array_keys($array_sort_key) as $i)
			{
	global $mysqli;
	$sql = $mysqli;
	
	$query = $stuff[$i]['QueryID'];
	$subject = $stuff[$i]['SubjectID'];
	$percent = ($stuff[$i]['PCIdent'] / 100);
	$spid = $db_array[$stuff[$i]['SubjectID']]['SpeciesID'];
	$qstart = $stuff[$i]['QueryStart'];
	$qend = $stuff[$i]['QueryEnd'];
	$sstart = $stuff[$i]['SeqStart'];
	$send = $stuff[$i]['SeqEnd'];
	$glid = ($db_array[$stuff[$i]['SubjectID']]['GlobalPosition'] - ($stuff[$i]['QueryStart'] - 1)) + ($stuff[$i]['SeqStart'] - 1);	
	if($percent == 1.00)
		{
		$species = $spid;
		$public = 1;
		}
		else
			{
			$species = NULL;
			$public = 0;
			}
			
if($qstart > $qend || $sstart > $send)
{	
	echo "<h2>Rerun required " . $query . "</h2>";
	$stmt = $sql->prepare("UPDATE MT_GLOBAL_QA SET MT_SEQUENCE = revcomp(MT_SEQUENCE) WHERE ID = ?");
	$stmt->bind_param("i", $query);
	$stmt->execute();
	$stmt->store_result();
	$stmt->free_result();

	$stmt->close();
}
	$stmt = $sql->prepare("UPDATE MT_GLOBAL_QA SET SEQ_MATCH = ?, SEQ_MATCH_PC = ?, QUE_START = ?, QUE_END = ?, SEQ_MATCH_START = ?, SEQ_MATCH_END = ?, GLOBAL_POSITION = ? WHERE ID = ?");
	$stmt->bind_param("isiiiiii", $subject, $percent, $qstart, $qend, $sstart, $send, $glid, $query);
	$stmt->execute();
	$stmt->store_result();
	$stmt->free_result();

	$stmt->close();	
	
	unset($query, $subject, $percent, $glid);
			}
			}
			else
				{
				echo $result;
				}
	
	
	
	#Create Alignments
	global $mysqli;
	$gbid = array();
		$stmt = $mysqli->prepare("SELECT DISTINCT GENBANK_ID FROM MT_GLOBAL_QA");
	$stmt->execute();
	$stmt->bind_result($gbk);
	while($stmt->fetch())
	{
	$gbid[] = $gbk;
	}
	$stmt->close();
	
foreach($gbid as $genbank_id)
	{
	$speciesid = 1;
	$limit = 100000;
	$species_name = 'AllSeq';
	global $mysqli;
	echo "Processing - " . $genbank_id;
	
	$trim_point = 693;
	$trim_length = 2000;
	
	$stmt = $mysqli->prepare("SELECT MT.ID, MT.GENBANK_ID, UPPER(MT.MT_SEQUENCE) AS MT_SEQUENCE, MT.SUB_NAME, MT.SEQ_MATCH_START, MT.SEQ_MATCH_END, LENGTH(MT.MT_SEQUENCE) AS LE, MT.SEQ_MATCH, MT.QUE_START, MT.QUE_END, MT.GLOBAL_POSITION, MT.SOURCEDB
	FROM MT_GLOBAL_QA MT WHERE OUTGROUP != 1 AND MT.GENBANK_ID = ? ORDER BY RAND();");
	$stmt->bind_param("s", $genbank_id);
	$stmt->execute();
	$stmt->bind_result($id, $genbankID, $sequence, $subname, $sstart, $send, $length, $smatch, $qstart, $qend, $glid, $sourcedb);
	$db_array = array();
	$tofile = '';
	$spec = array();
	$species_grouped = array();
	$species_name = array();
while($stmt->fetch())
{
	$species = $sourcedb;
	$eval = preg_match('/[^AGCT]/', $sequence);
	$eval = 0;
		if(!isset($spec[$speciesid]))
		{
		$add_array = array($speciesid => 0);
		$spadd_array = array($speciesid => $species);
		$species_group = array($speciesid => array());
		$spec = $spec + $add_array;
		$species_name = $species_name + $spadd_array;
		$species_grouped = $species_grouped + $species_group;
		unset($add_array, $species_group, $spadd_array);
		}
		echo "<br>EVAL - " . $eval . "SPECID - " . $spec[$speciesid] . " | " . $limit;
	if($eval === 0 && $spec[$speciesid] < $limit)
		{
			$update = $spec[$speciesid] + 1; 
			$spec[$speciesid] = $update;

	//PADDING FINISH
	//Align and Trim Start
	//PADDING START
	if(($trim_point - $glid) < 0)
	{
	$pad_left = $length + $glid;	
	$sequence = str_pad($sequence, $pad_left, "-", STR_PAD_LEFT);
	unset($pad_left);
	$trim_left = 0;
	}
	else
		{
		$trim_left = $trim_point - $glid;
		}

	$trim_right = $trim_length;
	
	$sequence = substr($sequence, $trim_left, $trim_right);
	//Align and Trim Finish
	
	$result = '';
	$result = $id;
	$seq_result = '';
	$seq_split = str_split($sequence, 71);
	
	foreach($seq_split as $n)
	{
	$seq_result = $seq_result . "\n" . $n;
	}
	
	$tofile = $tofile . '>' . $result . "_" . $speciesid . $seq_result . "\n";
	
	array_push($species_grouped[$speciesid], array($result . "_" . $speciesid . "_" . $species . "_" . $genbankID, $sequence));
	
	$seqlength = strlen($seq_result);

	$db_array[$id] = array('SpeciesID' => $genbankID, 'SubmissionName' => 'APLACE');
	
	unset($seq_result, $seqlength, $eval);
		}
	}
	foreach($species_grouped as $k => $x)
	{
	$sptofile = '';
		foreach($x as $y)
		{
		$sptofile = $sptofile . '>' . $y[0] . "\n" . $y[1] . "\n";	
		}
	file_put_contents('blastdir/THELIST_' . $genbank_id . '.fasta', $sptofile);
	unset($sptofile);
	}
	
	//file_put_contents('blastdir/alignment_altr.fasta', $tofile);	
	//print_r($spec);
	//echo "<br><br>";
	//print_r($species_grouped);
	print_r($tofile);

	}
	*/
	
	
	//Create_Alignment();
	
	
	}


?>