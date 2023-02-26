<?php
/** Lancer ce script de cette façon (dans un conteneur Docker sae103-php) :
 * ./sample-args.php toto tutu 123
 * donc avec Docker ça doit être quelque chose du genre :
 * docker run --rm -ti -v /Docker/votre_volume:/work -w /work sae103-php ./sample-args.php toto lulu 1234
 * Ne pas oublier de faire un chmod +x sample-args.php auparavant (besoin 1 seule fois, à la création du script)
 */
for ($loop = 0; $loop < sizeof($argv); $loop++) {
    $stdin = $argv[$loop];
}


$fichierTexte = fopen('texte.dat','w');
$fichierTab = fopen('tableau.dat','w');

//On définit la date du traitement
$date_trimestre = date("m");
if($date_trimestre <= 3){
    $date_trimestre = 1;
}else if($date_trimestre<= 6){
    $date_trimestre = 2;
}else if($date_trimestre <=9){
    $date_trimestre = 3;
}else if($date_trimestre<=12){
    $date_trimestre = 4;
} 
$date = date("Y");
fwrite($fichierTexte,"<h1>Resultats Trimestriels ".$date_trimestre." - ".$date." </h1>");

//On prend le texte d'introduction du fichier
$lines = file($stdin);
$i = 0;
$b = 2;
$pattern_crochet = "#\[[^\]]*\]#";  //pattern pour séléctionner entre crochet "[]"
$pattern_parenthese = "#\((.*?)\)\)#";  //pattern pour séléctionner entre parenthèse "()"
$trouve = false; //si "FIN_TEXTE" trouvé
while(($i < count($lines)) && (rtrim(strtoupper($lines[$i])) != "DEBUT_STATS" || rtrim(strtolower($lines[$i])) != "début_stats") ){ //arrête la boucle soit à la fin du fichier soit quand "FIN_TEXTE" est trouvé
    //On prend que les titres et les sous-titres du texte
    if (((strpos(strtoupper($lines[$i]),"TITRE=") !== false) || (strpos(strtolower($lines[$i]),"titre=") !== false)) && ((strpos(strtoupper($lines[$i]),"SOUS_TITRE=") == false) && (strpos(strtolower($lines[$i]),"sous_titre=") == false) ) ){
        fwrite($fichierTexte,"<h".$b.">");
        $titre = explode("=",$lines[$i]);
        fwrite($fichierTexte,$titre[1]);
        fwrite($fichierTexte,"</h".$b.">");
        if($b == 2){
            $b++;
        }
    }
    
    if (rtrim(strtoupper($lines[$i])) == "DEBUT_TEXTE" || rtrim(strtolower($lines[$i])) == "début_texte" ) {  //si "DEBUT_TEXTE" ou "début_texte" sont trouvés 
        fwrite($fichierTexte,"<p>");
        $j=$i+1;
        while (rtrim(strtoupper($lines[$j])) != "FIN_TEXTE"  && $j<count($lines)) {  //arrête la boucle quand "FIN_TEXTE" est trouvé ou quand arrive à la fin du fichier
            preg_match_all($pattern_crochet, $lines[$j], $matches); //voir si crochets
            if(isset($matches) && !empty($matches[0])){ //si $matches n'est pas nul et n'est pas vide
                preg_match_all($pattern_parenthese, $lines[$j], $liens); //voir si parenthèses
                if(isset($liens) && !empty($liens[0])){  //si $liens n'est pas nul et n'est pas vide
                    $lines_t = explode(strval($matches[0][0]." ".$liens[0][0]),$lines[$j]);
                    if(empty($lines_t[1])){
                        $lines_confirme[$j] = explode(strval($matches[0][0].$liens[0][0]),$lines[$j]); 
                    }else{
                        $lines_confirme[$j] = $lines_t[1];
                    }
                    $liens[0][0][0] = ' ';
                    $liens[0][0][strlen($liens[0][0])-1] = ' '; //On enlève les parenthèses du lien         
                    $matches = trim($matches[0][0],"[]"); //On enlève les crochets du texte qui suit le lien
                    fwrite($fichierTexte, $lines_confirme[$j][0]);
                    fwrite($fichierTexte, "<a href='".$liens[0][0]."'>"); //On met dans la balise <a> le lien
                    fwrite($fichierTexte, $matches."<a>");
                    fwrite($fichierTexte, $lines_confirme[$j][1]);
                }
            }else{
                fwrite($fichierTexte, $lines[$j]);
            }
            $j++;
        }
        fwrite($fichierTexte,"</p>");
    }
    
    $i++;
}



