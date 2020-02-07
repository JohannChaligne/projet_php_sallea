<?php
include '../inc/init.inc.php';

if(!user_is_admin()){
    header("location:../connexion.php");
    exit();
}


// CODE
$id_produit = '';
$date_arrivee = '';
$date_depart = '';
$salle = '';
$prix = '';
$etat = '';

//
//
// MODIFICATION PRODUITS
//
//
if(isset($_GET['action']) && $_GET['action'] == 'modifier' && isset($_GET['id_produit']) && is_numeric($_GET['id_produit'])) {
	// une requête pour récupérer les informations en BDD
	$recup_infos = $pdo->prepare("SELECT * FROM produit WHERE id_produit = :id_produit");
	$recup_infos->bindParam(":id_produit", $_GET['id_produit'], PDO::PARAM_STR);
	$recup_infos->execute();
	
	$infos_produit = $recup_infos->fetch(PDO::FETCH_ASSOC); 
    
    $id_produit = $infos_produit['id_produit']; 
	$date_arrivee = $infos_produit['date_arrivee']; 
    $date_depart = $infos_produit['date_depart'];
    $salle = $infos_produit['id_salle'];
    $prix = $infos_produit['prix'];
}


//
//
// SUPRESSION PRODUITS
//
//

if(isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id_produit']) && is_numeric($_GET['id_produit'])) {
	$suppression_salle = $pdo->prepare("DELETE FROM produit WHERE id_produit = :id_produit");
	$suppression_salle->bindParam(':id_produit', $_GET['id_produit'], PDO::PARAM_STR);
	$suppression_salle->execute();
	$msg .= '<div class="alert alert-success">Le produit n°' . $_GET['id_produit'] . ' a bien été supprimé</div>';
	$_GET['action'] = 'affichage';
}


//
//
// ENREGISTREMENT PRODUITS
//
//

