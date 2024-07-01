<?php
// On lit le fichier inscriptions
$file = "joueurs.json";
$joueurs = [];
if (file_exists($file)) {
	$json = file_get_contents($file);
	$joueurs = json_decode($json, true);
}

// On filtre les utilisateurs actifs
$activeUsers = array_filter($joueurs, function($value) {
	return $value['valeur'] == 1;
});

// On créer un tableau associatif avec le nom d'utilisateur et la couleur
$activeUsersWithColor = [];
foreach ($activeUsers as $username => $user) {
	$activeUsersWithColor[] = [
		'username' => $username,
		'color' => $user['couleur']
	];
}

// On retourne les utilisateurs actifs et leur couleur au format JSON
echo json_encode($activeUsersWithColor);
?>
