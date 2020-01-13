<?php
include 'inc/init.inc.php';

include 'inc/header.inc.php';
include 'inc/nav.inc.php';
?>
<div class="accueil" id="fond_accueil">
<main role="main" class="container">

  <div class="starter-template text-center marge_haute">
    <h1 id="titre_accueil">Votre salle de réunion, de formation et de conférences</h1>
    <p class="para_description" >Découvrez toutes nos salles pour chacun de vos évènements d'entreprises.</p>
    <a href="location_salle.php" class="btn btn-primary">Réserver votre salle</a>
  </div>
</main><!-- /.container -->
</div>
<div class="row">
        <div class="col-12 text-center mt-3">
            <h2>Nos salles les plus populaires</h2>
        </div>
    </div>
    <div class="row justify-content-center mb-5">
    <?php 
   $liste_salles_pop = $pdo->prepare("SELECT DISTINCT *, date_format(date_arrivee, '%d/%m/%Y') AS date_arrivee, date_format(date_depart, '%d/%m/%Y') AS date_depart FROM salle, produit, avis WHERE date_arrivee > now() AND salle.id_salle = produit.id_salle AND salle.id_salle = avis.id_salle GROUP BY salle.id_salle ORDER BY note DESC LIMIT 5");
   $liste_salles_pop->execute();
    while($salle_pop = $liste_salles_pop->fetch(PDO::FETCH_ASSOC)) {
        $avis_salles = $pdo->prepare("SELECT *, (sum(note)/count(note)) AS moyenne FROM avis, salle WHERE salle.id_salle = avis.id_salle AND avis.id_salle = :id_salle");
        $avis_salles->bindParam(':id_salle', $salle_pop['id_salle'], PDO::PARAM_STR);
        $avis_salles->execute();

        $liste_avis_pop = $avis_salles->fetch(PDO::FETCH_ASSOC);

        echo '<div class="col-2">';
        echo '<div class="card mt-3">
              <img src="' . URL . $salle_pop['photo'] . '" class="card-img-top p-2" alt="' . $salle_pop['titre'] . '">
              <div class="card-body">
              <div class="row">
                  <h5 class="card-title col-8">' . ucfirst($salle_pop['titre']) . '</h5>
                  <p class="card-text col-4">' . $salle_pop['prix'] . ' €</p>
              </div>
              <p class="card-text">Située à ' . ucfirst($salle_pop['ville']) . '.</p>
              <p class="card-text">' . iconv_substr($salle_pop['description'], 0, 20) . '...</p>
              <p class="card-text"><i class="fas fa-calendar-week"></i> ' . $salle_pop['date_arrivee'] . ' au ' . $salle_pop['date_depart'] . '</p>
              <p class="card-text">' . afficheretoile(round($liste_avis_pop['moyenne'], 2)) . '</p>
              <div class="row">
                  <a class="offset-8 col-4" href="fiche_produit.php?id_produit=' . $salle_pop['id_produit'] . '" class="btn btn-info"><i class="fas fa-search"></i> Voir</a>
              </div>
          </div>
          </div>';
        echo '</div>';
    } ?>
    </div>	
</div>

<?php
include 'inc/footer.inc.php';
