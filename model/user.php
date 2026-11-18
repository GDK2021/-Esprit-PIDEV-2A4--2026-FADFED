<?php 

class user{
    private int $id;
    private string $nom; 
    private string $prenom;
    private int $age;
    private int $genre;
    private int $bloodtype;
    private int $role
    private int $email;
    private int $pseudo;
    private int $def;
    private int $gold;
    
    public function __construct(int $id, string $pseudo, string $mdp ){
        $this->id = $id;
        
    }
    public function __get($prop){
        return $this->$prop; 
    }

    public function __set($prop , $value){
        $this->$prop = $value; 
    }



}