<?php
if(isset($_SESSION['Auth']))
	{
	if($_SESSION['Auth'] == 1)
		{
		//Nothing
		}
		else
			{
			$banner = '<img src="banner.jpg" id="nli_banner"></a>';
			}
	}

$title = 'Whiteflybase 2.0';	
	
#Head plus Nav
echo '
<!doctype html>
<html class="no-js" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>';echo $title;echo'</title>
	<link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/foundation.css" />
	<link rel="stylesheet" href="css/jquery.dataTables.min.css" />
	<link rel="stylesheet" href="css/biocore-css.php" />
	<script src="js/chartjs/Chart.min.js"></script>
	<script src="js/vendor/modernizr.js"></script>
    </head>

  <body>

';

if(isset($_SESSION['Auth']))
		{
		if($_SESSION['Auth'] != 1)
			{
			echo $banner;
			}
		}
echo '
<div class="top-bar">
';

if($_SESSION['Auth'] == 1)
	{	
	echo '
	 <div class="top-bar-left">
		<ul class="dropdown menu" data-dropdown-menu>
		<li class="menu-text">WhiteflyBase</li>
			<li><a onclick="loadSequences();">The Database</a></li>
				<li class="has-submenu"><a href="#" onclick="loadMySequences();">My Data</a>
					<ul class="submenu menu vertical" data-submenu>
						<li><a href="#" onclick="upload_sequences();">Upload</a></li>
						<li><a href="#" onclick="loadMySequences();">Sequences</a></li>
						<!-- <li><a href="#" onclick="blast_sequences();">Blast Sequences</a></li> -->						
						<li><a href="#" onclick="loadLists();">Lists</a></li>
					</ul> 
				</li>
<li class="has-submenu"><a href="#">I Want To</a>
					<ul class="submenu menu vertical" data-submenu>
						<li><a href="#" onclick="upload_sequences();">Upload a new Fasta File</a></li>
						<li><a href="#" onclick="loadMySequences();">Review Previous Queries</a></li>
						<li><a href="#" onclick="loadLists();">Create a List</a></li>
					</ul> 
				</li>				
				
	<li class="has-submenu"><a href="#">Sites</a>
					<ul class="submenu menu vertical" data-submenu>
						<li><a href="blogstatic">Downloads</a></li>
						<li><a href="http://devblog.whiteflybase.org">Devblog</a></li>
						<li><a href="http://www.lauraboykinresearch.com/">Boykin Lab</a></li>
					</ul> 
				</li>				
	';
	}
if($_SESSION['Admin'] == 1)
	{
		echo '<li class="has-submenu"><a href="#" onclick="loadMySequences();">Admin Functions</a>
					<ul class="submenu menu vertical" data-submenu>
						<li><a href="#" onclick="loadGenbankXML();">Read Genbank XML</a></li>	
						<li><a href="#" onclick="blastAssay();">Run Blast Assay</a></li>
						<li><a href="#" onclick="statistics();">Statistics</a></li>	
						<li><a href="#" onclick="export_alignments(' . "'0'" . ');">Export Alignments</a></li>							
						<li><a href="#" onclick="load_sandbox();">Sandbox</a></li>							
					</ul> 
				</li>';
	}
if($_SESSION['Auth'] == 1)
	{	
	echo '</ul>	
	</div>
	';
	
	
	}

if($_SESSION['Auth'] != 1)
	{
	echo '
	 <div class="top-bar-left">
		<ul class="dropdown menu" data-dropdown-menu>
			<li class="menu-text">WhiteflyBase</li>
			<li><a onclick="loadSequences();">The Database</a></li>
	<li class="has-submenu"><a href="#">Sites</a>
					<ul class="submenu menu vertical" data-submenu>
						<li><a href="blogstatic">Downloads</a></li>
						<li><a href="http://devblog.whiteflybase.org">Devblog</a></li>						
						<li><a href="http://www.lauraboykinresearch.com/">Boykin Lab</a></li>						
						
					</ul> 
				</li>
		</ul>
	</div>		
	<div class="top-bar-right">
	<ul class="menu">
	<li role="menuitem" id="helper_anchor"><b></b></li>
	<li role="menuitem" id="registerbutton"><a onclick="loadRegister();"><b>Register</b></a></li>
	<li role="menuitem" id="loginbutton"><a onclick="loadLogin();"><b>Login</b></a></li>
	</ul>	
	</div>
		';
		echo '</div>
			  <div class="callout secondary" id="login_helper_alert">To get started click the blue login button above</div>
			  ';
	}
if($_SESSION['Auth'] == 1)
	{
		
	echo '
	<div class="top-bar-right">
	<ul class="menu">
	<li><a onclick="loadLogin();">' . $_SESSION['email'] . '</a></li>
	</ul>
	</div>
	</div>';
//DISABLED

	echo '
<div class="reveal" id="data-reveal" data-reveal>

	  </div>	
		';	
		
	}
$maintenance = 0;
$maint_message = 'Backend blast services are currently unavailable due to a failed server upgrade.';

if($maintenance == 1)
	{
	echo '

	<div class="callout small alert" id="maint_box"><b>Live Maintenance:</b> ' . $maint_message . '</div>

	';
	}

?>