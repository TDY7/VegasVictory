<?php
session_start();
if (!isset($_SESSION['pseudo'])) {
	header("Location: connexion.html");
}

$_SESSION['last_timestamp'] = isset($_SESSION['last_timestamp']) ? $_SESSION['last_timestamp'] : 0;

// Ajout d'une variable de session pour suivre si le joueur a déjà pris une décision
$_SESSION['decision_prise'] = isset($_SESSION['decision_prise']) ? $_SESSION['decision_prise'] : false;

// On lit le fichier inscriptions et on garde les données dans un tableau
$file = "inscriptions.csv";
$data = [];
if (($handle = fopen($file, "r")) !== FALSE) {
	fgetcsv($handle, 1000, ","); // Ignorer la première ligne (en-têtes de colonne)
	while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$row[] = 0;
		$data[] = $row;
	}
	fclose($handle);
}

// Prendre la couleur de l'utilisateur connecté
$userColor = '';
for ($i = 0; $i < count($data); $i++) {
	if ($data[$i][0] === $_SESSION['pseudo']) {
		$userColor = $data[$i][4];
		break;
	}
}

// Garder la couleur actuelle du pseudo dans une variable de session
$_SESSION['color'] = $userColor;

$userEntry = null;
foreach ($data as $row) {
	if ($row[0] === $_SESSION['pseudo']) {
		$userEntry = $row;
		break;
	}
}

// Si l'utilisateur est trouvé, on récupére son score
$userScore = 0; // Score par défaut si l'utilisateur n'est pas trouvé
if ($userEntry !== null) {
	$userScore = $userEntry[3]; // Score à la 4ème place (index 3)
}

// Garder le score actuel de l'utilisateur dans une variable
$_SESSION['score'] = $userScore;

function addMessage($message) {
	$messages = getAllMessages();
	$messages[] = $message;
	file_put_contents('messages.json', json_encode($messages));
}

// On vérifie si un message a été envoyé
if (isset($_POST['message'])) {
	$newMessage = $_POST['message'];
	addMessage($newMessage);
}

function genererNomsCartes() {
	$suites = ['coeur', 'carreau', 'pique', 'trefle'];
	$valeurs = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'valet', 'dame', 'roi', 'as'];
	$cartes = [];
	foreach ($suites as $suite) {
		foreach ($valeurs as $valeur) {
			$cartes[] = $valeur . '_' . $suite . '.png';
		}
	}
	return $cartes;
}

// Fonction pour distribuer les cartes au croupier et au joueur
function distribuerCartes(&$paquet, &$croupier, &$joueur) {
	shuffle($paquet);
	$croupier = array_slice($paquet, 0, 2);
	$joueur = array_slice($paquet, 2, 2);
}

function afficherCartes($cartes, $elementId) {
	foreach ($cartes as $carte) {
		echo '<img src="cartes/' . $carte . '" alt="' . $carte . '">';
	}
	echo '<div id="totalJoueur">Total : ' . calculerValeurCartes($cartes) . '</div>';
}

// Fonction pour calculer la valeur des cartes
function calculerValeurCartes($cartes) {
	$total = 0;
	$as = 0; // Compteur d'as pour ajuster la valeur (1 ou 11)
	foreach ($cartes as $carte) {
		$valeur = substr($carte, 0, strpos($carte, '_'));
		if ($valeur === 'valet' || $valeur === 'dame' || $valeur === 'roi') {
			$valeur = 10;
		} elseif ($valeur === 'as') {
			$as++; // On incrémente le compteur d'as
			$valeur = 11;
		} else {
			$valeur = intval($valeur);
		}
		$total += $valeur;
	}
	// Si le total dépasse 21 et il y a des as, on ajuste la valeur des as
	while ($total > 21 && $as > 0) {
		$total -= 10; // On réduit la valeur de l'as de 11 à 1
		$as--; // On décrémente le compteur d'as
	}
	return $total;
}

