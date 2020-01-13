<?php
include 'inc/init.inc.php';

// Restriction d'accès pour les non-utilisateurs
if(!user_is_connect()) {
	session_destroy();
	header("location:connexion.php");
}

// CODE
if(isset($_POST['confirm_update'])){
	//Récupération de l'avatar actuel dans le cadre de la modif
	
	if(
		isset($_POST['pseudo']) &&
		isset($_POST['email']) &&
		isset($_POST['nom']) &&
		isset($_POST['prenom']) &&
		isset($_POST['civilite'])){
		if($_POST['pseudo'] != $_SESSION['membre']['pseudo'] || $_POST['email'] != $_SESSION['membre']['email'] || $_POST['nom'] != $_SESSION['membre']['nom'] || $_POST['prenom'] != $_SESSION['membre']['prenom'] || $_POST['civilite'] != $_SESSION['membre']['civilite']) {
			$nouveau_pseudo = strip_tags(trim($_POST['pseudo']));
			$nouveau_email = strip_tags(trim($_POST['email']));
			$nouveau_nom = strip_tags(trim($_POST['nom']));
			$nouveau_prenom = strip_tags(trim($_POST['prenom']));
			$nouveau_civilite = strip_tags(trim($_POST['civilite']));
			if(iconv_strlen($nouveau_pseudo) < 4 || iconv_strlen($nouveau_pseudo) > 20){
				$msg .= '<div class="alert alert-danger"> ATTENTION,<br> Le pseudo doit avoir entre 4 et 20 caractères</div>';
			}

			// Vérification des caractères du pseudo
			$verif_pseudo = preg_match('#^[a-zA-Z0-9_]+$#', $nouveau_pseudo);
			if(!$verif_pseudo){
				// Message en cas d'erreurs
				$msg .= '<div class="alert alert-danger"> ATTENTION,<br> Le pseudo n\'est pas valide. Caractères autorisés : a - z, A - Z et 0 - 9.</div>';
			}

			// Vérification de la validité de l'email
			if(!filter_var($nouveau_email, FILTER_VALIDATE_EMAIL)) {
				$msg .= '<div class="alert alert-danger"> ATTENTION,<br> L\'email n\'est pas valide. Veuillez vérifier votre saisie.</div>';
			}

			// Contrôle de la taille du nom et du prénom
			if(iconv_strlen($nouveau_nom) < 1 && iconv_strlen($nouveau_prenom) < 1){
				$msg .= '<div class="alert alert-danger"> ATTENTION,<br> Vous devez obligatoirement remplir le champ "Nom" et "Prénom".</div>'; 
			}
	
			// Vérification de la validité du prénom
			$verif_nom = preg_match('#^[a-zA-Z_]+$#', $nouveau_nom);
			$verif_prenom = preg_match('#^[a-zA-Z_]+$#', $nouveau_prenom);
			if(!$verif_nom){
				$msg .= '<div class="alert alert-danger"> ATTENTION,<br> Le nom ne doit pas comporté de caractères speciaux ni de chiffres. Caractères autorisés : a - z et A - Z.</div>';
			}
			if(!$verif_prenom){
				$msg .= '<div class="alert alert-danger"> ATTENTION,<br> Le prénom ne doit pas comporté de caractères speciaux ni de chiffres. Caractères autorisés : a - z et A - Z.</div>';
			}
			
			else {
				$req = $pdo->prepare("UPDATE membre SET pseudo = :pseudo, email = :email, nom = :nom, prenom = :prenom, civilite = :civilite WHERE id_membre = :id_membre");
				$req->bindParam(':id_membre', $_SESSION['membre']['id_membre'], PDO::PARAM_STR);
				$req->bindParam(':pseudo', $nouveau_pseudo, PDO::PARAM_STR);
				$req->bindParam(':email', $nouveau_email, PDO::PARAM_STR);
				$req->bindParam(':nom', $nouveau_nom, PDO::PARAM_STR);
				$req->bindParam(':prenom', $nouveau_prenom, PDO::PARAM_STR);
				$req->bindParam(':civilite', $nouveau_civilite, PDO::PARAM_STR);
				$req->execute();
				$msg .= '<div class="alert alert-success"> Les données ont bien été mises à jour.</div>';
				$_SESSION['membre']['pseudo'] = $nouveau_pseudo;
				$_SESSION['membre']['email'] = $nouveau_email;
				$_SESSION['membre']['nom'] = $nouveau_nom;
				$_SESSION['membre']['prenom'] = $nouveau_prenom;
				$_SESSION['membre']['civilite'] = $nouveau_civilite;
			}
		}
	}

	// Controle sur l'avatar s'il n'est pas vide.
	if(isset($_FILES['avatar']) && !empty($_FILES['avatar']['name'])) {
		$tab_extension = array('png', 'jpg', 'jpeg', 'gif');
		$extension = strrchr($_FILES['avatar']['name'], '.');
		$extension = strtolower(substr($extension, 1));
		$verif_avatar = in_array($extension, $tab_extension);

		if(!$verif_avatar) {
			$msg .= '<div class="alert alert-danger"> Image invalide, format acceptés : png, jpg, jpeg, gif</div>';
		} 
		else {
			$nom_avatar = $_SESSION['membre']['id_membre'] . '-' . $_FILES['avatar']['name'];
			$nouvel_avatar = 'avatar_users/' . $nom_avatar;
			$chemin_dossier = SERVER_ROOT . ROOT_URL . $nouvel_avatar;
			$resultat = copy($_FILES['avatar']['tmp_name'], $chemin_dossier);
			if($resultat){
				$updateavatar = $pdo->prepare("UPDATE membre SET avatar = :avatar WHERE id_membre = :id_membre");
				$updateavatar->bindParam(':id_membre', $_SESSION['membre']['id_membre'], PDO::PARAM_STR);
				$updateavatar->bindParam(':avatar', $nom_avatar, PDO::PARAM_STR);
				$updateavatar->execute();
			} else {
				$msg .= '<div class="alert alert-danger"> Erreur durant l\'importation de votre avatar</div>';
			}
		}
	} // Est-ce qu'une avatar a été chargée
}







