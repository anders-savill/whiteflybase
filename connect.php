<?php
//This is the web services server.
			//phpinfo();
$fields = array('id' => '1', 'api_key' => '1234567890');

		$dbsource = curl_file_create('blastdir/wfly_reference.fa');
		$dbquery = curl_file_create('blastdir/wfly_query.fa', 'text/plain', 'wfly_query.fa');

		$data = array('file' => $dbsource, 'file2' => $dbquery);
		
			$rc = curl_init();
			curl_setopt($rc, CURLOPT_URL, "http://146.118.97.250/connect.php");
			curl_setopt($rc, CURLOPT_POST, 1);
			curl_setopt($rc, CURLOPT_POSTFIELDS, $data);
			curl_setopt($rc, CURLOPT_SAFE_UPLOAD, TRUE);

			$result = curl_exec($rc);
			$res = curl_error($rc);
			curl_close($rc);
			print_r($res);
			echo $result();
			
			//curl_setopt($rc, CURLOPT_INFILE, $dbsource);
			//curl_setopt($rc, CURLOPT_POSTFIELDS, http_build_query($data));
			//curl_setopt($rc, CURLOPT_RETURNTRANSFER, TRUE);			

?>