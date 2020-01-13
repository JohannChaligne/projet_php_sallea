<?php
include 'inc/init.inc.php';

if(empty($_GET['id_produit']) && !intval($_GET['id_produit'])) {
	header('location:location_salle.php');
}

// CODE

// récupération de des données produit en BDD
$recup_salles_produit_avis = $pdo->prepare("SELECT *, date_format(date_arrivee, '%d/%m/%Y') AS date_arrivee, date_format(date_depart, '%d/%m/%Y') AS date_depart FROM salle, produit WHERE id_produit = :id_produit AND salle.id_salle = produit.id_salle"); 
$recup_salles_produit_avis->bindParam(':id_produit', $_GET['id_produit'], PDO::PARAM_STR);
$recup_salles_produit_avis->execute();

// if($recup_salles_produit_avis->rowCount() < 1) {
// 	header('location:location_salle.php');
// }

$liste_salles_produit_avis = $recup_salles_produit_avis->fetch(PDO::FETCH_ASSOC);
$id_salle = $liste_salles_produit_avis['id_salle'];

$tab_moyenne_avis = $pdo->prepare("SELECT (sum(note)/count(note)) AS moyenne  FROM avis WHERE id_salle = :id_salle");
$tab_moyenne_avis->bindParam(':id_salle', $id_salle, PDO::PARAM_STR);
$tab_moyenne_avis->execute();
$moyenne_avis = $tab_moyenne_avis->fetch(PDO::FETCH_ASSOC);


include 'inc/header.inc.php';
include 'inc/nav.inc.php';
?>

<main role="main" class="container">

  <div class="starter-template marge_haute">
    <div class="row">
        <div class="col-8">
            <h1><?php echo ucfirst($liste_salles_produit_avis['categorie']) . ' ' . ucfirst($liste_salles_produit_avis['titre']) . ' ' . afficheretoile(round($moyenne_avis['moyenne'], 2)); ?></h1>
        </div>
        <div class="offset-2 col-2">
        <?php 
			if($liste_salles_produit_avis['etat']== 'libre') {
                echo '<form method="post" action="panier.php">';
				echo '<input type="hidden" name="id_produit" value="' . $liste_salles_produit_avis['id_produit'] . '">';	
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
            <img src="<?php echo  URL . $liste_salles_produit_avis['photo']; ?>" class="w-75">
        </div>
        <div class="col-4">
            <ul class="list-group">
                <li class="list-group-item">Description : <?php echo $liste_salles_produit_avis['description']; ?></li>
                <li class="list-group-item">Localisation : <iframe width="100%" height="250" src="<?php echo $liste_salles_produit_avis['localisation']; ?>" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe></li>                        
            </ul>
        </div>
    </div>
    <p>Informations complémentaires</p>
    <div class="row">
        <div class="col-4">
            <ul class="list-group">
                <li class="list-group-item"><i class="fas fa-calendar-week"></i> Arrivée : <?php echo $liste_salles_produit_avis['date_arrivee']; ?></li>
                <li class="list-group-item"><i class="fas fa-calendar-week"></i> Départ : <?php echo $liste_salles_produit_avis['date_depart']; ?></li>
            </ul>
        </div>
        <div class="col-4">
            <ul class="list-group">
                <li class="list-group-item"><i class="fas fa-users"></i> Capacité : <?php echo $liste_salles_produit_avis['capacite']; ?></li>
                <li class="list-group-item"><i class="fas fa-chess-rook"></i> Categorie : <?php echo $liste_salles_produit_avis['categorie']; ?></li>
            </ul>
        </div>
        <div class="col-4">
            <ul class="list-group">
                <li class="list-group-item"><i class="fas fa-map-marker-alt"></i> Adresse : <?php echo $liste_salles_produit_avis['adresse']; ?></li>
                <li class="list-group-item"><i class="fas fa-tags"></i> Tarif : <?php echo $liste_salles_produit_avis['prix'] . ' €'; ?></li>
            </ul>
        </div>
    </div>
    <?php 
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
                $pseudo = strip_tags(trim($_POST['pseudo']));
                $note = strip_tags(trim($_POST['note']));
                $commentaire = strip_tags(trim($_POST['commentaire']));
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
    }
  
        ?>
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
        <input type="submit" class="btn btn-primary w-100 mt-2" value="Poster" name="submit_commentaire">
        </div>
        <div class="col-6">
        <br>
        <?php 
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
        <div class="alert alert-secondary"><span style="font-weight: bold;"><?php echo ucfirst($liste_commentaires['pseudo']) . '</span> - ' . $liste_commentaires['note'] . ' / 5 étoiles (Le ' . $liste_commentaires['date_enregistrement_avis'] . ')<p>' . $liste_commentaires['commentaire'] . '</p>';?></div>
        
        <?php }
        echo '<ul class="pagination justify-content-center">';
        for($i=1; $i<=$pages_totales; $i++){
            if($i == $page_courante){
                echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
            } else {
                echo '<li class="page-item"><a class="page-link" href="fiche_produit.php?id_produit=' . $liste_salles_produit_avis['id_produit'] . '&page=' . $i . '">' . $i . '</a></li>';
            }
        }
        echo '</ul>';
         ?>

        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <h2>D'autres produits sont disponibles</h2>
        </div>
    </div>
    <?php 
    $titre = $liste_salles_produit_avis['titre'];
    $categorie = $liste_salles_produit_avis['categorie'];
    $recup_infos_salle = $pdo->prepare("SELECT *, date_format(date_arrivee, '%d/%m/%Y') AS date_arrivee, date_format(date_depart, '%d/%m/%Y') AS date_depart FROM salle, produit WHERE salle.id_salle = produit.id_salle AND titre = :titre AND categorie = :categorie LIMIT 0,4");
    $recup_infos_salle->bindParam(':titre', $titre, PDO::PARAM_STR);
    $recup_infos_salle->bindParam(':categorie', $categorie, PDO::PARAM_STR);
    $recup_infos_salle->execute();
    echo '<div class="row">';
    while($ligne = $recup_infos_salle->fetch(PDO::FETCH_ASSOC)){
        echo '<div class="col-3">';
        echo '<div class="card mt-3">
                <img src="' . URL . $liste_salles_produit_avis['photo'] . '" class="card-img-top p-2" alt="' . $liste_salles_produit_avis['titre'] . '">
                <div class="card-body">
                    <div class="row">
                        <h5 class="card-title col-6">' . ucfirst($ligne['titre']) . '</h5>
                        <p class="card-text offset-2 col-4">' . $ligne['prix'] . ' €</p>
                    </div>
                    <p class="card-text">' . iconv_substr($liste_salles_produit_avis['description'], 0, 20) . '...</p>
                    <p class="card-text"><i class="fas fa-calendar-week"></i> ' . $ligne['date_arrivee'] . ' au ' . $ligne['date_depart'] . '</p>
                    <div class="row">
                        <div class="col-7"><p class="card-text">' . round($moyenne_avis['moyenne'], 2) . ' / 5 étoiles</p></div>
                        <a class="offset-1 col-4" href="fiche_article.php?id_produit=' . $ligne['id_produit'] . '" class="btn btn-primary"><i class="fas fa-search"></i> Voir</a>
                    </div>
                </div>
            </div>';
        echo '</div>';
    }
    echo '</div>'; ?>
    <div class="row">
        <div class="col-3">
            <a href="location_salle.php">Revenir au catalogue</a>
        </div>
    </div>
  
  
    </div>

</main><!-- /.container -->


<?php
include 'inc/footer.inc.php';
