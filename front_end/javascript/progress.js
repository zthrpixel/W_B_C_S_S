/* Progress tracking page. */

const PROGRESS_API = "../back_end/progress/";

function escapeHtml(value) {
    return String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Today's date in the visitor's own timezone (toISOString() would give the UTC date,
// which is the wrong day for part of every day in most timezones).
function localToday() {
    const now = new Date();
    const month = String(now.getMonth() + 1).padStart(2, "0");
    const day = String(now.getDate()).padStart(2, "0");
    return `${now.getFullYear()}-${month}-${day}`;
}

async function apiFetch(url, options = {}) {
    const response = await fetch(url, options);
    let data;
    try {
        data = await response.json();
    } catch (error) {
        throw new Error("The server returned an invalid response.");
    }
    if (response.status === 401) {
        window.location.href = "index.php";
        throw new Error("Please log in again.");
    }
    if (!response.ok || data.success === false) {
        throw new Error(data.message || "Something went wrong.");
    }
    return data;
}

function showAlert(message, type = "danger") {
    document.getElementById("progressAlert").innerHTML =
        `<div class="alert alert-${escapeHtml(type)}" role="alert">${escapeHtml(message)}</div>`;
}

function clearAlert() {
    document.getElementById("progressAlert").innerHTML = "";
}

const childSelect = document.getElementById("progressChild");
const dateInput = document.getElementById("progressDate");

// Today's numbers for the chosen child (or all children) and date.
async function loadSummary() {
    const params = new URLSearchParams();
    if (childSelect.value) params.set("child_id", childSelect.value);
    if (dateInput.value) params.set("date", dateInput.value);

    const data = await apiFetch(PROGRESS_API + "summary.php?" + params.toString());
    const summary = data.summary;
    document.getElementById("progressLabel").textContent =
        `${summary.completed_tasks} of ${summary.total_tasks} tasks completed`;
    document.getElementById("progressPercent").textContent = `${summary.percentage}%`;
    const bar = document.getElementById("progressBar");
    bar.style.width = `${summary.percentage}%`;
    bar.textContent = `${summary.percentage}%`;
}

// Saved records across all dates for the chosen child (or all children).
async function loadHistory() {
    const params = new URLSearchParams();
    if (childSelect.value) params.set("child_id", childSelect.value);

    const data = await apiFetch(PROGRESS_API + "read.php?" + params.toString());
    const records = data.progress || [];
    document.getElementById("completionHistory").innerHTML = records.length
        ? records.map((record) => `
            <article class="card card-body mb-2">
                <strong>${escapeHtml(record.child_first_name + " " + record.child_last_name)}</strong>
                <span>${escapeHtml(record.progress_date)} —
                    ${escapeHtml(record.completed_tasks)} of ${escapeHtml(record.total_tasks)}
                    completed (${escapeHtml(record.completion_percentage)}%)</span>
                ${record.remarks ? `<p class="mb-0 mt-2">${escapeHtml(record.remarks)}</p>` : ""}
            </article>`).join("")
        : '<p class="alert alert-info">No saved progress records for this selection.</p>';
}

async function refresh() {
    clearAlert();
    const canSave = Boolean(childSelect.value);
    document.getElementById("saveProgressButton").disabled = !canSave;
    document.getElementById("saveProgressHint").hidden = canSave;
    try {
        await loadSummary();
        await loadHistory();
    } catch (error) {
        showAlert(error.message);
    }
}

async function initProgress() {
    dateInput.value = localToday();

    try {
        const data = await apiFetch("../back_end/tasks/read.php?action=children");
        data.children.forEach((child) => {
            const option = document.createElement("option");
            option.value = child.child_id;
            option.textContent = `${child.first_name} ${child.last_name}`;
            childSelect.appendChild(option);
        });
    } catch (error) {
        showAlert(error.message);
        return;
    }

    childSelect.addEventListener("change", refresh);
    dateInput.addEventListener("change", refresh);

    document.getElementById("saveProgressForm").addEventListener("submit", async (event) => {
        event.preventDefault();
        if (!childSelect.value) {
            showAlert("Choose a child to save progress for.", "warning");
            return;
        }
        const remarksInput = document.getElementById("progressRemarks");
        try {
            await apiFetch(PROGRESS_API + "create.php", {
                method: "POST",
                body: new URLSearchParams({
                    child_id: childSelect.value,
                    progress_date: dateInput.value,
                    remarks: remarksInput.value
                })
            });
            remarksInput.value = "";
            await refresh();
            showAlert("Progress record saved.", "success");
        } catch (error) {
            showAlert(error.message);
        }
    });

    await refresh();
}

initProgress();
