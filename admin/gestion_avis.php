<?php
include '../inc/init.inc.php';

if(!user_is_admin()){
    header("location:../connexion.php");
    exit();
}

// CODE
$id_avis = '';
$commentaire = '';
$note = '';
$membre = '';
$salle = '';

//
//
// MODIFICATION AVIS
//
//
if(isset($_GET['action']) && $_GET['action'] == 'modifier' && isset($_GET['id_avis']) && is_numeric($_GET['id_avis'])) {
	// une requête pour récupérer les informations en BDD
	$recup_infos = $pdo->prepare("SELECT * FROM avis WHERE id_avis = :id_avis");
	$recup_infos->bindParam(":id_avis", $_GET['id_avis'], PDO::PARAM_STR);
	$recup_infos->execute();
	
	$infos_avis = $recup_infos->fetch(PDO::FETCH_ASSOC); 
    
    $id_avis = $infos_avis['id_avis']; // pour la partie "modification"
    $commentaire = $infos_avis['commentaire'];
    $note = $infos_avis['note'];

}


//
//
// SUPRESSION AVIS
//
//

if(isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id_avis']) && is_numeric($_GET['id_avis'])) {
	$suppression_avis = $pdo->prepare("DELETE FROM avis WHERE id_avis = :id_avis");
	$suppression_avis->bindParam(':id_avis', $_GET['id_avis'], PDO::PARAM_STR);
	$suppression_avis->execute();
	$msg .= '<div class="alert alert-success">L\'avis n°' . $_GET['id_avis'] . ' a bien été supprimé</div>';
	$_GET['action'] = 'affichage';
}


