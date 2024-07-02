<?php
// On lit le fichier inscriptions
$json = file_get_contents('joueurs.json');
$joueurs = json_decode($json, true);

// On compte le nombre d'utilisateurs
$playerCount = count($joueurs);

// On renvoie le nombre d'utilisateurs sous forme de réponse JSON
echo json_encode($playerCount);
?>
