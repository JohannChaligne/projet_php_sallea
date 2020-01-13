<?php
include 'inc/init.inc.php';

// Déconnexion de l'utilisateur
if(isset($_GET['action']) && $_GET['action'] == 'deconnexion') {
    session_destroy();
}

// CODE
$id_membre = $_GET['id_membre'];
$reset_mdp = $_GET['token'];
if(isset($_GET['id_membre']) && isset($_GET['token']) && is_numeric($_GET['id_membre'])){
    $membre = $pdo->prepare("SELECT * FROM membre WHERE id_membre = :id_membre AND reset_mdp IS NOT NULL AND reset_mdp = :reset_mdp AND duree_reset > DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
    $membre->bindParam(':id_membre', $id_membre, PDO::PARAM_STR);
    $membre->bindParam(':reset_mdp', $reset_mdp, PDO::PARAM_STR);
    $membre->execute();

    $infos_membre = $membre->fetch(PDO::FETCH_ASSOC);

        if($infos_membre) {
            if(!empty($_POST)){
                if(!empty($_POST['mdp']) && $_POST['mdp'] == $_POST['confirm_mdp']){
                    $mdp = password_hash($_POST['mdp'], PASSWORD_DEFAULT);
                    $modif_mdp = $pdo->prepare("UPDATE membre SET mdp = :mdp, reset_mdp = NULL, duree_reset = NULL WHERE id_membre = :id_membre");
				    $modif_mdp->bindParam(':id_membre', $id_membre, PDO::PARAM_STR);
				    $modif_mdp->bindParam(':mdp', $mdp, PDO::PARAM_STR);
                    $modif_mdp->execute();
                    header('location:connexion.php');
                }
                else {
                    $msg .= '<div class="alert alert-danger"> ATTENTION,<br> Les mots de passes ne sont pas identiques.</div>';
                }
            }
        }
        else {
        $msg .= '<div class="alert alert-danger"> ATTENTION,<br> Le lien de réinitialisation n\'est pas correct ou valide. Merci de cliquer sur le lien envoyé dans votre boite mail pour modifier votre mot de passe.</div>';
        }
}

else {
    header('location:connexion.php'); 
}


include 'inc/header.inc.php';
include 'inc/nav.inc.php';
?>

<html>
    <head>
    <meta charset="utf-8">
    </head>
    <body>
        <main role="main" class="container">
            <div class="starter-template text-center marge_haute">
                <h1>ENREGISTREZ VOTRE NOUVEAU MOT DE PASSE</h1>
                <p class="lead"><?php echo $msg; ?></p>
            </div>
            <div class="col-12">
                <form method="post" action="">
                    <div class="row">
                        <div class="offset-3 col-6">
                        <div class="form-group">
                                <label for="mdp">Mot de passe</label>
                                <input type="password" name="mdp" id="mdp" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="confirm_mdp">Confirmation du mot de passe</label>
                                <input type="password" name="confirm_mdp" id="confirm_mdp" class="form-control">
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary w-100">Réinitialiser mon mot de passe</button>
                            </div>
                        </div>                
                    </div>
                </form>
            </div>
        </main><!-- /.container -->
    </body>
</html>

<?php
include 'inc/footer.inc.php';
