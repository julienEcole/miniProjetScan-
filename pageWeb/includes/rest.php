<?php 
    require("class/nettoyage.php");
    session_start();
    if(isset($_SESSION['serveur']) && isset($_SESSION['database']) && isset($_SESSION['user'])){
        nettoyage $monNettoyage($_SESSION['nom'],$_SESSION['nom'],$_SESSION['nom'],$_SESSION['nom']);        //a modifier avec les bonne session
        if(isset($_SERVER["PATH_INFO"])){
            $donnesARecup = file_get_contents("php://input");
            $donnesARecup = json_decode($donnesARecup);                                                     //a ce Jayson toujours le 1er a encoder
            switch($_SERVEUR["REQUEST_METHODE"]){
                case ("get"):
                    $request_path = $_SERVER["PATH_INFO"];
					$request_data = explode("/",$request_path);
                    switch ($request_data[1]) {
                        case("client"):
                            $SQLCommand = "SELECT DISTINCT client.clientPrenom,client.clientNom,client.clientAdresse,client.clientIdImg FROM protabase.client WHERE client.ClientCode = ?";
                            $monNettoyage->_bdd->_SQLPointer->preparer($SQLCommand);                                                                                                        //a executer plus tard
                            break;
                        default :
                            echo("La Table a laquel vous tentez de vous connecter n'est pas publique");
                            break;
                    break;
                case ("post"):
                    $SQLCommand = "INSERT INTO client () VALUE ()";
                    break;
                case ("put"):
                    //TODO
                    break;
                case ("delete"):
                    //TODO
                    break;
                default:
                    //ERROR
            }          
        }
        else{
            echo("la methode get necessite quelque chose dans l'URL, sans quoi il est innutile de l'uiliser <br />");
        }    
    }
    else{
        echo("an error has occur aboard mission <br />");
    }
    
?>