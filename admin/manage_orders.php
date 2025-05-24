<?php
// admin/manage_orders.php
session_start();
require_once '../db.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['id_utilisateur']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../connexion.php");
    exit();
}

$message_erreur = "";
$all_orders = [];

try {
    // Récupérer toutes les commandes avec le nom de l'utilisateur
    $stmt_orders = $conn->query("
        SELECT c.id_commande, c.date_commande, u.nom_utilisateur
        FROM commandes c
        JOIN utilisateurs u ON c.id_utilisateur = u.id_utilisateur
        ORDER BY c.date_commande DESC
    ");
    $all_orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message_erreur = "Erreur lors du chargement des commandes : " . $e->getMessage();
}

include '../header.php';
?>
        <h2>Gestion des Commandes</h2>
        <p style="text-align: center;"><a href="dashboard.php" class="button button-secondary"><i class="fas fa-arrow-circle-left"></i> Retour au tableau de bord</a></p>

        <?php if (!empty($message_erreur)): ?>
            <p class="error-message"><?php echo htmlspecialchars($message_erreur); ?></p>
        <?php endif; ?>

        <?php if (empty($all_orders)): ?>
            <p style="text-align: center;">Aucune commande n'a été passée pour le moment.</p>
        <?php else: ?>
            <?php foreach ($all_orders as $order): ?>
                <div class="commande-item">
                    <h3>Commande #<?php echo htmlspecialchars($order['id_commande']); ?></h3>
                    <p><strong>Client :</strong> <?php echo htmlspecialchars($order['nom_utilisateur']); ?></p>
                    <p><strong>Date de commande :</strong> <?php echo date("d/m/Y H:i:s", strtotime($order['date_commande'])); ?></p>

                    <h4>Détails des produits :</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Quantité</th>
                                <th>Prix Unitaire</th>
                                <th>Sous-total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_order = 0;
                            try {
                                $stmt_details = $conn->prepare("
                                    SELECT p.nom, p.prix, cp.quantite
                                    FROM commande_produit cp
                                    JOIN produits p ON cp.id_produit = p.id_produit
                                    WHERE cp.id_commande = ?
                                ");
                                $stmt_details->execute([$order['id_commande']]);
                                $products_in_order = $stmt_details->fetchAll(PDO::FETCH_ASSOC);

                                foreach ($products_in_order as $prod):
                                    $sub_total = $prod['prix'] * $prod['quantite'];
                                    $total_order += $sub_total;
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($prod['nom']); ?></td>
                                        <td><?php echo htmlspecialchars($prod['quantite']); ?></td>
                                        <td><?php echo number_format($prod['prix'], 2, ',', ' '); ?> €</td>
                                        <td><?php echo number_format($sub_total, 2, ',', ' '); ?> €</td>
                                    </tr>
                                <?php endforeach;
                            } catch (PDOException $e) {
                                echo '<tr><td colspan="4" style="color:red;">Erreur lors du chargement des détails de la commande.</td></tr>';
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" style="text-align: right;"><strong>Total de la commande :</strong></td>
                                <td><strong><?php echo number_format($total_order, 2, ',', ' '); ?> €</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
<?php
include '../footer.php';
?>