if(
	isset($_POST['date_arrivee']) &&
	isset($_POST['date_depart']) &&
	isset($_POST['salle']) &&
	isset($_POST['prix'])) {

	// récupération de l'id_produit dans le cadre de la modification
	if(!empty($_POST['id_produit'])){
		$id_produit = htmlentities(trim($_POST['id_produit']));
	}
    
    
    $date_arrivee = htmlentities(date('Y/m/d', strtotime($_POST['date_arrivee'])));
    $date_depart = htmlentities(date('Y/m/d', strtotime($_POST['date_depart'])));
	$salle = explode(' -', $_POST['salle']);
    $prix = htmlentities(trim($_POST['prix']));

    // Conditions pour les valeurs des dates soient remplies
    if(empty($date_arrivee) && empty($date_depart)){
        $msg .= '<div class="alert alert-danger"> La date d\'arrivée et la date de départ doivent être complétées.</div>';
    }
    
    
    // Comparaison date d'arrivée à la date de départ
    if(strtotime($date_arrivee) > strtotime($date_depart)){
        $msg .= '<div class="alert alert-danger"> La date d\'arrivée doit être plus ancienne que la date de départ</div>';
        }
    
    // Condition pour des valeurs numériques dans Prix
    if(!is_numeric($prix)) {
        $msg .= '<div class="alert alert-danger"> Le prix doit être numérique</div>';
    }

    if(empty($msg)){
		if(empty($id_produit)) {
		// ENREGISTREMENT D'UN PRODUIT
            $enregistrement_produit = $pdo->prepare("INSERT INTO produit (id_salle, date_arrivee, date_depart, prix, etat) VALUES (:id_salle, :date_arrivee, :date_depart, :prix, 'libre')");
                //Contrôle de la salle pour affichage
		} else {
			// UPDATE => MODIFICATION D'UN PRODUIT
			$enregistrement_produit = $pdo->prepare("UPDATE produit SET id_salle = :id_salle, date_arrivee = :date_arrivee, date_depart = :date_depart, prix = :prix WHERE id_produit = :id_produit");
			$enregistrement_produit->bindParam(':id_produit', $id_produit, PDO::PARAM_STR); 
        }
        
		$enregistrement_produit->bindParam(':id_salle', $salle[0], PDO::PARAM_STR);
		$enregistrement_produit->bindParam(':date_arrivee', $date_arrivee, PDO::PARAM_STR);
        $enregistrement_produit->bindParam(':date_depart', $date_depart, PDO::PARAM_STR);			
        $enregistrement_produit->bindParam(':prix', $prix, PDO::PARAM_STR);
        $enregistrement_produit->execute();
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
            <li class="breadcrumb-item active">Gestion Produits</li>
        </ol>

        <!-- Page Content -->
        <h1>Gestion des produits</h1>
        <hr>
        <div class="starter-template text-center">
            <p class="lead"><?php echo $msg; // variable destinée à afficher des messages utilisateurs ?></p>
            <a href="?action=enregistrement" class="btn btn-outline-primary">Enregistrement des produits</a>
            <a href="?action=affichage" class="btn btn-outline-danger">Affichage des produits</a>
        </div>

        <?php if(isset($_GET['action']) && ($_GET['action'] == 'enregistrement' || $_GET['action'] == 'modifier')) { ?>

        <div class="row">
        <div class="col-8 mx-auto">
            <form method="post" action="" enctype="multipart/form-data">
                <input type="hidden" name="id_produit" readonly value="<?php echo $id_produit; ?>"> 
                <div class="from-group">
                    <label for="date_arrivee">Date d'arrivée</label>
                    <input type="text" class="datepick form-control" name="date_arrivee" id="date_arrivee" value="<?php echo $date_arrivee; ?>">
                </div>
                <div class="form-group">
                    <label for="date_depart">Date de départ</label>
                    <input type="text" class="datepick form-control" name="date_depart" id="date_depart" value="<?php echo $date_depart; ?>">
                </div>
                <div class="from-group">
                    <label for="salle">Salle</label>
                    <select class="form-control" name="salle" id="salle">
                        <?php 
                        $affichage_salle = $pdo->query("SELECT * FROM salle ORDER BY ville");

                        while($ligne = $affichage_salle->fetch(PDO::FETCH_ASSOC)){
                            echo '<option ';
                            if($salle == $ligne['id_salle']){ echo 'selected'; }
                            echo ' >' . htmlentities($ligne['id_salle']) . ' - ' . htmlentities($ligne['titre']) . ' - ' . htmlentities($ligne['adresse']) . ', ' . htmlentities($ligne['cp']) . ', ' . htmlentities($ligne['ville']) . ' - ' . htmlentities($ligne['capacite']) . ' personnes</option>';
                        } 
                        ?>
                    </select>  
                </div>
                <div class="from-group">
                    <label for="prix">Tarif</label>
                    <input type="text" class="form-control" name="prix" id="prix" placeholder="prix en €uros" value="<?php echo $prix; ?>">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary w-100 mt-2"><i class="fas fa-pen-alt"></i> Enregistrement</button>
                </div>
            </form>
        </div>
        </div>
    <?php } 

        if(isset($_GET['action']) && $_GET['action'] == 'affichage') {
        $liste_produit = $pdo->query("SELECT * FROM produit ORDER BY id_produit");
        $recup_tab_salle = $pdo->query("SELECT *, date_format(date_arrivee, '%d/%m/%Y') AS date_arrivee, date_format(date_depart, '%d/%m/%Y') AS date_depart FROM salle, produit WHERE salle.id_salle = produit.id_salle");
        echo '<div class="row">';
        echo '<div class="col-12">';

        echo '<p>Nombre total de produits : ' . $liste_produit->rowCount() . '.</p>';

        echo '<table class="table table-bordered">';
        echo '<tr>';
        echo '<th class="text-center">N°</th><th class="text-center">Date arrivée</th><th class="text-center">Date départ</th><th class="text-center">id_salle</th><th class="text-center">Prix</th><th class="text-center">Etat</th><th class="text-center">Action</th>';

        // une boucle pour afficher les salles dans le tableau
        while($ligne = $recup_tab_salle->fetch(PDO::FETCH_ASSOC)){
            echo '<tr>';
            echo '<td>' . htmlentities($ligne['id_produit']) . '</td>';
            echo '<td>' . htmlentities($ligne['date_arrivee']) . '</td>';
            echo '<td>' . htmlentities($ligne['date_depart']) . '</td>';
            echo '<td>' . htmlentities($ligne['id_salle']) . ' - ' . htmlentities($ligne['titre']) . ' - <img src="' . URL . htmlentities($ligne['photo']) . '" class="img-thumbnail" width="100"></td>';
            echo '<td>' . htmlentities($ligne['prix']) . '</td>';
            echo '<td>' . htmlentities($ligne['etat']) . '</td>';
            echo '<td><a href="?action=modifier&id_produit=' . htmlentities($ligne['id_produit']) . '" class="btn" title="Modifier"><i class="fas fa-edit"></i></a><a href="?action=supprimer&id_produit=' . htmlentities($ligne['id_produit']) . '" class="btn" onclick="return(confirm(\'Etes-vous sur ?\'))" title="Supprimer"><i class="fas fa-trash-alt"></i></td>';
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