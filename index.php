<?php
// We always work with UTF8 encoding
mb_internal_encoding('UTF-8');

// Make sure we have a timezone set
date_default_timezone_set('Europe/Paris');
include('php-typography.php');
$typo = new phpTypography();

$monfichier = fopen('cache_fr.php', 'r');

// on passe les 2 première lignes du fichier
$firstLine = fgets($monfichier); 
$firstLine = fgets($monfichier); 

// on lit la première ligne de donnée
$firstLine = fgets($monfichier); // On lit la première ligne 

// on récupère la traduction de la ligne courante
$parseTrad = explode("=>", $firstLine);

//on supprime les caractères indésirables
$parseTrad = substr($parseTrad[1],2,-3); 
// traduction corrigée
$correctTrad = $typo->process($parseTrad);

echo "Traduction originale :  ".$parseTrad."<br/>";
echo "Traduction corrigée : ".$correctTrad;

?>