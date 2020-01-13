<?php
include '../inc/init.inc.php';

if(!user_is_admin()){
    header("location:../connexion.php");
    exit();
}

// CODE
$id_membre = '';
$mdp_actuel = '';
$pseudo = '';
$mdp = '';
$email = '';
$nom = '';
$prenom = '';
$civilite = '';
$statut = '';

//
//
// MODIFICATION MEMBRES
//
//
if(isset($_GET['action']) && $_GET['action'] == 'modifier' && isset($_GET['id_membre']) && is_numeric($_GET['id_membre'])) {
	// une requête pour récupérer les informations en BDD
	$recup_infos = $pdo->prepare("SELECT * FROM membre WHERE id_membre = :id_membre");
	$recup_infos->bindParam(":id_membre", $_GET['id_membre'], PDO::PARAM_STR);
	$recup_infos->execute();
	
	$infos_membre = $recup_infos->fetch(PDO::FETCH_ASSOC); 
    
    $id_membre = $infos_membre['id_membre']; // pour la partie "modification"
    $mdp_actuel = $infos_membre['mdp']; // pour la partie "modification"
    $pseudo = $infos_membre['pseudo'];
    $mdp = $infos_membre['mdp'];
    $email = $infos_membre['email'];
    $nom = $infos_membre['nom'];
    $prenom = $infos_membre['prenom'];
    $civilite = $infos_membre['civilite'];
    $statut = $infos_membre['statut'];
    
}

//
//
// SUPPRESSION MEMBRES
//
//

if(isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id_membre']) && is_numeric($_GET['id_membre'])) {
	$suppression_salle = $pdo->prepare("DELETE FROM membre WHERE id_membre = :id_membre");
	$suppression_salle->bindParam(':id_membre', $_GET['id_membre'], PDO::PARAM_STR);
	$suppression_salle->execute();
	$msg .= '<div class="alert alert-success">Le membre n°' . $_GET['id_membre'] . ' a bien été supprimé</div>';
	$_GET['action'] = 'affichage';
}


//
//
// ENREGISTREMENT MEMBRES
//
//

