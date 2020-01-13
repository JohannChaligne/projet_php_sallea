<?php 

// connexion BDD
$host = 'mysql:host=sql25;dbname=wjn72161';
$login = '';
$mdp = '';
$options = array(
	PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING, // pour la gestion des erreurs
	PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'); // pour utf8

$pdo = new PDO($host, $login, $mdp, $options);

// Déclaration de $msg pour afficher des messages utilisateurs
$msg = '';

// Ouverture de la session
session_start();

// Déclaration de constante
define('URL', 'http://localhost/php/projet_back_end/'); // Il faudra faire attention lorsque le site sera en ligne car cela va être modifié. 

// Constante contenant la racine serveur.
define('SERVER_ROOT', $_SERVER['DOCUMENT_ROOT']);

// Constante chemin du dossier photo depuis la racine serveur
define('ROOT_URL', '/php/projet_back_end/');

include 'fonction.inc.php';