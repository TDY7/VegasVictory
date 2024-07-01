function togglePasswordVisibility() {
    const passwordInput = document.getElementById("password");
    const passwordToggleImg = document.getElementById("password-toggle-img");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        passwordToggleImg.src = "images/eye-open.png"; // Chemin vers l'icône d'œil ouvert
    } else {
        passwordInput.type = "password";
        passwordToggleImg.src = "images/eye-closed.png"; // Chemin vers l'icône d'œil fermé
    }
}

$(document).ready(function() {
    // Fonction pour envoyer une requête AJAX pour mettre à jour le compteur de joueurs
    function updatePlayerCounter() {
        $.ajax({
            url: 'get_player_counter.php',
            method: 'GET',
            success: function(response) {
                $('#player-counter').text(response + ' joueur(s) présent(s) dans la partie');
            },
            error: function(error) {
                console.error('Erreur lors de la requête AJAX :', error);
            }
        });
    }

    // Fonction pour envoyer une requête AJAX pour mettre à jour le chronomètre
    function updateTimer() {
        $.ajax({
            url: 'get_timer.php',
            method: 'GET',
            success: function(response) {
                $('#timer').text(response);
            },
            error: function(error) {
                console.error('Erreur lors de la requête AJAX :', error);
            }
        });
    }

    // Fonction pour envoyer une requête AJAX pour rejoindre la partie
    function joinGame() {
        $.ajax({
            url: 'join_game.php',
            method: 'POST',
            success: function(response) {
                // Si la requête réussit, rediriger l'utilisateur vers jeu.php
                window.location.href = 'jeu.php';
            },
            error: function(error) {
                console.error('Erreur lors de la requête AJAX :', error);
            }
        });
    }

    // Fonction pour envoyer une requête AJAX pour commencer l'entraînement
    function startTraining() {
        // Si la requête réussit, rediriger l'utilisateur vers entrainement.php
        window.location.href = 'entrainement.php';
    }

    // Appeler la fonction updatePlayerCounter toutes les 5 secondes
    setInterval(updatePlayerCounter, 5000);

    // Ajouter un écouteur d'événement au bouton "JOUER"
    $('#join-game-button').click(function() {
        joinGame();
    });

    // Ajouter un écouteur d'événement au bouton "S'ENTRAINER"
    $('#practice-button').click(function() {
        startTraining();
    });
});

function openPopup() {
    var popup = document.getElementById("popup");
    popup.classList.add("show");
    popup.style.display = "block";
    popup.addEventListener("animationend", function() {
        popup.style.display = "none";
    });
    document.getElementById("jouer-bouton").style.display = "inline-block";
}

function closePopup() {
    var popup = document.getElementById("popup");
    popup.classList.remove("show");
    popup.style.display = "block";
    popup.addEventListener("animationend", function() {
        popup.style.display = "none";
    });
}

function openPopupJeu() {
    var popup = document.getElementById("popup-jeu");
    popup.classList.add("show");
    popup.style.display = "block";
    popup.addEventListener("animationend", function() {
        popup.style.display = "none";
    });
}

function closePopupJeu() {
    var popup = document.getElementById("popup-jeu");
    popup.classList.remove("show");
    popup.style.display = "block";
    popup.addEventListener("animationend", function() {
        popup.style.display = "none";
    });
}




// Appeler openPopupJeu() après un délai de 2 secondes
setTimeout(function(){
    openPopupJeu();
}, 2000);