// Génération des noms de fichiers des cartes
$paquet = genererNomsCartes();
$croupier = [];
$joueur = [];
distribuerCartes($paquet, $croupier, $joueur);
$boutonsDesactives = false;
$_SESSION['decision_prise'] = isset($_SESSION['decision_prise']) ? $_SESSION['decision_prise'] : false;

// Si le joueur clique sur "TIRER"
if (isset($_POST['action']) && $_POST['action'] === 'tirer') {
	// Ajouter une nouvelle carte au joueur
	$nouvelleCarte = array_pop($paquet);
	$joueur[] = $nouvelleCarte;
	$valeurJoueur = calculerValeurCartes($joueur); // On calcule la nouvelle valeur des cartes du joueur
	$valeurTroisiemeCarte = calculerValeurCartes([$nouvelleCarte]); // On récupére la valeur de la troisième carte
	$boutonsDesactives = $valeurJoueur >= 21;// On détermine si les boutons de jeu doivent être désactivés en fonction du résultat total
	// On renvoie la nouvelle carte et la valeur totale des cartes et la valeur de la troisième carte
	echo json_encode(['nouvelle_carte' => $nouvelleCarte, 'valeur_joueur' => $valeurJoueur, 'valeur_troisieme_carte' => $valeurTroisiemeCarte, 'boutons_desactives' => $boutonsDesactives]);
	exit;
}

