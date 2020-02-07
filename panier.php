<?php
include 'inc/init.inc.php';

// CODE
// Vider le panier avant la création pour qu'il soit recrée grâce à la fonction qui suit
if(isset($_GET['action']) && $_GET['action'] == 'vider'){
    unset($_SESSION['panier']);
}
// Payer le panier
if(isset($_GET['action']) && $_GET['action'] == 'payer' && !empty($_SESSION['panier']['titre'])){
    // enregistrement de la commande dans la table commande
    $nb_produit = sizeof($_SESSION['panier']['titre']);
    for($i = 0; $i < $nb_produit ; $i++){
        $commande = $pdo->prepare("INSERT INTO commande (id_membre, id_produit, date_enregistrement) VALUE (:id_membre, :id_produit, NOW())");
        $commande->bindParam(':id_membre', $_SESSION['membre']['id_membre'], PDO::PARAM_STR);
        $commande->bindParam(':id_produit', $_SESSION['panier']['id_produit'][$i], PDO::PARAM_STR);
        $commande->execute();
    }

    $id_commande = $pdo->lastInsertId();
    $etat = 'reservation';

    // Envoi de la confirmation de la commande par mail
    $email = $_SESSION['membre']['email'];
    $sujet_mail = "Confirmation de votre commande chez SalleA";
    $headers = "From: postmaster@johann-chaligne.fr\n";
    $headers .= "Reply-To: postmaster@johann-chaligne.fr\n";
    $headers .= 'Content-Type: text/html; charset="UTF-8"';
    $message_mail = '<div class="row">';
    $message_mail .= '<div class="offset-1 col-5">';
    $message_mail .= "<p>Entreprise SalleA</p>";
    $message_mail .= "<p>Adresse : 37 rue Saint-Sébastien</p>";
    $message_mail .= '<p>Code Postal - Ville : 75011 Paris</p>';
    $message_mail .= '<p>N° SIRET : 456 456 546 645 RCS PARIS</p>';
    $message_mail .= '</div>';
    $message_mail .= '<div class="col-6">';
    $message_mail .= '<p>Bonjour '.$_SESSION['membre']['nom'].' '.$_SESSION['membre']['prenom'].',';
    $message_mail .= '<p>Nous vous remercions de votre achat et nous vous confirmons votre commande n° '.$id_commande.'.</p>';
    $message_mail .= '<p>Un récapitulatif de votre commande sera accessible via votre profil dans "Vos commandes réalisée".</p>';
    $message_mail .= '<br>';
    $message_mail .= '<br>';
    $message_mail .= '<br>';
    $message_mail .= '<p>L\'équipe SalleA.</p>';
    $message_mail .= '</div>';
    $message_mail .= '</div>';
    mail($email, $sujet_mail, $message_mail, $headers);

    // Changement de l'état de la table produit
    if(!empty($id_commande)){
        for($i = 0; $i < $nb_produit ; $i++){
        $maj_etat_produit = $pdo->prepare("UPDATE produit SET etat = :etat WHERE id_produit = :id_produit");
        $maj_etat_produit->bindParam(':id_produit', $_SESSION['panier']['id_produit'][$i], PDO::PARAM_STR);
        $maj_etat_produit->bindParam(':etat', $etat, PDO::PARAM_STR);
        $maj_etat_produit->execute();
        }
    }

    unset($_SESSION['panier']);
    $msg .= '<div class="container pt-2 mt-4"><div class="row text-center"><div class="col-6 offset-3 alert alert own-alert">Commande enregistrée ! Son numéro est le '.$id_commande.'. Vous allez recevoir un mail de confirmation de votre commande.<br> Merci de nous avoir fait confiance.<br> Vos produits arrivent bientôt <i class="far fa-smile"></i></div></div></div>';
}

//// Création panier
creation_panier();

