<?php

	include ("HFSQL.php");

	class nettoyage{
		private $_bdd = NULL;		//mon HFSQL
		private $_tables = array();	//la liste contenant toutes mes tables de façon ordonné array(nomTable => array('tableMere' => nomTableMere, nomClefPrimaire => Id, nbLignes => 0), laSuite)
		private $_idCompany = "";	//l'ID de la company que je vais traiter plus tard

		public function __construct($Serveur,$Bdd = "test",$Utilisateur = "root",$mdp = ""){
			$this->_bdd = new HFSQL($Serveur,$Bdd,$Utilisateur,$mdp);
		}

		public function AjouterTables($tables,$tableMere = "Company",$nomClefPrimaire = "Id"){ //changer Company par la table principale a laquel toutes les autres sont reliés, a voir si  possible de le mettre dans setter
			if((!isset($this->_tables[$tables]))){
				$this->_tables[$tables] = array("tableMere" => $tableMere,"nomClefPrimaire" => $nomClefPrimaire, "nbLignes" => 0);
			}
			else{
				foreach($tables as $value){
					if(!isset($this->_tables[$value])){
						$this->_tables[$value] = array("tableMere" => $tableMere,"nomClefPrimaire" => $nomClefPrimaire, "nbLignes" => 0);
					}
				}
			}
		}

		public function __get($value){	//a voir si on doit garder, permet de voir les donnés privés sas les modifier (voir __set pour modif)
			return $this->$value;
		}

		public function __set($name,$value){//mon setter de mes différents attributs
			switch ($name) {
				case '_idCompany':	//opérationnel, peut être a changer car pas le bon procédé mais résultat équivalent
					$this->_idCompany = $this->getCompany($value); 
					break;
				default:
					echo("l'attribut que vous tentez de modifier est innacessible pour vous, vous n'avez pas les droits <br />");
			}
		}

		public function getCompany($nom){
			$result = $this->executerCommande("SELECT Id from Company WHERE name = '".$nom."'");
			$this->_idCompany = $result;
			return $result;
		}

		private function executerCommande($sqlCommande){ //sert a optimiser les applications de commandes unique, ne marche pas si il n'y a pas de retour ou une liste de retour
			try{
				$this->_bdd->preparer($sqlCommande);
				$this->_bdd->_ressource->execute();
			}
			catch(PDOException $e){
				echo ("Erreur : ".$e->getMessage()."<br />");
			}
			catch(Exeption $e){
				echo ("Erreur : ".$e->getMessage()."<br />");
			}

			$result = $this->_bdd->_ressource->fetch();
			return ($result[0]);
		}

		private function resetNbLigne(){// je l'utilise dans plusieurs fonctions
			foreach($this->_tables as $tableActuel){
				$tableActuel['nbLignes'] = 0;
			}
		}

		private function executerCommandeSansReturn($sqlCommande){ //operationnel, voir si optimisable avec executerCommande
			try{
				$this->_bdd->preparer($sqlCommande);
				$this->_bdd->_ressource->execute();
			}
			catch(PDOException $e){
				echo ("Erreur e : ".$e->getMessage()."<br />");
				$this->_bdd->_SQLPointer->rollBack();
				echo("suppression annulle <br/>");
			}
			catch(Exeption $e){
				echo ("Erreur e : ".$e->getMessage()."<br />");
				$this->_bdd->_SQLPointer->rollBack();
				echo("suppression annulle <br/>");
			}
			//echo("suppression effectue");
		}

		public function getNbLignes(){	//operationnel marche fortement similaire avec la suivante optimisation possible
			$result = array();
			$this->resetNbLigne();

			foreach($this->_tables as $nomTable => $value){
				if($value['tableMere'] != "Company"){
					$sqlCommande = "SELECT COUNT(*)
						FROM ". 
						$this->_bdd->_nomBdd.".dbo.$nomTable, ".
						$this->_bdd->_nomBdd.".dbo.".$value['tableMere'].
						" WHERE  ".
						"$nomTable.".$value['nomClefPrimaire']." = ".$value['tableMere'].".Id".
						" and ".
						$value['tableMere'].".Company = '".$this->_idCompany."'";//commande indirect
				}
				else{
					$sqlCommande = "SELECT COUNT(*) FROM ".	
						$this->_bdd->_nomBdd.".dbo.$nomTable
						WHERE ".$this->_bdd->_nomBdd.".dbo.$nomTable.Company = '$this->_idCompany'";//commande direct opérationnel
				}
				
				$result[$nomTable] = $this->executerCommande($sqlCommande);
				$this->_tables[$nomTable]["nbLignes"] = $result[$nomTable];
			}
			return $result; //renverra un tableau de la forme array($nomDeLaTable => $nombreDeLigne)
		}

		public function getNbLignesRemoved($removed = 1){	//operationnel fortement similaire avec la précedente optimisation possible
			$result = array();
			$this->resetNbLigne();
			foreach($this->_tables as $nomTable => $value){	//ajouter condition pour savoir si on a table company qui ne doit pas être regardé mais seras dans la liste
				
				if($value['tableMere'] != "Company"){
					$sqlCommande = "SELECT COUNT(*)
						FROM ". 
						$this->_bdd->_nomBdd.".dbo.$nomTable, ".
						$this->_bdd->_nomBdd.".dbo.".$value['tableMere'].
						" WHERE  ".
						"$nomTable.".$value['nomClefPrimaire']." = ".$value['tableMere'].".Id".
						" and ".
						$value['tableMere'].".Company = '".$this->_idCompany."' and ".
						$this->_bdd->_nomBdd.".dbo.$nomTable.Removed = $removed";//commande indirect
				}
				else{
					$sqlCommande = "SELECT COUNT(*) FROM ".
						$this->_bdd->_nomBdd.".dbo.$nomTable
						WHERE ".$this->_bdd->_nomBdd.".dbo.$nomTable.Company = '$this->_idCompany' and ".
						$this->_bdd->_nomBdd.".dbo.$nomTable.Removed = $removed";//commande direct opérationnel
				}
				
				$result[$nomTable] = $this->executerCommande($sqlCommande);
				$this->_tables[$nomTable]["nbLignes"] = $result[$nomTable];
			}
			return $result;	//renverra un tableau de la forme array($nomDeLaTable => $nombreDeLigneAvecRemoved,...)
		}

		public function suppLignes($removed = 1){// TODO quand je ferai le formulaire de dévalidaion il faudras modifier cet fonction
			$result = $this->getNbLignesRemoved($removed);
			$this->_bdd->_SQLPointer->beginTransaction();		//jusque la la fonction semble sûr
			
			
			
			foreach($this->_tables as $nomTable => $value){
				
				if($nomTable == "Company"){
					$sqlCommande = "UPDATE Company SET removed = 1 WHERE Id = '$this->_idCompany'";
				}else{
					if($value['tableMere'] != "Company"){//TODO
						$sqlCommande = "DELETE FROM ". 
							$this->_bdd->_nomBdd.".dbo.$nomTable".
							" WHERE  ".
							"$nomTable.".$value['nomClefPrimaire']." in (SELECT ".$value['tableMere'].".Id FROM ".$value['tableMere'].")".
							" and '".
							$this->_idCompany."' in (SELECT ".$value['tableMere'].".Company FROM ".$value['tableMere'].") and ".
							$this->_bdd->_nomBdd.".dbo.$nomTable.Removed = $removed";//commande indirect
					}
					else{
						$sqlCommande = "DELETE FROM ".
							$this->_bdd->_nomBdd.".dbo.$nomTable
							WHERE ".$this->_bdd->_nomBdd.".dbo.$nomTable.Company = '$this->_idCompany' and ".
							$this->_bdd->_nomBdd.".dbo.$nomTable.Removed = $removed";//commande direct opérationnel
					}
					//echo("$sqlCommande <br/>");
				}
				
				//commande SQL construite
				//echo("$sqlCommande <br/>");
				
				$this->executerCommandeSansReturn($sqlCommande);				//la partie suppression pour une valeur					
			}
			
			$this->_bdd->_SQLPointer->commit();
			return $result;					//array ($nomTables => $nbLignesSuppriméParLOperation,...	)
		}

		public function suppTout(){// TODO quand je ferai le formulaire de dévalidaion il faudras modifier cet fonction
			$result = $this->getNbLignes();
			$this->_bdd->_SQLPointer->beginTransaction();		//jusque la la fonction semble sûr
			
			
			
			foreach($this->_tables as $nomTable => $value){
				
				if($nomTable == "Company"){
					$sqlCommande = "UPDATE Company SET removed = 1 WHERE Id = '$this->_idCompany'";
				}else{
					if($value['tableMere'] != "Company"){//TODO
						$sqlCommande = "DELETE FROM ". 
							$this->_bdd->_nomBdd.".dbo.$nomTable".
							" WHERE  ".
							"$nomTable.".$value['nomClefPrimaire']." in (SELECT ".$value['tableMere'].".Id FROM ".$value['tableMere'].")".
							" and '".
							$this->_idCompany."' in (SELECT ".$value['tableMere'].".Company FROM ".$value['tableMere'].")";//commande indirect
					}
					else{
						$sqlCommande = "DELETE FROM ".
							$this->_bdd->_nomBdd.".dbo.$nomTable
							WHERE ".$this->_bdd->_nomBdd.".dbo.$nomTable.Company = '$this->_idCompany'";//commande direct opérationnel
					}
					//echo("$sqlCommande <br/>");
				}
				
				$this->executerCommandeSansReturn($sqlCommande);				//la partie suppression pour une valeur					
			}
			
			$this->_bdd->_SQLPointer->commit();
			return $result;					//array ($nomTables => $nbLignesSuppriméParLOperation,...	)
		}

		public function afficheTableau(){	//opérationnel
			echo("<table>");
			echo("<tr> <td>Nom de la table </td>
				<td> Nom de la table Mere </td>
				<td>Nombre de ligne affecte par la commande </td> </tr>");
			foreach($this->_tables as $key => $value){
				if($value["nbLignes"] != 0){
					echo("<tr> <td> $key </td>
						<td> ".$value["tableMere"]." </td>
						<td> ".$value["nbLignes"]."</td> </tr>");
				}
			}
			echo("</table> <br />");
		}
	}
?>