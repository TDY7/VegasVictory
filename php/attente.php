<?php
	session_start();
	if (!isset($_SESSION['pseudo'])) {
		header("Location: connexion.html");
	}

	$_SESSION['last_timestamp'] = isset($_SESSION['last_timestamp']) ? $_SESSION['last_timestamp'] : 0;

	// Lire le fichier CSV et garder les données dans un tableau
	$file = "inscriptions.csv";
	$data = [];
	if (($handle = fopen($file, "r")) !== FALSE) {
		fgetcsv($handle, 1000, ","); // Pour ignorer la première ligne qui fait office d'exemple
		while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
			$row[] = 0;
			$data[] = $row;
		}
		fclose($handle);
	}

	// On trie le tableau en fonction du score dans l'ordre décroissant
	usort($data, function($a, $b) {
		return (int)$b[3] - (int)$a[3];
	});

	// On récupère uniquement les 10 premiers
	$top10 = array_slice($data, 0, 10);

	// Pour avoir la couleur de l'utilisateur qui est connecté
	$userColor = '';
	for ($i = 0; $i < count($data); $i++) {
		if ($data[$i][0] === $_SESSION['pseudo']) {
			$userColor = $data[$i][4];
			break;
		}
	}

	$_SESSION['color'] = $userColor;

	function addMessage($message) {
		$messages = getAllMessages();
		$messages[] = $message;
		file_put_contents('messages.json', json_encode($messages));
	}

	// Vérifier si un message a été envoyé
	if (isset($_POST['message'])) {
		$newMessage = $_POST['message'];
		addMessage($newMessage);
	}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>VegasBlackJack - Connexion</title>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<link rel="stylesheet" href="bases.css">
	<style>
		.yellow {
			background-color: #E8DB27;
		}

		.blue {
			background-color: #1E88E5;
		}

		.pink {
			background-color: #F69CEF;
		}
	</style>
	<script>
		let selectedColorName = '';

		function changeColor(color) {
			document.getElementById('pseudo').style.color = color;
			selectedColorName = color;
			// Pour envoyer une requête AJAX à save_color afin de mettre à jour la couleur dans le fichier inscription
			const pseudo = '<?php echo $_SESSION['pseudo']; ?>';
			$.ajax({
				url: 'save_color.php',
				method: 'POST',
				data: {
					pseudo: pseudo,
					color: color
				},
				success: function(response) {
					console.log('Couleur sauvegardée :', color);
				},
				error: function(error) {
					console.error('Erreur lors de la sauvegarde de la couleur :', error);
				}
			});
		}

		function saveProfile() {
			const pseudo = '<?php echo $_SESSION['pseudo']; ?>'; // Pseudo
			const currentColor = '<?php echo $_SESSION['color']; ?>'; // Couleur
			if (selectedColorName !== '' && selectedColorName !== currentColor) { // Si la couleur a changé
				const color = selectedColorName;
				$.ajax({
					url: 'save_color.php',
					method: 'POST',
					data: {
						pseudo: pseudo,
						color: color
					},
					success: function(response) {
						console.log('Couleur sauvegardée :', color);
						location.reload();
					},
					error: function(error) {
						console.error('Erreur lors de la sauvegarde de la couleur :', error);
					}
				});
			}
		}

		document.querySelectorAll('.color-option').forEach(function(element) {
			element.addEventListener('click', function() {
				document.querySelectorAll('.color-option').forEach(function(el) {
					el.classList.remove('selected');
				});
				this.classList.add('selected');
			});
		});
	</script>
</head>

<body class="connexion">
	<img src="images/vegaslogo.png" alt="Vegas Logo" id="vegas-logoP">
	<div id="scoreboard">
		<div id="scoreboard-title"><span style="color: #981b22; display: inline-block; width: 10px; background-color: #981b22; height: 3px;vertical-align: middle;"></span> CLASSEMENT <span style="color: #981b22; display: inline-block; width: 10px; background-color: #981b22; height: 3px;vertical-align: middle;"></span></div>
		<?php for ($i = 0; $i < count($top10); $i++): ?>
			<div class="scoreboard-entry"><span style="color: <?= $top10[$i][4] ?>;"><?= ($i + 1) ?> : <?= $top10[$i][0] ?> (<?= $top10[$i][3] ?>)</span></div>
		<?php endfor; ?>
	</div>

	<div id="tchat">
		<div id="tchat-title"><span style="color: #981b22; display: inline-block; width: 10px; background-color: #981b22; height: 3px;vertical-align: middle;"></span> DISCUSSION <span style="color: #981b22; display: inline-block; width: 10px; background-color: #981b22; height: 3px;vertical-align: middle;"></span></div>
		<input type="text" id="messageInput" placeholder="Entrez votre message...">
		<button class="send-button" onclick="sendMessage('messageInput')">ENVOYER</button>
		<div class="toggle-squareA"></div>
		<div class="toggle-squareB" id="squareB"></div>
	</div>

	<div id="profile">
		<div id="profile-title"><span style="color: #981b22; display: inline-block; width: 10px; background-color: #981b22; height: 3px;vertical-align: middle;"></span> PROFIL <span style="color: #981b22; display: inline-block; width: 10px; background-color: #981b22; height: 3px;vertical-align: middle;"></span></div>
		<div id="pseudo" style="color: <?= $userColor ?>; font-size: 24px;"> <?php echo $_SESSION['pseudo']; ?></div>
		<h3 style="color: #fdfdd6; font-size: 17px;"> Choisissez la couleur<br>de votre pseudonyme : </h3>
		<div id="color-picker">
			<div class="color-column">
				<div class="color-option red" onclick="changeColor('red')"></div>
				<div class="color-option orange" onclick="changeColor('orange')"></div>
				<div class="color-option yellow" onclick="changeColor('#E8DB27')"></div>
			</div>
			<div class="color-column">
				<div class="color-option green" onclick="changeColor('green')"></div>
				<div class="color-option blue" onclick="changeColor('#1E88E5')"></div>
				<div class="color-option pink" onclick="changeColor('#F69CEF')"></div>
			</div>
		</div>
		<button class="send-button" onclick="saveProfile()">ACTUALISER</button>
	</div>

	<div class="form-containerA">
		<h2 class="form-titleA"><span style="color: #981b22; display: inline-block; width: 10px; background-color: #981b22; height: 3px;vertical-align: middle;"></span> COMMENCER UNE PARTIE <span style="color: #981b22; display: inline-block; width: 10px; background-color: #981b22; height: 3px;vertical-align: middle;"></span></h2>
		<p><span class="motivation">Prêt à relever le défi ?</span></p>
		<div id="online-game">
			<button id="join-game-button" class="join-game-button">JOUER</button>
			<div id="player-counter">0 joueur(s) présent(s) dans la partie</div>
		</div>
		<br>
		<p><span class="motivation">Le chemin vers la grandeur commence ici !</span></p>
		<div id="offline-game">
			<button id="practice-button" class="join-game-button">S'ENTRAINER</button>
		</div>
	</div>
	<div class="buttons">
		<a href="logout.php"><strong>DÉCONNEXION</strong></a>
	</div>
	<script src="script.js"></script>
	<script src="tchat.js"></script>
</body>
</html>