//// Ajouter au panier
if(!empty($_POST['id_produit']) && is_numeric($_POST['id_produit'])){

    // On récupère en BDD les informations de l'article à ajouter pour avoir son prix et son titre
    $infos_salle_produit = $pdo->prepare("SELECT *, date_format(date_arrivee, '%d/%m/%Y') AS date_arrivee, date_format(date_depart, '%d/%m/%Y') AS date_depart FROM salle, produit WHERE id_produit = :id_produit AND salle.id_salle = produit.id_salle");
    $infos_salle_produit->bindParam(':id_produit', $_POST['id_produit'], PDO::PARAM_STR);
    $infos_salle_produit->execute();

    if($infos_salle_produit->rowCount() > 0){
        $salle_produit = $infos_salle_produit->fetch(PDO::FETCH_ASSOC);
        $_SESSION['panier']['id_produit'][] = htmlentities($_POST['id_produit']);
        $_SESSION['panier']['date_arrivee'][] = $salle_produit['date_arrivee'];
        $_SESSION['panier']['date_depart'][] = $salle_produit['date_depart'];
        $_SESSION['panier']['prix'][] = $salle_produit['prix'];
        $_SESSION['panier']['titre'][] = $salle_produit['titre'];
        // Pour ne pas conserver les données de POST sur un F5, on charge la page sans POST
        header('location:'.$_SERVER['PHP_SELF']);
    }

}

include 'inc/header.inc.php';
include 'inc/nav.inc.php';
?>

<main class="container">

    <div class="starter-template text-center marge_haute">
        <h1>Panier</h1>
        <p class="lead"><?php echo $msg; ?></p>
    </div>
    <div class='row'>
        <div class="col-12">
            <?php
            if(!empty($_SESSION['panier']['titre'])){
                echo '<a href="?action=vider" class="btn btn-danger">Vider le panier</a><hr>';
            }
            ?>
            <table class="table table-bordered border border-warning">
                <thead class="thead-dark">
                    <tr>
                        <th>N°Produit</th>
                        <th>Titre</th>
                        <th>Date arrivée</th>
                        <th>Date départ</th>
                        <th>Prix TTC</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                    if(!empty($_SESSION['panier']['id_produit'])){
                        //si le panier est rempli
                        $montant_total = 0;
                        $nb_produit = sizeof($_SESSION['panier']['prix']);
                        $nb_produit = count($_SESSION['panier']['id_produit']);
                        for($i = 0; $i < $nb_produit ; $i++){
                            echo '<tr><td>'.$_SESSION['panier']['id_produit'][$i].'</td><td>'.'<span class="p-2 badge badge-pill badge-info w-100">'.$_SESSION['panier']['titre'][$i].'</span></td><td>'.$_SESSION['panier']['date_arrivee'][$i].'</td><td>'.$_SESSION['panier']['date_depart'][$i].'</td><td>'.$_SESSION['panier']['prix'][$i].' €</td></tr>';

                            // calcul du montant total du panier
                            $montant_total += ($_SESSION['panier']['prix'][$i]);

                        }
                        // On stocke le montant total dans la session
                        $_SESSION['panier']['montant_total'] = $montant_total;
                        echo '<tr>';
                        echo '<td colspan="3">';
                        // Affichage du d'un bouton (a href) payer si l'utilisateur est connecté. Sinon affichage d'un lien pour se connecter et d'un lien pour s'inscrire
                        if(user_is_connect()){
                            echo '<a href="?action=payer" class=" w-100 btn btn-insc">Payer</a>';
                        }else{
                            echo '<div class="text-center"><a href="connexion.php" class="mr-2 btn btn-warning">Se connecter</a>';
                            echo '<a href="inscription.php" class=" btn btn-warning">S\'inscrire</a></div>';
                        }
                        echo '</td><td colspan="2">';
                        echo '<div><b>Montant total TTC :</b><span class="float-right">'.htmlentities($montant_total).' €</span></div>';
                        echo '</td>';
                        echo '<tr>';
                    }else{
                        echo '<tr><td colspan="4">Votre panier est vide</td></tr>';
                    }
                ?>
                </tbody>    
            </table>
        </div>
    </div>
</main>


<?php
include 'inc/footer.inc.php';
