<?php
// produits.php
session_start();
require_once 'db.php';

$message_erreur = "";
$message_succes = "";

// Récupérer les produits depuis la base de données
try {
    $stmt = $conn->query("SELECT id_produit, nom, prix, quantite_stock, description, image FROM produits ORDER BY nom ASC");
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message_erreur = "Erreur lors du chargement des produits : " . $e->getMessage();
    $produits = []; // Assurez-vous que $produits est un tableau vide en cas d'erreur
}


// Gestion de l'ajout au panier (via session)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter_panier'])) {
    $id_produit = intval($_POST['id_produit']);
    $quantite_ajoutee = intval($_POST['quantite']);

    if ($quantite_ajoutee <= 0) {
        $message_erreur = "La quantité doit être supérieure à zéro.";
    } else {
        try {
            // Récupérer les détails du produit pour vérifier la quantité en stock et le prix
            $stmt_prod = $conn->prepare("SELECT nom, prix, quantite_stock FROM produits WHERE id_produit = :id_produit");
            $stmt_prod->bindParam(':id_produit', $id_produit);
            $stmt_prod->execute();
            $produit_detail = $stmt_prod->fetch(PDO::FETCH_ASSOC);

            if ($produit_detail) {
                // Initialiser le panier si ce n'est pas déjà fait
                if (!isset($_SESSION['panier'])) {
                    $_SESSION['panier'] = [];
                }

                // Calculer la quantité totale demandée (quantité déjà dans le panier + nouvelle quantité)
                $quantite_actuelle_panier = isset($_SESSION['panier'][$id_produit]) ? $_SESSION['panier'][$id_produit]['quantite'] : 0;
                $quantite_totale_demandee = $quantite_actuelle_panier + $quantite_ajoutee;

                // Vérifier la quantité en stock par rapport à la quantité totale demandée
                if ($quantite_totale_demandee <= $produit_detail['quantite_stock']) {
                    // Ajouter ou mettre à jour le produit dans le panier
                    $_SESSION['panier'][$id_produit] = [
                        'nom' => $produit_detail['nom'],
                        'prix' => $produit_detail['prix'],
                        'quantite' => $quantite_totale_demandee
                    ];
                    $message_succes = htmlspecialchars($produit_detail['nom']) . " a été ajouté à votre panier !";
                    // Pas de redirection immédiate pour permettre l'affichage du message de succès
                    // header("Location: panier.php"); // Décommenter si vous voulez rediriger directement
                    // exit();
                } else {
                    $message_erreur = "Quantité insuffisante en stock pour " . htmlspecialchars($produit_detail['nom']) . ". (Disponible: " . htmlspecialchars($produit_detail['quantite_stock']) . ", déjà dans le panier: " . htmlspecialchars($quantite_actuelle_panier) . ")";
                }
            } else {
                $message_erreur = "Produit introuvable.";
            }
        } catch (PDOException $e) {
            $message_erreur = "Erreur lors de l'ajout au panier : " . $e->getMessage();
        }
    }
}

include 'header.php';
?>
        <h2>Nos Produits</h2>
        <?php if (!empty($message_erreur)): ?>
            <p class="error-message"><?php echo $message_erreur; ?></p>
        <?php endif; ?>
        <?php if (!empty($message_succes)): ?>
            <p class="success-message"><?php echo $message_succes; ?></p>
        <?php endif; ?>

        <?php if (empty($produits)): ?>
            <p style="text-align: center;">Aucun produit n'est disponible pour le moment.</p>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($produits as $produit): ?>
                    <div class="product-card">
                        <img src="uploads/<?php echo htmlspecialchars($produit['image']); ?>" alt="<?php echo htmlspecialchars($produit['nom']); ?>">
                        <h3><?php echo htmlspecialchars($produit['nom']); ?></h3>
                        <p class="price"><?php echo number_format($produit['prix'], 2, ',', ' '); ?> €</p>
                        <p class="stock">Stock: <?php echo htmlspecialchars($produit['quantite_stock']); ?></p>
                        <p><?php echo htmlspecialchars(substr($produit['description'], 0, 100)) . (strlen($produit['description']) > 100 ? '...' : ''); ?></p>
                        <form action="produits.php" method="post">
                            <input type="hidden" name="id_produit" value="<?php echo htmlspecialchars($produit['id_produit']); ?>">
                            <label for="quantite_<?php echo htmlspecialchars($produit['id_produit']); ?>">Quantité:</label>
                            <input type="number" id="quantite_<?php echo htmlspecialchars($produit['id_produit']); ?>" name="quantite" value="1" min="1" max="<?php echo htmlspecialchars($produit['quantite_stock']); ?>" required>
                            <button type="submit" name="ajouter_panier">Ajouter au panier</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
<?php
include 'footer.php';
?>