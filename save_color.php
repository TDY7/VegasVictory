<?php
session_start();

if (!isset($_SESSION['pseudo'])) {
	header("Location: attente.php");
}

$file = "inscriptions.csv";
$pseudo = $_SESSION['pseudo'];
$color = $_POST['color'];

// On lit le fichier inscriptions et on garde les données dans un tableau
$data = [];
if (($handle = fopen($file, "r")) !== FALSE) {
	while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$data[] = $row;
	}
	fclose($handle);
}

// On met à jour la couleur de l'utilisateur
for ($i = 0; $i < count($data); $i++) {
	if ($data[$i][0] === $pseudo) {
		$data[$i][4] = $color;
		break;
	}
}

// On écrit les données mises à jour dans le fichier inscriptions
$handle = fopen($file, "w");
foreach ($data as $row) {
	fputcsv($handle, $row);
}
fclose($handle);

?>