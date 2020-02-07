<?php
include '../inc/init.inc.php';

if(!user_is_admin()){
  header("location:../connexion.php");
  exit();
}
// CODE

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
            <li class="breadcrumb-item active">Statistiques</li>
        </ol>

        <!-- Page Content -->
        <h1>Statistiques</h1>
        <hr>
        <div class="starter-template text-center">
            <p class="lead"><?php echo $msg; // variable destinée à afficher des messages utilisateurs ?></p>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="dropdown show text-center">
                    <button class="btn btn-success dropdown-toggle w-50 " type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Liste des TOP 5</button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <h6 class="dropdown-header">Les Salles</h6>
                        <a class="dropdown-item" href="?action=filtre_note_salle">Les salles les mieux notées</a>
                        <a class="dropdown-item" href="?action=filtre_commande_salle">Les salles les plus commandées</a>
                        <div class="dropdown-divider"></div>
                        <h6 class="dropdown-header">Les Membres</h6>
                        <a class="dropdown-item" href="?action=filtre_produit_commande_membre_quantite">Les membres qui achètent le plus en quantité</a>
                        <a class="dropdown-item" href="?action=filtre_produit_commande_membre_prix">Les membres qui ont la plus grosse commande en euros</a>
                        <a class="dropdown-item" href="?action=filtre_cumul_commande_membre_prix">Les membres qui ont le plus acheté en euros</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
            <?php 
            // Stats sur les salles les mieux notés
            if(isset($_GET['action']) && ($_GET['action'] == 'filtre_note_salle')) { 
                $stat_note_salle = $pdo->query("SELECT *, (sum(note)/count(note)) AS nb_de_note FROM salle, avis WHERE salle.id_salle = avis.id_salle GROUP BY titre ORDER BY nb_de_note DESC LIMIT 5");
                echo '<ul>';
                while($ligne = $stat_note_salle->fetch(PDO::FETCH_ASSOC)){ 
                    echo '<li class="list-group-item d-flex justify-content-between">Salle ' . htmlentities(ucfirst($ligne['titre'])) . ' ' . htmlentities($ligne['id_salle']) . '<span class="badge-dark badge-pill">' . htmlentities(round($ligne['nb_de_note'], 2)) . '</span></li>';
                    }
                echo '</ul>';
                }

                // Stats sur les salles les plus commandés
                elseif(isset($_GET['action']) && ($_GET['action'] == 'filtre_commande_salle')) { 
                    $stat_commande_salle = $pdo->query("SELECT *, count(salle.id_salle) AS nb_commande_salle FROM commande, produit, salle WHERE commande.id_produit = produit.id_produit AND salle.id_salle = produit.id_salle GROUP BY titre ORDER BY nb_commande_salle DESC LIMIT 5");
                    echo '<ul>';
                    while($ligne = $stat_commande_salle->fetch(PDO::FETCH_ASSOC)){ 
                        echo '<li class="list-group-item d-flex justify-content-between">Salle ' . htmlentities(ucfirst($ligne['titre'])) . ' ' . htmlentities($ligne['id_salle']) . '<span class="badge-dark badge-pill">' . htmlentities(ucfirst($ligne['nb_commande_salle'])) . '</span></li>';
                        }
                    echo '</ul>';
                    }

                // Stats sur les membres qui achètent le plus (en quantité)
                elseif(isset($_GET['action']) && ($_GET['action'] == 'filtre_produit_commande_membre_quantite')) { 
                    $stat_commande_membre = $pdo->query("SELECT *, count(id_commande) AS nb_commande_membre FROM commande, membre WHERE commande.id_membre = membre.id_membre GROUP BY pseudo ORDER BY nb_commande_membre DESC LIMIT 5");
                    echo '<ul>';
                    while($ligne = $stat_commande_membre->fetch(PDO::FETCH_ASSOC)){ 
                        echo '<li class="list-group-item d-flex justify-content-between">' . htmlentities(ucfirst($ligne['pseudo'])) . '<span class="badge-dark badge-pill">' . htmlentities(ucfirst($ligne['nb_commande_membre'])) . '</span></li>';
                        }
                    echo '</ul>';
                    }

                // Stats sur les membres qui ont la plus grosse commande
                elseif(isset($_GET['action']) && ($_GET['action'] == 'filtre_produit_commande_membre_prix')) { 
                    $stat_prix_commande_membre = $pdo->query("SELECT *, sum(prix) AS nb_prix_commande_membre FROM commande, produit, membre WHERE commande.id_membre = membre.id_membre AND commande.id_produit = produit.id_produit GROUP BY prix ORDER BY nb_prix_commande_membre DESC LIMIT 5");
                    echo '<ul>';
                    while($ligne = $stat_prix_commande_membre->fetch(PDO::FETCH_ASSOC)){ 
                        echo '<li class="list-group-item d-flex justify-content-between">' . htmlentities(ucfirst($ligne['pseudo'])) . '<span class="badge-dark badge-pill">' . htmlentities(ucfirst($ligne['nb_prix_commande_membre'])) . ' €</span></li>';
                        }
                    echo '</ul>';
                    }

                // Stats sur les membres qui ont acheté le plus (en euros)
                elseif(isset($_GET['action']) && ($_GET['action'] == 'filtre_cumul_commande_membre_prix')) { 
                    $stat_cumul_prix_commande_membre = $pdo->query("SELECT *, sum(prix) AS nb_prix_commande_membre FROM commande, produit, membre WHERE commande.id_membre = membre.id_membre AND commande.id_produit = produit.id_produit GROUP BY pseudo ORDER BY nb_prix_commande_membre DESC LIMIT 5");
                    echo '<ul>';
                    while($ligne = $stat_cumul_prix_commande_membre->fetch(PDO::FETCH_ASSOC)){ 
                        echo '<li class="list-group-item d-flex justify-content-between">' . htmlentities(ucfirst($ligne['pseudo'])) . '<span class="badge-dark badge-pill">' . htmlentities(ucfirst($ligne['nb_prix_commande_membre'])) . ' €</span></li>';
                        }
                    echo '</ul>';
                    }
            ?>
            </div>
        </div>

    </div>
</div>

<?php
include 'inc/footer_admin.inc.php';