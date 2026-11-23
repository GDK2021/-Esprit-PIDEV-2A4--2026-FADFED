const form = document.getElementById("login-form");
const errorBox = document.getElementById("error");
const loginArea = form.dataset.loginArea || "frontoffice";

function showError(message) {
    errorBox.textContent = message;
    errorBox.hidden = false;
}

form.addEventListener("submit", function (event) {
    event.preventDefault();
    errorBox.hidden = true;

    const login = document.getElementById("login").value.trim();
    const mdp = document.getElementById("mdp").value;

    if (login === "" || mdp === "") {
        showError("Veuillez saisir votre identifiant et votre mot de passe.");
        return;
    }

    fetch("../../control/loginCheck.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ login, mdp, area: loginArea })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                showError(data.message || "Identifiant ou mot de passe incorrect.");
            }
        })
        .catch(() => showError("Une erreur est survenue. Veuillez réessayer."));
});