<?php
    include('simple_html_dom.php');

    if(isset($_POST)) {

        $name = $_POST['name'];
        $url = $_POST['nameUrl'];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $curl_scraped_page = curl_exec($ch);

        $html = new simple_html_dom();
        $html->load($curl_scraped_page);
        
        // Get Image of pokemon
        $image = $html->find('.grid-col.span-md-6.span-lg-4', 1);
        $image = $image->children(0)->children(0)->children(0)->src;
        // $image = "https://img.pokemondb.net/sprites/bank/normal/".strtolower($name).".png";

        // Get Pokedex data of pokemon
        $pokedex = $html->find('.grid-col.span-md-6.span-lg-4', 2);
        $natNo = $pokedex->find('tr', 0)->children(1);
        $type = str_replace("href=\"/","target='_blank' href=\"https://pokemondb.net/", $pokedex->find('tr', 1)->children(1));
        $species = $pokedex->find('tr', 2)->children(1);
        $height = $pokedex->find('tr', 3)->children(1);
        $weight = $pokedex->find('tr', 4)->children(1);
        $abilities = str_replace("href=\"/","target='_blank' href=\"https://pokemondb.net/", $pokedex->find('tr', 5)->children(1));

        // Get Training data of pokemon
        $training = $html->find('.grid-col.span-md-6.span-lg-12', 0);
        $catchRate = $training->find('tr', 1)->children(1);
        $growthRate = $training->find('tr', 4)->children(1);

        // Get Base Stats of pokemon
        $stats = $html->find('.grid-col.span-md-12.span-lg-8', 0)->children(2)->children(0);
        $hp = $stats->find('tr', 0);
        $hp->children(2)->outertext = "";
        $attack = $stats->find('tr', 1);
        $attack->children(2)->outertext = "";
        $defense = $stats->find('tr', 2);
        $defense->children(2)->outertext = "";
        $spAttack = $stats->find('tr', 3);
        $spAttack->children(2)->outertext = "";
        $spDefense = $stats->find('tr', 4);
        $spDefense->children(2)->outertext = "";
        $speed = $stats->find('tr', 5);
        $speed->children(2)->outertext = "";

        // Get Evolution Chart of pokemon
        // str_replace("<span class=\"img-fixed img-sprite\" data-","<img ",
        if($html->find('.infocard-list-evo', 0)) {
            $evoChart = str_replace("<span class=\"img-fixed img-sprite\" data-","<img ", $html->find('.infocard-list-evo', 0)->outertext);
            $evoChart = str_replace("</span>","", $evoChart);
            $evoChart = "<h5>".$name."'s Evolution</h5>".$evoChart;
        } else {
            $evoChart = "<h5>No Evolution for this pokemon.</h5>";
        }
        // $evoChart = $html->find('.infocard-list-evo', 0);
        
        
        echo "<div class'spacer-2rem'><h4 class='text-center'>".$name." Data</h4></div>";
        echo "<div class='text-center poke-img-container'>";
            echo "<img class='poke-img' src='".$image."'>";
        echo "</div>";
        echo "<div class=''>";
            echo "<div class='row'>";
                echo "<div class='col-6 text-right'>National No:</div><div class='col-6'> #".$natNo."</div>";
                echo "<div class='col-6 text-right'>Type:</div><div class='col-6'> ".$type."</div>";
                echo "<div class='col-6 text-right'>Species:</div><div class='col-6'> ".$species."</div>";
                echo "<div class='col-6 text-right'>Height:</div><div class='col-6'> ".$height."</div>";
                echo "<div class='col-6 text-right'>Weight:</div><div class='col-6'> ".$weight."</div>";
                echo "<div class='col-6 text-right'>Abilities:</div><div class='col-6'> ".$abilities."</div>";
                echo "<div class='col-6 text-right'>Catch Rate:</div><div class='col-6'> ".$catchRate."</div>";
                echo "<div class='col-6 text-right'>Growth Rate:</div><div class='col-6'> ".$growthRate."</div>";
            echo "</div>";
        echo "</div>";

        echo "<div class'spacer-2rem'><h4 class='text-center'>".$name." Base Stats</h4></div>";
        echo "<div class=''>";
            echo "<div class='row'>";
                echo "<table class='table table-dark table-noborder'>";
                    echo "<thead>";
                    echo "<tbody>";
                        echo $hp;
                        echo $attack;
                        echo $defense;
                        echo $spAttack;
                        echo $spDefense;
                        echo $speed;
                    echo "</tbody>";
                echo "</table>";
            echo "</div>";
        echo "</div>";
        echo $evoChart;
        
        // Halaman detail minimal menampilkan National №, Images, Type, Species, Height, Weight, Abilities, Catch Rate, Growth Rate, Base Stat, Evolution Chart
        // Rekomendasi pokemon menampilkan National №, Images, Species
        // print_r($_POST['pokemon']);
    }

?>