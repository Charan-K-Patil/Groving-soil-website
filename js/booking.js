import { isSupabaseConfigured, supabase } from "./supabase.js";

const form = document.getElementById("visitForm");
const status = document.getElementById("visitFormStatus");
const submitButton = form?.querySelector('button[type="submit"]');

if (form && status && submitButton) {
    if (!isSupabaseConfigured) {
        status.textContent = "Visit booking is being set up. Please contact us directly for now.";
        submitButton.disabled = true;
    } else {
        form.addEventListener("submit", async (event) => {
            event.preventDefault();
            status.textContent = "Sending your request...";
            status.dataset.state = "pending";
            submitButton.disabled = true;

            const data = new FormData(form);
            const { error } = await supabase.from("visits").insert({
                name: data.get("name").trim(),
                phone: data.get("phone").trim(),
                email: data.get("email").trim(),
                visit_date: data.get("visit_date") || null,
                message: data.get("message").trim(),
                status: "New"
            });

            if (error) {
                status.textContent = "We couldn't send your request. Please try again.";
                status.dataset.state = "error";
                submitButton.disabled = false;
                return;
            }

            status.textContent = "Your visit request has been sent. We'll contact you shortly.";
            status.dataset.state = "success";
            form.reset();

            const successPopup = document.getElementById("successPopup");
            if (successPopup) successPopup.style.display = "flex";

            submitButton.disabled = false;
        });
    }
}