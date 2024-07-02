function sendMessage(squareId) {
    const message = document.getElementById(squareId).value.trim();
    if (message !== '') {
        $.ajax({
            url: 'save_message.php',
            method: 'POST',
            data: { message: message },
            success: function(response) {
                displayMessage(response, squareId); // Passer l'identifiant du carré auquel ajouter le message
            },
            error: function(error) {
                console.error('Erreur lors de l\'envoi du message :', error);
            }
        });
        document.getElementById(squareId).value = '';
    }
}

function displayMessage(message, squareId) {
    const toggleSquare = document.getElementById(squareId);
    let maxSquareHeight = 320; // Hauteur maximale par défaut

    // Modifier la hauteur maximale en fonction de l'identifiant du carré
    if (squareId === 'squareE') {
        maxSquareHeight = 680; // Nouvelle hauteur maximale pour squareE
    }
    else if (squareId === 'squareF') {
        maxSquareHeight = 500; // Nouvelle hauteur maximale pour squareE
    }

    // Extraire l'heure, le pseudo, la couleur et le message à partir de la chaîne reçue
    const parts = message.split(" - ");
    const time = parts[0];
    const msgParts = parts[1].split(":");
    const pseudo = msgParts[0];
    const color = msgParts[1];
    const msg = msgParts.slice(2).join(":");

    // Créer un élément pour l'heure du message
    const timeElement = document.createElement('span');
    timeElement.textContent = time;

    // Créer un élément pour le " - " entre l'heure et le pseudo
    const separatorTimeElement = document.createElement('span');
    separatorTimeElement.textContent = "  ";

    // Créer un élément pour le pseudo de l'utilisateur avec sa couleur et en gras
    const pseudoElement = document.createElement('span');
    pseudoElement.textContent = pseudo;
    pseudoElement.style.color = color;
    pseudoElement.style.fontWeight = 'bold'; // Appliquer le style gras au pseudo

    // Créer un élément pour le ":" entre le pseudo et le message
    const separatorElement = document.createElement('span');
    separatorElement.textContent = ": ";

    // Créer un élément pour le message
    const messageElement = document.createElement('span');
    messageElement.textContent = msg;

    // Créer un élément <br> pour le saut de ligne
    const lineBreak = document.createElement('br');

    // Ajouter les éléments dans le carré
    toggleSquare.appendChild(timeElement);
    toggleSquare.appendChild(separatorTimeElement); // Ajouter " ! " entre l'heure et le pseudo
    toggleSquare.appendChild(pseudoElement);
    toggleSquare.appendChild(separatorElement);
    toggleSquare.appendChild(messageElement);
    toggleSquare.appendChild(lineBreak); // Ajouter le saut de ligne

    // Mesurer la hauteur totale du carré de discussion après l'ajout du message
    const squareHeight = toggleSquare.scrollHeight;

    // Vérifier si la hauteur totale dépasse la hauteur maximale
    if (squareHeight > maxSquareHeight) {
        // Ajuster la taille du carré de discussion pour qu'elle soit égale à la hauteur maximale
        toggleSquare.style.height = maxSquareHeight + 'px';
        // Activer la barre de défilement verticale si nécessaire
        toggleSquare.style.overflowY = 'auto';
    }

toggleSquare.scrollTop = toggleSquare.scrollHeight;
}

document.getElementById('messageInput').addEventListener('keydown', function(event) {
    if (event.keyCode === 13) { // Vérifier si la touche appuyée est la touche "Entrée"
        sendMessage(); // Appeler la fonction sendMessage pour envoyer le message
        document.getElementById('messageInput').value = '';
    }
});

function fetchNewMessages(squareId) {
    $.ajax({
        url: 'get_messages.php',
        method: 'GET',
        dataType: 'json',
        success: function(messages) {
            messages.forEach(function(message) {
                displayMessage(message, squareId);
            });
            // Appeler fetchNewMessages() à nouveau après avoir reçu les nouveaux messages
            fetchNewMessages(squareId);
        },
        error: function(error) {
            console.error('Erreur lors de la récupération des nouveaux messages :', error);
            // Appeler fetchNewMessages() à nouveau en cas d'erreur pour continuer à écouter les nouveaux messages
            fetchNewMessages(squareId);
        }
    });
}

// Appeler fetchNewMessages() pour la première fois lorsque la page est chargée
fetchNewMessages('squareB'); // Pour squareB