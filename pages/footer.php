<?php

#Head plus Nav

echo '
<!doctype html>
<html class="no-js" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Welcome to BioCore</title>
    <link rel="stylesheet" href="css/foundation.css" />
    <link rel="stylesheet" href="slickticker/slick/slick.css" />	
    <link rel="stylesheet" href="slickticker/slick/slick-theme.css" />	
	<link rel="stylesheet" href="css/biocore-css.php" />
    <script src="js/vendor/modernizr.js"></script>
    </head>
  <body>
<nav class="top-bar" data-topbar role="navigation">
  <ul class="title-area">
    <li class="name">
      <h1><a href="#">Navigation</a></h1>
    </li>
     <!-- Remove the class "menu-icon" to get rid of menu icon. Take out "Menu" to just have icon alone -->
    <li class="toggle-topbar menu-icon"><a href="#"><span>Menu</span></a></li>
  </ul>

  <section class="top-bar-section">
    <!-- Right Nav Section -->
    <ul class="right">
	<li><a href="#" onclick="loadDB();">Login</a></li>
    </ul>

    <!-- Left Nav Section -->
    <ul class="left">
      <li><a href="#" onclick="loadSequences();">The Database</a></li>
    </ul>
  </section>
</nav>


	';

?>