$mdp = '';
$confirm_mdp = '';

if(
	isset($_POST['mdp']) &&
	isset($_POST['confirm_mdp'])){
		$mdp = strip_tags(trim($_POST['mdp']));
		$confirm_mdp = strip_tags(trim($_POST['confirm_mdp']));
	}


include 'inc/header.inc.php';
include 'inc/nav.inc.php';

?>

<main role="main" class="container">

  <div class="starter-template text-center marge_haute">
    <h1>PROFIL</h1>
	<p class="lead">Bienvenue <?php echo $_SESSION['membre']['pseudo']; ?></p>
	<p class="lead"><?php echo $msg; // variable destinée à afficher des messages utilisateurs ?></p>
  </div>
  <div class="row">
  	<div class="col-6">
  		<ul class="list-group">
			<li class="list-group-item active">Vos informations</li>
			<li class="list-group-item">N° : <?php echo $_SESSION['membre']['id_membre']?> </li>
  			<li class="list-group-item">Pseudo : <?php echo $_SESSION['membre']['pseudo']?> </li>
  			<li class="list-group-item">Nom : <?php echo $_SESSION['membre']['nom']?> </li>
  			<li class="list-group-item">Prénom : <?php echo $_SESSION['membre']['prenom']?> </li>
  			<li class="list-group-item">Email : <?php echo $_SESSION['membre']['email']?> </li>
  			<li class="list-group-item">Sexe : 
	  			<?php 
	  			if($_SESSION['membre']['civilite'] == 'm') {
	  				echo 'Homme';
	  			}
	  			else {
	  				echo 'Femme';
	  			}
	  			?>
  			</li>
  			<li class="list-group-item">Statut : 
  				<?php 
  				if($_SESSION['membre']['statut'] == 1){
  					echo 'Membre';
  				}
  				else {
  					echo 'Administrateur';
  				}
  				?>	
  			</li>
  		</ul>	
  	</div>
  	<div class="col-6">
		  <?php 
		  $avatar_membre = $pdo->prepare("SELECT avatar FROM membre WHERE id_membre = :id_membre");
		  $avatar_membre->bindParam(':id_membre', $_SESSION['membre']['id_membre'], PDO::PARAM_STR);
		  $avatar_membre->execute();

		  $infos_avatar = $avatar_membre->fetch(PDO::FETCH_ASSOC);
		  if(!empty($infos_avatar['avatar'])){ ?>
			<img src="avatar_users/<?php echo $infos_avatar['avatar']; ?>">
		  <?php } ?>
		  
	</div>
  </div>

  <div class="row">
	  	<div class="col-3">
		<a href="?action=modifier" class="btn btn-outline-primary w-100 mt-2">Modifier votre profil</a>
		</div>
		<div class="col-3">
		<a href="?action=modifier_mdp" class="btn btn-outline-primary w-100 mt-2">Modifier votre mot de passe</a>
		</div>
		<div class="col-3">
		<a href="?action=commandes_realisees" class="btn btn-outline-primary w-100 mt-2">Vos commandes réalisées</a>
		</div>
	</div>

  <?php if(isset($_GET['action']) && ($_GET['action'] == 'modifier')) { ?>

  <div class="row">
	  	<div class="col-8">
		  <form method="post" action="" enctype="multipart/form-data">  
		  <div class="form-group">
                    <label for="pseudo">Pseudo</label>
                    <input type="text" name="pseudo" id="pseudo" class="form-control" value="<?php echo $_SESSION['membre']['pseudo']; ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="text" name="email" id="email" class="form-control" value="<?php echo $_SESSION['membre']['email']; ?>">
                </div>
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" name="nom" id="nom" class="form-control" value="<?php echo $_SESSION['membre']['nom']; ?>">
                </div>
                <div class="form-group">
                    <label for="prenom">Prenom</label>
                    <input type="text" name="prenom" id="prenom" class="form-control" value="<?php echo $_SESSION['membre']['prenom']; ?>">
                </div>
                <div class="form-group">
                    <label for="civilite">Civilité</label>
                    <select name="civilite" id="civilite" class="w-100">
                        <option value="m">Homme</option>
                        <option value="f" <?php if($_SESSION['membre']['civilite'] == 'f'){ echo 'selected'; } ?> >Femme</option>
                    </select>
                </div>
				<div class="form-group">
                    <label for="avatar">Avatar</label>
                    <input type="file" name="avatar" id="avatar" class="form-control">
                </div>
                <div class="form-group">
                    <button type="submit" name="confirm_update" class="btn btn-primary w-100">Modifier vos données</button>
                </div>
            </form>
		</div>
	</div>
	
	<?php }
	if(isset($_GET['action']) && ($_GET['action'] == 'modifier_mdp')) {
		if(!empty($_POST)){	
			if(empty($mdp) || $mdp != $confirm_mdp){
				$msg .= '<div class="alert alert-danger"> ATTENTION,<br> Les mots de passe ne correspondent pas.</div>';
			}
			else {
				$id_membre = $_SESSION['membre']['id_membre'];
				$mdp = password_hash($mdp, PASSWORD_DEFAULT);
				$modif_mdp = $pdo->prepare("UPDATE membre SET mdp = :mdp WHERE id_membre = :id_membre");
				$modif_mdp->bindParam(':id_membre', $id_membre, PDO::PARAM_STR);
				$modif_mdp->bindParam(':mdp', $mdp, PDO::PARAM_STR);
				$modif_mdp->execute();
		
				$msg .= '<div class="alert alert-success"> Votre mot de passe a bien été mis à jour.</div>';
			}
		
		} ?>
	
	<div class="row">
	  	<div class="col-8">
		  	<form method="post" action="">  
                <div class="form-group">
                    <label for="mdp">Mot de passe</label>
                    <input type="password" name="mdp" id="mdp" class="form-control" placeholder="Changer votre mot de passe">
				</div>
                <div class="form-group">
                    <label for="confirm_mdp">Confirmation Mot de passe</label>
                    <input type="password" name="confirm_mdp" id="confirm_mdp" class="form-control" placeholder="Confirmer votre nouveau mot de passe">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary w-100">Enregistrer</button>
                </div>
            </form>
		</div>
	</div>

	<?php } ?>
	<?php if(isset($_GET['action']) && ($_GET['action'] == 'commandes_realisees')) { 
		$commande = $pdo->prepare("SELECT *, commande.date_enregistrement AS date_commande FROM commande, produit, salle WHERE salle.id_salle = produit.id_salle AND produit.id_produit = commande.id_produit AND id_membre = :id_membre");
		$commande->bindParam(':id_membre', $_SESSION['membre']['id_membre'], PDO::PARAM_STR);
		$commande->execute();
		echo '<table class="table table-bordered">';
		echo '<tr>';
		echo '<th class="text-center">N° Commande</th><th class="text-center">N° Produit</th><th class="text-center">Titre</th><th class="text-center">Ville</th><th class="text-center">Date Arrivee</th><th class="text-center">Date Départ</th><th class="text-center">Prix</th><th class="text-center">Date de commande</th>';
		while($liste_commande = $commande->fetch(PDO::FETCH_ASSOC)) {
				echo '<tr>';
				echo '<td>' . $liste_commande['id_commande'] . '</td>';
				echo '<td>' . $liste_commande['id_produit'] . '</td>';
				echo '<td>' . $liste_commande['titre'] . '</td>';
				echo '<td>' . $liste_commande['ville'] . '</td>'; 
				echo '<td>' . $liste_commande['date_arrivee'] . '</td>';
				echo '<td>' . $liste_commande['date_depart'] . '</td>';
				echo '<td>' . $liste_commande['prix'] . '</td>';
				echo '<td>' . $liste_commande['date_commande'] . '</td>';		
				echo '</tr>';
			}
		
			echo '</tr>';
			echo '</table>';
			echo '</div>';
			echo '</div>';
		}
	?>

</main><!-- /.container -->


<?php
include 'inc/footer.inc.php';
