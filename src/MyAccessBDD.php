<?php
include_once("AccessBDD.php");

/**
 * Classe de construction des requêtes SQL
 * hérite de AccessBDD qui contient les requêtes de base
 * Pour ajouter une requête :
 * - créer la fonction qui crée une requête (prendre modèle sur les fonctions 
 *   existantes qui ne commencent pas par 'traitement')
 * - ajouter un 'case' dans un des switch des fonctions redéfinies 
 * - appeler la nouvelle fonction dans ce 'case'
 */
class MyAccessBDD extends AccessBDD {
	    
    /**
     * constructeur qui appelle celui de la classe mère
     */
    public function __construct(){
        try{
            parent::__construct();
        }catch(\Exception $e){
            throw $e;
        }
    }

    /**
     * demande de recherche
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return array|null tuples du résultat de la requête ou null si erreur
     * @override
     */	
    protected function traitementSelect(string $table, ?array $champs) : ?array{
        switch($table){  
            case "livre" :
                return $this->selectAllLivres();
            case "dvd" :
                return $this->selectAllDvd();
            case "revue" :
                return $this->selectAllRevues();
            case "exemplaire" :
                return $this->selectExemplairesRevue($champs);
            case "genre" :
            case "public" :
            case "rayon" :
            case "etat" :
                // select portant sur une table contenant juste id et libelle
                return $this->selectTableSimple($table);
            case "commandelivre":
                return $this->selectCommandesLivre($champs);
            case "suivi":
                return $this->selectTableSimple("suivi");
            case "commanderevue":
                return $this->selectCommandesRevue($champs);
            case "utilisateur":
                return $this->selectUtilisateur($champs);    
            case "" :
                // return $this->uneFonction(parametres);
            default:
                // cas général
                return $this->selectTuplesOneTable($table, $champs);
        }	
    }

    /**
     * demande d'ajout (insert)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples ajoutés ou null si erreur
     * @override
     */	
    protected function traitementInsert(string $table, ?array $champs) : ?int{
        switch($table){
            case "commandelivre":
                return $this->insertCommandeLivre($champs);
            case "commanderevue":
                return $this->insertCommandeRevue($champs);
            case "" :
                // return $this->uneFonction(parametres);
            default:                    
                // cas général
                return $this->insertOneTupleOneTable($table, $champs);	
        }
    }
    
    /**
     * demande de modification (update)
     * @param string $table
     * @param string|null $id
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples modifiés ou null si erreur
     * @override
     */	
    protected function traitementUpdate(string $table, ?string $id, ?array $champs) : ?int{
        switch($table){
            case "commandelivre":
                return $this->updateSuiviCommande($champs);
            case "commanderevue":
                return $this->updateAbonnement($champs);
            case "" :
                // return $this->uneFonction(parametres);
            default:                    
                // cas général
                return $this->updateOneTupleOneTable($table, $id, $champs);
        }	
    }  
    
    /**
     * demande de suppression (delete)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples supprimés ou null si erreur
     * @override
     */	
    protected function traitementDelete(string $table, ?array $champs) : ?int{
        switch($table){
            case "commandelivre":
                return $this->deleteCommandeLivre($champs);
            case "commanderevue":
                return $this->deleteCommandeRevue($champs);
            case "" :
                // return $this->uneFonction(parametres);
            default:                    
                // cas général
                return $this->deleteTuplesOneTable($table, $champs);	
        }
    }	    
        
    /**
     * récupère les tuples d'une seule table
     * @param string $table
     * @param array|null $champs
     * @return array|null 
     */
    private function selectTuplesOneTable(string $table, ?array $champs) : ?array{
        if(empty($champs)){
            // tous les tuples d'une table
            $requete = "select * from $table;";
            return $this->conn->queryBDD($requete);  
        }else{
            // tuples spécifiques d'une table
            $requete = "select * from $table where ";
            foreach ($champs as $key => $value){
                $requete .= "$key=:$key and ";
            }
            // (enlève le dernier and)
            $requete = substr($requete, 0, strlen($requete)-5);	          
            return $this->conn->queryBDD($requete, $champs);
        }
    }	

