<?php
include 'inc/init.inc.php';

// CODE
$id_membre = intval($_GET['id_membre']);
$confirmation_mail = $_GET['token'];

$verif_token = $pdo->prepare("SELECT * FROM membre WHERE id_membre = :id_membre AND confirmation_mail = :confirmation_mail");
$verif_token->bindParam(':id_membre', $id_membre, PDO::PARAM_STR);
$verif_token->bindParam(':confirmation_mail', $confirmation_mail, PDO::PARAM_STR);
$verif_token->execute();

$membre = $verif_token->FETCH(PDO::FETCH_ASSOC);

if(isset($membre) && $membre['confirmation_mail'] == $confirmation_mail){
    $maj_valid_mail = $pdo->prepare('UPDATE membre SET confirmation_mail = NULL, validation_compte = NOW() WHERE id_membre = :id_membre');
    $maj_valid_mail->bindParam(':id_membre', $id_membre, PDO::PARAM_STR);
    $maj_valid_mail->execute();
    header('location: connexion.php');
} else {
    $msg .= '<div class="alert alert-danger"> ATTENTION,<br> Le lien de validation n\'est pas correct ou valide. Merci de cliquer sur le lien envoyé dans votre boite mail pour activer votre compte.</div>';
}

include 'inc/header.inc.php';
include 'inc/nav.inc.php';
?>

<main role="main" class="container">

    <div class="starter-template text-center marge_haute">
      <h1>Confirmation de votre mail</h1>
      <p class="lead"><?php echo $msg; ?></p>
    </div>

</main>


<?php
include 'inc/footer.inc.php';
