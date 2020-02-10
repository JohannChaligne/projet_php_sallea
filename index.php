<?php
include 'inc/init.inc.php';

include 'inc/header.inc.php';
include 'inc/nav.inc.php';
?>
<div class="accueil" id="fond_accueil">
    <main class="container">
        <div class="starter-template text-center marge_haute">
            <h1 id="titre_accueil">Votre salle de réunion, de formation et de conférences</h1>
            <p class="para_description" >Découvrez toutes nos salles pour chacun de vos évènements d'entreprises.</p>
            <a href="location_salle.php" class="btn btn-info">Réserver votre salle</a>
        </div>
    </main>
</div>

    <div class="row bg-index">
        <div class="col-12 text-center mt-3">
            <h2>Top 3 des salles les plus populaires</h2>
        </div>
    </div>
    <div class="row bg-index justify-content-center mb-3">
    <?php 
    // Affichage des salles les plus populaires 
   $liste_salles_pop = $pdo->prepare("SELECT DISTINCT *, date_format(date_arrivee, '%d/%m/%Y') AS date_arrivee, date_format(date_depart, '%d/%m/%Y') AS date_depart FROM salle, produit, avis WHERE date_arrivee > now() AND salle.id_salle = produit.id_salle AND salle.id_salle = avis.id_salle GROUP BY salle.id_salle ORDER BY note DESC LIMIT 3");
   $liste_salles_pop->execute();
    while($salle_pop = $liste_salles_pop->fetch(PDO::FETCH_ASSOC)) {
        $avis_salles = $pdo->prepare("SELECT *, (sum(note)/count(note)) AS moyenne FROM avis, salle WHERE salle.id_salle = avis.id_salle AND avis.id_salle = :id_salle");
        $avis_salles->bindParam(':id_salle', $salle_pop['id_salle'], PDO::PARAM_STR);
        $avis_salles->execute();

        $liste_avis_pop = $avis_salles->fetch(PDO::FETCH_ASSOC);

        echo '<div class="col-md-3 col-9">';
        echo '<div class="card mt-3">
              <img src="' . URL . htmlentities($salle_pop['photo']) . '" class="card-img-top p-2" alt="' . htmlentities($salle_pop['titre']) . '">
              <div class="card-body">
              <div class="row">
                  <h5 class="card-title col-8">Salle ' . htmlentities(ucfirst($salle_pop['titre'])) . '</h5>
                  <p class="card-text col-4"><span class="badge-info badge_price">' . htmlentities($salle_pop['prix']) . ' €</span></p>
              </div>
              <p class="card-text"><span class="couleur-star">' . afficheretoile(round($liste_avis_pop['moyenne'], 2)) . '</span> <span class="taille-mini">' . htmlentities(round($liste_avis_pop['moyenne'], 2)) . '/5 étoiles</span></p>
              <p class="card-text">Lieu : ' . htmlentities(ucfirst($salle_pop['ville'])) . '</p>
              <p class="card-text">' . htmlentities(iconv_substr($salle_pop['description'], 0, 60)) . '...</p>
              <p class="card-text"><i class="fas fa-calendar-week"></i> ' . htmlentities($salle_pop['date_arrivee']) . ' au ' . htmlentities($salle_pop['date_depart']) . '</p>
              <div class="row">
                  <a class="btn btn-info col-12" href="fiche_produit.php?id_produit=' . htmlentities($salle_pop['id_produit']) . '"><i class="fas fa-search"></i> Voir le produit</a>
              </div>
          </div>
          </div>';
        echo '</div>';
    } ?>	
</div>
<?php
include 'inc/footer.inc.php';