    /**
     * demande d'ajout (insert) d'un tuple dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples ajoutés (0 ou 1) ou null si erreur
     */	
    private function insertOneTupleOneTable(string $table, ?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // construction de la requête
        $requete = "insert into $table (";
        foreach ($champs as $key => $value){
            $requete .= "$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ") values (";
        foreach ($champs as $key => $value){
            $requete .= ":$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ");";
        return $this->conn->updateBDD($requete, $champs);
    }

    /**
     * demande de modification (update) d'un tuple dans une table
     * @param string $table
     * @param string\null $id
     * @param array|null $champs 
     * @return int|null nombre de tuples modifiés (0 ou 1) ou null si erreur
     */	
    private function updateOneTupleOneTable(string $table, ?string $id, ?array $champs) : ?int {
        if(empty($champs)){
            return null;
        }
        if(is_null($id)){
            return null;
        }
        // construction de la requête
        $requete = "update $table set ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);				
        $champs["id"] = $id;
        $requete .= " where id=:id;";		
        return $this->conn->updateBDD($requete, $champs);	        
    }
    
    /**
     * demande de suppression (delete) d'un ou plusieurs tuples dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples supprimés ou null si erreur
     */
    private function deleteTuplesOneTable(string $table, ?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // construction de la requête
        $requete = "delete from $table where ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key and ";
        }
        // (enlève le dernier and)
        $requete = substr($requete, 0, strlen($requete)-5);   
        return $this->conn->updateBDD($requete, $champs);	        
    }
 
    /**
     * récupère toutes les lignes d'une table simple (qui contient juste id et libelle)
     * @param string $table
     * @return array|null
     */
    private function selectTableSimple(string $table) : ?array{
        $requete = "select * from $table order by libelle;";		
        return $this->conn->queryBDD($requete);	    
    }
    
