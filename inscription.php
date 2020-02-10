<?php
include 'inc/init.inc.php';

if(user_is_connect()) {
	header("location:index.php");
}

// CODE

$pseudo = '';
$mdp = '';
$confirm_mdp = '';
$email = '';
$nom = '';
$prenom = '';
$civilite = '';

if(
    isset($_POST['pseudo']) &&
    isset($_POST['mdp']) &&
    isset($_POST['confirm_mdp']) &&
    isset($_POST['email']) &&
    isset($_POST['nom']) &&
    isset($_POST['prenom']) &&
    isset($_POST['civilite'])) {
      
        $pseudo = htmlentities(trim($_POST['pseudo']));
        $mdp = htmlentities(trim($_POST['mdp']));
        $confirm_mdp = htmlentities(trim($_POST['confirm_mdp']));
        $email = htmlentities(trim($_POST['email']));
        $nom = htmlentities(trim($_POST['nom']));
        $prenom = htmlentities(trim($_POST['prenom']));
        $civilite = htmlentities(trim($_POST['civilite'])); 
        
        if(iconv_strlen($pseudo) < 4 || iconv_strlen($pseudo) > 20){
            $msg .= '<div class="alert alert-danger"> ATTENTION,<br> Le pseudo doit avoir entre 4 et 20 caractères</div>';
        }
        
        // Vérification des caractères du pseudo
        $verif_pseudo = preg_match('#^[a-zA-Z0-9_]+$#', $pseudo);
        if(!$verif_pseudo){
            // Message en cas d'erreurs
            $msg .= '<div class="alert alert-danger"> ATTENTION,<br> Le pseudo n\'est pas valide. Caractères autorisés : a - z, A - Z et 0 - 9.</div>';
        }

        // Vérification du mot de passe
        if($confirm_mdp !== $mdp){
            $msg .= '<div class="alert alert-danger"> ATTENTION,<br> Le mot de passe n\'est pas identique. Veuillez vérifier vos saisies.</div>';
        }

        // Vérification de la validité de l'email
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $msg .= '<div class="alert alert-danger"> ATTENTION,<br> L\'email n\'est pas valide. Veuillez vérifier votre saisie.</div>';
        }

        // Contrôle de la taille du nom et du prénom
        if(iconv_strlen($nom) < 1 && iconv_strlen($prenom) < 1){
            $msg .= '<div class="alert alert-danger"> ATTENTION,<br> Vous devez obligatoirement remplir le champ "Nom" et "Prénom".</div>'; 
        }

        // Vérification de la validité du prénom et du nom
        $verif_nom = preg_match('#^[a-záàâäãåçéèêëíìîïñóòôöõúùûüýÿæœA-ZÁÀÂÄÃÅÇÉÈÊËÍÌÎÏÑÓÒÔÖÕÚÙÛÜÝŸÆŒ]+-?[a-záàâäãåçéèêëíìîïñóòôöõúùûüýÿæœA-ZÁÀÂÄÃÅÇÉÈÊËÍÌÎÏÑÓÒÔÖÕÚÙÛÜÝŸÆŒ]+$#', $nom);
        $verif_prenom = preg_match('#^[a-záàâäãåçéèêëíìîïñóòôöõúùûüýÿæœA-ZÁÀÂÄÃÅÇÉÈÊËÍÌÎÏÑÓÒÔÖÕÚÙÛÜÝŸÆŒ]+-?[a-záàâäãåçéèêëíìîïñóòôöõúùûüýÿæœA-ZÁÀÂÄÃÅÇÉÈÊËÍÌÎÏÑÓÒÔÖÕÚÙÛÜÝŸÆŒ]+$#', $prenom);
        if(!$verif_nom){
             $msg .= '<div class="alert alert-danger"> ATTENTION,<br> Le nom ne doit pas comporté de caractères speciaux ni de chiffres. Caractères autorisés : a - z et A - Z (Accent compris).</div>';
         }
        if(!$verif_prenom){
            $msg .= '<div class="alert alert-danger"> ATTENTION,<br> Le prénom ne doit pas comporté de caractères speciaux ni de chiffres. Caractères autorisés : a - z et A - Z (Accent compris).</div>';
        }


        // S'il n'y pas d'erreur lors du remplissage du formulaire d'inscription
        if(empty($msg)) {
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
            } else {
                // Hashage du Mot de Passe lors de l'enregistrement
                $mdp = password_hash($mdp, PASSWORD_DEFAULT);

                $enregistrement = $pdo->prepare("INSERT INTO membre (pseudo, mdp, nom, prenom, email, civilite, statut, date_enregistrement, confirmation_mail, validation_compte) VALUES (:pseudo, :mdp, :nom, :prenom, :email, :civilite, 1, NOW(), :confirmation_mail, NULL)");
                $token = str_random(60);
                $enregistrement->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
                $enregistrement->bindParam(':mdp', $mdp, PDO::PARAM_STR);
                $enregistrement->bindParam(':nom', $nom, PDO::PARAM_STR);
                $enregistrement->bindParam(':prenom', $prenom, PDO::PARAM_STR);
                $enregistrement->bindParam(':email', $email, PDO::PARAM_STR);
                $enregistrement->bindParam(':civilite', $civilite, PDO::PARAM_STR);
                $enregistrement->bindParam(':confirmation_mail', $token, PDO::PARAM_STR);
                $enregistrement->execute();
                $user_id = $pdo->lastInsertId();
                // Envoi du mail de confirmation d'inscription à l'utilisateur
                $sujet_mail = 'Confirmation de votre compte SalleA';
                $message_mail = '<div class="row">';
                $message_mail .= '<div class="offset-1 col-5">';
                $message_mail .= "<p>Entreprise SalleA</p>";
                $message_mail .= "<p>Adresse : 37 rue Saint-Sébastien</p>";
                $message_mail .= '<p>Code Postal - Ville : 75011 Paris</p>';
                $message_mail .= '<p>N° SIRET : 456 456 546 645 RCS PARIS</p>';
                $message_mail .= '</div>';
                $message_mail .= '</div>';
                $message_mail .= '<br>';
                $message_mail .= '<br>';
                $message_mail .= '<br>';
                $message_mail .= '<div class="row">';
                $message_mail .= '<div class="offset-1 col-10">';
                $message_mail .= '<p>Cher membre !</p>';
                $message_mail .= '<p>Vous recevez cet e-mail car vous vous êtes récemment inscrit sur notre site.</p>';
                $message_mail .= "<p>Merci d\'avance de cliquer sur le lien ci-dessous pour vérifier votre email : <a href='" . URL ."confirm.php?id_membre=$user_id&token=$token'>cliquez ici</a>.</p>";
                $message_mail .= "<p>Après cela, vous pourrez directement vous connecter avec vos identifiants déterminés lors de votre inscription.</p>";
                $message_mail .= '<p>L\'équipe SalleA.</p>';
                $message_mail .= '</div>';
                $message_mail .= '</div>';
                $headers = "From: postmaster@johann-chaligne.fr\n";
                $headers .= "Reply-To: postmaster@johann-chaligne.fr\n";
                $headers .= 'Content-Type: text/html; charset="UTF-8"';
                mail($email, $sujet_mail, $message_mail, $headers);

                $msg .= "<div class='alert alert-success'> Un mail de confirmation vous a été envoyé pour valider votre compte. N'hésitez pas à regarder dans vos spams.</div>";    
            }
        }
    }


include 'inc/header.inc.php';
include 'inc/nav.inc.php';
?>

<main class="container">
    <div class="starter-template text-center marge_haute">
        <h1>INSCRIPTION</h1>
        <p class="lead"><?php echo $msg; ?></p>
    </div>
    <div class="col-12">
        <form method="post">
            <div class="row">
                <div class="offset-3 col-6">
                    <div class="form-group">
                        <label for="pseudo">Pseudo</label>
                        <input type="text" name="pseudo" id="pseudo" class="form-control" value="<?php echo $pseudo; ?>">
                    </div>
                    <div class="form-group">
                        <label for="mdp">Mot de passe</label>
                        <input type="password" name="mdp" id="mdp" class="form-control" value="">
                    </div>
                    <div class="form-group">
                        <label for="confirm_mdp">Confirmation du mot de passe</label>
                        <input type="password" name="confirm_mdp" id="confirm_mdp" class="form-control" value="">
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
                        <button type="submit" class="btn btn-info w-100">Inscription</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>
<?php
include 'inc/footer.inc.php';
