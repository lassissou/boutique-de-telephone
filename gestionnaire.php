<?php

class Gestionnaire {
    private $host = "localhost"; // Hôte de la base de données
    private $username = "root"; // Nom d'utilisateur
    private $password = ""; // Mot de passe
    private $database = "vente_telephone"; // Nom de la base de données
    private $connexion;

    public function __construct() {
        $this->connecter();
    }

    // Méthode pour établir une connexion PDO
    private function connecter() {
        try {
            $this->connexion = new PDO(
                "mysql:host={$this->host};dbname={$this->database};charset=utf8",
                $this->username,
                $this->password
            );
            $this->connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

    // Méthode pour obtenir l'objet PDO
    public function getConnexion() {
        return $this->connexion;
    }

    // Méthode pour exécuter une requête SQL avec des paramètres
    public function executerRequete($sql, $params = []) {
        try {
            $stmt = $this->connexion->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            die("Erreur lors de l'exécution de la requête : " . $e->getMessage());
        }
    }

    public function __destruct() {
        $this->connexion = null; // Fermer la connexion PDO
    }
}