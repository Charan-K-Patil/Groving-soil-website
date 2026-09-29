import { isSupabaseConfigured, supabase } from "../js/supabase.js";

const status = document.getElementById("dashboardStatus");
const body = document.getElementById("leadsTableBody");
const searchInput = document.getElementById("searchInput");
const emptyState = document.getElementById("emptyState");
const signOutButton = document.getElementById("signOutButton");
const leads = [];
const statuses = ["New", "Contacted", "Visited", "Closed"];

function setStatus(message, state = "") {
    status.textContent = message;
    status.dataset.state = state;
}

function isAdmin(user) {
    return supabase
        .from("admin_users")
        .select("user_id")
        .eq("user_id", user.id)
        .maybeSingle()
        .then(({ data, error }) => !error && Boolean(data));
}

function updateStats() {
    document.getElementById("totalCount").textContent = leads.length;
    document.getElementById("newCount").textContent = leads.filter((lead) => lead.status === "New").length;
    document.getElementById("contactedCount").textContent = leads.filter((lead) => lead.status === "Contacted").length;
    document.getElementById("closedCount").textContent = leads.filter((lead) => lead.status === "Closed").length;
}

function createCell(value, className = "") {
    const cell = document.createElement("td");
    cell.textContent = value || "-";
    if (className) cell.className = className;
    return cell;
}

function renderLeads() {
    const query = searchInput.value.trim().toLocaleLowerCase();
    const filtered = leads.filter((lead) =>
        [lead.name, lead.phone, lead.email].some((value) =>
            (value || "").toLocaleLowerCase().includes(query)
        )
    );

    body.replaceChildren();
    emptyState.hidden = filtered.length > 0;

    for (const lead of filtered) {
        const row = document.createElement("tr");
        row.append(
            createCell(lead.name),
            createCell(lead.phone),
            createCell(lead.email),
            createCell(lead.visit_date)
        );

        const messageCell = createCell(lead.message, "message-cell");
        const createdAt = document.createElement("small");
        createdAt.textContent = new Date(lead.created_at).toLocaleString();
        messageCell.append(createdAt);
        row.append(messageCell);

        const statusCell = document.createElement("td");
        const select = document.createElement("select");
        select.setAttribute("aria-label", `Status for ${lead.name}`);
        for (const value of statuses) {
            const option = document.createElement("option");
            option.value = value;
            option.textContent = value;
            option.selected = lead.status === value;
            select.append(option);
        }
        select.addEventListener("change", async () => {
            select.disabled = true;
            const { error } = await supabase
                .from("visits")
                .update({ status: select.value })
                .eq("id", lead.id);

            if (error) {
                setStatus("Could not update this request. Please try again.", "error");
                select.value = lead.status;
            } else {
                lead.status = select.value;
                updateStats();
                setStatus("Request status updated.");
            }
            select.disabled = false;
        });
        statusCell.append(select);
        row.append(statusCell);

        const actionCell = document.createElement("td");
        const deleteButton = document.createElement("button");
        deleteButton.type = "button";
        deleteButton.className = "delete-button";
        deleteButton.textContent = "Delete";
        deleteButton.setAttribute("aria-label", `Delete request from ${lead.name}`);
        deleteButton.addEventListener("click", async () => {
            if (!window.confirm(`Delete the visit request from ${lead.name}?`)) return;
            deleteButton.disabled = true;
            const { error } = await supabase.from("visits").delete().eq("id", lead.id);
            if (error) {
                setStatus("Could not delete this request. Please try again.", "error");
                deleteButton.disabled = false;
                return;
            }

            leads.splice(leads.indexOf(lead), 1);
            updateStats();
            renderLeads();
            setStatus("Request deleted.");
        });
        actionCell.append(deleteButton);
        row.append(actionCell);
        body.append(row);
    }
}

async function loadLeads() {
    leads.length = 0;
    const pageSize = 500;
    let offset = 0;

    while (true) {
        const { data, error } = await supabase
            .from("visits")
            .select("id,name,phone,email,visit_date,message,status,created_at")
            .order("created_at", { ascending: false })
            .range(offset, offset + pageSize - 1);

        if (error) throw error;
        leads.push(...data);
        if (data.length < pageSize) break;
        offset += pageSize;
    }

    updateStats();
    renderLeads();
    setStatus(`${leads.length} request${leads.length === 1 ? "" : "s"} loaded.`);
}

if (!isSupabaseConfigured) {
    setStatus("Admin dashboard is unavailable until Supabase is configured.", "error");
    signOutButton.disabled = true;
} else {
    const { data: { session } } = await supabase.auth.getSession();
    if (!session || !await isAdmin(session.user)) {
        await supabase.auth.signOut();
        window.location.replace("./index.html");
    } else {
        try {
            await loadLeads();
        } catch {
            setStatus("Could not load requests. Check your Supabase setup and access policies.", "error");
        }
    }

    supabase.auth.onAuthStateChange((event) => {
        if (event === "SIGNED_OUT") window.location.replace("./index.html");
    });
    searchInput.addEventListener("input", renderLeads);
    signOutButton.addEventListener("click", async () => {
        signOutButton.disabled = true;
        const { error } = await supabase.auth.signOut();
        if (error) {
            setStatus("Could not sign out. Please try again.", "error");
            signOutButton.disabled = false;
        }
    });
}