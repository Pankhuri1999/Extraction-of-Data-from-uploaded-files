<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Example of reading excel file</title>
	<script src="js/jquery.min.js"></script>
  </head>
  <body>

	<body>
		
		<h1 align="center">Extracting data from uploaded file</h1><br>
			<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" enctype="multipart/form-data">
				<ul>
				<li>Choose your excel file by clicking button - "Choose File"</li>
				<li> After that click on "upload" button</li>
				<li> Only files with extensions xls and xlsx are allowed.</li>
				</ul>
				<br>
				<input type="file" name="uploadFile" value="" />
				<input type="submit" name="submit" value="Upload" />
			</form>
		
		
			<?php
			if(isset($_POST['submit'])) {
				if(isset($_FILES['uploadFile']['name']) && $_FILES['uploadFile']['name'] != "") {
					$extension = array("xls","xlsx");               // only these extensions which will only be considered for an input
					$ext = pathinfo($_FILES['uploadFile']['name'], PATHINFO_EXTENSION);
					if(in_array($ext, $extension)) {
						$memoryOfFile = $_FILES['uploadFile']['size'] / 1024;
						if($memoryOfFile < 50) {
							$file = "uploads/".$_FILES['uploadFile']['name'];
							$uploadTrue = copy($_FILES['uploadFile']['tmp_name'], $file);
							if($uploadTrue) {
								include("db.php");
								include("Classes/PHPExcel/IOFactory.php");
								try {
									//Load the excel(.xls/.xlsx) file
									$objPHPExcel = PHPExcel_IOFactory::load($file);
								} catch (Exception $e) {
									die('Error loading file "' . pathinfo($file, PATHINFO_BASENAME). '": ' . $e->getMessage());
								}
									
								
								$sheet = $objPHPExcel->getSheet(0);  // here we have specified which sheet will be working on if excel file contains more than one sheet

								$total_rows = $sheet->getHighestRow();

								$highest_column = $sheet->getHighestColumn();
								
								echo '<h4>Data extracted from your uploaded excel file</h4>';
								echo '<table cellpadding="1" cellspacing="5"  class="responsive">';
								
								$query = "insert into `sample_file` (`s.no`, `col1`, `col2`, `col3`, 'col4') VALUES ";
					
								for($row =2; $row <= $total_rows; $row++) {

									$single_row = $sheet->rangeToArray('A' . $row . ':' . $highest_column . $row, NULL, TRUE, FALSE);
									echo "<tr>";

									$query .= "(";

									foreach($single_row[0] as $key=>$value) {
										echo "<td>".$value."</td>";
										$query .= "'".mysqli_real_escape_string($con, $value)."',";
									}
									$query = substr($query, 0, -1);
									$query .= "),";
									echo "</tr>";
								}
								$query = substr($query, 0, -1);
								echo '</table>';
								

								mysqli_query($con, $query);
								if(mysqli_affected_rows($con) > 0) {
									echo '<span class="msg">Database table updated!</span>';
								} else {
									echo ''; }
								unlink($file);
							} else {
								echo '<span class="msg">Please upload your file first</span>'; }
						} else {
							echo '<span class="msg">Your file should not be more than 50 KB</span>';	 }
					} else {
						echo '<span class="msg">File name with this extension is not allowed</span>'; }
				} else {
					echo '<span class="msg">Please select an excel file before clicking upload</span>'; }
			}
			?>
			
</body>