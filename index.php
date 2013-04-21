<html>

	<head>
	<title>Typo of the day</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	</head>

	<body>
	
	
	<?php
		#ini_set('display_errors','On');
		#error_reporting(E_ALL);
		if ($_POST['submit']){
			
			$conn = pg_connect('host=host.address port=1032 user=username password=password dbname=iii');
			 if (!$conn) {
				 die("Error in connection: " . pg_last_error());
			 } 
			
			$sel_wrongWord = $_POST['wrongWord'];
			$sel_rightWord = $_POST['rightWord'];
			$sel_searchType = $_POST['searchType'];
			
			if ($sel_searchType == 'full') {
				$results = pg_query_params($conn, "SELECT record_id FROM sierra_view.phrase_entry WHERE index_tag='t' AND varfield_type_code='t' AND type3='' AND index_entry ~* $1 AND index_entry !~* $2", array('(^|\s)'.$sel_wrongWord.'\s', '(^|\s)'.$sel_wrongWord.'\ssic\s'));
				} elseif ($sel_searchType == 'partial') {
				$results = pg_query_params($conn, "SELECT record_id FROM sierra_view.phrase_entry WHERE index_tag='t' AND varfield_type_code='t' AND type3='' AND index_entry ~* $1 AND index_entry !~* $2", array('(^|\s)'.$sel_wrongWord.'[\w]*', '(^|\s)'.$sel_wrongWord.'[\w]*\ssic\s'));
				} else {
				exit('<a href="index.php">Please choose a search type.</a>');
			}

			if (!$results) {
				die("Error in SQL query: " . pg_last_error());
			}
  
			#start printing table code, for those who want to tweak custom queries in the script
			$number_result = pg_num_rows($results);
			
			print "
			<p>
			<strong>($number_result records)</strong> List of results below...
			<br />
			<table border='1'>
			";

			while ($row = pg_fetch_row($results)) {
				echo "<tr>";
				for ($i=0; $i<pg_num_fields($results); $i++) {
					echo "<td>";
					echo "$row[$i]";
					echo "</td>";
			}
				echo "</tr>\n";
			}
			echo "</table>";
			#end printing table code
			
			#again, another debugging line to check to see if the form values are being passed into the script
			#echo "<br /><p>The right word is $sel_rightWord, and this is a $sel_searchType search.</p>"; 
			
			#creating output file 
			$my_file = $sel_wrongWord . '.out';
			$handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
			#alas, no API for the script to work with... so just fetching the results in an associative array.
			$array = pg_fetch_all($results);
			fwrite($handle, var_export($array, true));
			fclose($handle);
			
			#self-explanatory
			echo "<p>And now for the output file, edited by the pymarc script... since there is no API to pull out the records from the system via script, the pymarc script will return a file that has the record ids. Once the API is out, you can edit the editrecord.py script to edit the records.</p>";
			
			#python command! Make sure that the editrecord.py script is in the same directory as index.php
			$command = "python editrecord.py $sel_rightWord $sel_wrongWord $sel_searchType $my_file";
			$temp = exec($command, $output);
			
			#for now, placeholder link 
			echo "<a href=$my_file>Download file</a>";

			pg_free_result($results);
			
			pg_close($conn);
		}
	?>
	
	
		<h1>Typo of the day</h1>

		
		<h2>Get rid of your mispelled[sic] title words in one click.</h2>
		
			<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
				What is the offending word? <input type="text" name="wrongWord"><br />
				<input type="radio" name="searchType" value="partial">Search for a partial match<br />
				<input type="radio" name="searchType" value="full">Search for a full match<br />
				What is the correct word? <input type="text" name="rightWord">
				<input type="submit" name="submit">
			</form>		
			
		<p>Looking for some examples? Try <em>fom</em> for "from" (full search) or <em>preperat</em> for "preparat" (partial search).</p>

			
	</body>
</html>