// Si le joueur clique sur "RESTER"
if (isset($_POST['action']) && $_POST['action'] === 'piocher') {
	$nouvelleCarteCroupier = array_pop($paquet); // On donne une nouvelle carte au croupier
	$valeurCarteCroupier = calculerValeurCartes([$nouvelleCarteCroupier]); // On calcule la valeur de la nouvelle carte
	// On renvoie la nouvelle carte et sa valeur en JSON
	echo json_encode(['nouvelle_carte' => $nouvelleCarteCroupier, 'valeur_carte' => $valeurCarteCroupier]);
	exit;
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VegasBlackJack - Entrainement</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="bases.css">
    <style>

    </style>
</head>
<body class="connexion">
    <img src="images/vegaslogo.png" alt="Vegas Logo" id="vegas-logoJ">

    <div id="info-joueur">
        <div id="info-joueur-title">
            <span style="color: #981b22; display: inline-block; width: 10px; background-color: #981b22; height: 3px;vertical-align: middle;"></span>
            ENTRAINEMENT
            <span style="color: #981b22; display: inline-block; width: 10px; background-color: #981b22; height: 3px;vertical-align: middle;"></span>
        </div>
        <div id="pseudo" style="color: <?= $userColor ?>; font-size: 30px; font-weight: bold;">
            <?php echo $_SESSION['pseudo']; ?>
        </div>
        <br>
        <div id="pseudo" style="color: #fa9419; font-size: 30px;">
            Score : <?php echo $_SESSION['score']; ?>
        </div>
    </div>

    <div id="infosTraining-joueurs">
        <div id="infosTraining-joueurs-title">
            <span style="color: #981b22; display: inline-block; width: 10px; background-color: #981b22; height: 3px;vertical-align: middle;"></span>
            EN LIGNE
            <span style="color: #981b22; display: inline-block; width: 10px; background-color: #981b22; height: 3px;vertical-align: middle;"></span>
            <div>
                <button class="enLigne-image">
                    <img src="images/online.png" id="enLigneButton" alt="ligne">
                    <div class="enLigne-texte">EN LIGNE</div>
                </button>
            </div>
        </div>
    </div>

    <div id="interaction">
        <div id="interaction-title">
            <span style="color: #981b22; display: inline-block; width: 10px; background-color: #981b22; height: 3px;vertical-align: middle;"></span>
            INTERACTIONS
            <span style="color: #981b22; display: inline-block; width: 10px; background-color: #981b22; height: 3px;vertical-align: middle;"></span>
        </div>
        <div id="boutonsJeux" style="display: none;">
            <button class="tirer-image">
                <img src="images/tirer.png" alt="tirer">
                <div class="tirer-texte">TIRER</div>
            </button><br><br><br>
            <button class="rester-image">
                <img src="images/rester.png" alt="rester">
                <div class="rester-texte">RESTER</div>
            </button><br><br><br>
            <button class="recommencer-image" id="recommencerButton">
                <img src="images/recommencer.png" alt="recommencer">
                <div class="recommencer-texte">RECOMMENCER</div>
            </button>
        </div>
    </div>

    <div id="boutonsOptions">
        <div class="additional-buttons">
            <button onclick="openPopup()">RÈGLES</button>
            <button id="quitButton">QUITTER</button>
        </div>
    </div>

    <div id="popup" class="popup">
        <div class="popup-content">
            <span class="close" onclick="closePopup()">&times;</span>
            <h2>RÈGLES DU JEU</h2>
            <h3>OBJECTIF :</h3>
            <p><span class="jaune">Le but du jeu est d'obtenir un score plus élevé que le croupier sans dépasser 21.</span></p>
            <h3>DÉROULEMENT :</h3>
            <p><span class="jaune">Le croupier distribue deux cartes à chaque joueur. Ils peuvent choisir de "tirer" ou "rester". Si un joueur dépasse 21, il perd. Le croupier joue ensuite. S'il a moins de 17, il tire une carte. S'il dépasse 21, les joueurs gagnent. Sinon, le plus haut score gagne.</span></p>
            <h3>VALEURS DES CARTES :</h3>
            <p><span class="jaune">Du 2 au 9 : valeur nominale. 10, Valets, <br>Dames, Rois : 10. As : 1 ou 11 selon la main. <br>Blackjack : As + 10, totalisant 21.</span></p>
            <a href="#" class="close-bouton" id="jouer-bouton" onclick="closePopup()"><strong>JOUER</strong></a>
        </div>
    </div>

    <div id="tchat-game">
        <div id="tchatGame-title">
            <span style="color: #981b22; display: inline-block; width: 10px; background-color: #981b22; height: 3px;vertical-align: middle;"></span>
            DISCUSSION
            <span style="color: #981b22; display: inline-block; width: 10px; background-color: #981b22; height: 3px;vertical-align: middle;"></span>
        </div>
        <input type="text" id="messageInputGame" placeholder="Entrez votre message...">
        <button class="send-button" onclick="sendMessage('messageInputGame')">ENVOYER</button>
        <div class="toggle-squareD"></div>
        <div class="toggle-squareE" id="squareE"></div>
    </div>

    <div id="ovals">
        <div id="outerOval">
            <div id="innerOvalTraining">
                <div id="cartesCroupier" class="cartes-croupier" style="display: none;">
                    Cartes du croupier :<br><br>
                    <div id="totalCroupier">Score : <?php echo calculerValeurCartes($croupier); ?></div>
                </div>

                <div id="cartesJoueur" class="cartes-joueur" style="display: none;">
                    Vos cartes :<br><br>
                    <div id="totalJoueur">Score : <?php echo calculerValeurCartes($joueur); ?></div>
                </div>
                <div class="lancer">
                    <button id="lancerPartie" class="lancer-bouton">
                        <img src="images/start.png" alt="Lancer la partie" width="600" height="600">
                    </button>
                </div>
            </div>
        </div>
    </div>
<script>

document.getElementById("recommencerButton").addEventListener("click", function() {
	location.reload();
});

document.getElementById('lancerPartie').addEventListener('click', function() {
    // On cache le bouton "START"
    document.getElementById('lancerPartie').style.display = 'none';

    // On affiche les cartes du croupier avec un délai
    setTimeout(function() {
        document.getElementById('cartesCroupier').style.display = 'block';

        // On affiche les cartes du croupier
        afficherCartes(<?php echo json_encode($croupier); ?>, 'cartesCroupier');

        setTimeout(function() {
            document.getElementById('cartesJoueur').style.display = 'block';

            // On affiche les cartes du joueur
            afficherCartes(<?php echo json_encode($joueur); ?>, 'cartesJoueur');

            // Afficher les boutons disponibles avec un délai supplémentaire
            setTimeout(function() {
                document.getElementById('boutonsJeux').style.display = 'block';
            }, 2000);
        }, 1000);
    }, 1000);
});

document.querySelector('.rester-image').addEventListener('click', function() {
    // On récupére le total des cartes du joueur
    var totalJoueur = parseInt(document.getElementById('totalJoueur').textContent.split(' ')[2]);

    // On récupére le total des cartes du croupier
    var totalCroupier = <?php echo calculerValeurCartes($croupier); ?>;

    // On vérifie si le total du croupier est inférieur à 17
    if (totalCroupier < 17 && totalCroupier < totalJoueur){
        // Effectuer une requête AJAX pour que le croupier pioche une carte
        $.ajax({
            url: 'entrainement.php',
            type: 'POST',
            data: { action: 'piocher' },
            dataType: 'json',
            success: function(response) {
                // On met à jour l'affichage avec la nouvelle carte du croupier
                document.getElementById('cartesCroupier').innerHTML += '<img src="cartes/' + response.nouvelle_carte + '" alt="' + response.nouvelle_carte + '">';
                // On ajoute la valeur de la nouvelle carte au total du croupier
                totalCroupier += response.valeur_carte;
                // On met à jour l'affichage du total du croupier
                document.getElementById('totalCroupier').textContent = 'Total : ' + totalCroupier;
                // Après chaque pioche, on vérifie si le total du croupier est toujours inférieur à 17 ou pas
                if (totalCroupier < 17) {
                    // Si le total est toujours inférieur à 17 alors il piocher à nouveau
                    $.ajax(this);
                } else {
                    // Sinon, on compare les résultats
                    comparerResultats(totalJoueur, totalCroupier);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur lors de la requête AJAX : ' + error);
            }
        });
    } else {
        // Si le total du croupier est déjà supérieur ou égal à 17, on compare directement les résultats
        comparerResultats(totalJoueur, totalCroupier);
    }
    // On cache les boutons après que le joueur ait décidé de rester
    document.getElementById('boutonsJeux').style.display = 'none';
});

function comparerResultats(totalJoueur, totalCroupier) {
    setTimeout(function() {
        let resultatElement = document.createElement('div');
        resultatElement.className = 'resultat';
        if (totalJoueur > 21) {
            // Si le joueur a plus de 21, le croupier gagne
            resultatElement.innerHTML = '<span class="res">DÉFAITE</span><img src="images/defaite.png" alt="Défaite" class="defaite"><span class="restexte">Vous avez perdu !<br></span><span class="sauter">(Vous avez sauté)';
        } else if (totalCroupier > 21) {
            // Si le croupier a plus de 21, le joueur gagne
            resultatElement.innerHTML = '<span class="res">VICTOIRE</span><img src="images/victoire.png" alt="Victoire" class="victoire"><span class="restexte">Vous avez gagné !<br></span><span class="sauter">(Le croupier a sauté)';
        } else if (totalCroupier <= 21) {
            if (totalJoueur > totalCroupier) {
                // On affiche Victoire si le joueur a un total supérieur au croupier
                resultatElement.innerHTML = '<span class="res">VICTOIRE</span><br><img src="images/victoire.png" alt="Victoire" class="victoire"><span class="restexte">Vous avez gagné !</span>';
            } else if (totalJoueur < totalCroupier) {
                // On affiche Défaite si le joueur a un total inférieur au croupier
                resultatElement.innerHTML = '<span class="res">DÉFAITE</span><img src="images/defaite.png" alt="Défaite" class="defaite"><span class="restexte">Vous avez perdu !</span>';
            } else {
                // Afficher Égalité si égalité
                resultatElement.innerHTML = '<span class="res">ÉGALITÉ</span><img src="images/egal.png" alt="Égalité" class="egalite"><span class="restexte">Score identique !</span>';
            }
        }

// Centrer le résultat à la page
resultatElement.style.display = 'flex';
resultatElement.style.flexDirection = 'column';
resultatElement.style.alignItems = 'center';
resultatElement.style.justifyContent = 'center';
resultatElement.style.height = '83vh'; // Pour centrer verticalement à la page
resultatElement.style.position = 'initial';
resultatElement.style.transform = 'none';

document.getElementById('innerOvalTraining').innerHTML = '';
document.getElementById('innerOvalTraining').appendChild(resultatElement);

// Ajouter le bouton "Recommencer" à la page
let restartButton = document.createElement('button');
restartButton.className = 'restart-image';
restartButton.innerHTML = '<img src="images/recommencer.png" alt="Restart"><div class="restart-texte">RECOMMENCER</div>';
restartButton.addEventListener('click', function() {
    location.reload();
});

// On ajoute le bouton "Recommencer" à la page
document.body.appendChild(restartButton);
    }, 2000);
}

function afficherCartes(cartes, elementId) {
    var html = '';
    cartes.forEach(function(carte) {
        html += '<img src="cartes/' + carte + '" alt="' + carte + '">';
    });
    document.getElementById(elementId).innerHTML += html;
}
        var valeurCroupier = <?php echo calculerValeurCartes($croupier); ?>;
        var valeurJoueur = <?php echo calculerValeurCartes($joueur); ?>;
        document.getElementById("quitButton").addEventListener("click", function() {
            window.location.href = "attente.php";
        });
        document.getElementById("enLigneButton").addEventListener("click", function() {
            window.location.href = "attente.php";
        });

// Ajout d'une action pour le bouton "TIRER"
document.querySelector('.tirer-image').addEventListener('click', function() {
    // On effectue une requête AJAX pour tirer une nouvelle carte
    $.ajax({
        url: 'entrainement.php',
        type: 'POST',
        data: { action: 'tirer' },
        dataType: 'json',
        success: function(response) {
            // On met à jour l'affichage avec la nouvelle carte
            document.getElementById('cartesJoueur').innerHTML += '<img src="cartes/' + response.nouvelle_carte + '" alt="' + response.nouvelle_carte + '">';
            // On met à jour la valeur totale des cartes du joueur
            var totalJoueurElement = document.getElementById('totalJoueur');
            var totalJoueur = parseInt(totalJoueurElement.textContent.split(' ')[2]); // On récupère le total actuel
            totalJoueur += response.valeur_troisieme_carte; // On ajoute la valeur de la nouvelle carte
            totalJoueurElement.textContent = 'Total : ' + totalJoueur; // On met à jour l'affichage du total
            // On vérifie si le total des cartes du joueur dépasse 21 ou s'il est égal à 21
            if (totalJoueur >= 21) {
                document.querySelector('.tirer-image').disabled = true;
                comparerResultats(totalJoueur, <?php echo calculerValeurCartes($croupier); ?>);
            }
            // On vérifie si les boutons de jeu doivent être désactivés en fonction du total des cartes du joueur
            if (totalJoueur >= 21) {
                document.getElementById('boutonsJeux').style.display = 'none';
            } else {
                document.getElementById('boutonsJeux').style.display = 'block';
            }
        },
        error: function(xhr, status, error) {
            console.error('Erreur lors de la requête AJAX : ' + error);
        }
    });
});
</script>
    <script src="script.js"></script>
    <script src="tchat.js"></script>
<script>
fetchNewMessages('squareE');
</script>
</body>
</html>