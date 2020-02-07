<?php
include '../inc/init.inc.php';

if(!user_is_admin()) { 
	header("location:../connexion.php");
    exit();
}
// CODE

$id_salle= ''; // pour la partie "modification"
$photo_actuelle = ''; // pour la partie "modification"

$titre = '';
$description = '';
$photo = '';
$pays = '';
$ville = '';
$adresse = '';
$cp = '';
$capacite = '';
$categorie = '';
$localisation = '';

//
//
// MODIFICATION SALLES
//
//
if(isset($_GET['action']) && $_GET['action'] == 'modifier' && isset($_GET['id_salle']) && is_numeric($_GET['id_salle'])) {
	// une requête pour récupérer les informations en BDD
	$recup_infos = $pdo->prepare("SELECT * FROM salle WHERE id_salle = :id_salle");
	$recup_infos->bindParam(":id_salle", $_GET['id_salle'], PDO::PARAM_STR);
	$recup_infos->execute();
	
	$infos_salle = $recup_infos->fetch(PDO::FETCH_ASSOC); 
    
    $id_salle = $infos_salle['id_salle']; // pour la partie "modification"
	$photo_actuelle = $infos_salle['photo']; // pour la partie "modification"
    $titre = $infos_salle['titre'];
    $description = $infos_salle['description'];
    $pays = $infos_salle['pays'];
    $ville = $infos_salle['ville'];
    $adresse = $infos_salle['adresse'];
    $cp = $infos_salle['cp'];
    $capacite = $infos_salle['capacite'];
	$categorie = $infos_salle['categorie'];
	$localisation = $infos_salle['localisation'];
}


//
//
// SUPRESSION SALLES
//
//

if(isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id_salle']) && is_numeric($_GET['id_salle'])) {
	$suppression_salle = $pdo->prepare("DELETE FROM salle WHERE id_salle = :id_salle");
	$suppression_salle->bindParam(':id_salle', $_GET['id_salle'], PDO::PARAM_STR);
	$suppression_salle->execute();
	$msg .= '<div class="alert alert-success">La salle n°' . $_GET['id_salle'] . ' a bien été supprimé</div>';
	$_GET['action'] = 'affichage';
}


//
//
// ENREGISTREMENT SALLES
//
//

