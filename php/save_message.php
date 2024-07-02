<?php
session_start();

// On lit le fichier inscriptions et on garde les données dans un tableau
$file = "inscriptions.csv";
$data = [];
if (($handle = fopen($file, "r")) !== FALSE) {
	fgetcsv($handle, 1000, ","); // Ignorer la première ligne (en-têtes de colonne)
	while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$data[] = $row;
	}
	fclose($handle);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
	$message = $_POST['message'];
	$pseudo = $_SESSION['pseudo'];
	$time = date('H:i:s');
	$userColor = '';
	for ($i = 0; $i < count($data); $i++) {
		if ($data[$i][0] === $pseudo) {
			$userColor = $data[$i][4];
			break;
		}
	}
	$file = "chat_log.txt";
	file_put_contents($file, $time . " - " . $pseudo . ":" . $userColor . ": " . $message . PHP_EOL, FILE_APPEND);
	file_put_contents("last_timestamp.txt", $time);
}
?>