<?php
include 'inc/init.inc.php';

// Déconnexion de l'utilisateur
if(isset($_GET['action']) && $_GET['action'] == 'deconnexion') {
    session_destroy();
}

if(user_is_connect()){
    header('location:profil.php');
}

// CODE
$email = '';


if(isset($_POST['email'])) {
    $email = strip_tags(trim($_POST['email']));

    // Vérification du mail dans la BDD
    $membre = $pdo->prepare("SELECT * FROM membre WHERE email = :email AND validation_compte IS NOT NULL");
    $membre->bindParam(":email", $email, PDO::PARAM_STR);
    $membre->execute();

    $infos_membre = $membre->fetch(PDO::FETCH_ASSOC);

        if($infos_membre) {
            $reset_token = str_random(60);
            $id_user = $infos_membre['id_membre'];
            $maj_mdp_par_mail = $pdo->prepare('UPDATE membre SET reset_mdp = :reset_mdp, duree_reset = NOW() WHERE id_membre = :id_membre');
            $maj_mdp_par_mail->bindParam(':id_membre', $id_user, PDO::PARAM_STR);
            $maj_mdp_par_mail->bindParam(':reset_mdp', $reset_token, PDO::PARAM_STR);
            $maj_mdp_par_mail->execute();
            // Envoi du mail pour le lien du changement de Mot de passe
            $sujet_mail = 'Reinitialisation de votre mot de passe chez SalleA';
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
            $message_mail .= '<p>Bonjour cher membre,</p>';
            $message_mail .= '<p>Nous avons reçu une demande pour réinitialiser votre mot de passe.</p>';
            $message_mail .= "<p>Si vous n'avez pas fait la demande, ignorez simplement ce message. Sinon, vous pouvez réinitialiser votre mot de passe via ce <a href='". URL . "reset_mdp.php?id_membre=$id_user&token=$reset_token'>lien</a>.</p>";
            $message_mail .= '<p>Merci.</p>';
            $message_mail .= '<p>L\'équipe SalleA.</p>';
            $message_mail .= '</div>';
            $message_mail .= '</div>';
            $headers = "From: postmaster@johann-chaligne.fr\n";
            $headers .= "Reply-To: postmaster@johann-chaligne.fr\n";
            $headers .= 'Content-Type: text/html; charset="UTF-8"';
            mail($email, $sujet_mail, $message_mail, $headers);
            
            header('location:connexion.php');
        }  else {
            $msg .= '<div class="alert alert-danger">ATTENTION,<br>Cette adresse mail n\'existe pas.</div>';
        }
    }


include 'inc/header.inc.php';
include 'inc/nav.inc.php';
?>
<main class="container">
    <div class="starter-template text-center marge_haute">
        <h1>MOT DE PASSE OUBLIE ?</h1>
        <p class="lead"><?php echo $msg; ?></p>
    </div>
    <div class="col-12">
        <form method="post">
            <div class="row">
                <div class="offset-3 offset-sm-4 col-sm-4 col-6">
                    <div class="form-group">
                        <label for="email">Email de votre compte</label>
                        <input type="text" placeholder="Email" name="email" id="email" class="form-control">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-info w-100">Envoyer</button>
                    </div>
                </div>                
            </div>
        </form>
    </div>
</main>

<?php
include 'inc/footer.inc.php';