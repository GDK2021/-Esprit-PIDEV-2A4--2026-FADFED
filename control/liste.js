// Page unique : liste + recherche par ID + ajout (fenêtre) + suppression

const corps     = document.getElementById('corps');
const msg       = document.getElementById('msg');
const vide      = document.getElementById('vide');
const compteur  = document.getElementById('compteur');
const rechercheId = document.getElementById('recherche-id');
const dlg       = document.getElementById('dlg-ajout');
const formAjout = document.getElementById('form-ajout');
const msgAjout  = document.getElementById('msg-ajout');

let users = [];
let filtreId = null; // null = tout afficher

// ---------- utilitaires ----------
function showMessage(el, text, ok) {
    el.textContent = text;
    el.className = 'msg ' + (ok ? 'ok' : 'ko');
}

function connectionHint(err) {
    console.error('Erreur fetch :', err);
    return location.protocol === 'file:'
        ? "Ouvre la page via http://localhost/... (pas en double-cliquant sur le fichier)."
        : "Impossible de contacter le serveur. Vérifie qu'Apache/MySQL sont démarrés.";
}

async function parseJson(res) {
    try { return await res.json(); }
    catch { return { success: false, message: 'Réponse invalide du serveur.' }; }
}

// ---------- affichage ----------
const COLS = ['id_user','nom','prenom','age','genre','bloodtype','role','email','pseudo','login','num_telephone','date_inscription'];

function render() {
    const liste = filtreId === null ? users : users.filter(u => Number(u.id_user) === filtreId);

    corps.replaceChildren();
    for (const u of liste) {
        const tr = document.createElement('tr');
        for (const c of COLS) {
            const td = document.createElement('td');
            td.textContent = u[c];            // textContent : pas d'injection HTML
            tr.appendChild(td);
        }
        const tdAction = document.createElement('td');
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'del';
        btn.textContent = 'Supprimer';
        btn.addEventListener('click', () => supprimer(u, btn));
        tdAction.appendChild(btn);
        tr.appendChild(tdAction);
        corps.appendChild(tr);
    }

    vide.hidden = liste.length > 0;
    vide.textContent = filtreId === null ? 'Aucun utilisateur à afficher.' : `Aucun utilisateur avec l'ID ${filtreId}.`;
    compteur.textContent = `${liste.length} / ${users.length} utilisateur(s)`;
}

async function charger() {
    try {
        const res  = await fetch('liste.php', { credentials: 'same-origin' });
        const data = await parseJson(res);
        if (!data.success) { showMessage(msg, data.message, false); return; }
        users = data.users;
        render();
    } catch (err) {
        showMessage(msg, connectionHint(err), false);
    }
}

// ---------- recherche par ID ----------
document.getElementById('form-recherche').addEventListener('submit', e => {
    e.preventDefault();
    const v = rechercheId.value.trim();
    if (v === '') { filtreId = null; msg.textContent = ''; render(); return; }
    if (!/^\d+$/.test(v)) { showMessage(msg, "L'ID doit être un nombre entier.", false); return; }
    msg.textContent = '';
    filtreId = Number(v);
    render();
});

document.getElementById('btn-reset').addEventListener('click', () => {
    rechercheId.value = '';
    filtreId = null;
    msg.textContent = '';
    render();
});

// ---------- suppression ----------
async function supprimer(u, btn) {
    if (!confirm(`Supprimer définitivement ${u.prenom} ${u.nom} (ID ${u.id_user}) ?`)) return;

    btn.disabled = true;
    try {
        const fd = new FormData();
        fd.append('id_user', u.id_user);
        const res  = await fetch('sup.php', { method: 'POST', body: fd, credentials: 'same-origin' });
        const data = await parseJson(res);
        showMessage(msg, data.message, data.success);
        if (data.success) {
            users = users.filter(x => x.id_user !== u.id_user);
            render();
        } else {
            btn.disabled = false;
        }
    } catch (err) {
        showMessage(msg, connectionHint(err), false);
        btn.disabled = false;
    }
}

// ---------- ajout : validation ----------
const rules = {
    nom:    v => /^[A-Za-zÀ-ÿ' -]{2,50}$/.test(v) || 'Nom invalide (2 à 50 lettres).',
    prenom: v => /^[A-Za-zÀ-ÿ' -]{2,50}$/.test(v) || 'Prénom invalide (2 à 50 lettres).',
    age:    v => (/^\d+$/.test(v) && +v >= 0 && +v <= 120) || 'Âge invalide (0 à 120).',
    genre:  v => v !== '' || 'Choisis un genre.',
    bloodtype: v => v !== '' || 'Choisis un groupe sanguin.',
    role:   v => v !== '' || 'Choisis un rôle.',
    email:  v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) || 'Email invalide.',
    num_telephone: v => /^\d{8}$/.test(v) || 'Le numéro doit contenir 8 chiffres.',
    pseudo: v => /^[\w.-]{3,30}$/.test(v) || 'Pseudo invalide (3 à 30 caractères : lettres, chiffres, . _ -).',
    login:  v => /^[\w.-]{3,30}$/.test(v) || 'Login invalide (3 à 30 caractères : lettres, chiffres, . _ -).',
    mdp:    v => v.length >= 8 || 'Mot de passe trop court (8 caractères minimum).',
};

function setError(name, message) {
    const input = formAjout.elements[name];
    const span  = formAjout.querySelector(`.error[data-for="${name}"]`);
    if (span)  span.textContent = message;
    if (input) input.classList.toggle('invalid', message !== '');
}

function validateAjout() {
    let valid = true;
    for (const [name, rule] of Object.entries(rules)) {
        const raw = formAjout.elements[name].value;
        const result = rule(name === 'mdp' ? raw : raw.trim());
        setError(name, result === true ? '' : result);
        if (result !== true) valid = false;
    }
    return valid;
}

formAjout.addEventListener('input', e => { if (e.target.name) setError(e.target.name, ''); });

// ---------- ajout : fenêtre ----------
function fermerFenetre() {
    dlg.close();
    formAjout.reset();
    msgAjout.textContent = '';
    for (const name of Object.keys(rules)) setError(name, '');
}

document.getElementById('btn-ouvrir').addEventListener('click', () => dlg.showModal());
document.getElementById('btn-annuler').addEventListener('click', fermerFenetre);
dlg.addEventListener('cancel', e => { e.preventDefault(); fermerFenetre(); }); // touche Échap

// ---------- ajout : envoi ----------
formAjout.addEventListener('submit', async e => {
    e.preventDefault();
    const btn = document.getElementById('btn-ajout');
    msgAjout.textContent = '';

    if (!validateAjout()) {
        showMessage(msgAjout, 'Corrige les champs en rouge.', false);
        return;
    }

    btn.disabled = true;
    try {
        const res  = await fetch('ajout.php', { method: 'POST', body: new FormData(formAjout), credentials: 'same-origin' });
        const data = await parseJson(res);
        if (data.success) {
            fermerFenetre();
            showMessage(msg, data.message, true);
            await charger();                       // recharge la liste avec le nouvel utilisateur
        } else {
            showMessage(msgAjout, data.message, false);
        }
    } catch (err) {
        showMessage(msgAjout, connectionHint(err), false);
    } finally {
        btn.disabled = false;
    }
});

charger();