// Visit booking success popup
document.addEventListener("DOMContentLoaded", function () {
    const successPopup = document.getElementById("successPopup");

    if (!successPopup) return;

    const params = new URLSearchParams(window.location.search);

    if (params.get("success") === "1") {

        // Show popup
        successPopup.style.display = "flex";

        // Remove ?success=1 from the URL immediately
        window.history.replaceState(
            {},
            document.title,
            window.location.pathname + "#visit"
        );
    }
});

function closeSuccessPopup() {
    const successPopup = document.getElementById("successPopup");

    if (successPopup) {
        successPopup.style.display = "none";
    }
}