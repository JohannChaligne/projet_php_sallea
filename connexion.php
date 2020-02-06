<?php
include 'inc/init.inc.php';

// Déconnexion de l'utilisateur
if(isset($_GET['action']) && $_GET['action'] == 'deconnexion') {
    session_destroy();
}

if(user_is_connect()){
    header('location:index.php');
}

if(isset($_COOKIE['souvenir_session'])){
    $souvenir_session = $_COOKIE['souvenir_session'];
    $partie = explode('==', $souvenir_session);
    $user_id = $partie[0];
    $id_du_membre = $pdo->prepare('SELECT * FROM membre WHERE id_membre = id_membre');
    $id_du_membre->bindParam(':id_membre', $user_id, PDO::PARAM_STR);
    $id_du_membre->execute();

    $info_membre = $id_du_membre->fetch(PDO::FETCH_ASSOC);
    if($info_membre){
        $token_souvenir_session = $user_id . '==' . $souvenir_session . sha1($user_id . 'cleintrouvable');
        if($token_souvenir_session == $souvenir_session){
            header('location:index.php');
        } else {
        }
    }
}

// CODE
$pseudo_mail = '';
$mdp = '';

if(isset($_POST['pseudo_mail']) && isset($_POST['mdp'])) {
    $pseudo_mail = strip_tags(trim($_POST['pseudo_mail']));
    $mdp = strip_tags(trim($_POST['mdp']));

    // Vérification du pseudo dans la BDD
    $membre = $pdo->prepare("SELECT * FROM membre WHERE (pseudo = :pseudo OR email = :pseudo) AND validation_compte IS NOT NULL");
    $membre->bindParam(":pseudo", $pseudo_mail, PDO::PARAM_STR);
    $membre->execute();

    // Si le pseudo n'est pas correct : 
    if($membre->rowCount() < 1){
        $msg .= '<div class="alert alert-danger">ATTENTION,<br>L\'identifiant et/ou le mot de passe n\'existent pas. Veuillez recommencer !</div>';
    } else {
        $infos_membre = $membre->fetch(PDO::FETCH_ASSOC);
        // On vérifie le mot de passe hashé
        if(password_verify($mdp, $infos_membre['mdp'])) {
        // on place les informations du membre récupérées de la BDD dans la session pour pouvoir les intérroger à tout moment.
            $_SESSION['membre'] = array();
            $_SESSION['membre']['id_membre'] = $infos_membre['id_membre'];
            $_SESSION['membre']['pseudo'] = $infos_membre['pseudo'];
            $_SESSION['membre']['nom'] = $infos_membre['nom'];
            $_SESSION['membre']['prenom'] = $infos_membre['prenom'];
            $_SESSION['membre']['email'] = $infos_membre['email'];
            $_SESSION['membre']['civilite'] = $infos_membre['civilite'];
            $_SESSION['membre']['statut'] = $infos_membre['statut'];

            if(strip_tags($_POST['souvenir_session'])){
                $souvenir_session = str_random(255);
                $id_membre = $infos_membre['id_membre'];
                $creation_souvenir_session = $pdo->prepare("UPDATE membre SET souvenir_session = :souvenir_session WHERE id_membre = :id_membre");
                $creation_souvenir_session->bindParam(':id_membre', $id_membre, PDO::PARAM_STR);
                $creation_souvenir_session->bindParam(':souvenir_session', $souvenir_session, PDO::PARAM_STR);
                $creation_souvenir_session->execute();
                setcookie('souvenir_session', $id_membre . '==' . $souvenir_session . sha1($id_membre . 'cleintrouvable'), time() + 60 * 60 * 24 * 7);
            }
            // une fois la connexion mise en place, on redirige vers la page profil.php
            header('location:profil.php');
        } else {
            $msg .= '<div class="alert alert-danger">ATTENTION,<br>L\'identifiant et/ou le mot de passe n\'existent pas. Veuillez recommencer !</div>';
        }
    }
}

include 'inc/header.inc.php';
include 'inc/nav.inc.php';
?>

<main class="container">
    <div class="starter-template text-center marge_haute">
        <h1>CONNEXION</h1>
        <p class="lead"><?php echo $msg; ?></p>
    </div>
    <div class="col-12">
        <form method="post">
             <div class="row">
                <div class="offset-3 offset-sm-4 col-sm-4 col-6">
                    <div class="form-group">
                        <label for="pseudo_mail">Identifiant</label>
                        <input type="text" placeholder="Pseudo ou Email" name="pseudo_mail" id="pseudo_mail" class="form-control" value="<?php echo $pseudo_mail; ?>">
                    </div>
                    <div class="form-group">
                        <label for="mdp">Mot de passe</label>
                        <input type="password" name="mdp" id="mdp" class="form-control" value="">
                        <a href="oublie_mdp.php" class="bg-connexion">Mot de passe oublié ?</a>
                    </div>
                    <div class="form-group">
                        <input type="checkbox" name="souvenir_session" id="souvenir_session" value="1"> Se souvenir de moi
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-info w-100">Connexion</button>
                    </div>
                </div>                
            </div>
        </form>
    </div>
</main>

<?php
include 'inc/footer.inc.php';


