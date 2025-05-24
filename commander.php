<?php
// commander.php
session_start();
require_once 'db.php';

$message_erreur = "";
$message_succes = "";

// Rediriger si l'utilisateur n'est pas connecté
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: connexion.php");
    exit();
}

// Rediriger si le panier est vide
if (empty($_SESSION['panier'])) {
    header("Location: panier.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirmer_commande'])) {
    $id_utilisateur = $_SESSION['id_utilisateur'];

    try {
        $conn->beginTransaction(); // Début de la transaction pour assurer l'intégrité des données

        // 1. Insérer la nouvelle commande
        $stmt_commande = $conn->prepare("INSERT INTO commandes (id_utilisateur) VALUES (?)");
        $stmt_commande->execute([$id_utilisateur]);
        $id_nouvelle_commande = $conn->lastInsertId(); // Récupérer l'ID de la commande nouvellement insérée

        // 2. Insérer les produits de la commande dans la table d'association `commande_produit`
        foreach ($_SESSION['panier'] as $id_produit => $item) {
            // Vérifier si la quantité en stock est suffisante avant d'insérer
            $stmt_check_stock = $conn->prepare("SELECT quantite_stock FROM produits WHERE id_produit = ? FOR UPDATE"); // Verrouille la ligne
            $stmt_check_stock->execute([$id_produit]);
            $produit_stock = $stmt_check_stock->fetchColumn();

            if ($produit_stock < $item['quantite']) {
                throw new Exception("Quantité insuffisante pour le produit " . htmlspecialchars($item['nom']) . ". Seulement " . htmlspecialchars($produit_stock) . " en stock.");
            }

            $stmt_cp = $conn->prepare("INSERT INTO commande_produit (id_commande, id_produit, quantite) VALUES (?, ?, ?)");
            $stmt_cp->execute([$id_nouvelle_commande, $id_produit, $item['quantite']]);

            // 3. Mettre à jour la quantité en stock du produit (décrémenter)
            $stmt_update_stock = $conn->prepare("UPDATE produits SET quantite_stock = quantite_stock - ? WHERE id_produit = ?");
            $stmt_update_stock->execute([$item['quantite'], $id_produit]);
        }

        $conn->commit(); // Confirmer toutes les opérations de la transaction

        // Vider le panier après la commande réussie
        unset($_SESSION['panier']);
        $message_succes = "Votre commande a été passée avec succès !";
        header("Location: mes_commandes.php?commande_success=true"); // Rediriger avec un paramètre de succès
        exit();

    } catch (Exception $e) { // Capturer PDOException et notre Exception personnalisée
        $conn->rollBack(); // Annuler la transaction en cas d'erreur
        $message_erreur = "Erreur lors de la finalisation de la commande : " . $e->getMessage();
    }
}

// Calculer le total du panier pour l'affichage du récapitulatif
$total_panier = 0;
if (isset($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $item) {
        $total_panier += $item['prix'] * $item['quantite'];
    }
}

include 'header.php';
?>
        <h2>Finaliser votre commande</h2>
        <?php if (!empty($message_erreur)): ?>
            <p class="error-message"><?php echo $message_erreur; ?></p>
        <?php endif; ?>

        <h3>Récapitulatif de votre commande :</h3>
        <?php if (empty($_SESSION['panier'])): ?>
            <p style="text-align: center;">Votre panier est vide. Veuillez retourner aux <a href="produits.php">produits</a>.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Prix Unitaire</th>
                        <th>Quantité</th>
                        <th>Sous-total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['panier'] as $id_produit => $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['nom']); ?></td>
                            <td><?php echo number_format($item['prix'], 2, ',', ' '); ?> €</td>
                            <td><?php echo htmlspecialchars($item['quantite']); ?></td>
                            <td><?php echo number_format($item['prix'] * $item['quantite'], 2, ',', ' '); ?> €</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right;"><strong>Total à payer :</strong></td>
                        <td><strong><?php echo number_format($total_panier, 2, ',', ' '); ?> €</strong></td>
                    </tr>
                </tfoot>
            </table>
            <br>
            <form action="commander.php" method="post" style="text-align: center;">
                <button type="submit" name="confirmer_commande">Confirmer la commande</button>
            </form>
            <p style="text-align: center; margin-top: 20px;"><a href="panier.php">Retour au panier</a></p>
        <?php endif; ?>
<?php
include 'footer.php';
?>