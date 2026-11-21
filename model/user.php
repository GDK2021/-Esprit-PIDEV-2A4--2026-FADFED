<?php 

class user{
    private int $id_user;
    private string $nom; 
    private string $prenom;
    private int $age;
    private string $genre;
    private string $email;
    private string $login;
    private string $mdp;
    private int $num_telephone;
    private string $pseudo;
    private string $bloodtype;
    private string $role;
    private string $date_inscription;
    
    
    
    public function __construct( 
                                    int $id_user ,
                                    string $nom  ,
                                    string $prenom ,
                                    int $age ,
                                    string $genre ,
                                    string $bloodtype ,
                                    string $role ,
                                    string $email ,
                                    string $pseudo ,
                                    string $login ,
                                    string $mdp ,
                                    int $num_telephone ,
                                    string $date_inscription 
                                    )
    {
        $this->id_user = $id_user;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->age = $age;
        $this->genre = $genre;
        $this->email = $email;
        $this->pseudo = $pseudo;
        $this->bloodtype = $bloodtype;
        $this->role = $role;
        $this->login = $login;
        $this->mdp = $mdp;
        $this->num_telephone = $num_telephone;
        $this->date_inscription = $date_inscription;

        
    }
    public function __get($prop){
        return $this->$prop; 
    }

    public function __set($prop , $value){
        $this->$prop = $value; 
    }



}


class admin extends user{
     private int $id_admin;
     private string  $niveau_acces;

     public function __construct( 
                                    int $id_user ,
                                    string $nom  ,
                                    string $prenom ,
                                    int $age ,
                                    string $genre ,
                                    string $bloodtype ,
                                    string $role ,
                                    string $email ,
                                    string $pseudo ,
                                    string $login ,
                                    string $mdp ,
                                    int $num_telephone ,
                                    string $date_inscription ,
                                    int $id_admin ,
                                    string  $niveau_acces
)
    {

        $this->niveau_acces = $niveau_acces;
        $this->id_admin = $id_admin;
        parent::__construct($id_user , $nom  , $prenom , $age , $genre , $bloodtype , $role , $email , $pseudo );
    }

}


class pro extends user{
     private int $id_pro;
     private string $specialite;
     private string  $gouvernorat;
     private string  $adresse_cabinet;
     private int $tarif;


     public function __construct( 
                                    int $id_user ,
                                    string $nom  ,
                                    string $prenom ,
                                    int $age ,
                                    string $genre ,
                                    string $bloodtype ,
                                    string $role ,
                                    string $email ,
                                    string $pseudo ,
                                    string $login ,
                                    string $mdp ,
                                    int $num_telephone ,
                                    string $date_inscription ,
                                    int $id_pro ,
                                    string  $specialite ,
                                    string  $gouvernorat,
                                    string  $adresse_cabinet,
                                    int $tarif
)
    {

        $this->id_pro = $id_pro;
        $this->specialite = $specialite;
        $this->gouvernorat = $gouvernorat;
        $this->adresse_cabinet = $adresse_cabinet;
        $this->tarif = $tarif;
        parent::__construct($id_user , $nom  , $prenom , $age , $genre , $bloodtype , $role , $email , $pseudo );
    }

}