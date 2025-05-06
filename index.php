<?php
session_start();
require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();
$message = "";

// Récupération de la liste de toutes les marques (pour le menu)
try {
    $stmtBrands = $connexion->query("SELECT id, nom FROM marques ORDER BY nom ASC");
    $listeMarques = $stmtBrands->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $listeMarques = [];
}

// Vérifier si une marque est sélectionnée via GET (pour le menu)
$filtreMarque = isset($_GET['marque']) ? trim($_GET['marque']) : "";

// --- MOTEUR DE RECHERCHE ---
// Si l'utilisateur a soumis une recherche (via GET)
$recherche = isset($_GET['recherche']) ? trim($_GET['recherche']) : "";

function rechercherProduitsEtPromotions($connexion, $query) {
    $query = trim($query);
    $wildcard = '%' . strtolower($query) . '%';

    // Recherche des produits correspondants (insensible à la casse)
    $stmtProd = $connexion->prepare("
        SELECT p.id, p.nom, p.prix, p.lien_image, m.nom AS marque 
        FROM produits p 
        JOIN marques m ON p.marques_id = m.id 
        JOIN status s ON p.id_status = s.id 
        WHERE s.status = 'visible' 
          AND (LOWER(p.nom) LIKE :query OR LOWER(m.nom) LIKE :query)
        ORDER BY p.date_ajouter DESC
    ");
    $stmtProd->bindValue(':query', $wildcard, PDO::PARAM_STR);
    $stmtProd->execute();
    $resultats['produits'] = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

    // Recherche des promotions correspondantes
    $stmtPromo = $connexion->prepare("
        SELECT 
            p.id, 
            p.nom, 
            p.lien_image, 
            pr.prix_initial, 
            CASE 
                WHEN pr.type_reduction = 'pourcentage' THEN pr.prix_initial - (pr.prix_initial * (pr.valeur_reduction / 100))
                WHEN pr.type_reduction = 'montant' THEN pr.prix_initial - pr.valeur_reduction
                ELSE pr.prix_initial
            END AS prix_promo, 
            pr.type_reduction, 
            pr.valeur_reduction, 
            pr.date_debut, 
            pr.date_fin
        FROM produits p
        JOIN promotions pr ON p.id = pr.produit_id 
        JOIN marques m ON p.marques_id = m.id
        WHERE CURDATE() BETWEEN pr.date_debut AND pr.date_fin 
          AND (LOWER(p.nom) LIKE :query OR LOWER(m.nom) LIKE :query)
        ORDER BY pr.date_debut ASC
    ");
    $stmtPromo->bindValue(':query', $wildcard, PDO::PARAM_STR);
    $stmtPromo->execute();
    $resultats['promotions'] = $stmtPromo->fetchAll(PDO::FETCH_ASSOC);

    return $resultats;
}

// Si une recherche est effectuée, on récupère ses résultats
if (!empty($recherche)) {
    $resultatsRecherche = rechercherProduitsEtPromotions($connexion, $recherche);
} 

// --- FIN DU MOTEUR DE RECHERCHE ---

// 1. Ajout au panier (inchangé)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_au_panier'])) {
    $pid = intval($_POST['produit_id']);
    $qte = intval($_POST['quantite']);
    if ($pid > 0 && $qte > 0) {
        if (!isset($_SESSION['panier'])) {
            $_SESSION['panier'] = [];
        }
        if (isset($_SESSION['panier'][$pid])) {
            $_SESSION['panier'][$pid] += $qte;
        } else {
            $_SESSION['panier'][$pid] = $qte;
        }
    }
}

// Si aucune recherche n'est effectuée, on charge les nouveautés et promotions habituelles.
// Sinon, on affiche les résultats de la recherche.
if (empty($recherche)) {
    // 2. Requête pour les nouveautés
    try {
        // Requete de base
        $sqlNouveautes = "SELECT p.id, p.nom, p.prix, p.lien_image, m.nom AS marque 
                          FROM produits p 
                          JOIN marques m ON p.marques_id = m.id 
                          JOIN status s ON p.id_status = s.id 
                          WHERE s.status = 'visible' ";
        // Filtrer par marque si sélectionnée dans le menu
        if (!empty($filtreMarque)) {
            $sqlNouveautes .= "AND m.nom = :marque ";
        }
        $sqlNouveautes .= "ORDER BY p.date_ajouter DESC LIMIT 6";
    
        $stmt = $connexion->prepare($sqlNouveautes);
        if (!empty($filtreMarque)) {
            $stmt->bindValue(':marque', $filtreMarque, PDO::PARAM_STR);
        }
        $stmt->execute();
        $nouveautes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<p>Erreur lors de la récupération des nouveautés : " . $e->getMessage() . "</p>";
        $nouveautes = [];
    }
    
    // 3. Requête pour les promotions
    try {
        $sqlPromotions = "SELECT 
                p.id, 
                p.nom, 
                p.lien_image, 
                pr.prix_initial, 
                CASE 
                    WHEN pr.type_reduction = 'pourcentage' THEN pr.prix_initial - (pr.prix_initial * (pr.valeur_reduction / 100))
                    WHEN pr.type_reduction = 'montant' THEN pr.prix_initial - pr.valeur_reduction
                    ELSE pr.prix_initial
                END AS prix_promo, 
                pr.type_reduction, 
                pr.valeur_reduction, 
                pr.date_debut, 
                pr.date_fin
            FROM produits p
            JOIN promotions pr ON p.id = pr.produit_id 
            JOIN marques m ON p.marques_id = m.id
            WHERE CURDATE() BETWEEN pr.date_debut AND pr.date_fin ";
        if (!empty($filtreMarque)) {
            $sqlPromotions .= "AND m.nom = :marque ";
        }
        $sqlPromotions .= "ORDER BY pr.date_debut ASC";
    
        $stmt = $connexion->prepare($sqlPromotions);
        if (!empty($filtreMarque)) {
            $stmt->bindValue(':marque', $filtreMarque, PDO::PARAM_STR);
        }
        $stmt->execute();
        $promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<p>Erreur lors de la récupération des promotions : " . $e->getMessage() . "</p>";
        $promotions = [];
    }
} else {
    // Pour une recherche, on récupère les résultats
    $nouveautes = $resultatsRecherche['produits'];
    $promotions = $resultatsRecherche['promotions'];
}

// Traitement de l'ajout d'un avis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_avis'])) {
    if (isset($_SESSION['utilisateur'])) {
        $produit_id = intval($_POST['produit_id']);
        $commentaire = trim($_POST['commentaire']);
        $note = intval($_POST['note']);
        
        if ($produit_id > 0 && !empty($commentaire) && $note >= 1 && $note <= 5) {
            try {
                $stmt = $connexion->prepare("INSERT INTO avis (utilisateur_id, produit_id, commentaire, note, approuve) 
                                            VALUES (:user_id, :prod_id, :comment, :note, 0)");
                $stmt->bindValue(':user_id', $_SESSION['utilisateur']['id'], PDO::PARAM_INT);
                $stmt->bindValue(':prod_id', $produit_id, PDO::PARAM_INT);
                $stmt->bindValue(':comment', $commentaire, PDO::PARAM_STR);
                $stmt->bindValue(':note', $note, PDO::PARAM_INT);
                $stmt->execute();
                
                $message = "Votre avis a été soumis et est en attente de modération.";
            } catch (PDOException $e) {
                $message = "Erreur lors de l'enregistrement de l'avis : " . $e->getMessage();
            }
        } else {
            $message = "Veuillez remplir correctement tous les champs.";
        }
    }
}



?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Accueil - Vente de téléphones</title>
    <link rel="stylesheet" href="index.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<header>
    <div class="logo">
        <a href="index.php"><img src="images/phone11.jpg" alt="Logo du site"></a>
    </div>
    <nav>
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <!-- Menu dynamique des marques -->
            <li>
                <a href="#">Marques</a>
                <ul>
                    <?php if(!empty($listeMarques)): ?>
                        <?php foreach ($listeMarques as $marque): ?>
                            <li>
                                <a href="index.php?marque=<?php echo urlencode($marque['nom']); ?>">
                                    <?php echo htmlspecialchars($marque['nom']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>Aucune marque définie</li>
                    <?php endif; ?>
                </ul>
            </li>
            <li><a href="nouveautes.php">Nouveautés</a></li>
            <li><a href="promotions.php">Promotions</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="panier.php">Panier (<?php echo isset($_SESSION['panier']) ? count($_SESSION['panier']) : 0; ?>)</a></li>
            <li>
                <?php if (isset($_SESSION['utilisateur'])): ?>
                    <a href="compte.php">Mon compte</a>
                    <a href="deconnexion.php" style="color:red;">Déconnexion</a>
                <?php else: ?>
                    <a href="connexion.php">Connexion/Inscription</a>
                <?php endif; ?>
            </li>
        </ul>
    </nav>
    <!-- Formulaire de recherche -->
    <div class="recherche">
        <form method="GET" action="index.php" class="rechercher">
            <input type="text" name="recherche" placeholder="Rechercher un produit ou une marque..." value="<?php echo htmlspecialchars($recherche); ?>">
            <button type="submit">Rechercher</button>
        </form>
    </div>
</header>

<!-- Bannière -->
<div class="banner">
    <div class="bannier-content">
        <h1>Bienvenue dans notre boutique!</h1>
        <p>Découvrez les dernières nouveautés et promotions sur les téléphones.</p>
        <a href="nouveautes.php" class="bouton-plus">Voir toutes les nouveautés</a>
    </div>

    <div class="bannier-slider">
        <div class="slider-image active">
            <img src="images/1744389108_479244395_1176540220831865_5327526399896685911_n.jpg" alt="Promotion spéciale">  
        </div>
        <div class="slider-image">
            <img src="images/1744309125_480900948_1141645627401114_1532489086935915470_n.jpg" alt="Nouveaux modèles">  
        </div>
        <div class="slider-image">
            <img src="images/1744361212_472546353_968299898514204_7450903685162314867_n.jpg" alt="Meilleures ventes">  
        </div>
    </div>
    
    <div class="slider-controls">
        <span class="slider-dot active" data-index="0"></span>
        <span class="slider-dot" data-index="1"></span>
        <span class="slider-dot" data-index="2"></span>
    </div>
</div>

<!-- Section Nouveautés -->
<section class="nouveautes">
    <h2>Nouveautés <?php echo (!empty($filtreMarque)) ? " - " . htmlspecialchars($filtreMarque) : ""; ?>
     <?php echo (!empty($recherche)) ? " - Résultats pour : " . htmlspecialchars($recherche) : ""; ?>
    </h2>
    <div class="liste-produits">
        <?php if (!empty($nouveautes)): ?>
            <?php foreach ($nouveautes as $produit): ?>
                <div class="produit">
                    <img src="images/<?php echo htmlspecialchars($produit['lien_image']); ?>" alt="<?php echo htmlspecialchars($produit['nom']); ?>">
                    <h3><?php echo htmlspecialchars($produit['nom']); ?></h3>
                    <p><?php echo number_format($produit['prix'], 2); ?> Fcfa</p>
                    <form method="POST" action="index.php">
                        <input type="hidden" name="produit_id" value="<?php echo $produit['id']; ?>">
                        <input type="number" name="quantite" value="1" min="1">
                        <button type="submit" name="ajouter_au_panier">Ajouter au panier</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun produit trouvé.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Section Promotions -->
<!-- Section Promotions -->
<section class="promotions">
    <h2>Promotions <?php echo (!empty($filtreMarque)) ? " - " . htmlspecialchars($filtreMarque) : ""; ?>
     <?php echo (!empty($recherche)) ? " - Résultats pour : " . htmlspecialchars($recherche) : ""; ?>
    </h2>
    <div class="liste-promotions">
        <?php if (!empty($promotions)): ?>
            <?php foreach ($promotions as $promotion): ?>
                <div class="promotion">
                    <img src="images/<?php echo htmlspecialchars($promotion['lien_image']); ?>" alt="<?php echo htmlspecialchars($promotion['nom']); ?>">
                    <h3><?php echo htmlspecialchars($promotion['nom']); ?></h3>
                    <p class="prix-initial">
                        <s><?php echo number_format($promotion['prix_initial'], 2); ?> Fcfa</s>
                    </p>
                    <p class="prix-promotion">
                        <?php echo number_format($promotion['prix_promo'], 2); ?> Fcfa
                        <span class="reduction">
                            <?php if ($promotion['type_reduction'] === 'pourcentage'): ?>
                                (-<?php echo htmlspecialchars($promotion['valeur_reduction']); ?>%)
                            <?php else: ?>
                                (-<?php echo number_format($promotion['valeur_reduction'], 2); ?> Fcfa)
                            <?php endif; ?>
                        </span>
                    </p>
                    <form method="POST" action="index.php">
                        <input type="hidden" name="produit_id" value="<?php echo $promotion['id']; ?>">
                        <input type="number" name="quantite" value="1" min="1">
                        <button type="submit" name="ajouter_au_panier">Ajouter au panier</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucune promotion disponible pour le moment.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Affichage des avis existants -->

<section class="affichage-avis">
    <h2>Derniers avis</h2>
    
    <?php
    try {
        $stmt = $connexion->prepare("
            SELECT a.*, u.nom AS utilisateur_nom, p.nom AS produit_nom
            FROM avis a
            JOIN utilisateurs u ON a.utilisateur_id = u.id
            JOIN produits p ON a.produit_id = p.id
            WHERE a.approuve = 1
            ORDER BY a.date_avis DESC
            LIMIT 8
        ");
        $stmt->execute();
        $avis = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($avis)) {
            foreach ($avis as $a) {
                echo '<div class="avis-card">';
                echo '<div class="avis-header">';
                echo '<span class="user">'.htmlspecialchars($a['utilisateur_nom']).'</span>';
                echo '<span class="product">'.htmlspecialchars($a['produit_nom']).'</span>';
                echo '<span class="date">'.date('d/m/Y', strtotime($a['date_avis'])).'</span>';
                echo '</div>';
                
                echo '<div class="rating">';
                for ($i = 1; $i <= 5; $i++) {
                    echo ($i <= $a['note']) ? '★' : '☆';
                }
                echo '</div>';
                
                echo '<div class="comment">'.nl2br(htmlspecialchars($a['commentaire'])).'</div>';
                echo '</div>';
            }
        } else {
            echo '<p class="no-reviews">Aucun avis disponible pour le moment.</p>';
        }
    } catch (PDOException $e) {
        echo '<p class="db-error">Erreur de chargement des avis.</p>';
    }
    ?>
</section>
<!-- Section Avis -->
<section class="deposer-avis">
<?php
// Vérification si l'utilisateur est connecté
if (isset($_SESSION['utilisateur'])): 
?>
    <section class="ajout-avis">
        <h3>Donnez votre avis</h3>
        <form method="POST" action="traitement_avis.php" class="form-avis">
            <div class="form-group">
                <label for="produit_nom">Produit :</label>
                <select name="produit_id" id="produit_nom" required>
                    <option value="">-- Sélectionnez un produit --</option>
                    <?php
                    // Récupération de la liste des produits
                    $stmt = $connexion->query("SELECT id, nom FROM produits ORDER BY nom ASC");
                    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($produits as $produit) {
                        echo '<option value="'.$produit['id'].'">'.htmlspecialchars($produit['nom']).'</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Note :</label>
                <div class="rating-stars">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" id="star<?php echo $i; ?>" name="note" value="<?php echo $i; ?>" required>
                        <label for="star<?php echo $i; ?>">★</label>
                    <?php endfor; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="commentaire">Votre avis :</label>
                <textarea name="commentaire" id="commentaire" rows="5" required 
                          placeholder="Décrivez votre expérience avec ce produit..."></textarea>
            </div>
            
            <button type="submit" name="submit_avis" class="btn-submit">Envoyer l'avis</button>
        </form>
    </section>
        <?php else: ?>
            <div class="connexion-requise">
                    <p>Vous devez être connecté pour poster un avis.</p>
                    <a href="connexion.php" class="btn-connexion">Se connecter</a>
            </div>
        <?php endif; ?>
</section>

<!-- Section Contact -->
<section id="contact" class="contact-section">
    <h2>Contactez-nous</h2>
    <?php if (!empty($message)): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="POST" action="#contact">
            <div class="form-group">
                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" required>
            </div>
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="sujet">Sujet :</label>
                <input type="text" id="sujet" name="sujet" required>
            </div>
            <div class="form-group">
                <label for="message">Message :</label>
                <textarea id="message" name="message" required></textarea>
            </div>
            <button type="submit" name="submit_contact">Envoyer</button>
        </form>
    
    <div class="contact-info">
        <div class="info-item">
            <i class="fas fa-map-marker-alt"></i>
            <p>123 Rue des Téléphones, Ville</p>
        </div>
        <div class="info-item">
            <i class="fas fa-phone"></i>
            <p>01 23 45 67 89</p>
        </div>
        <div class="info-item">
            <i class="fas fa-envelope"></i>
            <p>contact@votresite.com</p>
        </div>
    </div>
</section>


<!-- Footer -->
<footer>
    
    <div class="contenu-footer">
        <div class="colonnes-footer">
            <div class="colonne">
                <h3>Informations</h3>
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="promotions.php">Promotions</a></li>
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="cgv.php">CGV</a></li>
                    <li><a href="politique-confidentialite.php">Politique de confidentialité</a></li>
                </ul>
            </div>
            <div class="colonne">
                <h3>Contact</h3>
                <p>Adresse : 123 Rue des Téléphones, Ville</p>
                <p>Téléphone : 01 23 45 67 89</p>
                <p>Email : contact@votresite.com</p>
            </div>
            <div class="colonne reseaux-sociaux">
                <h3>Suivez-nous</h3>
                <a href="https://www.facebook.com" target="_blank"><i class="fab fa-facebook"></i> Facebook</a><br>
                <a href="https://www.twitter.com" target="_blank"><i class="fab fa-twitter"></i> Twitter</a><br>
                <a href="https://www.instagram.com" target="_blank"><i class="fab fa-instagram"></i> Instagram</a><br>
                <a href="https://www.linkedin.com" target="_blank"><i class="fab fa-linkedin"></i> LinkedIn</a>
            </div>
            <div class="colonne paiements">
                <h3>Paiements sécurisés</h3>
                <img src="images/visa.png" alt="Visa">
                <img src="images/mastercard.png" alt="Mastercard">
                <img src="images/paypal.png" alt="PayPal">
            </div>
        </div>
        <div class="copyright">
            <p>&copy; <?php echo date("Y"); ?> Vente de téléphones. Tous droits réservés.</p>
        </div>
    </div>
</footer>
<script>
  // Si l'URL contient des paramètres, on les retire pour afficher l'état initial dès rafraîchissement
  if(window.location.search) {
      window.history.replaceState(null, null, window.location.pathname);
  }
</script>
<script src="index.js"></script>
</body>
</html>