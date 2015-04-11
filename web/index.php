<?php

$typo = new Debach\PhpTypography\PhpTypography();

$monfichier = fopen(DATA_ROOT . 'cache_fr.php', 'r');

// on passe les 2 première lignes du fichier
$firstLine = fgets($monfichier);
$firstLine = fgets($monfichier);

// on lit la première ligne de donnée
$firstLine = fgets($monfichier); // On lit la première ligne

// on récupère la traduction de la ligne courante
$parseTrad = explode("=>", $firstLine);

//on supprime les caractères indésirables
$parseTrad = substr($parseTrad[1], 2, -3);

// traduction corrigée
$correctTrad = $typo->process($parseTrad);

// View
?>
<html>
<meta charset="utf-8">
<body>

<?php

echo "Traduction originale :  " . $parseTrad . "<br/>";
echo "Traduction corrigée : " . $correctTrad;

?>
</body>
</html>
