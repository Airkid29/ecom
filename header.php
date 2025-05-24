<?php // header.php
// Vérifie si la session n'est pas déjà démarrée pour éviter les erreurs
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFO e-Shop</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <h1><a href="index.php">AFO e-Shop</a></h1>
        <nav>
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i> Accueil</a></li>
                <li><a href="produits.php"><i class="fas fa-box-open"></i> Produits</a></li>
                <li><a href="panier.php"><i class="fas fa-shopping-cart"></i> Panier (<?php echo isset($_SESSION['panier']) ? count($_SESSION['panier']) : 0; ?>)</a></li>
                <?php if (isset($_SESSION['id_utilisateur'])): ?>
                    <li><a href="mes_commandes.php"><i class="fas fa-clipboard-list"></i> Mes Commandes</a></li>
                    <li><a href="deconnexion.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="connexion.php"><i class="fas fa-user-circle"></i> Connexion</a></li>
                    <li><a href="inscription.php"><i class="fas fa-user-plus"></i> Inscription</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>