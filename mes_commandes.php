<?php
// mes_commandes.php
session_start();
require_once 'db.php';

// Rediriger si l'utilisateur n'est pas connecté
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: connexion.php");
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];
$message_succes = "";

if (isset($_GET['commande_success']) && $_GET['commande_success'] == 'true') {
    $message_succes = "Votre commande a été passée avec succès !";
}

// Récupérer les commandes de l'utilisateur
$commandes = [];
try {
    $stmt_commandes = $conn->prepare("SELECT id_commande, date_commande FROM commandes WHERE id_utilisateur = ? ORDER BY date_commande DESC");
    $stmt_commandes->execute([$id_utilisateur]);
    $commandes = $stmt_commandes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Gérer l'erreur si nécessaire, par exemple, logguer l'erreur
    $message_erreur = "Erreur lors du chargement de vos commandes : " . $e->getMessage();
}

include 'header.php';
?>
        <h2>Mes Commandes</h2>
        <?php if (!empty($message_succes)): ?>
            <p class="success-message"><?php echo $message_succes; ?></p>
        <?php endif; ?>
        <?php if (!empty($message_erreur)): ?>
            <p class="error-message"><?php echo $message_erreur; ?></p>
        <?php endif; ?>

        <?php if (empty($commandes)): ?>
            <p style="text-align: center;">Vous n'avez pas encore passé de commandes.</p>
            <p style="text-align: center;"><a href="produits.php">Commencer vos achats</a></p>
        <?php else: ?>
            <?php foreach ($commandes as $commande): ?>
                <div class="commande-item">
                    <h3>Commande #<?php echo htmlspecialchars($commande['id_commande']); ?></h3>
                    <p>Date : <?php echo date("d/m/Y H:i:s", strtotime($commande['date_commande'])); ?></p>
                    <p class="print-link-container">
                        <a href="imprimer_recu.php?id_commande=<?php echo htmlspecialchars($commande['id_commande']); ?>" target="_blank" class="button button-print"><i class="fas fa-print"></i> Imprimer le reçu</a>
                    </p>

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
                            $total_commande = 0;
                            try {
                                $stmt_details = $conn->prepare("
                                    SELECT p.nom, p.prix, cp.quantite
                                    FROM commande_produit cp
                                    JOIN produits p ON cp.id_produit = p.id_produit
                                    WHERE cp.id_commande = ?
                                ");
                                $stmt_details->execute([$commande['id_commande']]);
                                $produits_commande = $stmt_details->fetchAll(PDO::FETCH_ASSOC);

                                foreach ($produits_commande as $prod):
                                    $sous_total = $prod['prix'] * $prod['quantite'];
                                    $total_commande += $sous_total;
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($prod['nom']); ?></td>
                                        <td><?php echo htmlspecialchars($prod['quantite']); ?></td>
                                        <td><?php echo number_format($prod['prix'], 2, ',', ' '); ?> €</td>
                                        <td><?php echo number_format($sous_total, 2, ',', ' '); ?> €</td>
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
                                <td><strong><?php echo number_format($total_commande, 2, ',', ' '); ?> €</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
<?php
include 'footer.php';
?>