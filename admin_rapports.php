<?php
// filepath: c:\wamp64\www\vente téléphone\admin_rapports.php

require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

$message = "";
$rapport = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_report'])) {
    $type_rapport = trim($_POST['type_rapport']);
    $date_debut = trim($_POST['date_debut']);
    $date_fin = trim($_POST['date_fin']);

    if (!empty($type_rapport) && !empty($date_debut) && !empty($date_fin)) {
        try {
            switch ($type_rapport) {
                case 'ventes':
                    $requete = $connexion->prepare("
                        SELECT SUM(montant_total) AS total_ventes, COUNT(*) AS nombre_commandes
                        FROM commandes
                        WHERE date_commande BETWEEN :date_debut AND :date_fin
                    ");
                    break;
                case 'utilisateurs':
                    $requete = $connexion->prepare("
                        SELECT COUNT(*) AS nombre_utilisateurs
                        FROM utilisateurs
                        WHERE date_inscription BETWEEN :date_debut AND :date_fin
                    ");
                    break;
                case 'commandes':
                    $requete = $connexion->prepare("
                        SELECT statut, COUNT(*) AS nombre_commandes
                        FROM commandes
                        WHERE date_commande BETWEEN :date_debut AND :date_fin
                        GROUP BY statut
                    ");
                    break;
                default:
                    $message = "Type de rapport invalide.";
                    break;
            }
            
            if (isset($requete)) {
                $requete->execute([
                    ':date_debut' => $date_debut,
                    ':date_fin' => $date_fin
                ]);
                $rapport = $requete->fetchAll(PDO::FETCH_ASSOC);
                
                // Enregistrer le rapport dans la table rapports
                $requeteInsert = $connexion->prepare("
                    INSERT INTO rapports (type_rapport, date_debut, date_fin, resultat)
                    VALUES (:type_rapport, :date_debut, :date_fin, :resultat)
                ");
                $requeteInsert->execute([
                    ':type_rapport' => $type_rapport,
                    ':date_debut'  => $date_debut,
                    ':date_fin'    => $date_fin,
                    ':resultat'    => json_encode($rapport)
                ]);
                
                $message = "Rapport généré et enregistré avec succès.";
            }
        } catch (PDOException $e) {
            $message = "Erreur lors de la génération du rapport : " . $e->getMessage();
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestion des rapports - Administration</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #f8f9fa;
        }
        h2, h3 {
            text-align: center;
            color: #333;
        }
        .message {
            max-width: 800px;
            margin: 20px auto;
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            text-align: center;
        }
        form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 15px;
        }
        button[type="submit"] {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            border: none;
            border-radius: 6px;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button[type="submit"]:hover {
            background-color: #0056b3;
        }
        table {
            max-width: 800px;
            margin: 20px auto;
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f1f1f1;
        }
        tbody tr:nth-child(even) {
            background-color: #fafafa;
        }
        /* Ajoutez ce style dans votre fichier CSS ou dans un bloc <style> de la page */
.btn-retour {
    display: inline-block;
    margin: 20px;
    padding: 10px 20px;
    background-color: #007BFF;
    color: #fff;
    text-decoration: none;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

.btn-retour:hover {
    background-color: #0056b3;
}
    </style>
</head>
<body>
    <!-- Insérez ce code dans votre page à l'endroit désiré, par exemple juste avant le footer -->
<a href="javascript:history.back()" class="btn-retour">Retour</a>

    <h2>Gestion des rapports</h2>
    <?php if (!empty($message)): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <h3>Générer un rapport</h3>
    <form method="POST" action="admin_rapports.php">
        <div class="form-group">
            <label for="type_rapport">Type de rapport :</label>
            <select id="type_rapport" name="type_rapport" required>
                <option value="ventes">Rapport des ventes</option>
                <option value="utilisateurs">Rapport des utilisateurs</option>
                <option value="commandes">Rapport des commandes</option>
            </select>
        </div>
        <div class="form-group">
            <label for="date_debut">Date de début :</label>
            <input type="date" id="date_debut" name="date_debut" required>
        </div>
        <div class="form-group">
            <label for="date_fin">Date de fin :</label>
            <input type="date" id="date_fin" name="date_fin" required>
        </div>
        <button type="submit" name="generate_report">Générer le rapport</button>
    </form>

    <?php if (!empty($rapport)): ?>
        <h3>Résultats du rapport</h3>
        <table>
            <thead>
                <tr>
                    <?php foreach (array_keys($rapport[0]) as $colonne): ?>
                        <th><?php echo htmlspecialchars($colonne); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rapport as $ligne): ?>
                    <tr>
                        <?php foreach ($ligne as $valeur): ?>
                            <td><?php echo htmlspecialchars(isset($valeur) ? $valeur : ''); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>