<?php
include 'inc/init.inc.php';

if(empty($_GET['id_produit']) && !intval($_GET['id_produit'])) {
	header('location:location_salle.php');
}

// CODE

// récupération des données dans la table produit et salle en BDD
$recup_salles_produit_avis = $pdo->prepare("SELECT *, date_format(date_arrivee, '%d/%m/%Y') AS date_arrivee, date_format(date_depart, '%d/%m/%Y') AS date_depart FROM salle, produit WHERE id_produit = :id_produit AND salle.id_salle = produit.id_salle"); 
$recup_salles_produit_avis->bindParam(':id_produit', $_GET['id_produit'], PDO::PARAM_STR);
$recup_salles_produit_avis->execute();

$liste_salles_produit_avis = $recup_salles_produit_avis->fetch(PDO::FETCH_ASSOC);
$id_salle = $liste_salles_produit_avis['id_salle'];

// Calcul de la moyenne des avis
$tab_moyenne_avis = $pdo->prepare("SELECT (sum(note)/count(note)) AS moyenne  FROM avis WHERE id_salle = :id_salle");
$tab_moyenne_avis->bindParam(':id_salle', $id_salle, PDO::PARAM_STR);
$tab_moyenne_avis->execute();
$moyenne_avis = $tab_moyenne_avis->fetch(PDO::FETCH_ASSOC);


include 'inc/header.inc.php';
include 'inc/nav.inc.php';
?>

