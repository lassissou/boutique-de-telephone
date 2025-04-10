<?php
// filepath: c:\wamp64\www\vente téléphone\admin_dashboard.php

// Inclure le fichier gestionnaire.php pour la connexion à la base de données
require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

// Récupérer les statistiques générales
try {
    // Nombre total de produits
    $requeteProduits = $connexion->query("SELECT COUNT(*) FROM produits");
    $totalProduits = $requeteProduits->fetchColumn();

    // Nombre total de commandes
    $requeteCommandes = $connexion->query("SELECT COUNT(*) FROM commandes");
    $totalCommandes = $requeteCommandes->fetchColumn();

    // Nombre total d'utilisateurs
    $requeteUtilisateurs = $connexion->query("SELECT COUNT(*) FROM utilisateurs");
    $totalUtilisateurs = $requeteUtilisateurs->fetchColumn();

    // Revenus totaux
    $requeteRevenus = $connexion->query("SELECT SUM(montant_total) FROM commandes WHERE statut = 'livrée'");
    $totalRevenus = $requeteRevenus->fetchColumn();

    // Produits les plus vendus (exemple simplifié)
    $requeteMeilleursProduits = $connexion->query("
        SELECT p.nom, COUNT(c.id_produit) AS nombre_ventes
        FROM commandes_produits c
        JOIN produits p ON c.id_produit = p.id
        GROUP BY c.id_produit
        ORDER BY nombre_ventes DESC
        LIMIT 5
    ");
    $meilleursProduits = $requeteMeilleursProduits->fetchAll(PDO::FETCH_ASSOC);

    // Commandes récentes (exemple simplifié)
    $requeteCommandesRecentes = $connexion->query("
        SELECT id, date_commande, montant_total, statut
        FROM commandes
        ORDER BY date_commande DESC
        LIMIT 5
    ");
    $commandesRecentes = $requeteCommandesRecentes->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erreur lors de la récupération des données : " . $e->getMessage();
    $totalProduits = 0;
    $totalCommandes = 0;
    $totalUtilisateurs = 0;
    $totalRevenus = 0;
    $meilleursProduits = [];
    $commandesRecentes = [];
}
?>

<h2>Tableau de bord</h2>

<div class="dashboard-container">
    <div class="dashboard-section">
        <h3>Statistiques générales</h3>
        <p>Nombre total de produits : <?php echo htmlspecialchars($totalProduits); ?></p>
        <p>Nombre total de commandes : <?php echo htmlspecialchars($totalCommandes); ?></p>
        <p>Nombre total d'utilisateurs : <?php echo htmlspecialchars($totalUtilisateurs); ?></p>
        <p>Revenus totaux : <?php echo htmlspecialchars(number_format(isset($totalRevenus) ? $totalRevenus : 0, 2));?> €</p>
    </div>

    <div class="dashboard-section">
        <h3>Produits les plus vendus</h3>
        <?php if (!empty($meilleursProduits)): ?>
            <ul>
                <?php foreach ($meilleursProduits as $produit): ?>
                    <li><?php echo htmlspecialchars($produit['nom']); ?> (<?php echo htmlspecialchars($produit['nombre_ventes']); ?> ventes)</li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Aucun produit vendu pour le moment.</p>
        <?php endif; ?>
    </div>

    <div class="dashboard-section">
        <h3>Commandes récentes</h3>
        <?php if (!empty($commandesRecentes)): ?>
            <ul>
                <?php foreach ($commandesRecentes as $commande): ?>
                    <li>
                        Commande #<?php echo htmlspecialchars($commande['id']); ?> -
                        Date : <?php echo htmlspecialchars($commande['date_commande']); ?> -
                        Montant : <?php echo htmlspecialchars(number_format($commande['montant_total'], 2)); ?> € -
                        Statut : <?php echo htmlspecialchars($commande['statut']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Aucune commande récente.</p>
        <?php endif; ?>
    </div>
</div>

<style>
.dashboard-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-around;
}

.dashboard-section {
    width: 30%;
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #f9f9f9;
}

.dashboard-section h3 {
    margin-top: 0;
    margin-bottom: 10px;
}

.dashboard-section p {
    margin-bottom: 5px;
}

.dashboard-section ul {
    list-style: none;
    padding: 0;
}

.dashboard-section li {
    margin-bottom: 5px;
}
</style>