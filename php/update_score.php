<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['pseudo'])) {
	header('Location: connexion.php');
	exit();
}

// Nom du fichier CSV
$filename = 'inscriptions.csv';

// Lire le contenu du fichier CSV dans un tableau
$file = fopen($filename, 'r');
$rows = array();
while (($row = fgetcsv($file, 1000, ',')) !== false) {
	$rows[] = $row;
}
fclose($file);

// Trouver la ligne correspondant à l'utilisateur connecté
$pseudo = $_SESSION['pseudo'];
$index = -1;
for ($i = 0; $i < count($rows); $i++) {
	if ($rows[$i][0] == $pseudo) {
		$index = $i;
		break;
	}
}

// Si la ligne a été trouvée, mettre à jour le score de l'utilisateur
if ($index !== -1) {
	$rows[$index][3]++; // Incrémenter le score

	// Écrire le contenu mis à jour dans le fichier CSV
	$file = fopen($filename, 'w');
	foreach ($rows as $row) {
		fputcsv($file, $row);
	}
	fclose($file);
}

// Rediriger l'utilisateur vers la page de jeu
header('Location: jeu.php');
exit();
?>