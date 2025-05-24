<?php
// admin/manage_users.php
session_start();
require_once '../db.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['id_utilisateur']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../connexion.php");
    exit();
}

$message_erreur = "";
$all_users = [];

try {
    $stmt = $conn->query("SELECT id_utilisateur, nom_utilisateur, email, role FROM utilisateurs ORDER BY id_utilisateur DESC");
    $all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message_erreur = "Erreur lors du chargement des utilisateurs : " . $e->getMessage();
}

include '../header.php';
?>
        <h2>Gestion des Utilisateurs</h2>
        <p style="text-align: center;"><a href="dashboard.php" class="button button-secondary"><i class="fas fa-arrow-circle-left"></i> Retour au tableau de bord</a></p>

        <?php if (!empty($message_erreur)): ?>
            <p class="error-message"><?php echo htmlspecialchars($message_erreur); ?></p>
        <?php endif; ?>

        <?php if (empty($all_users)): ?>
            <p style="text-align: center;">Aucun utilisateur enregistré.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom d'utilisateur</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id_utilisateur']); ?></td>
                            <td><?php echo htmlspecialchars($user['nom_utilisateur']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
<?php
include '../footer.php';
?>