//
//
// ENREGISTREMENT AVIS
//
//
if(
    isset($_POST['membre']) &&
    isset($_POST['salle']) &&
    isset($_POST['commentaire']) &&
    isset($_POST['note'])) {
      
        if(!empty($_POST['id_avis'])){
            $id_avis = htmlentities(trim($_POST['id_avis']));
        }

        $membre = explode(' -', $_POST['membre']);
        $salle = explode(' -', $_POST['salle']);
        $commentaire = htmlentities(trim($_POST['commentaire']));
        $note = htmlentities(trim($_POST['note']));

        // Vérificiation de Note pour qu'il ne soit pas vide
        if(empty($note)){
            $msg .= '<div class="alert alert-danger"> ATTENTION,<br> Une note doit être obligatoirement saisie.</div>';
        }
        if(empty($msg)){
            if(empty($id_avis)) {
                
                // ENREGISTREMENT D'UN AVIS
                $enregistrement_avis = $pdo->prepare("INSERT INTO avis (id_membre, id_salle, commentaire, note, date_enregistrement) VALUES (:id_membre, :id_salle, :commentaire, :note, NOW())");
                }
                else {
                // UPDATE => MODIFICATION D'UN AVIS
                $enregistrement_avis = $pdo->prepare("UPDATE avis SET id_membre = :id_membre, id_salle = :id_salle, commentaire = :commentaire, note = :note, date_enregistrement = NOW() WHERE id_avis = :id_avis");
                $enregistrement_avis->bindParam(':id_avis', $id_avis, PDO::PARAM_STR); 
            }

            $enregistrement_avis->bindParam(':id_membre', $membre[0], PDO::PARAM_STR);
            $enregistrement_avis->bindParam(':id_salle', $salle[0], PDO::PARAM_STR);
            $enregistrement_avis->bindParam(':commentaire', $commentaire, PDO::PARAM_STR);
            $enregistrement_avis->bindParam(':note', $note, PDO::PARAM_STR);
            $enregistrement_avis->execute();
            $_GET['action'] = 'affichage'; // on force l'affichage du tableau
        }
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
            <li class="breadcrumb-item active">Gestion Avis</li>
        </ol>

        <!-- Page Content -->
        <h1>Gestion des Avis</h1>
        <hr>
        <div class="starter-template text-center">
            <p class="lead"><?php echo $msg; // variable destinée à afficher des messages utilisateurs ?></p>
            <a href="?action=enregistrement" class="btn btn-outline-primary">Enregistrement avis</a>
            <a href="?action=affichage" class="btn btn-outline-danger">Affichage des avis</a>
        </div>
        <?php if(isset($_GET['action']) && ($_GET['action'] == 'enregistrement' || $_GET['action'] == 'modifier')) { // Modification des avis?>
    </div>
    <div class="row">
        <div class="col-8 mx-auto">
            <form method="post" action="" enctype="multipart/form-data">
                <input type="hidden" name="id_avis" readonly value="<?php echo $id_avis; ?>"> 
                <div class="form-group">
                    <label for="membre">Membre</label>
                    <select class="form-control" name="membre" id="membre">
                        <?php 
                        $affichage_membre = $pdo->query("SELECT * FROM membre ORDER BY id_membre");

                        while($membre = $affichage_membre->fetch(PDO::FETCH_ASSOC)){
                            echo '<option value="' . $membre['id_membre'] . '"> ' . $membre['id_membre'] . ' - ' . $membre['email'] . '</option>';
                        } 
                        ?>
                    </select>  
                </div>
                <div class="form-group">
                    <label for="salle">Salle</label>
                    <select class="form-control" name="salle" id="salle">
                        <?php 
                        $affichage_salle = $pdo->query("SELECT * FROM salle ORDER BY ville");

                        while($salle = $affichage_salle->fetch(PDO::FETCH_ASSOC)){
                            echo '<option value="' . $salle['id_salle'] . '"> ' . $salle['id_salle'] . ' - ' . $salle['titre'] . '</option>';
                        } 
                        ?>
                    </select>  
                </div>
                <div class="form-group">
                    <label for="commentaire">Commentaire</label>
                    <textarea type="text" name="commentaire" id="commentaire" class="form-control"><?php echo $commentaire; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="note">Note</label>
                    <select name="note" id="note" class="w-100">
                        <option value="1">1 / 5 étoiles</option>
                        <option value="2" <?php if($note == '2'){ echo 'selected'; } ?> >2 / 5 étoiles</option>
                        <option value="3" <?php if($note == '3'){ echo 'selected'; } ?> >3 / 5 étoiles</option>
                        <option value="4" <?php if($note == '4'){ echo 'selected'; } ?> >4 / 5 étoiles</option>
                        <option value="5" <?php if($note == '5'){ echo 'selected'; } ?> >5 / 5 étoiles</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary w-100">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
    <?php } 
    // Affichage des avis
    if(isset($_GET['action']) && $_GET['action'] == 'affichage') {
    $liste_avis = $pdo->query("SELECT * FROM avis ORDER BY note");
    $recup_tab_salle_et_membre = $pdo->query("SELECT *, date_format(avis.date_enregistrement, '%d/%m/%Y %H:%i:%s') AS date_enregistrement_avis FROM avis, salle, membre WHERE avis.id_salle = salle.id_salle AND avis.id_membre = membre.id_membre");
    echo '<div class="row">';
    echo '<div class="col-12">';

    echo '<p>Nombre total des avis : ' . $liste_avis->rowCount() . '.</p>';

    echo '<table class="table table-bordered">';
    echo '<tr>';
    echo '<th class="text-center">id_avis</th><th class="text-center">id_membre</th><th class="text-center">id_salle</th><th class="text-center">Commentaires</th><th class="text-center">Note</th><th class="text-center">Date_enregistrement</th><th class="text-center">Action</th>';

    // une boucle pour afficher les salles dans le tableau
    while($ligne = $recup_tab_salle_et_membre->fetch(PDO::FETCH_ASSOC)){
        echo '<tr>';
        echo '<td>' . htmlentities($ligne['id_avis']) . '</td>';
        echo '<td>' . htmlentities($ligne['id_membre']) . ' - ' . htmlentities($ligne['email']) . '</td>';
        echo '<td>' . htmlentities($ligne['id_salle']) . ' - ' . htmlentities($ligne['titre']) . '</td>';
        echo '<td>' . htmlentities($ligne['commentaire']) . '</td>';
        echo '<td>' . htmlentities($ligne['note']) . ' / 5 étoiles</td>';
        echo '<td>' . htmlentities($ligne['date_enregistrement_avis']) . '</td>';
        echo '<td><a href="?action=modifier&id_avis=' . htmlentities($ligne['id_avis']) . '" class="btn" title="Modifier"><i class="fas fa-edit"></i></a><a href="?action=supprimer&id_avis=' . htmlentities($ligne['id_avis']) . '" class="btn" onclick="return(confirm(\'Etes-vous sur ?\'))" title="Supprimer"><i class="fas fa-trash-alt"></i></td>';

        echo '</tr>';
    }

    echo '</tr>';
    echo '</table>';
    echo '</div>';
    echo '</div>';
    }

    ?>
    </div>
</div>

<?php
include 'inc/footer_admin.inc.php';