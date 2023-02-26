<?php

$texte = file('texte.dat');
$tableau = file("tableau.dat");
$comm = file("comm.dat");
$lienSite = file("lienSite.dat");
$nomRegion = file("regionNom.dat");
$regions = file("region.config");
$codeISO = file("iso.dat");
$heure = date("H") + 1 + date("I"); //heure UTC +1 +1 si été ou + 0 si hiver
$date = date("j-m-Y ".$heure.":i");
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <link rel="stylesheet" href="page.css">
    <style>
        figure {
            display: inline-block;
        }
        @media print {
   section {
      size: A4;
      margin: 0;
      page-break-after: always;
   }
}
    </style>
</head>


<body>
    <section id="Page_de_couverture">
        <?php 
        $i = 0;
        $trouve = false;
        while($trouve == false && $i<count($regions)-1){
            if(strpos(strtoupper($regions[$i]), strtoupper($nomRegion[0])) !== false ){
                $j = $i+1;
                if(strpos(strtoupper($regions[$i]), strtoupper("Nom de la région")) !==false ){
                    echo "<h1>Région ".$nomRegion[0]."</h1>" ;
                    echo "<ul>";
                    while($trouve == false && $j<count($regions)){
                        if(strpos(strtoupper($regions[$j]), strtoupper("Logo de la région")) !==false ){
                            $trouve = true;
                            $logoValide = explode(':',$regions[$j]);
                            $logoValide = $logoValide[1];
                            $logoValide = rtrim($logoValide);
                            
                        }else{
                            echo "<li>".$regions[$j]."</li>";
                        }
                        $j++;
                    }
                    echo "</ul>";
                }
            }
            $i++;
        }
        if(isset($logoValide) && !empty($logoValide)){
            echo "<img src='".$logoValide."'>";
        }
        echo "<footer>\n";
        echo "<p class='time'>".$date."</p>\n";
        echo "</footer>\n";
        ?>
        
    </section>
    <section id="page1">
        <?php 
        foreach($texte as $a_line){
            echo $a_line;
        }
        foreach($tableau as $a_table){
            echo $a_table;
        }
        echo "<footer>\n";
        echo "<p class='time'>".$date."</p>\n";
        echo "</footer>\n";
        ?>
    </section>
    <section id="Page2">
        <?php
        echo "<h1>Nos meilleurs vendeurs du trimestre</h1>\n";
        foreach($comm as $a_c){
            echo "<figure>\n";
            $a_c = explode("=",$a_c);
            $initiale = explode(" ",$a_c[0]);
            $initiale = $initiale[0][0].$initiale[0][1].$initiale[1][0];
            $initiale = strtolower($initiale);
            echo "<img src=\"img/$initiale.png\""." "."alt="."\"Photo du commerciale\"".">";
            echo "<figcaption>\n";
            echo $a_c[0]." - ".$a_c[1]." de CA\n";
            echo "</figcaption>\n";
            echo "</figure>\n";
            
        }
        echo "<footer>\n";
        echo "<p class='time'>".$date."</p>\n";
        echo "</footer>\n";

        ?>
    </section>
    <section id='page3'>
        <?php 
        foreach($lienSite as $a_line){
            echo $a_line;
        }
        ?>
        <figure>
        <?php
            $nomCode = strtolower($codeISO[0]);
            $nomCode = trim($nomCode);
            echo "<img src=\"img/$nomCode.png\""." "."alt="."\"Qrcode\"".">";
        ?>
        </figure>
        <?php
        echo "<footer>\n";
        echo "<p class='time'>".$date."</p>\n";
        echo "</footer>\n";

        ?>
        
    </section>
</body>

</html>