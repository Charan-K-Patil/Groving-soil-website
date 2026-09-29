const phoneInput = document.getElementById("phone");

if (phoneInput) {
    phoneInput.addEventListener("input", () => {
        phoneInput.value = phoneInput.value.replace(/\D/g, "").slice(0, 10);
    });
}

function closeSuccessPopup() {
    const successPopup = document.getElementById("successPopup");

    if (successPopup) {
        successPopup.style.display = "none";
    }
}