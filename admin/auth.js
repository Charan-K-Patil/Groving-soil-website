import { isSupabaseConfigured, supabase } from "../js/supabase.js";

const form = document.getElementById("loginForm");
const status = document.getElementById("loginStatus");
const submitButton = form.querySelector('button[type="submit"]');

function showError(message) {
    status.textContent = message;
    status.dataset.state = "error";
}

async function isAdmin(user) {
    const { data, error } = await supabase
        .from("admin_users")
        .select("user_id")
        .eq("user_id", user.id)
        .maybeSingle();

    return !error && Boolean(data);
}

if (!isSupabaseConfigured) {
    showError("Admin sign-in is unavailable until Supabase is configured.");
    submitButton.disabled = true;
} else {
    const { data: { session } } = await supabase.auth.getSession();
    if (session && await isAdmin(session.user)) {
        window.location.replace("./dashboard.html");
    }

    form.addEventListener("submit", async (event) => {
        event.preventDefault();
        status.textContent = "";
        submitButton.disabled = true;

        const values = new FormData(form);
        const { data, error } = await supabase.auth.signInWithPassword({
            email: values.get("email").trim(),
            password: values.get("password")
        });

        if (error) {
            showError("Sign-in failed. Check your credentials and try again.");
            submitButton.disabled = false;
            return;
        }

        if (!await isAdmin(data.user)) {
            await supabase.auth.signOut();
            showError("This account is not authorized for the admin dashboard.");
            submitButton.disabled = false;
            return;
        }

        window.location.replace("./dashboard.html");
    });
}