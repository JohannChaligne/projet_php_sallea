<?php

// CODE
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-info fixed-top">
  <a class="navbar-brand" href="#">LOGO du site</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample05" aria-controls="navbarsExample05" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarsExample05">
    <ul class="navbar-nav mr-auto">
    <li class="nav-item active">
        <a class="nav-link text-center" href="<?php echo URL;?>index.php">Home <span class="sr-only">(current)</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-center" href="<?php echo URL;?>location_salle.php">Louer une salle</a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-center" href="<?php echo URL;?>societe.php">Notre société</a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-center" href="<?php echo URL;?>contact.php">Contact</a>
      </li>
      <?php if(user_is_admin()){?>
      <li class="nav-item">
          <a class="nav-link text-center" href="<?php echo URL;?>admin/gestion_salle.php">Dashboard</a>
      </li>
      <?php } ?>
    </ul>
    <form class="form-inline my-2 my-md-0">
      <ul class="navbar-nav mr-auto">
        <li class="nav-item">
          <a class="nav-link text-center" href="<?php echo URL;?>panier.php">Votre panier</a>
        </li>
        <?php if(!user_is_connect()){ ?>
        <li class="nav-item dropdown no-arrow">
          <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Se connecter
          </a>
          <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
            <a class="dropdown-item" href="inscription.php">Inscription</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="connexion.php">Connexion</a>
          </div>
        </li>
        <?php } else { ?>
        <li class="nav-item dropdown no-arrow">
          <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Votre compte
          </a>
          <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
            <a class="dropdown-item" href="profil.php">Profil</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="<?php echo URL;?>connexion.php?action=deconnexion">Déconnexion</a>
          </div>
        </li>
        </li>
        <?php } ?>
      </ul>
    </form>
  </div>
</nav>


