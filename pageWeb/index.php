<html>
    <?php 
        session_start(); 
    ?>
    <head>  
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" href="includes/miseEnForme/style.css" type="text/css"/>
        <title>Formulaire de connexion PHP/MySQL</title>
    </head>
    <nav>
        <a href="index.php"></a>
        <!-- TODO -->
    </nav>
    <body>
        <?php 
            if(isset($_GET['error'])){
                switch($_GET['error']){
                    case "argumentInvalide" :
                        echo("<script> alert('la BDD n\'existe plus ou a été mis hors service'); </script>");
                        break;
                    case "inconnu" :
                        echo("<script> alert('Une erreur inconnu s\'est produite veuillez retenter l\'operation ou appeler le responsable si le probleme persiste.'); </script>");
                        break;
                    default :
                        echo("<script> alert('Une erreur inconnu s\'est produite'); </script>");
                        break;
                }
            }
        ?>
        <h1 id="titreConnexion">Connexion</h1>
        <form method="post" action="includes/connexion.php">
            <?php
                if(!isset($_SESSION['serveur']) || !isset($_SESSION['database']) || !isset($_SESSION['user'])){
                    header("Location : includes/connexion.php")
                }
                else{
                    echo("<input type=\"submit\" value=\"Connexion\" />");
                }
            ?>
        </form>
    </body>
</html>