<main class="container">

  <div class="starter-template marge_haute">
    <div class="row">
        <div class="col-8">
            <h1>Salle <?php echo htmlentities(ucfirst($liste_salles_produit_avis['titre'])) . ' <span class="couleur-star">' . afficheretoile(round($moyenne_avis['moyenne'], 2)) . '</span> <span class="taille-mini">' . htmlentities(round($moyenne_avis['moyenne'], 2)) . '/5 étoiles</span>'; ?></h1>
        </div>
        <div class="offset-2 col-2">
        <?php 
			if(htmlentities($liste_salles_produit_avis['etat'])== 'libre') {
                echo '<form method="post" action="panier.php">';
				echo '<input type="hidden" name="id_produit" value="' . htmlentities($liste_salles_produit_avis['id_produit']) . '">';	
				echo '<button type="submit" name="ajout_panier" class="btn btn-success w-100">Réserver</button>';
				echo '</form>';
					
			} else {
				echo '<span class="text-danger">Produit non disponible</span>';
			}
		?>
        </div>
    </div>
    <div class="row">
        <div class="col-8">
            <img src="<?php echo  URL . $liste_salles_produit_avis['photo']; ?>" class="w-75" alt="photo de la salle <?php htmlentities($liste_salles_produit_avis['titre']); ?>">
        </div>
        <div class="col-4">
            <ul class="list-group">
                <li class="list-group-item">Description : <?php echo htmlentities($liste_salles_produit_avis['description']); ?></li>
                <li class="list-group-item">Localisation : <iframe class="style_iframe_localisation" src="<?php echo htmlentities($liste_salles_produit_avis['localisation']); ?>"></iframe></li>  
            </ul>
        </div>
    </div>
    <p>Informations complémentaires</p>
    <div class="row">
        <div class="col-4">
            <ul class="list-group">
                <li class="list-group-item"><i class="fas fa-calendar-week"></i> Arrivée : <?php echo htmlentities($liste_salles_produit_avis['date_arrivee']); ?></li>
                <li class="list-group-item"><i class="fas fa-calendar-week"></i> Départ : <?php echo htmlentities($liste_salles_produit_avis['date_depart']); ?></li>
            </ul>
        </div>
        <div class="col-4">
            <ul class="list-group">
                <li class="list-group-item"><i class="fas fa-users"></i> Capacité : <?php echo htmlentities($liste_salles_produit_avis['capacite']); ?></li>
                <li class="list-group-item"><i class="fas fa-chess-rook"></i> Categorie : <?php echo htmlentities(ucfirst($liste_salles_produit_avis['categorie'])); ?></li>
            </ul>
        </div>
        <div class="col-4">
            <ul class="list-group">
                <li class="list-group-item"><i class="fas fa-map-marker-alt"></i> Adresse : <?php echo htmlentities($liste_salles_produit_avis['adresse']); ?></li>
                <li class="list-group-item"><i class="fas fa-tags"></i> Tarif : <?php echo htmlentities($liste_salles_produit_avis['prix']) . ' €'; ?></li>
            </ul>
        </div>
    </div>
    <?php 
    // Déposer un avis sur la salle
    $pseudo = '';
    $note = '';
    $commentaire = '';
    if(isset($_POST['submit_commentaire'])) {
        if(user_is_connect()){
            if(isset($_POST['pseudo']) &&
            isset($_POST['note']) &&
            isset($_POST['commentaire']) &&
            !empty($_POST['pseudo']) &&
            !empty($_POST['note']) &&
            !empty($_POST['commentaire'])){
                $pseudo = htmlentities(trim($_POST['pseudo']));
                $note = htmlentities(trim($_POST['note']));
                $commentaire = htmlentities(trim($_POST['commentaire']));
                if($pseudo == $_SESSION['membre']['pseudo']){
                    $insertion_commentaire = $pdo->prepare("INSERT INTO avis (id_membre, id_salle, commentaire, note, date_enregistrement) VALUES (:id_membre, :id_salle, :commentaire, :note, NOW())");
                    $insertion_commentaire->bindParam(':id_membre', $_SESSION['membre']['id_membre'], PDO::PARAM_STR);
		            $insertion_commentaire->bindParam(':id_salle', $id_salle, PDO::PARAM_STR);
                    $insertion_commentaire->bindParam(':commentaire', $commentaire, PDO::PARAM_STR);
                    $insertion_commentaire->bindParam(':note', $note, PDO::PARAM_STR);
                    $insertion_commentaire->execute();	
                    $msg .= '<div class="alert alert-success"> Votre commentaire a bien été posté.</div>';
                }
                else {
                    $msg .= '<div class="alert alert-danger"> Ce pseudo n\'est pas le vôtre.</div>'; 
                }
            }
            else {
                $msg .= '<div class="alert alert-danger"> Tous les champs doivent être complétés.</div>';
                }
        }
        else {
            $msg .= '<div class="alert alert-danger"> Vous devez être connecté pour déposer un commentaire. Pour se connecter, <a href="connexion.php">cliquez ici</a>. Si vous n\'êtes toujours pas inscrit, <a href="inscription.php">cliquez ici</a>.</div>';
        }   
    }?>
    <div class="row">
        <div class="col-6">
            <form method="post">
                <p><?php echo $msg; ?></p>
                <label>Pseudo</label>
                <input type="text" class="form-control" name="pseudo" id="pseudo" value="<?php if(user_is_connect()){ echo $_SESSION['membre']['pseudo'];} ?>">
                <label>Note</label>
                <select class="form-control" name="note" id="note">
                    <option value="1">1 / 5 étoiles</option>
                    <option value="2">2 / 5 étoiles</option>
                    <option value="3">3 / 5 étoiles</option>
                    <option value="4">4 / 5 étoiles</option>
                    <option value="5">5 / 5 étoiles</option>
                </select>
                <label for="commentaire">Commentaire</label>
		        <textarea name="commentaire" id="commentaire" class="form-control"></textarea>
                <input type="submit" class="btn btn-info w-100 mt-2" value="Poster" name="submit_commentaire">
            </form>
        </div>
        <div class="col-6"><br>
        <?php
        // Mettre en place une pagination sur les avis déposés 
        $liste_pagination_com = $pdo->prepare("SELECT *, date_format(avis.date_enregistrement, '%d/%m/%Y') AS date_enregistrement_avis FROM avis, membre WHERE id_salle = :id_salle AND avis.id_membre = membre.id_membre ORDER BY avis.date_enregistrement DESC");
        $liste_pagination_com->bindParam(':id_salle', $id_salle, PDO::PARAM_STR);
        $liste_pagination_com->execute();
        $commentaires_par_pages = 3;
        $commentaires_totaux = $liste_pagination_com->rowCount();
        $pages_totales = ceil($commentaires_totaux/$commentaires_par_pages);


        if(isset($_GET['page']) AND !empty($_GET['page']) AND $_GET['page'] > 0){
            $_GET['page'] = intval($_GET['page']);
            $page_courante = $_GET['page'];
        } else {
            $page_courante = 1;
        }

        $depart = ($page_courante-1)*$commentaires_par_pages;

        $commentaires = $pdo->prepare("SELECT *, date_format(avis.date_enregistrement, '%d/%m/%Y') AS date_enregistrement_avis FROM avis, membre WHERE id_salle = :id_salle AND avis.id_membre = membre.id_membre ORDER BY avis.date_enregistrement DESC LIMIT $depart, $commentaires_par_pages");
        $commentaires->bindParam(':id_salle', $id_salle, PDO::PARAM_STR);
        $commentaires->execute();

        while($liste_commentaires = $commentaires->fetch(PDO::FETCH_ASSOC)){ ?>
        <div class="alert alert-secondary"><span style="font-weight: bold;"><?php echo htmlentities(ucfirst($liste_commentaires['pseudo'])) . '</span> - ' . htmlentities($liste_commentaires['note']) . ' / 5 étoiles (Le ' . htmlentities($liste_commentaires['date_enregistrement_avis']) . ')<p>' . htmlentities($liste_commentaires['commentaire']) . '</p>';?></div>
        
        <?php }
        echo '<ul class="pagination justify-content-center">';
        for($i=1; $i<=$pages_totales; $i++){
            if($i == $page_courante){
                echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
            } else {
                echo '<li class="page-item"><a class="page-link" href="fiche_produit.php?id_produit=' . htmlentities($liste_salles_produit_avis['id_produit']) . '&page=' . $i . '">' . $i . '</a></li>';
            }
        }
        echo '</ul>';
         ?>

        </div>
    </div>
    <div class="row">
        <div class="col-12 mt-4">
            <h2>D'autres produits sont disponibles</h2>
        </div>
    </div>
    <?php 
    // Proposition des produits de la même salle à des dates différentes
    $titre = $liste_salles_produit_avis['titre'];
    $categorie = $liste_salles_produit_avis['categorie'];
    $recup_infos_salle = $pdo->prepare("SELECT *, date_format(date_arrivee, '%d/%m/%Y') AS date_arrivee, date_format(date_depart, '%d/%m/%Y') AS date_depart FROM salle, produit WHERE salle.id_salle = produit.id_salle AND titre = :titre AND categorie = :categorie ORDER BY date_arrivee ASC LIMIT 0,4");
    $recup_infos_salle->bindParam(':titre', $titre, PDO::PARAM_STR);
    $recup_infos_salle->bindParam(':categorie', $categorie, PDO::PARAM_STR);
    $recup_infos_salle->execute();
    echo '<div class="row">';
    while($ligne = $recup_infos_salle->fetch(PDO::FETCH_ASSOC)){
        echo '<div class="col-3">';
        echo '<div class="card mt-3">
        <img src="' . URL . htmlentities($liste_salles_produit_avis['photo']) . '" class="card-img-top p-2" alt="' . htmlentities($ligne['titre']) . '">
        <div class="card-body">
        <div class="row">
            <h5 class="card-title col-8">Salle ' . htmlentities(ucfirst($ligne['titre'])) . '</h5>
            <p class="card-text col-4"><span class="badge-info badge_price">' . htmlentities($ligne['prix']) . ' €</span></p>
        </div>
        <p class="card-text"><span class="couleur-star">' . afficheretoile(round($moyenne_avis['moyenne'], 2)) . '</span> <span class="taille-mini">' . htmlentities(round($moyenne_avis['moyenne'], 2)) . '/5 étoiles</span></p>
        <p class="card-text">Lieu : ' . htmlentities(ucfirst($liste_salles_produit_avis['ville'])) . '</p>
        <p class="card-text">' . htmlentities(iconv_substr($liste_salles_produit_avis['description'], 0, 60)) . '...</p>
        <p class="card-text"><i class="fas fa-calendar-week"></i> ' . htmlentities($ligne['date_arrivee']) . ' au ' . htmlentities($ligne['date_depart']) . '</p>
        <div class="row">
            <a class="btn btn-info col-12" href="fiche_produit.php?id_produit=' . htmlentities($ligne['id_produit']) . '"><i class="fas fa-search"></i> Voir le produit</a>
        </div>
        </div>';
        echo '</div>';
        echo '</div>';
    }
    echo '</div>'; ?>
    <div class="row">
        <div class="col-3">
            <a href="location_salle.php" class="bg_fiche_produit_home">Revenir au catalogue</a>
        </div>
    </div>
  
  
    </div>

</main>


<?php
include 'inc/footer.inc.php';
