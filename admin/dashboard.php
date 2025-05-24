<?php
// admin/dashboard.php
session_start();
require_once '../db.php'; // Remonter d'un niveau pour trouver db.php

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['id_utilisateur']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../connexion.php"); // Rediriger vers la page de connexion
    exit();
}

// Inclure le header spécifique à l'admin ou le header général
// Pour l'instant, utilisons le header général pour simplifier
include '../header.php'; // Remonter d'un niveau pour trouver header.php
?>
        <h2 style="text-align: center;">Tableau de Bord Administrateur</h2>

        <div class="admin-dashboard-grid">
            <div class="admin-card">
                <i class="fas fa-boxes fa-4x"></i>
                <h3>Gestion des Produits</h3>
                <p>Ajouter, modifier ou supprimer des produits de votre boutique.</p>
                <a href="manage_products.php" class="button"><i class="fas fa-cogs"></i> Gérer les produits</a>
            </div>

            <div class="admin-card">
                <i class="fas fa-users fa-4x"></i>
                <h3>Gestion des Utilisateurs</h3>
                <p>Voir la liste des utilisateurs enregistrés.</p>
                <a href="manage_users.php" class="button button-secondary"><i class="fas fa-users-cog"></i> Gérer les utilisateurs</a>
            </div>

            <div class="admin-card">
                <i class="fas fa-shopping-cart fa-4x"></i>
                <h3>Gestion des Commandes</h3>
                <p>Consulter toutes les commandes passées sur le site.</p>
                <a href="manage_orders.php" class="button"><i class="fas fa-receipt"></i> Gérer les commandes</a>
            </div>
        </div>

        <style> /* Styles spécifiques au dashboard admin */
            .admin-dashboard-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 30px;
                margin-top: 40px;
                padding: 20px;
            }
            .admin-card {
                background-color: var(--bg-card);
                border: 1px solid var(--border-subtle);
                border-radius: 15px;
                padding: 30px;
                text-align: center;
                box-shadow: 0 6px 20px var(--shadow-soft);
                transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            }
            .admin-card:hover {
                transform: translateY(-8px);
                box-shadow: 0 12px 30px var(--shadow-medium);
            }
            .admin-card .fas {
                color: var(--accent-color);
                margin-bottom: 20px;
                font-size: 4em;
            }
            .admin-card h3 {
                font-family: 'Montserrat', sans-serif;
                color: var(--primary-color);
                font-size: 1.8em;
                margin-bottom: 15px;
            }
            .admin-card p {
                color: var(--text-light);
                margin-bottom: 25px;
                font-size: 1em;
            }
            .admin-card .button {
                width: 100%; /* Boutons pleine largeur dans les cartes admin */
                margin-top: 15px;
                font-size: 1em;
            }

            @media (max-width: 768px) {
                .admin-dashboard-grid {
                    grid-template-columns: 1fr;
                    padding: 0;
                }
            }
        </style>
<?php
include '../footer.php'; // Remonter d'un niveau pour trouver footer.php
?>