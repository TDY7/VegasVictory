<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['pseudo'])) {
	header("Location: connexion.html");
	exit();
}

$userColor = $_SESSION['color'];

// On lit le fichier JSON et décoder les données
$file = "joueurs.json";
$joueurs = [];
if (file_exists($file)) {
	$json = file_get_contents($file);
	$joueurs = json_decode($json, true);
}

// On ajoute le pseudo et la couleur de l'utilisateur actuellement connecté
$joueurs[$_SESSION['pseudo']] = ['couleur' => $userColor, 'valeur' => 1];

// On encode les données au format JSON
$json = json_encode($joueurs);
file_put_contents($file, $json);
?>