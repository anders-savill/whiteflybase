<?php
header("Content-type: text/css");
#To be created
$blast_assay_submitted_color = '#b0ff99';
$navbar_color = '#4d4d4d';
$menu_text = '#FFFFFF';
$sbtimage = '#d9d9d9';
$loginbuttoncolor = '#88BBD6';
$overtextcolor = '#4d4d4d';
echo '
#nli_banner
	{

	}

.cross_red
	{
	color: red;
	}

.check_green
	{
	color: green;
	}
	
#userform input
	{
	width: 80%;
	display: inline-flex;
	}

#secondcontent.row
	{
	width: 95%;
	max-width: 100%;
	}

#maincontent
	{
	color: ' . $overtextcolor . ';
	}
	
#maintable_filter
{
	float:left;
	text-align:left;
}	

#maintable_filter input
	{
	margin-left: 0;
	
	}

#global_functions
	{
	float: right;
	text-align: right;
	}
	
#mysequencesubnamespan
	{
	display: block;	
	width: 300px;
	overflow-wrap: break-word;
	}
	
#mysequencediagnostic
	{
	display: block;	
	width: 190px;
	}

#mysequencespeciesname	
	{
	display: block;	
	width: 150px;
	}

#offCanvas
	{
	padding-left: 10px;
	padding-top: 10px;	
	background-color: ' . $navbar_color . ';	
	height: 100%;
	color: ' . $menu_text . ';
	}

	#maint_box
	{
	text-align: center;	
	}
	
td#flags
	{
	white-space: nowrap;	
	}
	
span#fright
	{
	float: right;
	}
	
span#fleft
	{
	float: left;
	}	
	
span.checkmarkspan
	{
	color: green;	
	}

span.crossmarkspan
	{
	color: red;	
	}
	
#sidebartoggle
	{
	width: 100px;
	float: left;	
	}
	
#sbtimage	
	{
	padding-top: 10px;
	padding-bottom: 10px;
	padding-right: 10px;
	padding-left: 10px;
	width: 50px;
	background-color: ' . $sbtimage . ';

	}

td.blue_column
	{
	background-color: ' . $blast_assay_submitted_color . ';
	}

.top-bar
	{
	background-color: ' . $navbar_color . ';
	}
	
#loginbutton, #registerbutton
	{
	border-style: solid;
	border-color: ' . $navbar_color . ';	
	background-color: ' . $loginbuttoncolor . ';
	}
	
.top-bar ul
	{
	background-color: ' . $navbar_color . ';
	}

.dropdown.menu .is-dropdown-submenu-parent.is-down-arrow > a::after 
	{	
	border-color: ' . $menu_text . ' transparent transparent;
	}
.top-bar a
	{
	color: ' . $menu_text . ';
	font-family: "Arial", Helvetica, sans-serif;	
	}

.submenu a
	{
	background-color: ' . $navbar_color . ';
	}	

.menu-text
	{
	color: ' . $menu_text . ';
	}

.dropdown.menu > li.is-dropdown-submenu-parent > a::after
	{
	border-color: #FFFFFF transparent transparent;
	}

#canvasp
	{
    display: block;
    margin: auto;
    width: 70%;
	}
	
#login_helper_alert	
	{
	position: relative;
	float: right;
	margin: 8px;
	padding: 4px;
	}

	';	
?>