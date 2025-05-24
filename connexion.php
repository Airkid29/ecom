<?php
// connexion.php
session_start(); // Démarrer la session en premier
require_once 'db.php'; // Inclure le fichier de connexion à la base de données

$erreur = ""; // Variable pour stocker les messages d'erreur

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom_utilisateur = trim($_POST['nom_utilisateur']); // Supprimer les espaces avant/après
    $mot_de_passe = $_POST['mot_de_passe'];

    if (empty($nom_utilisateur) || empty($mot_de_passe)) {
        $erreur = "Veuillez remplir tous les champs.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT id_utilisateur, nom_utilisateur, mot_de_passe FROM utilisateurs WHERE nom_utilisateur = :nom_utilisateur");
            $stmt->bindParam(':nom_utilisateur', $nom_utilisateur);
            $stmt->execute();
            $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
                // Connexion réussie : stocker les informations de l'utilisateur en session
                $_SESSION['id_utilisateur'] = $utilisateur['id_utilisateur'];
                $_SESSION['nom_utilisateur'] = $utilisateur['nom_utilisateur'];
                header("Location: index.php"); // Rediriger vers la page d'accueil
                exit();
            } else {
                $erreur = "Nom d'utilisateur ou mot de passe incorrect.";
            }
        } catch (PDOException $e) {
            $erreur = "Erreur lors de la connexion : " . $e->getMessage();
        }
    }
}

include 'header.php'; // Inclut l'en-tête commun
?>
        <h2>Connexion</h2>
        <?php if (!empty($erreur)): ?>
            <p class="error-message"><?php echo htmlspecialchars($erreur); ?></p>
        <?php endif; ?>
        <form action="connexion.php" method="post">
            <label for="nom_utilisateur">Nom d'utilisateur :</label>
            <input type="text" id="nom_utilisateur" name="nom_utilisateur" required><br>
            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required><br>
            <input type="submit" value="Se connecter">
        </form>
        <p style="text-align: center; margin-top: 20px;">
            Pas encore de compte ? <a href="inscription.php">Inscrivez-vous ici</a>.
        </p>
<?php
include 'footer.php'; // Inclut le pied de page commun
?>