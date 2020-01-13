<?php if(user_is_admin()){?>
<nav class="navbar navbar-expand navbar-dark bg-dark static-top">

<a class="navbar-brand mr-1" href="index.html">Start Bootstrap</a>

<button class="btn btn-link btn-sm text-white order-1 order-sm-0" id="sidebarToggle" href="#">
  <i class="fas fa-bars"></i>
</button>

<!-- Navbar Search -->
<form class="d-none d-md-inline-block form-inline ml-auto mr-0 mr-md-3 my-2 my-md-0">
  <div class="input-group">
    <input type="text" class="form-control" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
    <div class="input-group-append">
      <button class="btn btn-primary" type="button">
        <i class="fas fa-search"></i>
      </button>
    </div>
  </div>
</form>

<!-- Navbar -->
<ul class="navbar-nav ml-auto ml-md-0">
  <li class="nav-item dropdown no-arrow">
    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      <i class="fas fa-user-circle fa-fw"></i>
    </a>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
      <a class="dropdown-item" href="../profil.php">Frontboard</a>
      <div class="dropdown-divider"></div>
      <a class="dropdown-item" href="../connexion.php?action=deconnexion" data-toggle="modal" data-target="#logoutModal">Déconnexion</a>
    </div>
  </li>
</ul>

</nav>

<div id="wrapper">

<!-- Sidebar -->
<ul class="sidebar navbar-nav">
  <li class="nav-item">
    <a class="nav-link" href="index.html">
      <i class="fas fa-fw fa-tachometer-alt"></i>
      <span>Dashboard</span>
    </a>
  </li>
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      <i class="fas fa-fw fa-folder"></i>
      <span>Gestion Admin</span>
    </a>
    <div class="dropdown-menu" aria-labelledby="pagesDropdown">
      <h6 class="dropdown-header">Gestion boutique :</h6>
      <a class="dropdown-item" href="gestion_salle.php">Gestion Salles</a>
      <a class="dropdown-item" href="gestion_produit.php">Gestion Produits</a>
      <a class="dropdown-item" href="gestion_commande.php">Gestion Commandes</a>
      <a class="dropdown-item" href="gestion_avis.php">Gestion Avis</a>
      <div class="dropdown-divider"></div>
      <h6 class="dropdown-header">Gestion utilisateurs :</h6>
      <a class="dropdown-item" href="gestion_membre.php">Gestion Membres</a>
    </div>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="statistique.php">
      <i class="fas fa-fw fa-chart-area"></i>
      <span>Statistiques</span></a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="tables.html">
      <i class="fas fa-fw fa-table"></i>
      <span>Tables</span></a>
  </li>
</ul>


<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
<i class="fas fa-angle-up"></i>
</a>

<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
<div class="modal-dialog" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel">Êtes-vous de vouloir vous deconnecter ?</h5>
      <button class="close" type="button" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
      </button>
    </div>
    <div class="modal-body">Sélectionnez "Déconnexion" ci-dessous si vous êtes prêt à terminer votre session en cours.</div>
    <div class="modal-footer">
      <button class="btn btn-secondary" type="button" data-dismiss="modal">Annuler</button>
      <a class="btn btn-primary" href="../connexion.php?action=deconnexion">Déconnexion</a>
    </div>
  </div>
</div>
</div>
<?php } ?>