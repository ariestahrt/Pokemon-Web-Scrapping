<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Scrapping Web Pokemon</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
		integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
	<link rel="stylesheet" href="./css/style.css">
	<style>
		.gacha {
			height: 50vh;
			width: 90vw;
		}
	</style>
</head>

<body>
	<?php
		include('simple_html_dom.php');

		$url = "https://pokemondb.net";
		$pokedex = $url."/pokedex/all";

    $ch = curl_init($pokedex);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $curl_scraped_page = curl_exec($ch);

    $html = new simple_html_dom();
    $html->load($curl_scraped_page);

		$table = $html->find('table[id=pokedex]',0);
		$tableBody= $table->find('tbody',0);
	?>

	<div class="container mx-auto">
		<table class='table table-dark '>
			<thead class='thead-darks'>
				<tr>
					<th style="width:25%">National No</th>
					<th style="width:25%">Image</th>
					<th style="width:50%">Name</th>
				</tr>
			</thead>
			<!-- <tbody> -->
			<?php
				foreach (array_slice($tableBody->find('tr'), 0) as $rowChar) {

					$natNo = str_replace(" ","",$rowChar->children(0)->children(1)->plaintext);

					$name = str_replace(" ","",$rowChar->children(1)->children(0)->plaintext);
					if($rowChar->children(1)->children(2)) {
						$secName = str_replace(" ","",$rowChar->children(1)->children(2)->plaintext);
					} else {
						$secName = "";
					}
					$nameUrl = $url.$rowChar->children(1)->children(0)->href;

					// $pokemonUrl = file_get_html($nameUrl);
					// $imagesUrl = $pokemonUrl->find('/html/body/main/div[3]/div[2]/div/div[1]/div[1]/p[1]/a/img', 0)->src;
					$imagesUrl = $rowChar->children(0)->children(0)->children(0)->getAttribute('data-src');


					echo "<tr>";
						echo "<td>".$natNo."</td>";
						echo "<td><img src='".$imagesUrl."'></img></td>";
						if($secName != ""){
							echo "<td><a href='".$nameUrl."'>".$name."</a><br><div class='smaller-text'>".$secName."</div></td>";
						} else {
							echo "<td><a href='".$nameUrl."'>".$name."</a></td>";
						}
						// echo "<td><p>".$imagesUrl."</p></td>";
					echo "</tr>";
					
				}
			?>
		</table>

		</div>

	</div>
</body>

</html>