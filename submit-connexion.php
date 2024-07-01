<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$pseudo = $_POST["pseudo"];
	$password = $_POST["password"];
	$file = "inscriptions.csv";
	$found = false;
	if (($handle = fopen($file, "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			if ($data[0] == $pseudo && $data[1] == $password) {
				$found = true;
				break;
			}
		}
		fclose($handle);
	}
	if ($found) {
		$_SESSION['pseudo'] = $pseudo;
		echo "success";
	} else {
		echo "Pseudo ou mot de passe passe incorrect.";
	}
} else {
	echo "Une erreur s'est produite.";
}
?>