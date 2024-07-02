<?php
session_start();

// On vérifie si l'utilisateur est connecté
if (!isset($_SESSION['pseudo'])) {
	header("Location: connexion.html");
	exit();
}

// On lit le fichier JSON
$file = "joueurs.json";
$joueurs = [];
if (file_exists($file)) {
	$json = file_get_contents($file);
	$joueurs = json_decode($json, true);
}

// On retire l'utilisateur de la liste des joueurs
if (array_key_exists($_SESSION['pseudo'], $joueurs)) {
	unset($joueurs[$_SESSION['pseudo']]);
}

// On écrit dans le fichier
$json = json_encode($joueurs);
file_put_contents($file, $json);

header("Location: attente.php");
exit();
?>