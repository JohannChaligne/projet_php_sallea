<?php
include 'inc/init.inc.php';


// CODE
$categorie = '';
$ville = '';
$capacite = '';
$prix = '';
$date_arrivee = '';
$date_depart = '';

if(
    isset($_POST['categorie']) &&
    isset($_POST['ville']) &&
    isset($_POST['capacite']) &&
    isset($_POST['prix']) &&
    isset($_POST['date_arrivee']) &&
    isset($_POST['date_depart'])) {
        $categorie = htmlentities(trim($_POST['categorie']));
        $ville = htmlentities(trim($_POST['ville']));
        $capacite = htmlentities(trim($_POST['capacite']));
        $prix = htmlentities(trim($_POST['prix']));
        $date_arrivee = htmlentities(trim($_POST['date_arrivee']));
        $date_depart = htmlentities(trim($_POST['date_depart']));
    }

// Création du Multi-filtre
$where = '';
$args = array();
if(!empty($categorie)) {
	$where .= " AND  categorie = :categorie ";
	$args[':categorie'] = $categorie; 
}

if(!empty($ville)) {
	$where .= " AND ville = :ville ";
	$args[':ville'] = $ville; 
}

if(!empty($capacite)) {
	$where .= " AND capacite = :capacite ";
	$args[':capacite'] = $capacite; 
}

if(!empty($prix)) {
	$where .= " AND prix <= :prixmax ";
	$args[':prixmax'] = $prix; 
}

if(!empty($date_arrivee)) {
	$where .= " AND date_arrivee = :date_arrivee ";
	$args[':date_arrivee'] = $date_arrivee; 
}

if(!empty($date_depart)) {
	$where .= " AND date_depart = :date_depart ";
	$args[':date_depart'] = $date_depart; 
}

// Affichage de la pagination et du nombre de page à afficher en fonction du contenu par filtre
$liste_pagination = $pdo->prepare("SELECT *, date_format(date_arrivee, '%d/%m/%Y') AS date_arrivee, date_format(date_depart, '%d/%m/%Y') AS date_depart FROM salle, produit WHERE date_arrivee > now() AND salle.id_salle = produit.id_salle $where GROUP BY id_produit ORDER BY date_arrivee DESC");
$liste_pagination->execute($args);

$produits_par_pages = 6;
$produits_totaux = $liste_pagination->rowCount();
$pages_totales = ceil($produits_totaux/$produits_par_pages);

if(isset($_GET['page']) AND !empty($_GET['page']) AND $_GET['page'] > 0){
    $_GET['page'] = intval($_GET['page']);
    $page_courante = $_GET['page'];
} else {
    $page_courante = 1;
}


$depart = ($page_courante-1)*$produits_par_pages;


// Affichage des produits en fonction du filtre
$liste_salles = $pdo->prepare("SELECT *, date_format(date_arrivee, '%d/%m/%Y') AS date_arrivee, date_format(date_depart, '%d/%m/%Y') AS date_depart FROM salle, produit WHERE date_arrivee > now() AND salle.id_salle = produit.id_salle $where GROUP BY id_produit ORDER BY date_arrivee DESC LIMIT $depart, $produits_par_pages");
$liste_salles->execute($args);

include 'inc/header.inc.php';
include 'inc/nav.inc.php';
?>

