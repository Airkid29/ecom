<?php
// panier.php
session_start();
require_once 'db.php';

$message_erreur = "";
$message_succes = "";

// Si un produit est supprimé du panier
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['supprimer_du_panier'])) {
    $id_produit_a_supprimer = intval($_POST['id_produit']);
    if (isset($_SESSION['panier'][$id_produit_a_supprimer])) {
        unset($_SESSION['panier'][$id_produit_a_supprimer]);
        $message_succes = "Produit retiré du panier.";
        // header("Location: panier.php"); // Peut être commenté pour afficher le message
        // exit();
    }
}

// Si la quantité d'un produit est mise à jour
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifier_quantite'])) {
    $id_produit_a_modifier = intval($_POST['id_produit']);
    $nouvelle_quantite = intval($_POST['nouvelle_quantite']);

    if (isset($_SESSION['panier'][$id_produit_a_modifier]) && $nouvelle_quantite >= 0) {
        try {
            // Vérifier la quantité en stock avant de mettre à jour
            $stmt_prod = $conn->prepare("SELECT quantite_stock FROM produits WHERE id_produit = :id_produit");
            $stmt_prod->bindParam(':id_produit', $id_produit_a_modifier);
            $stmt_prod->execute();
            $produit_detail = $stmt_prod->fetch(PDO::FETCH_ASSOC);

            if ($produit_detail) {
                if ($nouvelle_quantite === 0) {
                    unset($_SESSION['panier'][$id_produit_a_modifier]);
                    $message_succes = "Produit retiré du panier.";
                } elseif ($nouvelle_quantite <= $produit_detail['quantite_stock']) {
                    $_SESSION['panier'][$id_produit_a_modifier]['quantite'] = $nouvelle_quantite;
                    $message_succes = "Quantité mise à jour.";
                } else {
                    $message_erreur = "Quantité demandée trop élevée pour le produit. Stock disponible : " . htmlspecialchars($produit_detail['quantite_stock']);
                }
            } else {
                $message_erreur = "Produit introuvable dans la base de données.";
            }
        } catch (PDOException $e) {
            $message_erreur = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
}

// Calculer le total du panier
$total_panier = 0;
if (isset($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $item) {
        $total_panier += $item['prix'] * $item['quantite'];
    }
}

include 'header.php';
?>
        <h2>Votre Panier</h2>
        <?php if (!empty($message_erreur)): ?>
            <p class="error-message"><?php echo $message_erreur; ?></p>
        <?php endif; ?>
        <?php if (!empty($message_succes)): ?>
            <p class="success-message"><?php echo $message_succes; ?></p>
        <?php endif; ?>

        <?php if (empty($_SESSION['panier'])): ?>
            <p style="text-align: center;">Votre panier est vide.</p>
            <p style="text-align: center;"><a href="produits.php">Retourner aux produits</a></p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Prix Unitaire</th>
                        <th>Quantité</th>
                        <th>Sous-total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['panier'] as $id_produit => $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['nom']); ?></td>
                            <td><?php echo number_format($item['prix'], 2, ',', ' '); ?> €</td>
                            <td>
                                <form action="panier.php" method="post" style="display:inline-flex; align-items:center; gap: 5px;">
                                    <input type="hidden" name="id_produit" value="<?php echo htmlspecialchars($id_produit); ?>">
                                    <input type="number" name="nouvelle_quantite" value="<?php echo htmlspecialchars($item['quantite']); ?>" min="0" style="width: 70px; text-align: center;">
                                    <button type="submit" name="modifier_quantite">Mettre à jour</button>
                                </form>
                            </td>
                            <td><?php echo number_format($item['prix'] * $item['quantite'], 2, ',', ' '); ?> €</td>
                            <td>
                                <form action="panier.php" method="post" style="display:inline;">
                                    <input type="hidden" name="id_produit" value="<?php echo htmlspecialchars($id_produit); ?>">
                                    <button type="submit" name="supprimer_du_panier">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right;"><strong>Total du panier :</strong></td>
                        <td><strong><?php echo number_format($total_panier, 2, ',', ' '); ?> €</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <br>
            <p style="text-align: center;">
                <a href="commander.php" class="button">Passer la commande</a>
                <a href="produits.php" class="button button-secondary" style="margin-left: 15px;">Continuer mes achats</a>
            </p>
        <?php endif; ?>

        <style> /* Styles spécifiques au panier */
            .button {
                display: inline-block;
                background-color: #007bff;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                text-decoration: none;
                font-size: 1em;
                transition: background-color 0.3s ease;
            }
            .button:hover {
                background-color: #0056b3;
            }
            .button-secondary {
                background-color: #6c757d;
            }
            .button-secondary:hover {
                background-color: #5a6268;
            }
        </style>
<?php
include 'footer.php';
?>