<?php
include '../inc/init.inc.php';

if(!user_is_admin()){
  header("location:../connexion.php");
  exit();
}
// CODE







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
      <li class="breadcrumb-item active">Blank Page</li>
    </ol>

    <!-- Page Content -->
    <h1>Blank Page</h1>
    <hr>
    <div class="starter-template text-center">
        <p class="lead"><?php echo $msg; // variable destinée à afficher des messages utilisateurs ?>Lorem ipsum</p>
        <a href="?action=enregistrement" class="btn btn-outline-primary">Enregistrement salle</a>
        <a href="?action=affichage" class="btn btn-outline-danger">Affichage des salles</a>
    </div>

  </div>
  <!-- /.container-fluid -->
</div>
<!-- /.content-wrapper -->




<?php
include 'inc/footer_admin.inc.php';