if(
    isset($_POST['pseudo']) &&
    isset($_POST['mdp']) &&
    isset($_POST['email']) &&
    isset($_POST['nom']) &&
    isset($_POST['prenom']) &&
    isset($_POST['civilite']) && 
    isset($_POST['statut'])) {
        
        if(!empty($_POST['id_membre'])){
            $id_membre = strip_tags(trim($_POST['id_membre']));
        }
        if(!empty($_POST['mdp_actuel'])){
            $mdp = strip_tags(trim($_POST['mdp_actuel']));
        }

        $pseudo = strip_tags(trim($_POST['pseudo']));
        $mdp = strip_tags(trim($_POST['mdp']));
        $email = strip_tags(trim($_POST['email']));
        $nom = strip_tags(trim($_POST['nom']));
        $prenom = strip_tags(trim($_POST['prenom']));
        $civilite = strip_tags(trim($_POST['civilite']));
        $statut = strip_tags(trim($_POST['statut'])); 
        
        // Vérification du nombre de caractères du pseudo
        if(iconv_strlen($pseudo) < 4 || iconv_strlen($pseudo) > 20){
            $msg .= '<div class="alert alert-danger"> ATTENTION,<br> Le pseudo doit avoir entre 4 et 20 caractères</div>';
        }
        
        // Vérification des caractères du pseudo
        $verif_pseudo = preg_match('#^[a-zA-Z0-9_]+$#', $pseudo);
        if(!$verif_pseudo){
            // Message en cas d'erreurs
            $msg .= '<div class="alert alert-danger"> ATTENTION,<br> Le pseudo n\'est pas valide. Caractères autorisés : a - z, A - Z et 0 - 9.</div>';
        }

        // Vérification de la validité de l'email
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $msg .= '<div class="alert alert-danger"> ATTENTION,<br> L\'email n\'est pas valide. Veuillez vérifier votre saisie.</div>';
        }

        // Contrôle de la taille du nom et du prénom
        if(iconv_strlen($nom) < 1 && iconv_strlen($prenom) < 1){
            $msg .= '<div class="alert alert-danger"> ATTENTION,<br> Vous devez obligatoirement remplir le champ "Nom" et "Prénom".</div>'; 
        }

        // Vérification de la validité du prénom
        $verif_nom = preg_match('#^[a-zA-Z_]+$#', $nom);
        $verif_prenom = preg_match('#^[a-zA-Z_]+$#', $prenom);
        if(!$verif_nom){
            $msg .= '<div class="alert alert-danger"> ATTENTION,<br> Le nom ne doit pas comporté de caractères speciaux ni de chiffres. Caractères autorisés : a - z et A - Z.</div>';
        }
        if(!$verif_prenom){
            $msg .= '<div class="alert alert-danger"> ATTENTION,<br> Le prénom ne doit pas comporté de caractères speciaux ni de chiffres. Caractères autorisés : a - z et A - Z.</div>';
        }

        if(empty($id_membre)) {
            // Contrôle si le pseudo est disponible 
            $pseudo_dispo = $pdo->prepare("SELECT * FROM membre WHERE pseudo = :pseudo");
            $pseudo_dispo->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
            $pseudo_dispo->execute();

            if($pseudo_dispo->rowCount() > 0){
                $msg .= '<div class="alert alert-danger"> ATTENTION,<br> Le pseudo est déjà pris par un utilisateur. Veuillez recommencer.</div>';
            }

            // Contrôle si le mail est disponible 
            $mail_dispo = $pdo->prepare("SELECT * FROM membre WHERE email = :email");
            $mail_dispo->bindParam(':email', $email, PDO::PARAM_STR);
            $mail_dispo->execute();
            
            if($mail_dispo->rowCount() > 0){
                $msg .= '<div class="alert alert-danger"> ATTENTION,<br> Le mail est déjà pris par un utilisateur. Veuillez recommencer.</div>';
            }
        }

        // S'il n'y pas d'erreur lors du remplissage du formulaire d'inscription
        if(empty($msg)) {
            if(empty($id_membre)) {
                // Hashage du Mot de Passe lors de l'enregistrement
                $mdp = password_hash($mdp, PASSWORD_DEFAULT);

                // ENREGISTREMENT D'UN MEMBRE
                $enregistrement_membre = $pdo->prepare("INSERT INTO membre (pseudo, mdp, nom, prenom, email, civilite, avatar, statut, date_enregistrement, confirmation_mail, validation_compte, reset_mdp, duree_reset, souvenir_session) VALUES (:pseudo, :mdp, :nom, :prenom, :email, :civilite, NULL, :statut, NOW(), :confirmation_mail, NULL, NULL, NULL, NULL)");
                $token = str_random(60);
                $enregistrement_membre->bindParam(':confirmation_mail', $token, PDO::PARAM_STR);
                $enregistrement_membre->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
            if(empty($_POST['mdp'])) {
                $enregistrement_membre->bindParam(':mdp', $mdp_actuel, PDO::PARAM_STR);
            } else {
                $mdp = password_hash($mdp, PASSWORD_DEFAULT);
                $enregistrement_membre->bindParam(':mdp', $mdp, PDO::PARAM_STR);
            }
            $enregistrement_membre->bindParam(':nom', $nom, PDO::PARAM_STR);
            $enregistrement_membre->bindParam(':prenom', $prenom, PDO::PARAM_STR);
            $enregistrement_membre->bindParam(':email', $email, PDO::PARAM_STR);
            $enregistrement_membre->bindParam(':civilite', $civilite, PDO::PARAM_STR);
            $enregistrement_membre->bindParam(':statut', $statut, PDO::PARAM_STR);
            $enregistrement_membre->execute();
            $user_id = $pdo->lastInsertId();
            $sujet_mail = 'Confirmation de votre compte SalleA';
            $message_mail = "Afin de valider votre compte, veuillez cliquez sur ce <a href='http://localhost/php/projet_back_end/confirm.php?id_membre=$user_id&token=$token'>lien</a>.";
            $headers = 'Content-type: text/html; charset="UTF-8"';
            mail($email, $sujet_mail, $message_mail, $headers);
            }
            else {  
                // UPDATE => MODIFICATION D'UN MEMBRE
                $enregistrement_membre = $pdo->prepare("UPDATE membre SET pseudo = :pseudo, mdp = :mdp, nom = :nom, prenom = :prenom, email = :email, civilite = :civilite, statut = :statut WHERE id_membre = :id_membre");
                $enregistrement_membre->bindParam(':id_membre', $id_membre, PDO::PARAM_STR); 
                $enregistrement_membre->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
            if(empty($_POST['mdp'])) {
                $enregistrement_membre->bindParam(':mdp', $mdp_actuel, PDO::PARAM_STR);
            } else {
                $mdp = password_hash($mdp, PASSWORD_DEFAULT);
                $enregistrement_membre->bindParam(':mdp', $mdp, PDO::PARAM_STR);
            }
            $enregistrement_membre->bindParam(':nom', $nom, PDO::PARAM_STR);
            $enregistrement_membre->bindParam(':prenom', $prenom, PDO::PARAM_STR);
            $enregistrement_membre->bindParam(':email', $email, PDO::PARAM_STR);
            $enregistrement_membre->bindParam(':civilite', $civilite, PDO::PARAM_STR);
            $enregistrement_membre->bindParam(':statut', $statut, PDO::PARAM_STR);
            $enregistrement_membre->execute();
            }
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
        <a href="index.html">Dashboard</a>
      </li>
      <li class="breadcrumb-item active">Gestion Membres</li>
    </ol>

    <!-- Page Content -->
    <h1>Gestion des Membres</h1>
    <hr>
    <div class="starter-template text-center">
        <p class="lead"><?php echo $msg; // variable destinée à afficher des messages utilisateurs ?></p>
        <a href="?action=enregistrement" class="btn btn-outline-primary">Enregistrement membres</a>
        <a href="?action=affichage" class="btn btn-outline-danger">Affichage des membres</a>
    </div>

    <?php if(isset($_GET['action']) && ($_GET['action'] == 'enregistrement' || $_GET['action'] == 'modifier')) { ?>

    <div class="row">
        <div class="col-8 mx-auto">
            <form method="post" action="" enctype="multipart/form-data">
                <input type="hidden" name="id_membre" readonly value="<?php echo $id_membre; ?>"> 
                <div class="form-group">
                    <label for="pseudo">Pseudo</label>
                    <input type="text" name="pseudo" id="pseudo" class="form-control" value="<?php echo $pseudo; ?>">
                </div>
                <?php
			        if(!empty($mdp_actuel)) {
				    // si $mdp_actuel n'est pas vide, on est dans la modif et un mot de passe existe pour le membre à modifier
				    echo '<div class="form-group"><label for="mdp_actuel">Mot de passe actuel</label>';
				    echo '<input type="text" name="mdp_actuel" id="mdp_actuel" class="form-control" value="' . $mdp_actuel . '">';
				    echo '</div>';
                    }
			    ?>
                <div class="form-group">
                    <label for="mdp">Mot de passe</label>
                    <input type="text" name="mdp" id="mdp" class="form-control">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="text" name="email" id="email" class="form-control" value="<?php echo $email; ?>">
                </div>
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" name="nom" id="nom" class="form-control" value="<?php echo $nom; ?>">
                </div>
                <div class="form-group">
                    <label for="prenom">Prenom</label>
                    <input type="text" name="prenom" id="prenom" class="form-control" value="<?php echo $prenom; ?>">
                </div>
                <div class="form-group">
                    <label for="civilite">Civilité</label>
                    <select name="civilite" id="civilite" class="w-100">
                        <option value="m">Homme</option>
                        <option value="f" <?php if($civilite == 'f'){ echo 'selected'; } ?> >Femme</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="statut">Statut</label>
                    <select name="statut" id="statut" class="w-100">
                        <option value="2">Admin</option>
                        <option value="1" <?php if($statut == '1'){ echo 'selected'; } ?> >Membre</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary w-100">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
    <?php } 

    if(isset($_GET['action']) && $_GET['action'] == 'affichage') {
    $liste_membre = $pdo->query("SELECT * FROM membre ORDER BY id_membre");
    echo '<div class="row">';
    echo '<div class="col-12">';

    echo '<p>Nombre total de membres : ' . $liste_membre->rowCount() . '.</p>';

    echo '<table class="table table-bordered">';
    echo '<tr>';
    echo '<th class="text-center">id_membre</th><th class="text-center">Pseudo</th><th class="text-center">Nom</th><th class="text-center">Prenom</th><th class="text-center">Email</th><th class="text-center">Civilité</th><th class="text-center">Statut</th><th class="text-center">Date enregistrement</th><th class="text-center">Action</th>';

    // une boucle pour afficher les salles dans le tableau
    while($ligne = $liste_membre->fetch(PDO::FETCH_ASSOC)){
        echo '<tr>';
        echo '<td>' . $ligne['id_membre'] . '</td>';
        echo '<td>' . $ligne['pseudo'] . '</td>';
        echo '<td>' . $ligne['nom'] . '</td>';
        echo '<td>' . $ligne['prenom'] . '</td>';
        echo '<td>' . $ligne['email'] . '</td>';
        echo '<td>' . $ligne['civilite'] . '</td>';
        echo '<td>' . $ligne['statut'] . '</td>';
        echo '<td>' . $ligne['date_enregistrement'] . '</td>';
        echo '<td><a href="?action=modifier&id_membre=' . $ligne['id_membre'] . '" class="btn" title="Modifier"><i class="fas fa-edit"></i></a><a href="?action=supprimer&id_membre=' . $ligne['id_membre'] . '" class="btn" onclick="return(confirm(\'Etes-vous sur ?\'))" title="Supprimer"><i class="fas fa-trash-alt"></i></td>';

        echo '</tr>';
    }

    echo '</tr>';
    echo '</table>';
    echo '</div>';
    echo '</div>';
    }

    ?>
  </div>
  <!-- /.container-fluid -->
</div>
<!-- /.content-wrapper -->




<?php
include 'inc/footer_admin.inc.php';


