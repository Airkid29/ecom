<?php
// imprimer_recu.php
session_start();
require_once 'db.php';

// Rediriger si l'utilisateur n'est pas connecté
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: connexion.php");
    exit();
}

if (!isset($_GET['id_commande'])) {
    header("Location: mes_commandes.php");
    exit();
}

$id_commande = intval($_GET['id_commande']);
$id_utilisateur = $_SESSION['id_utilisateur'];

$commande_details = null;
$produits_commande = [];
$total_commande = 0;

try {
    // 1. Récupérer les détails de la commande (s'assurer qu'elle appartient bien à l'utilisateur connecté)
    $stmt_commande = $conn->prepare("SELECT id_commande, date_commande, id_utilisateur FROM commandes WHERE id_commande = ? AND id_utilisateur = ?");
    $stmt_commande->execute([$id_commande, $id_utilisateur]);
    $commande_details = $stmt_commande->fetch(PDO::FETCH_ASSOC);

    if (!$commande_details) {
        // La commande n'existe pas ou n'appartient pas à cet utilisateur
        echo "<p style='text-align: center; color: red;'>Reçu introuvable ou vous n'avez pas l'autorisation d'accéder à cette commande.</p>";
        exit();
    }

    // 2. Récupérer les produits associés à cette commande
    $stmt_produits = $conn->prepare("
        SELECT p.nom, p.prix, cp.quantite
        FROM commande_produit cp
        JOIN produits p ON cp.id_produit = p.id_produit
        WHERE cp.id_commande = ?
    ");
    $stmt_produits->execute([$id_commande]);
    $produits_commande = $stmt_produits->fetchAll(PDO::FETCH_ASSOC);

    // Calculer le total
    foreach ($produits_commande as $prod) {
        $total_commande += $prod['prix'] * $prod['quantite'];
    }

} catch (PDOException $e) {
    echo "<p style='text-align: center; color: red;'>Erreur lors du chargement du reçu : " . htmlspecialchars($e->getMessage()) . "</p>";
    exit();
}

// Début du HTML du reçu
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu de commande #<?php echo htmlspecialchars($commande_details['id_commande']); ?></title>
    <style>
        /* Styles pour l'impression */
        body {
            font-family: 'Roboto', sans-serif;
            margin: 20px;
            color: #333;
            background-color: #fff; /* Fond blanc pour l'impression */
            line-height: 1.6;
        }
        .receipt-container {
            width: 80%;
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            background-color: #fff;
        }
        h1, h2 {
            text-align: center;
            color: #1A237E; /* Bleu nuit */
            margin-bottom: 20px;
        }
        h1 {
            font-size: 2.5em;
            border-bottom: 2px solid #1A237E;
            padding-bottom: 10px;
        }
        h2 {
            font-size: 1.8em;
            color: #42A5F5; /* Bleu ciel */
        }
        p {
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: bold;
        }
        table tfoot td {
            font-weight: bold;
            background-color: #e0e0e0;
            padding: 15px;
            font-size: 1.1em;
        }
        .total-row td {
            text-align: right;
            font-size: 1.2em;
            padding-top: 20px;
        }
        .footer-info {
            text-align: center;
            margin-top: 40px;
            font-size: 0.9em;
            color: #555;
            border-top: 1px dashed #ccc;
            padding-top: 20px;
        }
        .no-print {
            text-align: center;
            margin-top: 20px;
        }
        /* Masquer le bouton d'impression sur la version imprimée */
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0; /* Pas de marges pour l'impression */
            }
            .receipt-container {
                box-shadow: none; /* Pas d'ombre à l'impression */
                border: none; /* Pas de bordure à l'impression */
                width: 100%;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <h1>Reçu de Commande</h1>
        <p><strong>Commande # :</strong> <?php echo htmlspecialchars($commande_details['id_commande']); ?></p>
        <p><strong>Date de commande :</strong> <?php echo date("d/m/Y H:i:s", strtotime($commande_details['date_commande'])); ?></p>
        <p><strong>Client :</strong> <?php echo htmlspecialchars($_SESSION['nom_utilisateur']); ?></p>

        <h2>Détails des Articles</h2>
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
                <?php foreach ($produits_commande as $prod): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($prod['nom']); ?></td>
                        <td><?php echo htmlspecialchars($prod['quantite']); ?></td>
                        <td><?php echo number_format($prod['prix'], 2, ',', ' '); ?> €</td>
                        <td><?php echo number_format($prod['prix'] * $prod['quantite'], 2, ',', ' '); ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3">Total de la commande :</td>
                    <td><?php echo number_format($total_commande, 2, ',', ' '); ?> €</td>
                </tr>
            </tfoot>
        </table>

        <div class="footer-info">
            <p>Merci pour votre achat !</p>
            <p>Afo e-Shop | Contact: contact@mon-ecommerce.com</p>
        </div>
    </div>

    <div class="no-print">
        <p><button onclick="window.print()" class="button"><i class="fas fa-print"></i> Imprimer ce reçu</button></p>
        <p><a href="mes_commandes.php" class="button button-secondary"><i class="fas fa-arrow-circle-left"></i> Retour à mes commandes</a></p>
    </div>

    <script>
        window.onload = function() {
            // setTimeout(function() { window.print(); }, 500); // Optionnel: imprimer automatiquement après un petit délai
        }
    </script>
</body>
</html>