    /**
     * récupère toutes les lignes de la table Livre et les tables associées
     * @return array|null
     */
    private function selectAllLivres() : ?array{
        $requete = "Select l.id, l.ISBN, l.auteur, d.titre, d.image, l.collection, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from livre l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";		
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère toutes les lignes de la table DVD et les tables associées
     * @return array|null
     */
    private function selectAllDvd() : ?array{
        $requete = "Select l.id, l.duree, l.realisateur, d.titre, d.image, l.synopsis, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from dvd l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";	
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère toutes les lignes de la table Revue et les tables associées
     * @return array|null
     */
    private function selectAllRevues() : ?array{
        $requete = "Select l.id, l.periodicite, d.titre, d.image, l.delaiMiseADispo, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from revue l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère tous les exemplaires d'une revue
     * @param array|null $champs 
     * @return array|null
     */
    private function selectExemplairesRevue(?array $champs) : ?array{
        if(empty($champs)){
            return null;
        }
        if(!array_key_exists('id', $champs)){
            return null;
        }
        $champNecessaire['id'] = $champs['id'];
        $requete = "Select e.id, e.numero, e.dateAchat, e.photo, e.idEtat ";
        $requete .= "from exemplaire e join document d on e.id=d.id ";
        $requete .= "where e.id = :id ";
        $requete .= "order by e.dateAchat DESC";
        return $this->conn->queryBDD($requete, $champNecessaire);
    }
    
    /**
    * récupère toutes les commandes d'un livre avec le libellé du suivi
    * @param array|null $champs
    * @return array|null
    */
   private function selectCommandesLivre(?array $champs) : ?array {
    $requete  = "SELECT c.id, c.dateCommande, c.montant, cd.nbExemplaire, s.libelle as suivi, s.id as idSuivi, cd.idLivreDvd ";
    $requete .= "FROM commande c ";
    $requete .= "JOIN commandedocument cd ON c.id = cd.id ";
    $requete .= "JOIN suivi s ON cd.idSuivi = s.id ";
    if (!empty($champs) && array_key_exists('id', $champs) && !empty($champs['id'])) {
        $requete .= "WHERE cd.idLivreDvd = :id ";
        $requete .= "ORDER BY c.dateCommande DESC";
        return $this->conn->queryBDD($requete, ['id' => $champs['id']]);
    }
    $requete .= "ORDER BY c.dateCommande DESC";
    return $this->conn->queryBDD($requete);
}

    /**
     * insère une commande de livre (dans commande ET commandedocument)
     * @param array|null $champs
     * @return int|null
     */
    private function insertCommandeLivre(?array $champs) : ?int {
        if (empty($champs)) {
            return null;
        }
        $requeteCommande  = "INSERT INTO commande (id, dateCommande, montant) ";
        $requeteCommande .= "VALUES (:id, :dateCommande, :montant)";
        $resultat = $this->conn->updateBDD($requeteCommande, [
            'id'          => $champs['id'],
            'dateCommande'=> $champs['dateCommande'],
            'montant'     => $champs['montant']
        ]);
        if ($resultat === null || $resultat === 0) {
        return null;
        }
        $requeteCD  = "INSERT INTO commandedocument (id, nbExemplaire, idLivreDvd, idSuivi) ";
        $requeteCD .= "VALUES (:id, :nbExemplaire, :idLivreDvd, '00001')";
        return $this->conn->updateBDD($requeteCD, [
            'id'           => $champs['id'],
            'nbExemplaire' => $champs['nbExemplaire'],
            'idLivreDvd'   => $champs['idLivreDvd']
        ]);
    }

    /**
     * modifie le suivi d'une commande de livre
     * @param array|null $champs
     * @return int|null
     */
    private function updateSuiviCommande(?array $champs) : ?int {
        if (empty($champs) || !array_key_exists('id', $champs) || !array_key_exists('idSuivi', $champs)) {
            return null;
        }
        $requete  = "UPDATE commandedocument SET idSuivi = :idSuivi WHERE id = :id";
        return $this->conn->updateBDD($requete, [
            'id'      => $champs['id'],
            'idSuivi' => $champs['idSuivi']
        ]);
    }

    /**
     * supprime une commande de livre (dans commandedocument ET commande)
     * @param array|null $champs
     * @return int|null
     */
    private function deleteCommandeLivre(?array $champs) : ?int {
        if (empty($champs) || !array_key_exists('id', $champs)) {
            return null;
        }
        $requeteC = "DELETE FROM commande WHERE id = :id";
        return $this->conn->updateBDD($requeteC, ['id' => $champs['id']]);
    }
    
    /**
    * récupère toutes les commandes (abonnements) d'une revue
    */
   private function selectCommandesRevue(?array $champs) : ?array {
       $requete  = "SELECT c.id, c.dateCommande, c.montant, a.dateFinAbonnement, a.idRevue ";
       $requete .= "FROM commande c ";
       $requete .= "JOIN abonnement a ON c.id = a.id ";
       if (!empty($champs) && array_key_exists('id', $champs) && !empty($champs['id'])) {
           $requete .= "WHERE a.idRevue = :id ";
           $requete .= "ORDER BY c.dateCommande DESC";
           return $this->conn->queryBDD($requete, ['id' => $champs['id']]);
       }
       $requete .= "ORDER BY c.dateCommande DESC";
       return $this->conn->queryBDD($requete);
   }

   /**
    * insère une commande de revue (abonnement)
    */
   private function insertCommandeRevue(?array $champs) : ?int {
       if (empty($champs)) {
           return null;
       }
       $requeteCommande  = "INSERT INTO commande (id, dateCommande, montant) ";
       $requeteCommande .= "VALUES (:id, :dateCommande, :montant)";
       $resultat = $this->conn->updateBDD($requeteCommande, [
           'id'           => $champs['id'],
           'dateCommande' => $champs['dateCommande'],
           'montant'      => $champs['montant']
       ]);
       if ($resultat === null || $resultat === 0) {
           return null;
       }
       $requeteAbo  = "INSERT INTO abonnement (id, dateFinAbonnement, idRevue) ";
       $requeteAbo .= "VALUES (:id, :dateFinAbonnement, :idRevue)";
       return $this->conn->updateBDD($requeteAbo, [
           'id'                => $champs['id'],
           'dateFinAbonnement' => $champs['dateFinAbonnement'],
           'idRevue'           => $champs['idRevue']
       ]);
   }

   /**
    * modifie la date de fin d'abonnement (renouvellement)
    */
   private function updateAbonnement(?array $champs) : ?int {
       if (empty($champs) || !array_key_exists('id', $champs) || !array_key_exists('dateFinAbonnement', $champs)) {
           return null;
       }
       $requete = "UPDATE abonnement SET dateFinAbonnement = :dateFinAbonnement WHERE id = :id";
       return $this->conn->updateBDD($requete, [
           'id'                => $champs['id'],
           'dateFinAbonnement' => $champs['dateFinAbonnement']
       ]);
   }

   /**
    * supprime une commande de revue
    */
   private function deleteCommandeRevue(?array $champs) : ?int {
       if (empty($champs) || !array_key_exists('id', $champs)) {
           return null;
       }
       $requeteAbo = "DELETE FROM abonnement WHERE id = :id";
       $this->conn->updateBDD($requeteAbo, ['id' => $champs['id']]);
       $requeteC = "DELETE FROM commande WHERE id = :id";
       return $this->conn->updateBDD($requeteC, ['id' => $champs['id']]);
   }
   /**
    * vérifie un utilisateur et retourne son service
    */
   private function selectUtilisateur(?array $champs) : ?array {
       if (empty($champs) || !array_key_exists('login', $champs) || !array_key_exists('pwd', $champs)) {
           return null;
       }
       $requete  = "SELECT u.id, u.login, s.id as idService, s.libelle as service ";
       $requete .= "FROM utilisateur u ";
       $requete .= "JOIN service s ON u.idService = s.id ";
       $requete .= "WHERE u.login = :login AND u.pwd = :pwd";
       return $this->conn->queryBDD($requete, [
           'login' => $champs['login'],
           'pwd'   => $champs['pwd']
       ]);
   }
    
    
}
