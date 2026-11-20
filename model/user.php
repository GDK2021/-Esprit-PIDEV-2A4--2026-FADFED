<?php

class user
{
    private int $id;
    private string $nom;
    private string $prenom;
    private int $age;
    private string $genre;
    private string $email;
    private string $mdp;
    private int $num_telephone;
    private string $pseudo;
    private string $role;
    private string $date_inscription;


    public function __construct(
        int $id,
        string $nom,
        string $prenom,
        int $age,
        string $genre,
        string $role,
        string $email,
        string $pseudo,
        string $mdp,
        int $num_telephone,
        string $date_inscription
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->age = $age;
        $this->genre = $genre;
        $this->email = $email;
        $this->pseudo = $pseudo;
        $this->role = $role;
        $this->mdp = $mdp;
        $this->num_telephone = $num_telephone;
        $this->date_inscription = $date_inscription;
    }

    public function __get($prop)
    {
        return $this->$prop;
    }

    public function __set($prop, $value)
    {
        $this->$prop = $value;
    }
}


class admin extends user
{
    protected int $id_admin;
    protected string $niveau_acces;

    public function __construct(
        int $id,
        string $nom,
        string $prenom,
        int $age,
        string $genre,
        string $role,
        string $email,
        string $pseudo,
        string $mdp,
        int $num_telephone,
        string $date_inscription,
        int $id_admin,
        string $niveau_acces
    ) {
        $this->niveau_acces = $niveau_acces;
        $this->id_admin = $id_admin;
        parent::__construct($id, $nom, $prenom, $age, $genre, $role, $email, $pseudo, $mdp, $num_telephone, $date_inscription);
    }
}


class pro extends user
{
    protected int $id_pro;
    protected string $specialite;
    protected string $gouvernorat;
    protected string $adresse_cabinet;
    protected int $tarif;

    public function __construct(
        int $id,
        string $nom,
        string $prenom,
        int $age,
        string $genre,
        string $role,
        string $email,
        string $pseudo,
        string $mdp,
        int $num_telephone,
        string $date_inscription,
        int $id_pro,
        string $specialite,
        string $gouvernorat,
        string $adresse_cabinet,
        int $tarif
    ) {
        $this->id_pro = $id_pro;
        $this->specialite = $specialite;
        $this->gouvernorat = $gouvernorat;
        $this->adresse_cabinet = $adresse_cabinet;
        $this->tarif = $tarif;
        parent::__construct($id, $nom, $prenom, $age, $genre, $role, $email, $pseudo, $mdp, $num_telephone, $date_inscription);
    }
}


class patient extends user
{
    protected int $id_patient;
    protected string $allergies;

    // Champs lus par UserC::ajouterPatient() mais absents du formulaire d'inscription
    protected ?string $contact_urgence = null;
    protected ?int $num_urgence = null;
    protected ?string $antecedents = null;

    public function __construct(
        int $id,
        string $nom,
        string $prenom,
        int $age,
        string $genre,
        string $role,
        string $email,
        string $pseudo,
        string $mdp,
        int $num_telephone,
        string $date_inscription,
        int $id_patient,
        string $allergies
    ) {
        $this->id_patient = $id_patient;
        $this->allergies = $allergies;
        parent::__construct($id, $nom, $prenom, $age, $genre, $role, $email, $pseudo, $mdp, $num_telephone, $date_inscription);
    }
}
