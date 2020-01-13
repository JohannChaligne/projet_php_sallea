<?php 

// fonction pour savoir si l'utilisateur est connecté
function user_is_connect() {
	if(isset($_SESSION['membre'])) {
		return true;
	}
	return false; // return permet de sortir de la fonction. Du coup, pas besoin du else dans ce cas.
}

// fonction pour savoir si l'utilisateur est admin
function user_is_admin(){
	// pour récupérer l'information de la session, il faut que l'utilisateur soit connecté. On va utiliser la fonction definie juste au-dessus. 
	// De plus, on veut savoir si le statut du membre équivaut à 2 puisque le statut de l'administrateur a cette équivalence. donc on va tester si le statut de la sssion ouverte par l'utilisateur équivaut à 2. Si oui, on sort de la fonction directement par return. Si non, on sort de la fonction par le return false.
	if(user_is_connect() && $_SESSION['membre']['statut'] == 2){
		return true;
	}
	return false;
}

// création du panier
function creation_panier() {
	if(!isset($_SESSION['panier'])) {
		// si le panier n'existe pas dans session, on le crée, sinon rien.
		$_SESSION['panier'] = array();
		$_SESSION['panier']['id_produit'] = array();
		$_SESSION['panier']['prix'] = array();
		$_SESSION['panier']['date_arrivee'] = array();
		$_SESSION['panier']['date_depart'] = array();
		$_SESSION['panier']['titre'] = array();
	}
}

// Création d'un token pour confirmer l'adresse mail et valider le compte
function str_random($lenght){
	$chaine = "0123456789azertyuiopmlkjhgfdsqwxcvbnAZERTYUIOPQSDFGHJKLMNBVCXW";
	return substr(str_shuffle(str_repeat($chaine, $lenght)), 0, $lenght);
}

function afficheretoile($etoile) {
	$nb_etoiles = 5;
	$note_etoile = '';
	if($etoile == 0){
		$note_etoile .= 'Non noté';
	}
	for ($i=0; $i < $nb_etoiles; $i++) {
		if($etoile>$i && $etoile<$i+1){
		$note_etoile .= '<i class="fas fa-star-half"></i>';
	  }
		elseif($etoile>$i){
		$note_etoile .= '<i class="fas fa-star"></i>';
	  }
	}
		return $note_etoile;
  }