$i = 0;
$trouve = false;
$lines = file($stdin);
while($i < count($lines) && $trouve == false ){
    if(rtrim(strtoupper($lines[$i])) == "DEBUT_STATS" || rtrim(strtolower($lines[$i])) == "début_stats"){
        //On créer la première ligne des titres pour le tableau
        fwrite($fichierTab,"<table>");
        fwrite($fichierTab,"<tr>");
        fwrite($fichierTab,"<th>Nom du produit</th>");
        fwrite($fichierTab,"<th>Ventes du trimestre</th>");
        fwrite($fichierTab,"<th>Chiffres d'affaires du trimestre</th>");
        fwrite($fichierTab,"<th>Ventes du même trimestre année précédente</th>");
        fwrite($fichierTab,"<th>CA du même trimestre année précédente</th>");
        fwrite($fichierTab,"<th>Evolution de CA en %</th>");
        fwrite($fichierTab,"</tr>");
        $j=$i+1;
        while (rtrim(strtoupper($lines[$j])) != "FIN_STATS"  && $j<count($lines)){
            $lines[$j] = explode(",",$lines[$j]);
            fwrite($fichierTab,"<tr>");
            for($h = 0; $h<count($lines[$j])+1; $h++){
                if($h == count($lines[$j])){
                    //On calcule l'évolution du CA du trimestre de l'année dernière à celui d'aujourd'hui
                    $evolCA = (intval($lines[$j][$h-3])-intval($lines[$j][$h-1]))/intval($lines[$j][$h-1])*100;
                    //On le met en vert si l'evolution est positive et en rouge sinon
                    if(intval($evolCA) < 0){
                        $color = "red";
                    }else{
                        $color = "green";
                    }
                    fwrite($fichierTab,"<td>");
                    fwrite($fichierTab,"<span style='color : ".$color."'>".$evolCA."%</span>");
                    fwrite($fichierTab,"</td>");
                }else{
                    fwrite($fichierTab,"<td>");
                    fwrite($fichierTab,$lines[$j][$h]);
                    fwrite($fichierTab,"</td>");
                }
            }
            fwrite($fichierTab,"</tr>");
            $j++;
        }
        $trouve = true;
        fwrite($fichierTab,"</table>");
    }
    $i++;
}



$ficherSRC = file($stdin); // Import du fichier où extraire les informations, a remplacer par votre variable
$commDat = fopen("comm.dat","w");  // Ouverture du fichier où ecrire les information
foreach ($ficherSRC as $ligneSRC) {
    $Tabligne = explode("\n",$ligneSRC); // Permet de créer un tableau avec tout les lignes
    foreach($Tabligne as $ligne) {
        //Permet de tester si le debut de la ligne est : "MEILLEURS"
        $ligne = explode(":",$ligne);
        if (strtoupper($ligne[0]) == "MEILLEURS") {
            $meilleurCommercialeTab = explode(",",$ligne[1]); // Permet d'isoler les 3 meilleurs commerciaux dans un tableau
            $CommercialTab = [];
            foreach ($meilleurCommercialeTab as $commercial ) {
                    // On isole ensuite le nom du commerciale avec son chiffre d'affaire
                $Commercial = explode("/",$commercial);
                $Commercial = $Commercial[1];
                //On ajoute ensuite le nom au Tableau avec les 3 meilleurs
                array_push($CommercialTab,$Commercial);  
            }
        }
    }
}
// Permmet d'écrire les noms des 3 meilleurs commerciaux dans le fichier Comm.data
foreach ($CommercialTab as $commercial) {
    fwrite($commDat,$commercial);
    fwrite($commDat,"\n");
}


//Pour faire le lien du site internet de la région
$regions = explode("/",$stdin);
$regions = $regions[count($regions)-1];
$regions = trim($regions,'.txt');
$fichierLien = fopen('lienSite.dat','w');
$fichierRegion = fopen('regionNom.dat','w');
$fichierISO = fopen("iso.dat",'w');
$lines = file($stdin);
$trouve = false;
$i = 0;
while($trouve == false && $i<count($lines)){
    if (strpos(strtoupper($lines[$i]), "CODE=")!== false){
        $code = explode('=',$lines[$i]);
        $code = $code[1];
        fwrite($fichierISO,$code);
        fwrite($fichierLien,'<a href ="https//bigbrain.biz/'.$code.'">https//bigbrain.biz/'.$code.'</a>');
        $trouve = true;
    }
    $i++;
}

fwrite($fichierRegion, $regions);


?>