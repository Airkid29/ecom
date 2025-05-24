<?php
// inscription.php
session_start();
require_once 'db.php';

$erreur = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom_utilisateur = trim($_POST['nom_utilisateur']);
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];
    $mot_de_passe_conf = $_POST['mot_de_passe_conf'];

    if (empty($nom_utilisateur) || empty($email) || empty($mot_de_passe) || empty($mot_de_passe_conf)) {
        $erreur = "Veuillez remplir tous les champs.";
    } elseif ($mot_de_passe !== $mot_de_passe_conf) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($mot_de_passe) < 6) { // Exemple de validation de longueur
        $erreur = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        // Hacher le mot de passe avant de le stocker pour la sécurité
        $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);

        try {
            $stmt = $conn->prepare("INSERT INTO utilisateurs (nom_utilisateur, mot_de_passe, email) VALUES (:nom_utilisateur, :mot_de_passe, :email)");
            $stmt->bindParam(':nom_utilisateur', $nom_utilisateur);
            $stmt->bindParam(':mot_de_passe', $hashed_password);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            // Inscription réussie : connecter l'utilisateur immédiatement
            $_SESSION['id_utilisateur'] = $conn->lastInsertId();
            $_SESSION['nom_utilisateur'] = $nom_utilisateur;
            header("Location: index.php"); // Rediriger vers la page d'accueil
            exit();
        } catch (PDOException $e) {
            // Gérer les erreurs, notamment les contraintes d'unicité (nom d'utilisateur/email déjà pris)
            if ($e->getCode() == '23000') { // SQLSTATE pour violation de contrainte d'unicité
                $erreur = "Ce nom d'utilisateur ou cet e-mail est déjà utilisé.";
            } else {
                $erreur = "Erreur lors de l'inscription : " . $e->getMessage();
            }
        }
    }
}

include 'header.php';
?>
        <h2>Inscription</h2>
        <?php if (!empty($erreur)): ?>
            <p class="error-message"><?php echo htmlspecialchars($erreur); ?></p>
        <?php endif; ?>
        <form action="inscription.php" method="post">
            <label for="nom_utilisateur">Nom d'utilisateur :</label>
            <input type="text" id="nom_utilisateur" name="nom_utilisateur" required><br>
            <label for="email">E-mail :</label>
            <input type="email" id="email" name="email" required><br>
            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required><br>
            <label for="mot_de_passe_conf">Confirmer le mot de passe :</label>
            <input type="password" id="mot_de_passe_conf" name="mot_de_passe_conf" required><br>
            <input type="submit" value="S'inscrire">
        </form>
        <p style="text-align: center; margin-top: 20px;">
            Déjà un compte ? <a href="connexion.php">Connectez-vous ici</a>.
        </p>
<?php
include 'footer.php';
?>