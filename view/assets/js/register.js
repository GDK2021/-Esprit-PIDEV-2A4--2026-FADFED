const form = document.getElementById("registration-form");
const messageBox = document.getElementById("registration-message");

function showMessage(message, success) {
    messageBox.textContent = message;
    messageBox.classList.toggle("success", success);
    messageBox.hidden = false;
}

form.addEventListener("submit", async event => {
    event.preventDefault();
    messageBox.hidden = true;

    if (!form.reportValidity()) return;

    const formData = new FormData(form);
    if (formData.get("mdp") !== formData.get("mdp_confirmation")) {
        showMessage("Les mots de passe ne correspondent pas.", false);
        form.elements.mdp_confirmation.focus();
        return;
    }

    const submitButton = form.querySelector('button[type="submit"]');
    submitButton.disabled = true;

    try {
        const response = await fetch("../../control/userControl.php?action=register", {
            method: "POST",
            body: formData,
            credentials: "same-origin"
        });
        let data;
        try {
            data = await response.json();
        } catch {
            showMessage("Le serveur a renvoyé une réponse invalide.", false);
            return;
        }

        if (!response.ok || !data.success) {
            showMessage(data.message || "L’inscription n’a pas pu aboutir.", false);
            return;
        }

        form.hidden = true;
        messageBox.replaceChildren(
            document.createTextNode(data.message + " "),
            Object.assign(document.createElement("a"), {
                href: "login.php",
                textContent: "Se connecter"
            })
        );
        messageBox.classList.add("success");
        messageBox.hidden = false;
    } catch (error) {
        console.error("Erreur lors de l’inscription :", error);
        showMessage("Impossible de contacter le serveur. Vérifiez qu’Apache et MySQL sont démarrés.", false);
    } finally {
        submitButton.disabled = false;
    }
});
