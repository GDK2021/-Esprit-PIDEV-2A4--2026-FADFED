const form = document.querySelector("form");
const role = document.getElementById("role");

const champsPatient = document.getElementById("patient");
const champsPro = document.getElementById("medecin");
const champsAdmin = document.getElementById("admin");

/* ---------- Affichage des champs selon le rôle ---------- */

function afficherChamps() {

    const blocs = [champsPatient, champsPro, champsAdmin];

    // Cacher et désactiver tous les blocs, et enlever leurs erreurs
    blocs.forEach(bloc => {
        bloc.classList.remove("visible");
        bloc.querySelectorAll("input, select, textarea").forEach(champ => {
            champ.disabled = true;
            effacerErreur(champ);
        });
    });

    // Afficher et activer uniquement le bloc choisi
    let actif = null;
    if (role.value === "patient") actif = champsPatient;
    else if (role.value === "medecin") actif = champsPro;
    else if (role.value === "admin") actif = champsAdmin;

    if (actif) {
        actif.classList.add("visible");
        actif.querySelectorAll("input, select, textarea").forEach(champ => {
            champ.disabled = false;
        });
    }
}

//Gestion des erreurs 

function effacerErreur(champ) {
    champ.classList.remove("champ-erreur");
    const suivant = champ.nextElementSibling;
    if (suivant && suivant.classList.contains("msg-erreur")) {
        suivant.remove();
    }
}

function afficherErreur(champ, message) {
    champ.classList.add("champ-erreur");
    let msg = champ.nextElementSibling;
    if (!msg || !msg.classList.contains("msg-erreur")) {
        msg = document.createElement("small");
        msg.className = "msg-erreur";
        champ.insertAdjacentElement("afterend", msg);
    }
    msg.textContent = message;
}


const regles = {
    role: v => v ? "" : "Veuillez choisir un rôle.",

    nom: v => {
        if (!v.trim()) return "Le nom est obligatoire.";
        if (!/^[A-Za-zÀ-ÿ\s'-]+$/.test(v.trim())) return "Le nom ne doit contenir que des lettres.";
        return "";
    },

    prenom: v => {
        if (!v.trim()) return "Le prénom est obligatoire.";
        if (!/^[A-Za-zÀ-ÿ\s'-]+$/.test(v.trim())) return "Le prénom ne doit contenir que des lettres.";
        return "";
    },

    age: v => {
        if (v === "") return "L'âge est obligatoire.";
        const n = Number(v);
        if (!Number.isInteger(n) || n < 1 || n > 120) return "Entrez un âge valide (entre 1 et 120).";
        return "";
    },

    genre: v => v ? "" : "Veuillez choisir un genre.",

    email: v => {
        if (!v.trim()) return "L'email est obligatoire.";
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim())) return "Format d'email invalide (ex : nom@domaine.com).";
        return "";
    },

    pseudo: v => {
        if (!v.trim()) return "Le pseudo est obligatoire.";
        if (v.trim().length < 3) return "Le pseudo doit contenir au moins 3 caractères.";
        return "";
    },

    mdp: v => {
        if (!v) return "Le mot de passe est obligatoire.";
        if (v.length < 8) return "Le mot de passe doit contenir au moins 8 caractères.";
        if (!/[A-Za-z]/.test(v) || !/[0-9]/.test(v)) return "Le mot de passe doit contenir des lettres et des chiffres.";
        return "";
    },

    confirmation: v => {
        if (!v) return "Veuillez confirmer le mot de passe.";
        if (v !== form.elements["mdp"].value) return "Les mots de passe ne correspondent pas.";
        return "";
    },

    num_telephone: v => {
        if (!v.trim()) return "Le numéro de téléphone est obligatoire.";
        if (!/^[0-9]{8}$/.test(v.trim())) return "Le numéro doit contenir exactement 8 chiffres.";
        return "";
    },

    // Champs médecin
    specialite: v => v.trim() ? "" : "La spécialité est obligatoire.",
    gouvernorat: v => v.trim() ? "" : "Le gouvernorat est obligatoire.",
    adresse_cabinet: v => v.trim() ? "" : "L'adresse du cabinet est obligatoire.",
    tarif: v => {
        if (v === "") return "Le tarif est obligatoire.";
        if (Number(v) <= 0) return "Le tarif doit être supérieur à 0.";
        return "";
    },

    // Champ admin
    niveau_acces: v => v.trim() ? "" : "Le niveau d'accès est obligatoire."
};

function validerChamp(champ) {
    if (champ.disabled || !champ.name) return true;

    const regle = regles[champ.name];
    if (!regle) return true;

    const message = regle(champ.value);
    if (message) {
        afficherErreur(champ, message);
        return false;
    }
    effacerErreur(champ);
    return true;
}

/* ---------- Événements ---------- */

role.addEventListener("change", afficherChamps);

// Téléphone : on n'autorise que les chiffres
form.elements["num_telephone"].addEventListener("input", function () {
    this.value = this.value.replace(/[^0-9]/g, "");
});

// Validation en direct
form.querySelectorAll("input, select, textarea").forEach(champ => {

    // Quand on quitte le champ
    champ.addEventListener("blur", () => validerChamp(champ));

    // Pendant la saisie : on met à jour seulement si une erreur est déjà affichée
    champ.addEventListener("input", () => {
        if (champ.classList.contains("champ-erreur")) validerChamp(champ);

        // Si on modifie le mot de passe, on revérifie la confirmation
        if (champ.name === "mdp") {
            const conf = form.elements["confirmation"];
            if (conf.value !== "") validerChamp(conf);
        }
    });

    champ.addEventListener("change", () => validerChamp(champ));
});

// Validation à l'envoi
form.addEventListener("submit", function (e) {
    e.preventDefault();

    let premierErreur = null;

    Array.from(form.elements).forEach(champ => {
        if (!validerChamp(champ) && !premierErreur) {
            premierErreur = champ;
        }
    });

    if (premierErreur) {
        premierErreur.focus();
        premierErreur.scrollIntoView({ behavior: "smooth", block: "center" });
        return;
    }

    form.submit();
});

// Exécuter au chargement
afficherChamps();