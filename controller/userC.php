
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
            'nom' => $u->nom,
            'prenom' => $u->prenom,
            'age' => $u->age,
            'genre' => $u->genre,
            'role' => $u->role,
            'email' => $u->email,
            'pseudo' => $u->pseudo,
            'mdp' => password_hash($u->mdp, PASSWORD_DEFAULT),
            'telephone' => $u->num_telephone
        ]);

        $u->id = $db->lastInsertId();

        return $u->id;
    }


    public function ajouterPatient(patient $p)
    {
        $db = Config::getConnexion();

        $req = $db->prepare(
            "INSERT INTO patient
            (id, contact_urgence, num_urgence, antecedents, allergies)
            VALUES
            (:id, :contact, :numero, :antecedents, :allergies)"
        );

        $req->execute([
            'id' => $p->id,
            'contact' => $p->contact_urgence,
            'numero' => $p->num_urgence,
            'antecedents' => $p->antecedents,
            'allergies' => $p->allergies
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
            'id' => $p->id,
            'specialite' => $p->specialite,
            'gouvernorat' => $p->gouvernorat,
            'adresse' => $p->adresse_cabinet,
            'tarif' => $p->tarif
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
            'id' => $a->id,
            'niveau' => $a->niveau_acces
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
