<?php

require_once __DIR__ .  '/../config.php';
require_once __DIR__ .  '/../model/User.php';

class UserC
{
  


    public function ajouterUser(user $u)
    {
        $db = Config::getConnexion();

        $req = $db->prepare(
            "INSERT INTO user
            (nom, prenom, age, genre, role, email, pseudo,
             mdp, num_telephone)
            VALUES
            (:nom, :prenom, :age, :genre, :role, :email,
             :pseudo, :mdp, :telephone)"
        );

        $req->execute([
            'nom' => $u->getNom(),
            'prenom' => $u->getPrenom(),
            'age' => $u->getAge(),
            'genre' => $u->getGenre(),
            'role' => $u->getRole(),
            'email' => $u->getEmail(),
            'pseudo' => $u->getPseudo(),
            'mdp' => password_hash($u->getMdp(), PASSWORD_DEFAULT),
            'telephone' => $u->getNumTelephone()
        ]);

        $u->setId((int) $db->lastInsertId());

        return $u->getId();
    }


    public function ajouterPatient(patient $p)
    {
        $db = Config::getConnexion();

        $req = $db->prepare(
            "INSERT INTO patient
            (id, allergies)
            VALUES
            (:id, :allergies)"
        );

        $req->execute([
            'id' => $p->getId(),
            'allergies' => $p->getAllergies()
        ]);
    }


    public function ajouterPro(pro $p)
    {
        $db = Config::getConnexion();

        $req = $db->prepare(
            "INSERT INTO pro
            (id, specialite, gouvernorat, adresse_cabinet, tarif)
            VALUES
            (:id, :specialite, :gouvernorat, :adresse, :tarif)"
        );

        $req->execute([
            'id' => $p->getId(),
            'specialite' => $p->getSpecialite(),
            'gouvernorat' => $p->getGouvernorat(),
            'adresse' => $p->getAdresseCabinet(),
            'tarif' => $p->getTarif()
        ]);
    }


    public function ajouterAdmin(admin $a)
    {
        $db = Config::getConnexion();

        $req = $db->prepare(
            "INSERT INTO admin
            (id, niveau_acces)
            VALUES
            (:id, :niveau)"
        );

        $req->execute([
            'id' => $a->getId(),
            'niveau' => $a->getNiveauAcces()
        ]);
    }


    public function inscription(user $u)
    {

        $db = Config::getConnexion();

        try {
            $db->beginTransaction();

            $this->ajouterUser($u);

            if ($u instanceof patient) {
                $this->ajouterPatient($u);
            }
            elseif ($u instanceof pro) {
                $this->ajouterPro($u);
            }
            elseif ($u instanceof admin) {
                $this->ajouterAdmin($u);
            }

            $db->commit();

            return true;

        } catch (Exception $e) {

            if ($db->inTransaction()) {
                $db->rollBack();
            }

            return "Erreur : " . $e->getMessage();
        }
    }
}
?>