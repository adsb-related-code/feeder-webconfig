<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="../css/bootstrap.min.css" rel="stylesheet">
<script src="../js/bootstrap.min.js"></script>
<style>
	.btn-margin-bottom {
		margin-bottom: 5px !important;
	}

	body {
		background-color: #343434;
		color: #FFF;
	}

	.adsbx-green {
		color: #FFF;
	}

	.container-margin {
		padding: 5px 10px !important;
	}

	.logo-margin {
		padding: 10px 0px !important;
	}

	.btn-primary {
		/*width: 325px;*/
		padding: 10px;
		text-align: left;
		color: #fff;
		border-color: #545454;
		background-color: #828282;
	}

	.alert-success {
		color: #686868;
		font-weight: 900;
		background-color: #29d682;
		border-color: #828282;
	}

	.min-adsb-width {
		/*width: 325px;*/
	}

	.container-padding {
		padding: 5px;
	}
</style>

<script type="text/javascript">

function checkcoords() {
        var resp ;
        var xmlHttp ;

		var lat = document.forms['configform'].elements['LATITUDE'].value;
		var lon = document.forms['configform'].elements['LONGITUDE'].value;

		url =  'https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=' + lat + '&longitude=' + lon + '&localityLanguage=en';

        resp  = '' ;
        xmlHttp = new XMLHttpRequest();

        if(xmlHttp != null)
        {
            xmlHttp.open( "GET", url, false );
            xmlHttp.send( null );
			resp = xmlHttp.responseText
        }

		var locdata = JSON.parse(resp);
        thealert = "Location is:\n" + locdata.locality + ", " + locdata.principalSubdivisionCode + ",\n" + locdata.countryName;
		//alert(thealert);
		return thealert;
    }

</script>

<?php 
session_start();
if ($_SESSION['authenticated'] != 1) {
	$_SESSION['auth_URI'] = $_SERVER['REQUEST_URI'];
	header("Location: ../auth"); 
}
?>

</head>
<body>
<center>


			<h4 class="adsbx-green logo-margin"><img src="../img/adsbx-svg.svg" width="35"/>  ADSBexchange.com</h4>
			<h6>ADSBX ADS-B Anywhere <br />version 8.0</h6>
			<a class="btn btn-primary" href="../">(..back to main menu)</a><br /><br />


<form method='POST' name="configform" action="./index.php" onsubmit="return confirm(checkcoords() + '\nSave configuration and reboot?');">


 <?php 

 if (!empty($_POST["DUMP1090"])) {
	$content=file_get_contents("/home/pi/adsbexchange/webconfig/adsb-config.txt.webtemplate");

	foreach ( $_POST as $key => $value ) {
		//echo $key . ': ' . $value;
		//echo '<br>';
		//echo "%%" . $key . "%%";
		//echo '<br>';
		$content_chunks=explode("%%" . $key . "%%", $content);
		$content=implode($value, $content_chunks);

	}

	file_put_contents("/tmp/webconfig/adsb-config.txt", $content);

	?>
	<script type="text/javascript">
	var timeleft = 70;
	var downloadTimer = setInterval(function(){
	if(timeleft <= 0){
		clearInterval(downloadTimer);
		window.location.replace("../index.php");
	}
	document.getElementById("progressBar").value = 70 - timeleft;
	timeleft -= 1;
	}, 1000);
	</script>
	<progress value="0" max="70" id="progressBar"></progress>

	<?php
	echo '<p>Rebooting... visit <a href="../index.php">this link</a> to verify changes in about 70 secs..</form></body></html>';
	system('sudo /home/pi/adsbexchange/webconfig/install-adsbconfig.sh > /dev/null 2>&1 &');
	exit;
}

//echo $content;

$lines = file('/boot/adsb-config.txt');
?>
<div class="container col-10">
<table class="table table-striped table-dark">
<?php
foreach($lines as $line) {
	$pos = strpos($line, "=");
	if (substr($line, 0, 1) == '#') { $pos = false; } # If line is a comment, don't consider it a parameter even if an equal sign comes later.
	if ($pos === false) {
		$result .= $line;
		if (substr($line, 1, 1) == '#') { # if 2nd character is also #, this will be a continuation of comments to be displayed with field.
			$prevline = $prevline . substr($line, 2);
		} else {
			$prevline = substr($line, 2);
		}
	} else {
			echo '<tr><td>';
			$key = 	substr($line, 0, $pos);
			$value = substr($line, $pos + 1);
			$value = trim(str_replace('"', "", $value));

			echo $prevline;
			echo '<br>';
			if (strtolower($value) == 'yes') {
				?><select class="form-control" name="<?php echo $key; ?>">
					<option value="yes" selected>yes</option>
					<option value="no">no</option>
				</select><?php

			} elseif (strtolower($value) == 'no') {
				?><select class="form-control" name="<?php echo $key; ?>">
					<option value="yes">yes</option>
					<option value="no" selected>no</option>
				</select><?php

			} elseif ($key == "GAIN") {
				?><select class="form-control" name="<?php echo $key; ?>"><?php
				$gainoptions = array(-10, 0.0, 0.9, 1.4, 2.7, 3.7, 7.7, 8.7, 12.5, 14.4, 15.7, 16.6, 19.7, 20.7, 22.9, 25.4, 28.0, 29.7, 32.8, 33.8, 36.4, 37.2, 38.6, 40.2, 42.1, 43.4, 43.9, 44.5, 48.0, 49.6);

				foreach ($gainoptions as $gainval) {
					?><option value="<?php echo $gainval; ?>" <?php if ($gainval == $value) { echo 'selected'; } ?>><?php echo $gainval ; ?></option>
					<?php
					}
					echo '</select>';

			} elseif ($key == "LATITUDE") {
				?>
				<input class="form-control" type="text" name="<?php echo $key; ?>" value="<?php echo $value; ?>" pattern="[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)"/>
				<?php
					echo '</tr></td>';

			} elseif ($key == "LONGITUDE") {
				?>
				<input class="form-control" type="text" name="<?php echo $key; ?>" value="<?php echo $value; ?>" pattern="[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)"/>
				<br />
				<a class="btn btn-primary" href="javascript:alert(checkcoords())">Verify Coordinates</a>
				<?php
					echo '</tr></td>';
			} elseif ($key == "ALTITUDE") {
				?>
				<input class="form-control" type="text" name="<?php echo $key; ?>" value="<?php echo $value; ?>" pattern="-*[0-9]{1,}(m|ft)"/>
				<?php
					echo '</tr></td>';
			} elseif ($key == "USER") {
				?>
				<input class="form-control" type="text" name="<?php echo $key; ?>" value="<?php echo $value; ?>" pattern="[A-Za-z0-9._]+"/>
				<?php
					echo '</tr></td>';
			} else {
				?>
				<input class="form-control" type="text" name="<?php echo $key; ?>" value="<?php echo $value; ?>" />
				<?php
					echo '</tr></td>';
			}

	}

}
?>

</table>
</div>
<br />

<input class="btn btn-primary" type="submit" value="Save Configuration">
 </form>

 <?php

 ?>

 </center>

</body>
</html>