<?php
include '../inc/init.inc.php';

if(!user_is_admin()){
  header("location:../connexion.php");
  exit();
}

//
// SUPPRESSION COMMANDES
//
//

if(isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id_commande']) && is_numeric($_GET['id_commande'])) {
	$suppression_salle = $pdo->prepare("DELETE FROM commande WHERE id_commande = :id_commande");
	$suppression_salle->bindParam(':id_commande', $_GET['id_commande'], PDO::PARAM_STR);
	$suppression_salle->execute();
	$msg .= '<div class="alert alert-success">La commande n°' . $_GET['id_commande'] . ' a bien été supprimé</div>';
	$_GET['action'] = 'affichage';
}

include 'inc/header_admin.inc.php';
include 'inc/nav_admin.inc.php';
?>

<div id="content-wrapper">

    <div class="container-fluid">

      <!-- Breadcrumbs-->
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="#">Dashboard</a>
        </li>
        <li class="breadcrumb-item active">Gestion Commandes</li>
      </ol>

      <!-- Page Content -->
      <h1>Gestion des Commandes</h1>
      <hr>
      <div class="starter-template text-center">
          <p class="lead"><?php echo $msg; // variable destinée à afficher des messages utilisateurs ?></p>
      </div>
      <?php 

    $liste_commande = $pdo->query("SELECT * FROM commande ORDER BY id_commande");
      $commande_produit_membre = $pdo->query("SELECT *, date_format(date_arrivee, '%d/%m/%Y') AS date_arrivee, date_format(date_depart, '%d/%m/%Y') AS date_depart FROM commande, produit, membre, salle WHERE commande.id_produit = produit.id_produit AND commande.id_membre = membre.id_membre AND salle.id_salle = produit.id_salle");
      echo '<div class="row">';
    echo '<div class="col-12">';

    echo '<p>Nombre total de produits : ' . $liste_commande->rowCount() . '.</p>';

    echo '<table class="table table-bordered">';
    echo '<tr>';
    echo '<th class="text-center">id_commande</th><th class="text-center">id_membre</th><th class="text-center">id_produit</th><th class="text-center">prix</th><th class="text-center">date_enregistrement</th><th class="text-center">Action</th>';

    // une boucle pour afficher les salles dans le tableau
    while($ligne = $commande_produit_membre->fetch(PDO::FETCH_ASSOC)){
      echo '<tr>';
          echo '<td>' . $ligne['id_commande'] . '</td>';
          echo '<td>' . $ligne['id_membre'] . ' - ' . $ligne['email']. '</td>';
          echo '<td>' . $ligne['id_salle'] . ' - Salle ' . $ligne['titre'] . ' : ' . $ligne['date_arrivee'] . ' au ' . $ligne['date_depart'] . '</td>';
          echo '<td>' . $ligne['prix'] . '</td>';
          echo '<td>' . $ligne['date_enregistrement'] . '</td>';
      echo '<td><a href="?action=supprimer&id_commande=' . $ligne['id_commande'] . '" class="btn" onclick="return(confirm(\'Etes-vous sur ?\'))" title="Supprimer"><i class="fas fa-trash-alt"></i></td>';

      echo '</tr>';
    }

    echo '</tr>';
    echo '</table>';
    echo '</div>';
    echo '</div>';
  ?>

    </div>
</div>

<?php
include 'inc/footer_admin.inc.php';