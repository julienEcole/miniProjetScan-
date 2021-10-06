Utiliser un lecteur de code bar/QR code pour avoir uin visuel sur les différents traitements du
patient.

Dans un premier temps il faut finir la création de la BDD (diagrame + toutes les talbes fonctionnel et possiblement vide)

Dans un second temps commencer a développer la récupération des infos utile, l'affichage des différetes infos 

la commande SQL pour récup info client :
    SELECT DISTINCT client.clientPrenom,client.clientNom,client.clientAdresse,client.clientIdImg FROM protabase.client WHERE client.ClientCode = $mavariable;
la commande SQL pour recup info connexion :
    SELECT compteType FROM compte WHERE mdp = $mdp AND (pseudo = $pseudo OR email = $pseudo);
