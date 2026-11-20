<?php 

class user{
    private int $id_user;
    private string $nom; 
    private string $prenom;
    private int $age;
    private string $genre;
    private string $email;
    private string $pseudo;
    private string $bloodtype;
    private string $role;
    
    
    
    public function __construct( int $id , string $nom  , string $prenom , int $age , string $genre , string $bloodtype , string $role , string $email , string $pseudo ){
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->age = $age;
        $this->genre = $genre;
        $this->email = $email;
        $this->pseudo = $pseudo;
        $this->bloodtype = $bloodtype;
        $this->role = $role;
        
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
     private string $login;
     private string $mdp;
     private string 


}