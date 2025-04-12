<?php
require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

// Vérifier la présence de l'ID du produit
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin_produits.php");
    exit;
}
$produit_id = intval($_GET['id']);

try {
    // Suppression du produit
    $stmt = $connexion->prepare("DELETE FROM produits WHERE id = :id");
    $stmt->execute([':id' => $produit_id]);
    header("Location: admin_produits.php?message=Produit supprimé avec succès");
    exit;
} catch (PDOException $e) {
    echo "Erreur lors de la suppression du produit : " . $e->getMessage();
}
?>