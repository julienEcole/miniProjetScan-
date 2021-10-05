<?php
    session_start();
    include("class/nettoyage.php");
    // V�rifie qu'il provient d'un formulaire
    if (!isset($_SERVER["REQUEST_METHOD"])) {
        
  
        try{ //on essaie de se cr�er l'objet nettoyage qu'on utiliseras plus tard
            $monNettoyage = new nettoyage("172.20.21.5","protabase","docteur","peste");
        }
        catch(Exception $e){     //exeption inconnu
            header("Location: ../index.php?error=inconnu");
        }
        catch(PDOException $e){  //un des input donn�s par l'utilisateur est incorrect
            header("Location: ../index.php?error=argumentInvalide");
        }
        if(isset($monNettoyage)){  //dans ce cas la tout est bien dans le meilleurs des mondes
            $_SESSION["serveur"] = "172.20.21.5";
            $_SESSION["database"] = "protabase";
            $_SESSION["user"] = "docteur";
            $_SESSION["password"] = "peste";
            unset($monNettoyage);
            header("Location: ../index.php?ok=connexionReussi");
        }
        else{
            header("Location: ../index.php?error=argumentInvalide");
        }
    }
?>