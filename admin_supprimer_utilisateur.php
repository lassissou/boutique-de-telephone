<?php
require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();
 
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin_utilisateurs.php");
    exit;
}
$utilisateur_id = intval($_GET['id']);
 
try {
    $stmt = $connexion->prepare("DELETE FROM utilisateurs WHERE id = :id");
    $stmt->execute([':id' => $utilisateur_id]);
    header("Location: admin_utilisateurs.php?message=Utilisateur supprimé avec succès");
    exit;
} catch (PDOException $e) {
    echo "Erreur lors de la suppression de l'utilisateur : " . $e->getMessage();
}
?>