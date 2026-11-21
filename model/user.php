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
        int $num_telephone
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->age = $age;
        $this->genre = $genre;
        $this->role = $role;
        $this->email = $email;
        $this->pseudo = $pseudo;
        $this->mdp = $mdp;
        $this->num_telephone = $num_telephone;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom(string $nom)
    {
        $this->nom = $nom;
    }

    public function getPrenom()
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom)
    {
        $this->prenom = $prenom;
    }

    public function getAge()
    {
        return $this->age;
    }

    public function setAge(int $age)
    {
        $this->age = $age;
    }

    public function getGenre()
    {
        return $this->genre;
    }

    public function setGenre(string $genre)
    {
        $this->genre = $genre;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    public function getMdp()
    {
        return $this->mdp;
    }

    public function setMdp(string $mdp)
    {
        $this->mdp = $mdp;
    }

    public function getNumTelephone()
    {
        return $this->num_telephone;
    }

    public function setNumTelephone(int $num_telephone)
    {
        $this->num_telephone = $num_telephone;
    }

    public function getPseudo()
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo)
    {
        $this->pseudo = $pseudo;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole(string $role)
    {
        $this->role = $role;
    }
}


class admin extends user
{
    private int $id_admin;
    private string $niveau_acces;

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
        int $id_admin,
        string $niveau_acces
    ) {
        $this->id_admin = $id_admin;
        $this->niveau_acces = $niveau_acces;
        parent::__construct($id, $nom, $prenom, $age, $genre, $role, $email, $pseudo, $mdp, $num_telephone);
    }

    public function getIdAdmin()
    {
        return $this->id_admin;
    }

    public function setIdAdmin(int $id_admin)
    {
        $this->id_admin = $id_admin;
    }

    public function getNiveauAcces()
    {
        return $this->niveau_acces;
    }

    public function setNiveauAcces(string $niveau_acces)
    {
        $this->niveau_acces = $niveau_acces;
    }
}


class pro extends user
{
    private int $id_pro;
    private string $specialite;
    private string $gouvernorat;
    private string $adresse_cabinet;
    private int $tarif;

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
        parent::__construct($id, $nom, $prenom, $age, $genre, $role, $email, $pseudo, $mdp, $num_telephone);
    }

    public function getIdPro()
    {
        return $this->id_pro;
    }

    public function setIdPro(int $id_pro)
    {
        $this->id_pro = $id_pro;
    }

    public function getSpecialite()
    {
        return $this->specialite;
    }

    public function setSpecialite(string $specialite)
    {
        $this->specialite = $specialite;
    }

    public function getGouvernorat()
    {
        return $this->gouvernorat;
    }

    public function setGouvernorat(string $gouvernorat)
    {
        $this->gouvernorat = $gouvernorat;
    }

    public function getAdresseCabinet()
    {
        return $this->adresse_cabinet;
    }

    public function setAdresseCabinet(string $adresse_cabinet)
    {
        $this->adresse_cabinet = $adresse_cabinet;
    }

    public function getTarif()
    {
        return $this->tarif;
    }

    public function setTarif(int $tarif)
    {
        $this->tarif = $tarif;
    }
}


class patient extends user
{
    private int $id_patient;
    private string $allergies;

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
        int $id_patient,
        string $allergies
    ) {
        $this->id_patient = $id_patient;
        $this->allergies = $allergies;
        parent::__construct($id, $nom, $prenom, $age, $genre, $role, $email, $pseudo, $mdp, $num_telephone);
    }

    public function getIdPatient()
    {
        return $this->id_patient;
    }

    public function setIdPatient(int $id_patient)
    {
        $this->id_patient = $id_patient;
    }

    public function getAllergies()
    {
        return $this->allergies;
    }

    public function setAllergies(string $allergies)
    {
        $this->allergies = $allergies;
    }
}