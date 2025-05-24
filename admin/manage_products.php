<?php
// admin/manage_products.php
session_start();
require_once '../db.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['id_utilisateur']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../connexion.php");
    exit();
}

$message_erreur = "";
$message_succes = "";
$produit_a_modifier = null; // Pour le formulaire de modification

// Gérer les actions (Ajouter, Modifier, Supprimer)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // AJOUTER UN PRODUIT
    if (isset($_POST['add_product'])) {
        $nom = trim($_POST['nom']);
        $prix = floatval($_POST['prix']);
        $quantite_stock = intval($_POST['quantite_stock']);
        $description = trim($_POST['description']);
        $image = '';

        if (empty($nom) || $prix <= 0 || $quantite_stock < 0) {
            $message_erreur = "Tous les champs obligatoires doivent être remplis et valides.";
        } else {
            // Gestion de l'upload d'image
            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/';
                $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
                $image_path = $upload_dir . $image_name;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                    $image = $image_name;
                } else {
                    $message_erreur = "Erreur lors de l'upload de l'image.";
                }
            }

            if (empty($message_erreur)) {
                try {
                    $stmt = $conn->prepare("INSERT INTO produits (nom, prix, quantite_stock, description, image) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$nom, $prix, $quantite_stock, $description, $image]);
                    $message_succes = "Produit '" . htmlspecialchars($nom) . "' ajouté avec succès !";
                } catch (PDOException $e) {
                    $message_erreur = "Erreur lors de l'ajout du produit : " . $e->getMessage();
                }
            }
        }
    }

    // MODIFIER UN PRODUIT (Mise à jour)
    if (isset($_POST['update_product'])) {
        $id_produit = intval($_POST['id_produit']);
        $nom = trim($_POST['nom']);
        $prix = floatval($_POST['prix']);
        $quantite_stock = intval($_POST['quantite_stock']);
        $description = trim($_POST['description']);
        $current_image = trim($_POST['current_image']); // Image actuelle si pas de nouvelle

        if (empty($nom) || $prix <= 0 || $quantite_stock < 0) {
            $message_erreur = "Tous les champs obligatoires doivent être remplis et valides.";
        } else {
            $image_to_save = $current_image; // Par défaut, on garde l'ancienne image

            // Gestion de l'upload d'une nouvelle image
            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/';
                $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
                $image_path = $upload_dir . $image_name;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                    $image_to_save = $image_name;
                    // Supprimer l'ancienne image si elle existe et n'est pas la valeur par défaut
                    if (!empty($current_image) && file_exists($upload_dir . $current_image)) {
                        unlink($upload_dir . $current_image);
                    }
                } else {
                    $message_erreur = "Erreur lors de l'upload de la nouvelle image.";
                }
            }

            if (empty($message_erreur)) {
                try {
                    $stmt = $conn->prepare("UPDATE produits SET nom = ?, prix = ?, quantite_stock = ?, description = ?, image = ? WHERE id_produit = ?");
                    $stmt->execute([$nom, $prix, $quantite_stock, $description, $image_to_save, $id_produit]);
                    $message_succes = "Produit '" . htmlspecialchars($nom) . "' mis à jour avec succès !";
                } catch (PDOException $e) {
                    $message_erreur = "Erreur lors de la mise à jour du produit : " . $e->getMessage();
                }
            }
        }
    }

    // SUPPRIMER UN PRODUIT
    if (isset($_POST['delete_product'])) {
        $id_produit = intval($_POST['id_produit']);
        try {
            // Récupérer le nom de l'image avant de supprimer le produit
            $stmt_img = $conn->prepare("SELECT image FROM produits WHERE id_produit = ?");
            $stmt_img->execute([$id_produit]);
            $image_to_delete = $stmt_img->fetchColumn();

            $stmt = $conn->prepare("DELETE FROM produits WHERE id_produit = ?");
            $stmt->execute([$id_produit]);

            if ($stmt->rowCount() > 0) {
                $message_succes = "Produit supprimé avec succès.";
                // Supprimer le fichier image du serveur
                if (!empty($image_to_delete) && file_exists('../uploads/' . $image_to_delete)) {
                    unlink('../uploads/' . $image_to_delete);
                }
            } else {
                $message_erreur = "Produit non trouvé.";
            }
        } catch (PDOException $e) {
            $message_erreur = "Erreur lors de la suppression du produit : " . $e->getMessage();
        }
    }
}

// Afficher un produit pour modification (GET)
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id_produit_edit = intval($_GET['id']);
    try {
        $stmt = $conn->prepare("SELECT * FROM produits WHERE id_produit = ?");
        $stmt->execute([$id_produit_edit]);
        $produit_a_modifier = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$produit_a_modifier) {
            $message_erreur = "Produit à modifier introuvable.";
        }
    } catch (PDOException $e) {
        $message_erreur = "Erreur lors du chargement du produit pour modification : " . $e->getMessage();
    }
}

