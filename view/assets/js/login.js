const form = document.getElementById("login-form");
const errorBox = document.getElementById("error");

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
        showError("Please enter your name and password.");
        return;
    }

    fetch("../control/loginCheck.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ login, mdp })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                showError(data.message || "Incorrect name or password.");
            }
        })
        .catch(() => showError("Something went wrong. Try again."));
});