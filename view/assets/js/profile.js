const profileForm = document.getElementById("profile-form");
const profileMessage = document.getElementById("profile-message");
const photoInput = document.getElementById("photo");
const photoName = document.getElementById("photo-name");
const photoPreview = document.getElementById("profile-photo-preview");
const profilePhotoButton = document.querySelector(".profile-nav-link");

function showProfileMessage(message, success) {
    profileMessage.textContent = message;
    profileMessage.classList.toggle("success", success);
    profileMessage.hidden = false;
}

function renderPhoto(photoPath, pseudo) {
    const initial = pseudo.trim().charAt(0).toUpperCase() || "?";
    photoPreview.replaceChildren();
    if (photoPath) {
        const image = document.createElement("img");
        image.src = `../${photoPath}`;
        image.alt = `Photo de profil de ${pseudo}`;
        photoPreview.append(image);
    } else {
        photoPreview.textContent = initial;
    }

    if (profilePhotoButton) {
        const navImage = profilePhotoButton.querySelector("img");
        const navInitial = profilePhotoButton.querySelector(".profile-nav-avatar");
        if (photoPath) {
            if (navImage) {
                navImage.src = `../${photoPath}`;
                navImage.alt = "";
            } else if (navInitial) {
                const image = document.createElement("img");
                image.className = "profile-nav-image";
                image.src = `../${photoPath}`;
                image.alt = "";
                navInitial.replaceWith(image);
            }
        } else if (navImage) {
            const initialElement = document.createElement("span");
            initialElement.className = "profile-nav-avatar";
            initialElement.textContent = initial;
            navImage.replaceWith(initialElement);
        } else if (navInitial) {
            navInitial.textContent = initial;
        }
        profilePhotoButton.querySelector(".profile-nav-name").textContent = pseudo;
        profilePhotoButton.setAttribute("aria-label", `Profil de ${pseudo}`);
    }
}

function fillProfile(user) {
    for (const field of ["nom", "prenom", "age", "genre", "bloodtype", "email", "num_telephone", "pseudo", "login"]) {
        profileForm.elements[field].value = user[field] ?? "";
    }
    document.getElementById("profile-role").textContent = user.role ?? "—";
    document.getElementById("profile-registration-date").textContent = user.date_inscription ?? "—";
    renderPhoto(user.photo_profil, user.pseudo ?? "");
}

async function loadProfile() {
    try {
        const response = await fetch("../../control/profile.php", { credentials: "same-origin" });
        const data = await response.json();
        if (!response.ok || !data.success) {
            showProfileMessage(data.message || "Impossible de charger votre profil.", false);
            return;
        }
        fillProfile(data.user);
    } catch (error) {
        console.error("Erreur lors du chargement du profil :", error);
        showProfileMessage("Impossible de contacter le serveur.", false);
    }
}

photoInput.addEventListener("change", () => {
    photoName.textContent = photoInput.files.length
        ? photoInput.files[0].name
        : "Aucune nouvelle photo sélectionnée";
});

profileForm.addEventListener("submit", async event => {
    event.preventDefault();
    profileMessage.hidden = true;
    if (!profileForm.reportValidity()) return;

    const saveButton = document.getElementById("profile-save");
    saveButton.disabled = true;
    try {
        const response = await fetch("../../control/profile.php", {
            method: "POST",
            body: new FormData(profileForm),
            credentials: "same-origin"
        });
        const data = await response.json();
        if (!response.ok || !data.success) {
            showProfileMessage(data.message || "Les modifications n’ont pas pu être enregistrées.", false);
            return;
        }

        fillProfile(data.user);
        photoInput.value = "";
        photoName.textContent = "Aucune nouvelle photo sélectionnée";
        showProfileMessage(data.message, true);
    } catch (error) {
        console.error("Erreur lors de la mise à jour du profil :", error);
        showProfileMessage("Impossible de contacter le serveur.", false);
    } finally {
        saveButton.disabled = false;
    }
});

loadProfile();
