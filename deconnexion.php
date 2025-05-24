<?php
// deconnexion.php
session_start(); // Démarrer la session

// Supprime toutes les variables de session
session_unset();

// Détruit la session (supprime le fichier de session sur le serveur)
session_destroy();

// Redirige l'utilisateur vers la page d'accueil ou de connexion
header("Location: index.php");
exit();
?>