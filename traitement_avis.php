<?php
session_start();
require_once 'gestionnaire.php';

if (!isset($_SESSION['utilisateur'])) {
    header("Location: connexion.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_avis'])) {
    $gestionnaire = new Gestionnaire();
    $connexion = $gestionnaire->getConnexion();
    
    $utilisateur_id = $_SESSION['utilisateur']['id'];
    $produit_id = intval($_POST['produit_id']);
    $note = intval($_POST['note']);
    $commentaire = trim($_POST['commentaire']);
    
    // Vérification que le produit existe
    $stmtCheck = $connexion->prepare("SELECT id FROM produits WHERE id = ?");
    $stmtCheck->execute([$produit_id]);
    
    if ($stmtCheck->rowCount() === 0) {
        $_SESSION['avis_error'] = "Produit invalide";
        header("Location: ".$_SERVER['HTTP_REFERER']);
        exit();
    }
    
    try {
        // Insertion de l'avis
        $stmt = $connexion->prepare("
            INSERT INTO avis (utilisateur_id, produit_id, commentaire, note, approuve, date_avis)
            VALUES (:user_id, :prod_id, :comment, :note, 1, NOW())
        ");
        
        $stmt->execute([
            ':user_id' => $utilisateur_id,
            ':prod_id' => $produit_id,
            ':comment' => $commentaire,
            ':note' => $note
        ]);
        
        $_SESSION['avis_success'] = "Votre avis a été publié avec succès !";
        header("Location: ".$_SERVER['HTTP_REFERER']);
        exit();
        
    } catch (PDOException $e) {
        $_SESSION['avis_error'] = "Erreur: " . $e->getMessage();
        header("Location: ".$_SERVER['HTTP_REFERER']);
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}