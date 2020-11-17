<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Scrapping Web Pokemon</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
		integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
	<script src="https://code.jquery.com/jquery-3.5.1.js" integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc=" crossorigin="anonymous"></script>
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

	<div class="container mx-auto round container-padding">
		<div id="pokemon-detail">

		</div>
	</div>

	<div class="container mx-auto round">
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
				$i = 0;
				foreach (array_slice($tableBody->find('tr'), 0) as $rowChar) {
					
					$natNo = str_replace(" ","",$rowChar->children(0)->children(1)->plaintext);

					$name = str_replace(" ","",$rowChar->children(1)->children(0)->plaintext);
					if($rowChar->children(1)->children(2)) {
						$secName = str_replace(" ","",$rowChar->children(1)->children(2)->plaintext);
					} else {
						$secName = "";
					}
					$nameUrl = $url.$rowChar->children(1)->children(0)->href;

					$imagesUrl = $rowChar->children(0)->children(0)->children(0)->getAttribute('data-src');
					
					echo "<tr>";
					echo "<td>".$natNo."</td>";
					echo "<td><img src='".$imagesUrl."'></img></td>";
					if($secName != ""){
							echo "<td><a href='#pokemon-detail'"."onclick=\"displayDetail('".$name."','".$nameUrl."')\">".$name."</a><br><div class='smaller-text'>".$secName."</div></td>";
						} else {
							echo "<td><a href='#pokemon-detail'"."onclick=\"displayDetail('".$name."','".$nameUrl."')\">".$name."</a></td>";
						}
					echo "</tr>";
					
					$pokemon[$i] = array('name'=>$name, 'nameUrl'=>$nameUrl);
					$i=$i+1;
				}
			?>
		</table>
	</div>

	<script type="text/javascript">
		$('a[href*="#"]').not('[href="#"]').not('[href="#0"]').click(function(event) {
			if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
				// Figure out element to scroll to
				var target = $(this.hash);
				target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');

				// Does a scroll target exist?
				if (target.length) {
				// Only prevent default if animation is actually gonna happen
				event.preventDefault();
				$('html, body').animate({
				scrollTop: target.offset().top
				}, 1500, function() {
				// Callback after animation
				// Must change focus!
				var $target = $(target);
				$target.focus();
				if ($target.is(":focus")) { // Checking if the target was focused
					return false;
				} else {
					$target.attr('tabindex','-1'); // Adding tabindex for elements not focusable
					$target.focus(); // Set focus again
				};
				});
			}
			}
		});

		function displayDetail(pokeName, pokeUrl){
			console.log(pokeUrl);

			$.ajax({
				method: 'POST',
				url: './pokeDetail.php',
				data: {
					name: pokeName,
					nameUrl: pokeUrl
				}
			}).done(function(msg) {
				// console.log(msg);
				$('#pokemon-detail').html(msg);
			});
		}

		$(document).ready(function(){
			var pokemons = <?php echo json_encode($pokemon); ?>;
			var chosen = pokemons[Math.floor(Math.random() * 1045)];
			console.log(chosen);

			$.ajax({
				method: 'POST',
				url: './pokeDetail.php',
				data: {
					name: chosen['name'],
					nameUrl: chosen['nameUrl']
				}
			}).done(function(msg) {
				// console.log(msg);
				$('#pokemon-detail').html(msg);
			});
		});
	</script>
</body>

</html>