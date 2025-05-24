<?php
// index.php
include 'header.php'; // Inclut l'en-tête commun à toutes les pages
require_once 'db.php'; // Inclure la connexion à la DB

$message_accueil = "";
if (isset($_SESSION['nom_utilisateur'])) {
    $message_accueil = "Bienvenue, <strong>" . htmlspecialchars($_SESSION['nom_utilisateur']) . "</strong> !";
} else {
    $message_accueil = "Bienvenue sur Mon E-commerce Simple !";
}

// Récupérer quelques produits phares (ex: les 4 derniers ajoutés ou les 4 premiers)
$produits_phares = [];
try {
    $stmt_phares = $conn->query("SELECT id_produit, nom, prix, quantite_stock, description, image FROM produits ORDER BY id_produit DESC LIMIT 4");
    $produits_phares = $stmt_phares->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Gérer l'erreur si besoin, par exemple, logguer
    // echo "<p class='error-message' style='text-align: center;'>Impossible de charger les produits phares.</p>";
}
?>
        <section class="hero-section">
            <div class="hero-content">
                <h2><?php echo $message_accueil; ?></h2>
                <p>Découvrez notre sélection exclusive de produits de haute qualité. Trouvez ce qu'il vous faut, livré directement chez vous !</p>
                <a href="produits.php" class="button"><i class="fas fa-shopping-bag"></i> Commencer mes achats</a>
            </div>
            </section>

        <?php if (!empty($produits_phares)): ?>
            <section class="featured-products">
                <h2 style="text-align: center;">Produits Phares</h2>
                <div class="product-grid">
                    <?php foreach ($produits_phares as $produit): ?>
                        <div class="product-card">
                            <img src="uploads/<?php echo htmlspecialchars($produit['image']); ?>" alt="<?php echo htmlspecialchars($produit['nom']); ?>">
                            <h3><?php echo htmlspecialchars($produit['nom']); ?></h3>
                            <p class="price"><?php echo number_format($produit['prix'], 2, ',', ' '); ?> €</p>
                            <p class="stock">Stock: <?php echo htmlspecialchars($produit['quantite_stock']); ?></p>
                            <p><?php echo htmlspecialchars(substr($produit['description'], 0, 80)) . (strlen($produit['description']) > 80 ? '...' : ''); ?></p>
                            <a href="produits.php" class="button button-small"><i class="fas fa-eye"></i> Voir le produit</a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p style="text-align: center; margin-top: 30px;">
                    <a href="produits.php" class="button button-secondary"><i class="fas fa-list"></i> Voir tous les produits</a>
                </p>
            </section>
        <?php endif; ?>

        <section class="info-section">
            <h2 style="text-align: center;">Pourquoi nous choisir ?</h2>
            <div class="info-grid">
                <div class="info-item">
                    <i class="fas fa-truck fa-3x"></i>
                    <h3>Livraison Rapide</h3>
                    <p>Recevez vos articles en un temps record directement à votre porte.</p>
                </div>
                <div class="info-item">
                    <i class="fas fa-shield-alt fa-3x"></i>
                    <h3>Paiement Sécurisé</h3>
                    <p>Vos transactions sont protégées par les meilleures technologies de sécurité.</p>
                </div>
                <div class="info-item">
                    <i class="fas fa-headset fa-3x"></i>
                    <h3>Support Client</h3>
                    <p>Notre équipe est là pour vous 24/7 pour répondre à toutes vos questions.</p>
                </div>
            </div>
        </section>

<?php
include 'footer.php'; // Inclut le pied de page commun
?>