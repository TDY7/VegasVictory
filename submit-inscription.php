<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$pseudo = $_POST["pseudo"];
	$password = $_POST["password"];
	$day = $_POST["day"];
	$month = $_POST["month"];
	$year = $_POST["year"];
	$birthdate = $day . "-" . $month . "-" . $year;
	$score = 0;
	$color = "orange";
	$file = "inscriptions.csv";
	if (!file_exists($file)) {
		$handle = fopen($file, 'w');
		fwrite($handle, "pseudo,mot_de_passe,date_de_naissance,score,couleur\n");
		fclose($handle);
	}

	$handle = fopen($file, "a");
	// Écrire les données dans le fichier inscriptions
	$data = "$pseudo,$password,$birthdate,$score,$color\n"; // On ajoute le score et la couleur aux données
	if (fwrite($handle, $data) === false) {
		echo "Une erreur s'est produite lors de l'enregistrement des données.";
	} else {
		echo "Votre inscription a été enregistrée avec succès.";
		header("Location: accueil.html");
	}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$dob = $_POST["dob"];
	echo "Date de naissance enregistrée : " . $dob;
}
	// On ferme le fichier inscriptions
	fclose($handle);
} else {
	echo "Une erreur s'est produite.";
}
?>