if(
	isset($_POST['titre']) &&
	isset($_POST['description']) &&
	isset($_POST['pays']) &&
	isset($_POST['ville']) &&
	isset($_POST['adresse']) &&
	isset($_POST['cp']) &&
	isset($_POST['capacite']) &&
	isset($_POST['categorie']) &&
	isset($_POST['localisation'])) {

	// récupération de l'id_salle dans le cadre de la modification
	if(!empty($_POST['id_salle'])){
		$id_salle = htmlentities(trim($_POST['id_salle']));
	}

	//Récupération de la photo actuelle dans le cadre de la modif
	if(!empty($_POST['photo_actuelle'])){
		$photo = htmlentities(trim($_POST['photo_actuelle']));
    }
    
	$titre = htmlentities(trim($_POST['titre']));
	$description = htmlentities(trim($_POST['description']));
	$pays = htmlentities(trim($_POST['pays']));
	$ville = htmlentities(trim($_POST['ville']));
	$adresse = htmlentities(trim($_POST['adresse']));
	$cp = htmlentities(trim($_POST['cp']));
	$capacite = htmlentities(trim($_POST['capacite']));
	$categorie = htmlentities(trim($_POST['categorie']));
	$localisation = htmlentities(trim($_POST['localisation']));

    // Conditions pour que le titre, l'adresse et la localisation soient complétés obligatoirement
    if(empty($titre)){
        $msg .= '<div class="alert alert-danger"> Le titre doit être complété.</div>';
    }
    if(empty($adresse)){
        $msg .= '<div class="alert alert-danger"> L\'adresse doit être complétée.</div>';
	}
	if(empty($localisation)){
        $msg .= '<div class="alert alert-danger"> La localisation doit être complétée.</div>';
    }

    // Condition pour des valeurs numériques dans Code Postal et Capacité
    if(!is_numeric($cp)) {
		$msg .= '<div class="alert alert-danger"> Le code postal doit être numérique</div>';
	}

	if(!is_numeric($capacite)) {
		$msg .= '<div class="alert alert-danger"> La capacite doit être numérique</div>';
	}


    // Controle sur la photo si elle n'est pas vide.
	if(!empty($_FILES['photo']['name'])) {
		$tab_extension = array('png', 'jpg', 'jpeg', 'gif');
		$extension = strrchr($_FILES['photo']['name'], '.');
		$extension = strtolower(substr($extension, 1));
		$verif_photo = in_array($extension, $tab_extension);

		if(!$verif_photo) {
			$msg .= '<div class="alert alert-danger"> Photo invalide, format acceptés : png, jpg, jpeg, gif</div>';
        } 
        else {
		    $nom_photo = $id_salle . '-' . $_FILES['photo']['name'];
			$photo = 'photo/' . $nom_photo;
			$chemin_dossier = SERVER_ROOT . ROOT_URL . $photo;
			copy($_FILES['photo']['tmp_name'], $chemin_dossier);
		}
	} // Est-ce qu'une photo a été chargée

	if(empty($msg)){
		if(empty($id_salle)) {
		// ENREGISTREMENT D'UNE SALLE
			$enregistrement_salle = $pdo->prepare("INSERT INTO salle (titre, description, photo, pays, ville, adresse, cp, capacite, categorie, localisation) VALUES (:titre, :description, :photo, :pays, :ville, :adresse, :cp, :capacite, :categorie, :localisation)");
		}
		else {
			// UPDATE => MODIFICATION D'UNE SALLE
			$enregistrement_salle = $pdo->prepare("UPDATE salle SET titre = :titre, description = :description, photo = :photo, pays = :pays, ville = :ville, adresse = :adresse, cp = :cp, capacite = :capacite, categorie = :categorie, localisation = :localisation WHERE id_salle = :id_salle");
			$enregistrement_salle->bindParam(':id_salle', $id_salle, PDO::PARAM_STR); 
		}
			
		$enregistrement_salle->bindParam(':titre', $titre, PDO::PARAM_STR);
		$enregistrement_salle->bindParam(':description', $description, PDO::PARAM_STR);
		$enregistrement_salle->bindParam(':photo', $photo, PDO::PARAM_STR);			
		$enregistrement_salle->bindParam(':pays', $pays, PDO::PARAM_STR);
		$enregistrement_salle->bindParam(':ville', $ville, PDO::PARAM_STR);
		$enregistrement_salle->bindParam(':adresse', $adresse, PDO::PARAM_STR);
		$enregistrement_salle->bindParam(':cp', $cp, PDO::PARAM_STR);			
		$enregistrement_salle->bindParam(':capacite', $capacite, PDO::PARAM_STR);
		$enregistrement_salle->bindParam(':categorie', $categorie, PDO::PARAM_STR);
		$enregistrement_salle->bindParam(':localisation', $localisation, PDO::PARAM_STR);
        $enregistrement_salle->execute();
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
			<li class="breadcrumb-item active">Gestion salle</li>
		</ol>

		<!-- Page Content -->
		<h1>Gestion des salles</h1>
		<hr>
		<div class="starter-template text-center">
			<p class="lead"><?php echo $msg; // variable destinée à afficher des messages utilisateurs ?></p>
			<a href="?action=enregistrement" class="btn btn-outline-primary">Enregistrement des salles</a>
			<a href="?action=affichage" class="btn btn-outline-danger">Affichage des salles</a>
		</div>

		<?php if(isset($_GET['action']) && ($_GET['action'] == 'enregistrement' || $_GET['action'] == 'modifier')) { ?>

		<div class="row">
		<div class="col-8 mx-auto">
			<form method="post" action="" enctype="multipart/form-data">
				<input type="hidden" name="id_salle" readonly value="<?php echo $id_salle; ?>"> 
				<div class="from-group">
					<label for="titre">Titre</label>
					<input type="text" class="form-control" name="titre" id="titre" value="<?php echo $titre; ?>">
				</div>
				<div class="form-group">
					<label for="description">Description</label>
					<textarea name="description" id="description" class="form-control"><?php echo $description ?></textarea> 
				</div>
				<?php
				if(!empty($photo_actuelle)) {
					// si $photo_actuelle n'est pas vide, on est dans la modif et une photo existe pour le produit à modifier
					echo '<div class="form-group"><label>Photo actuelle</label>';
					echo '<input type="hidden" name="photo_actuelle" value="' . $photo_actuelle .'">';
					echo '<img src="' . URL . $photo_actuelle . '" class="img-thumbnail w-25">';
					echo '</div>';
				}

				?>
				<div class="from-group">
					<label for="photo">Photo</label>
					<input type="file" class="form-control" name="photo" id="photo">
				</div>
				<div class="from-group">
					<label for="pays">Pays</label>
					<select class="form-control" name="pays" id="pays">
						<option>France</option>
					</select>  
				</div>
				<div class="from-group">
					<label for="ville">Ville</label>
					<select class="form-control" name="ville" id="ville">
						<option>Paris</option>
						<option <?php if($ville == 'Lyon') {echo 'selected';} ?> >Lyon</option>
						<option <?php if($ville == 'Marseille') {echo 'selected';} ?> >Marseille</option>
					</select>  
				</div>
				<div class="from-group">
					<label for="adresse">Adresse</label>
					<input type="text" class="form-control" name="adresse" id="adresse" value="<?php echo $adresse; ?>">
				</div>
				<div class="from-group">
					<label for="cp">Code Postal</label>
					<input type="text" class="form-control" name="cp" id="cp" value="<?php echo $cp; ?>">
				</div>
				<div class="from-group">
					<label for="capacite">Capacité</label>
					<input type="text" class="form-control" name="capacite" id="capacite" value="<?php echo $capacite; ?>">
				</div>
				<div class="from-group">
					<label for="categorie">Categorie</label>
					<select class="form-control" name="categorie" id="categorie">
						<option>Réunion</option>
						<option <?php if($categorie == 'bureau') {echo 'selected';} ?> >Bureau</option>
						<option <?php if($categorie == 'formation') {echo 'selected';} ?> >Formation</option>
					</select>  
				</div>
				<div class="from-group">
					<label for="localisation">Localisation</label>
					<input type="text" class="form-control" name="localisation" id="localisation" value="<?php echo $localisation; ?>">
				</div>
				<div class="form-group">
					<button type="submit" class="btn btn-primary w-100 mt-2"><i class="fas fa-pen-alt"></i> Enregistrement</button>
				</div>
			</form>
		</div>
		</div>

		<?php } 

		if(isset($_GET['action']) && $_GET['action'] == 'affichage') {
		$liste_salle = $pdo->query("SELECT * FROM salle ORDER BY categorie");
		echo '<div class="row">';
		echo '<div class="col-10">';

		echo '<p>Nombre total de salles : ' . $liste_salle->rowCount() . '.</p>';

		echo '<table class="table table-bordered">';
		echo '<tr>';
		echo '<th class="text-center">N°</th><th class="text-center">Titre</th><th class="text-center">Description</th><th class="text-center">Photo</th><th class="text-center">Pays</th><th class="text-center">Ville</th><th class="text-center">Adresse</th><th class="text-center">Code Postal</th><th class="text-center">Capacité</th><th class="text-center">Catégorie</th><th class="text-center">Localisation</th><th class="text-center">Action</th>';

		// une boucle pour afficher les salles dans le tableau
		while($ligne = $liste_salle->fetch(PDO::FETCH_ASSOC)){
			echo '<tr>';
			echo '<td>' . htmlentities($ligne['id_salle']) . '</td>';
			echo '<td>' . htmlentities($ligne['titre']) . '</td>';
			echo '<td>' . htmlentities(iconv_substr($ligne['description'], 0 , 25)) . '...</td>';
			echo '<td><img src="' . URL . htmlentities($ligne['photo']) . '" class="img-thumbnail" width="100"></td>'; 
			echo '<td>' . htmlentities($ligne['pays']) . '</td>';
			echo '<td>' . htmlentities($ligne['ville']) . '</td>';
			echo '<td>' . htmlentities($ligne['adresse']) . '</td>';
			echo '<td>' . htmlentities($ligne['cp']) . '</td>';
			echo '<td>' . htmlentities($ligne['capacite']) . '</td>';
			echo '<td>' . htmlentities($ligne['categorie']) . '</td>';
			echo '<td>' . htmlentities(iconv_substr($ligne['localisation'], 0 , 5)) . '...</td>';
			echo '<td><a href="?action=modifier&id_salle=' . htmlentities($ligne['id_salle']) . '" class="btn" title="Modifier"><i class="fas fa-edit"></i></a><a href="?action=supprimer&id_salle=' . htmlentities($ligne['id_salle']) . '" class="btn" onclick="return(confirm(\'Etes-vous sur ?\'))" title="Supprimer"><i class="fas fa-trash-alt"></i></td>';

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