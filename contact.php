<?php
include 'inc/init.inc.php';

// CODE
$email = '';
$nom = '';
$prenom = '';
$sujet = '';
$message = '';

if(
    isset($_POST['email']) &&
    isset($_POST['nom']) &&
    isset($_POST['prenom']) &&
    isset($_POST['sujet']) &&
    isset($_POST['message'])) {
    
        $email = strip_tags(trim($_POST['email']));
        $nom = strip_tags(trim($_POST['nom']));
        $prenom = strip_tags(trim($_POST['prenom']));
        $sujet = strip_tags(trim($_POST['sujet']));
        $message = strip_tags(trim($_POST['message']));

        // Vérification de la validité de l'email
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $msg .= '<div class="alert alert-danger"> ATTENTION,<br> L\'email n\'est pas valide. Veuillez vérifier votre saisie.</div>';
        }

        // Contrôle de la taille du nom et du prénom
        if(iconv_strlen($nom) < 1 && iconv_strlen($prenom) < 1){
            $msg .= '<div class="alert alert-danger"> ATTENTION,<br> Vous devez obligatoirement remplir le champ "Nom" et "Prénom".</div>'; 
        }

        // Vérification de la validité du prénom
        $verif_nom = preg_match('#^[a-záàâäãåçéèêëíìîïñóòôöõúùûüýÿæœA-ZÁÀÂÄÃÅÇÉÈÊËÍÌÎÏÑÓÒÔÖÕÚÙÛÜÝŸÆŒ]+-?[a-záàâäãåçéèêëíìîïñóòôöõúùûüýÿæœA-ZÁÀÂÄÃÅÇÉÈÊËÍÌÎÏÑÓÒÔÖÕÚÙÛÜÝŸÆŒ]+$#', $nom);
        $verif_prenom = preg_match('#^[a-záàâäãåçéèêëíìîïñóòôöõúùûüýÿæœA-ZÁÀÂÄÃÅÇÉÈÊËÍÌÎÏÑÓÒÔÖÕÚÙÛÜÝŸÆŒ]+-?[a-záàâäãåçéèêëíìîïñóòôöõúùûüýÿæœA-ZÁÀÂÄÃÅÇÉÈÊËÍÌÎÏÑÓÒÔÖÕÚÙÛÜÝŸÆŒ]+$#', $prenom);
         if(!$verif_nom){
             $msg .= '<div class="alert alert-danger"> ATTENTION,<br> Le nom ne doit pas comporté de caractères speciaux ni de chiffres. Caractères autorisés : a - z et A - Z (Accent compris).</div>';
         }
        if(!$verif_prenom){
            $msg .= '<div class="alert alert-danger"> ATTENTION,<br> Le prénom ne doit pas comporté de caractères speciaux ni de chiffres. Caractères autorisés : a - z et A - Z (Accent compris).</div>';
        }

        if(iconv_strlen($message) < 10){
            $msg .= '<div class="alert alert-danger"> ATTENTION,<br> Vous devez obligatoirement écrire votre message avec 10 caractères minimum.</div>'; 
        }
    }


include 'inc/header.inc.php';
include 'inc/nav.inc.php';
?>

<main role="main" class="container">

  <div class="starter-template text-center marge_haute">
    <h1>Nous contacter</h1>
    <p class="lead"><?php echo $msg; ?></p>
  </div>
  <form method="post" action="">
        <div class="row">
            <div class="offset-3 col-6">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" name="nom" id="nom" class="form-control">
                </div>
                <div class="form-group">
                    <label for="prenom">Prenom</label>                        <input type="text" name="prenom" id="prenom" class="form-control">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="text" name="email" id="email" class="form-control">
                </div>
                <div class="form-group">
                    <label for="sujet">Sujet</label>
                    <select name="sujet" id="sujet" class="w-100">Sujet
                    <option>Location de salle</option>
                    <option>Factures</option>
                    <option>Service après Location</option>
                    <option>Mon espace personnel</option>
                    <option>Problèmes techniques du site</option>
                    <option>Données personnelles : Exercez vos droits</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
		            <textarea name="message" id="message" class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-info w-100">Envoyer</button>
                </div>
            </div>
        </div>
    </form>



</main><!-- /.container -->


<?php
include 'inc/footer.inc.php';