// Récupérer tous les produits pour l'affichage
$all_products = [];
try {
    $stmt_all_products = $conn->query("SELECT * FROM produits ORDER BY id_produit DESC");
    $all_products = $stmt_all_products->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message_erreur = "Erreur lors du chargement des produits : " . $e->getMessage();
}

include '../header.php';
?>
        <h2>Gestion des Produits</h2>
        <p style="text-align: center;"><a href="dashboard.php" class="button button-secondary"><i class="fas fa-arrow-circle-left"></i> Retour au tableau de bord</a></p>

        <?php if (!empty($message_erreur)): ?>
            <p class="error-message"><?php echo htmlspecialchars($message_erreur); ?></p>
        <?php endif; ?>
        <?php if (!empty($message_succes)): ?>
            <p class="success-message"><?php echo htmlspecialchars($message_succes); ?></p>
        <?php endif; ?>

        <h3><?php echo $produit_a_modifier ? 'Modifier un produit' : 'Ajouter un nouveau produit'; ?></h3>
        <form action="manage_products.php" method="post" enctype="multipart/form-data">
            <?php if ($produit_a_modifier): ?>
                <input type="hidden" name="id_produit" value="<?php echo htmlspecialchars($produit_a_modifier['id_produit']); ?>">
                <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($produit_a_modifier['image']); ?>">
            <?php endif; ?>

            <label for="nom">Nom du produit :</label>
            <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($produit_a_modifier['nom'] ?? ''); ?>" required><br>

            <label for="prix">Prix :</label>
            <input type="number" id="prix" name="prix" step="0.01" min="0.01" value="<?php echo htmlspecialchars($produit_a_modifier['prix'] ?? ''); ?>" required><br>

            <label for="quantite_stock">Quantité en stock :</label>
            <input type="number" id="quantite_stock" name="quantite_stock" min="0" value="<?php echo htmlspecialchars($produit_a_modifier['quantite_stock'] ?? ''); ?>" required><br>

            <label for="description">Description :</label>
            <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($produit_a_modifier['description'] ?? ''); ?></textarea><br>

            <label for="image">Image du produit :</label>
            <input type="file" id="image" name="image" accept="image/*"><br>
            <?php if ($produit_a_modifier && !empty($produit_a_modifier['image'])): ?>
                <p>Image actuelle: <img src="../uploads/<?php echo htmlspecialchars($produit_a_modifier['image']); ?>" alt="Image du produit" style="width: 100px; height: auto; vertical-align: middle; border-radius: 5px;"></p>
            <?php endif; ?>
            <br>

            <button type="submit" name="<?php echo $produit_a_modifier ? 'update_product' : 'add_product'; ?>">
                <i class="fas fa-<?php echo $produit_a_modifier ? 'save' : 'plus-circle'; ?>"></i>
                <?php echo $produit_a_modifier ? 'Mettre à jour le produit' : 'Ajouter le produit'; ?>
            </button>
            <?php if ($produit_a_modifier): ?>
                <a href="manage_products.php" class="button button-secondary" style="margin-left: 10px;"><i class="fas fa-times-circle"></i> Annuler la modification</a>
            <?php endif; ?>
        </form>

        <h3>Liste des produits</h3>
        <?php if (empty($all_products)): ?>
            <p style="text-align: center;">Aucun produit n'est enregistré.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Nom</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_products as $prod): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($prod['id_produit']); ?></td>
                            <td>
                                <?php if (!empty($prod['image']) && file_exists('../uploads/' . $prod['image'])): ?>
                                    <img src="../uploads/<?php echo htmlspecialchars($prod['image']); ?>" alt="<?php echo htmlspecialchars($prod['nom']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                <?php else: ?>
                                    <i class="fas fa-image fa-2x" style="color: #ccc;"></i>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($prod['nom']); ?></td>
                            <td><?php echo number_format($prod['prix'], 2, ',', ' '); ?> €</td>
                            <td><?php echo htmlspecialchars($prod['quantite_stock']); ?></td>
                            <td>
                                <a href="manage_products.php?action=edit&id=<?php echo htmlspecialchars($prod['id_produit']); ?>" class="button button-secondary button-small"><i class="fas fa-edit"></i> Éditer</a>
                                <form action="manage_products.php" method="post" style="display:inline-block; margin-left: 5px;">
                                    <input type="hidden" name="id_produit" value="<?php echo htmlspecialchars($prod['id_produit']); ?>">
                                    <button type="submit" name="delete_product" class="button button-danger button-small" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');"><i class="fas fa-trash-alt"></i> Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <style> /* Styles pour manage_products.php */
            .button.button-small {
                padding: 8px 15px;
                font-size: 0.9em;
                border-radius: 6px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
            .button.button-danger {
                background: linear-gradient(45deg, var(--error-color) 0%, #E57373 100%);
            }
            .button.button-danger:hover {
                background: linear-gradient(45deg, #E57373 0%, var(--error-color) 100%);
            }
            /* Assurer que les boutons sont centrés dans le formulaire d'ajout/modification */
            form button, form a.button {
                margin: 0 auto;
            }
        </style>
<?php
include '../footer.php';
?>