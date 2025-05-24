<?php
// db.php
$servername = "localhost"; // Généralement "localhost" pour un environnement de développement
$username = "root";     // Le nom d'utilisateur de votre base de données MySQL (souvent "root" par défaut)
$password = "";         // Le mot de passe de votre base de données MySQL (souvent vide par défaut)
$dbname = "ecommerce_simple"; // Le nom de la base de données que vous avez créée

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Configure le mode d'erreur PDO pour lancer des exceptions en cas d'erreur
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Assure que les caractères accentués et spéciaux sont correctement gérés (UTF-8)
    $conn->exec("SET NAMES 'utf8mb4'");
    // echo "Connexion réussie"; // Décommenter cette ligne pour tester si la connexion fonctionne
} catch(PDOException $e) {
    // Affiche un message d'erreur et arrête le script si la connexion échoue
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>