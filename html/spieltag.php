<?php include("../php/auth.php"); ?>
<!DOCTYPE html>
<html>

<head>

 	<title>FANTASY BUNDESLIGA</title> 

	<meta name="robots" content="noindex">
	<meta charset="UTF-8">   

	<link rel="stylesheet" type="text/css" media="screen, projection" href="../css/spieltag.css">
	<link rel="stylesheet" type="text/css" media="screen, projection" href="../css/nav.css">
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
   
	<!-- Custom scripts -->
	<script type="text/javascript" src="../js/spieltag-display-fixtures.js"></script>
	<script type="text/javascript" src="../js/spieltag-clickable-fixtures.js"></script>

</head>

<body>

<!-- Header image -->
<header>
	<?php require "header.php"; ?>
</header>

<?php include("navigation.php"); ?>

<main>
	
<div id = "hilfscontainer">
	<div class="flex-grid-fantasy"> 
		<div class="col">
			
			<div id="spieltag_tabelle"style="overflow-y: auto;">
				<!-- Fixture table is loaded here through spieltag-display-fixtures.js -->				
			</div>
			
			<!-- Button for choosing a round -->
			<div class="button">
					<select onfocus='this.size=10;' onblur='this.size=1;' onchange='this.size=1; this.blur();'>
						<option value="1">1. Spieltag</option>
						<option value="2">2. Spieltag</option>
			      <option value="3">3. Spieltag</option>
						<option value="4">4. Spieltag</option>
						<option value="5">5. Spieltag</option>
			      <option value="6">6. Spieltag</option>
			      <option value="7">7. Spieltag</option>
						<option value="8">8. Spieltag</option>
						<option value="9">9. Spieltag</option>
			      <option value="10">10. Spieltag</option>
						<option value="11">11. Spieltag</option>
						<option value="12">12. Spieltag</option>
			      <option value="13">13. Spieltag</option>
			      <option value="14">14. Spieltag</option>
			      <option value="15">15. Spieltag</option>
						<option value="16">16. Spieltag</option>
						<option value="17">17. Spieltag</option>
			      <option value="18">18. Spieltag</option>
			     	<option value="19">19. Spieltag</option>
			      <option value="20">20. Spieltag</option>
			      <option value="21">21. Spieltag</option>
			      <option value="22">22. Spieltag</option>
			      <option value="23">23. Spieltag</option>
			      <option value="24">24. Spieltag</option>
			      <option value="25">25. Spieltag</option>
			      <option value="26">26. Spieltag</option>
			      <option value="27">27. Spieltag</option>
			      <option value="28">28. Spieltag</option>
			      <option value="29">29. Spieltag</option>
			      <option value="30">30. Spieltag</option>
			      <option value="31">31. Spieltag</option>
			      <option value="32">32. Spieltag</option>
			      <option value="33">33. Spieltag</option>
			      <option value="34">34. Spieltag</option>
					</select>
				</div>
			</div>		
		</div>	
	</div>
</main>	
</body>
</html>