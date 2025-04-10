<?php
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
                
                // Enregistrement du rapport dans la table rapports
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
    <title>Gestion des rapports - Administration</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        .form-group {
            margin-bottom: 10px;
        }
        .form-group label { 
            display: block; 
            font-weight: bold; 
        }
        .form-group input, 
        .form-group select { 
            width: 100%; 
            padding: 5px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {  
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
        }
    </style>
</head>
<body>
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