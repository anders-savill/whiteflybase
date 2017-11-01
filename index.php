<?php
session_start();

if(isset($_SESSION['Auth']))
	{
	if($_SESSION['Auth'] == 1)
		{
	//echo "Logged In";
		}
	}
	else
		{
		$_SESSION['Auth'] = 0;
		$_SESSION['Admin'] = 0;
		}
include("pages/header.php");
include("sqlconnect.php");       
echo '
	

		
 
<div id="maincontent"><br>
      <div class="row" id="secondcontent">
  		<div class="large-12 columns">
		<h3>Welcome to Whiteflybase</h3>
		<p>Whiteflybase is a <i>Bemisia tabaci</i> species identification tool designed at the University of Western Australia.</p><p>All 5’ mtCOI sequences that are used for identification are sourced from Genbank and are identified using Blast and Bayesian Phylogenetic methods. </p><p> For more information about the methods please see <a href="https://f1000research.com/articles/6-1835/v1">Updated mtCOI reference dataset for the <i>Bemisia tabaci</i> species complex.</a></p><p>To use the application you will need to login and create an account. You can <a onclick="loadRegister()">Register Here.</a></p><p> Click here to get the <a href="../blogstatic/Whiteflybase_Getting_Started.pdf">Quick Start Guide</a></p>
		<p id="canvasp">
		<canvas id="myChart"></canvas>
		</p>
        
 </div>   
 </div>   
 </div>    
      <footer class="row">
        <div class="large-12 columns">
          <hr/>
          <div class="row">
            <div class="large-12 columns">
              <p align="center">Whiteflybase - <a href="ap.txt">Open Access Policy</a></p>
			  <p align="center"><a rel="license" href="http://creativecommons.org/licenses/by/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by/4.0/80x15.png" /></a><br />This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by/4.0/">Creative Commons Attribution 4.0 International License</a>.</p>
            </div>
			<div class="row">
            <div class="large-12 columns">
              <ul class="inline-list right">
			  </ul>
            </div>
			</div>
          </div>
        </div> 
      </footer>
 </div>
    </div>
</div> 
    <script src="js/vendor/jquery.js"></script>
    <script src="js/foundation.min.js"></script>
	<script src="js/jquery.dataTables.min.js"></script>	
	<script src="js/dataTables.colReorder.js"></script>
	<script>
    $(document).foundation();
	</script>
	<script type="text/javascript" src="js/biocore-js.js"></script>
	<script type="text/javascript" src="js/msa.min.gz.js"></script>
	
	<script>
	';
	if($_SESSION['Auth'] == 0)
	{
	echo '';
	}
	else
		{
		echo 'loadNoobNavigation();';
		}
	echo '
	</script>
</body>
</html>
';

?>