<main class="container main_bg">

    <div class="starter-template text-center marge_haute">
        <h1>Location de salle</h1>
    </div>
    <div class="row">
        <div class="col-3">
            <form method="post" action="location_salle.php">
                <div class="form-group">
                    <a href="location_salle.php" class="reset_filtre" style="text-decoration: underline;">Effacer les critères sélectionnés</a>
                </div>
                <?php
                // FILTRE PAR CATEGORIE
                $categorie_salle = $pdo->query("SELECT DISTINCT categorie FROM salle ORDER BY categorie");
                echo '<div class="form-group">';
                echo '<label for="categorie">Catégorie</label>';
                echo '<select name="categorie" id="categorie" class="form-control">';
                echo '<option></option>';
                while ($liste_categorie = $categorie_salle->fetch(PDO::FETCH_ASSOC)){
                    echo '<option';
                    if ($categorie == $liste_categorie['categorie']) { echo ' selected '; }
                        echo ' value="' . htmlentities($liste_categorie['categorie']) . '">' . htmlentities(ucfirst($liste_categorie['categorie'])) . '</option>';
                }  		
                echo '</select>';
                echo '</div>';


                // FILTRE PAR VILLE
                $ville_salle = $pdo->query("SELECT DISTINCT ville FROM salle ORDER BY ville"); 
                echo '<div class="form-group">';
                echo '<label for="ville">Ville</label>';
                echo '<select name="ville" id="ville" class="form-control">';
                echo '<option></option>';
                while ($liste_ville = $ville_salle->fetch(PDO::FETCH_ASSOC)){
                    echo '<option';
                    if ($ville == $liste_ville['ville']) { echo ' selected '; }
                        echo ' value="' . htmlentities($liste_ville['ville']) . '">' . htmlentities(ucfirst($liste_ville['ville'])) . '</option>';
                }  		
                echo '</select>';
                echo '</div>';


                // FILTRE PAR CAPACITE
                $capacite_salle = $pdo->query("SELECT DISTINCT capacite FROM salle ORDER BY capacite"); 
                echo '<div class="form-group">';
                echo '<label for="capacite">Capacite</label>';
                echo '<select name="capacite" id="capacite" class="form-control">';
                echo '<option></option>';
                while($liste_capacite = $capacite_salle->fetch(PDO::FETCH_ASSOC)){
                    echo '<option';
                    if ($capacite == $liste_capacite['capacite']) { echo ' selected '; }
                        echo ' value="' . htmlentities($liste_capacite['capacite']) . '">' . htmlentities(ucfirst($liste_capacite['capacite'])) . '</option>';
                }  		
                echo '</select>';
                echo '</div>';
                ?>

                <!-- FILTRE PAR PRIX -->
                <div class="form-group">
                    <label for="prix">Prix maximum (en €)</label>
                    <input type="text" class="form-control" name="prix" id="prix" value="<?php echo $prix; ?>">
                </div>


                <!-- FILTRE PAR DATE D'ARRIVEE -->
                <div class="form-group">
                    <label for="date_arrivee">Date d'arrivée</label>
                    <input type="text" class="datepick form-control" name="date_arrivee" id="date_arrivee" value="<?php echo $date_arrivee; ?>"> 
                </div>

                <!-- FILTRE PAR DATE DE DEPART -->
                <div class="form-group">
                    <label for="date_depart">Date de départ</label>
                    <input type="text" class="datepick form-control" name="date_depart" id="date_depart" value="<?php echo $date_depart; ?>">	
                </div>
            
                <div class="form-group">
                    <button type="submit" name='submit_filtre' class="btn btn-info w-100 mt-2">Valider</button>
			    </div>
            </form> 
  	    </div>
        <div class="col-9">
            <div class="row">
                <?php
                while($salle = $liste_salles->fetch(PDO::FETCH_ASSOC)) {
                    $avis = $pdo->prepare("SELECT *, (sum(note)/count(note)) AS moyenne FROM avis, salle WHERE salle.id_salle = avis.id_salle AND avis.id_salle = :id_salle");
                    $avis->bindParam(':id_salle', $salle['id_salle'], PDO::PARAM_STR);
                    $avis->execute();

                    $liste_avis = $avis->fetch(PDO::FETCH_ASSOC);

                    echo '<div class="col-4">';
                    echo '<div class="card mt-3">
                            <img src="' . URL . htmlentities($salle['photo']) . '" class="card-img-top p-2" alt="' . htmlentities($salle['titre']) . '">
                            <div class="card-body">
                            <div class="row">
                                <h5 class="card-title col-8">Salle ' . htmlentities(ucfirst($salle['titre'])) . '</h5>
                                <p class="card-text col-4"><span class="badge-info badge_price">' . htmlentities($salle['prix']) . ' €</span></p>
                            </div>
                            <p class="card-text"><span class="couleur-star">' . htmlentities(afficheretoile(round($liste_avis['moyenne'], 2))) . '</span> <span class="taille-mini">' . htmlentities(round($liste_avis['moyenne'], 2)) . '/5 étoiles</span></p>
                            <p class="card-text">Lieu : ' . htmlentities(ucfirst($salle['ville'])) . '</p>
                            <p class="card-text">' . htmlentities(iconv_substr($salle['description'], 0, 55)) . '...</p>
                            <p class="card-text"><i class="fas fa-calendar-week"></i> ' . htmlentities($salle['date_arrivee']) . ' au ' . $salle['date_depart'] . '</p>
                            <div class="row">
                                <a class="btn btn-info col-12" href="fiche_produit.php?id_produit=' . htmlentities($salle['id_produit']) . '"><i class="fas fa-search"></i> Voir le produit</a>
                            </div>
                        </div>
                        </div>';
                    echo '</div>';
                } ?>	
            </div>
        </div>
        <div class="offset-6">
            <div class="row">
                <?php
        echo '<ul class="pagination justify-content-center mt-2">';
        for($i=1; $i<=$pages_totales; $i++){
            if($i == $page_courante){
                echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
            } else {
                echo '<li class="page-item"><a class="page-link" href="location_salle.php?page=' . $i . '">' . $i . '</a></li>';
            }
        }
        echo '</ul>'; ?>	
            </div>
        </div>
    </div>
</main>


<?php
include 'inc/footer.inc.php';