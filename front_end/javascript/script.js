/* Shared front-end behavior for task management and the child-friendly view. */

const API_BASE = "../back_end/tasks/";

// Escape every external value before it is placed in HTML.
function escapeHtml(value) {
    return String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Read JSON responses and send users back to login when their session expires.
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

function showAlert(elementId, message, type = "danger") {
    const element = document.getElementById(elementId);
    if (!element) return;
    element.innerHTML = `<div class="alert alert-${escapeHtml(type)}" role="alert">${escapeHtml(message)}</div>`;
}

// Populate child dropdowns with children owned by the logged-in caregiver.
const CHILD_SELECT_PLACEHOLDERS = {
    taskChild: "Choose a child",
    filterChild: "All children",
    childViewPicker: "Choose a child"
};

async function loadChildren(selectIds) {
    const data = await apiFetch(API_BASE + "read.php?action=children");
    selectIds.forEach((selectId) => {
        const select = document.getElementById(selectId);
        if (!select) return;
        select.innerHTML = "";
        const placeholder = document.createElement("option");
        placeholder.value = "";
        placeholder.textContent = CHILD_SELECT_PLACEHOLDERS[selectId] || "Choose a child";
        select.appendChild(placeholder);
        data.children.forEach((child) => {
            const option = document.createElement("option");
            option.value = child.child_id;
            option.textContent = `${child.first_name} ${child.last_name}`;
            select.appendChild(option);
        });
    });
    return data.children;
}

// "08:00:00" -> "8:00 AM"
function formatTime(value) {
    if (!value) return "";
    const [hours, minutes] = String(value).split(":");
    const hour = parseInt(hours, 10);
    if (Number.isNaN(hour) || minutes === undefined) return String(value);
    return `${hour % 12 || 12}:${minutes} ${hour >= 12 ? "PM" : "AM"}`;
}

// Render the caregiver task list using escaped values.
async function loadTasks() {
    const taskList = document.getElementById("taskList");
    if (!taskList) return;
    const childId = document.getElementById("filterChild")?.value || "";
    const status = document.getElementById("filterStatus")?.value || "";
    const params = new URLSearchParams();
    if (childId) params.set("child_id", childId);
    if (status) params.set("status", status);

    try {
        const data = await apiFetch(API_BASE + "read.php?" + params.toString());
        if (!data.tasks.length) {
            taskList.innerHTML = '<div class="alert alert-info">No tasks found.</div>';
            return;
        }

        taskList.innerHTML = data.tasks.map((task) => {
            const completed = task.status === "completed";
            const badgeClass = task.priority === "high" ? "danger" : task.priority === "low" ? "secondary" : "warning";
            return `
                <article class="card task-card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between gap-2 align-items-start">
                            <div>
                                <h2 class="h5 mb-1 ${completed ? "text-decoration-line-through" : ""}">${escapeHtml(task.title)}</h2>
                                <p class="mb-2">${escapeHtml(task.description || "No description")}</p>
                            </div>
                            <span class="badge text-bg-${badgeClass}">${escapeHtml(task.priority)}</span>
                        </div>
                        <div class="task-meta mb-3">
                            <span>${escapeHtml(task.child_first_name + " " + task.child_last_name)}</span>
                            <span>Status: ${escapeHtml(task.status.replace("_", " "))}</span>
                            <span>Due: ${escapeHtml(task.due_date || "No date")}</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            ${completed ? "" : `<button class="btn btn-success min-touch complete-task" data-task-id="${escapeHtml(task.task_id)}">Complete</button>`}
                            ${completed ? `<button class="btn btn-outline-primary min-touch reopen-task" data-task-id="${escapeHtml(task.task_id)}"
                                data-title="${escapeHtml(task.title)}" data-description="${escapeHtml(task.description || "")}"
                                data-due="${escapeHtml(task.due_date || "")}" data-priority="${escapeHtml(task.priority)}">Reopen</button>` : `<button class="btn btn-primary min-touch edit-task" data-task-id="${escapeHtml(task.task_id)}"
                                data-title="${escapeHtml(task.title)}" data-description="${escapeHtml(task.description || "")}"
                                data-due="${escapeHtml(task.due_date || "")}" data-priority="${escapeHtml(task.priority)}"
                                data-status="${escapeHtml(task.status)}">Edit</button>`}
                            <button class="btn btn-outline-danger min-touch delete-task" data-task-id="${escapeHtml(task.task_id)}">Delete</button>
                        </div>
                    </div>
                </article>`;
        }).join("");
    } catch (error) {
        showAlert("taskAlert", error.message);
    }
}

// Initialize the caregiver task manager when its page elements exist.
async function initTasksPage() {
    if (!document.getElementById("taskList")) return;

    try {
        const children = await loadChildren(["taskChild", "filterChild"]);
        if (!children.length) {
            showAlert("taskAlert", "Add a child profile first, then you can create tasks.", "info");
        }
        await loadTasks();
    } catch (error) {
        showAlert("taskAlert", error.message);
    }

    document.getElementById("filterChild")?.addEventListener("change", loadTasks);
    document.getElementById("filterStatus")?.addEventListener("change", loadTasks);

    document.getElementById("createTaskForm")?.addEventListener("submit", async (event) => {
        event.preventDefault();
        const form = event.currentTarget;
        try {
            await apiFetch(API_BASE + "create.php", { method: "POST", body: new FormData(form) });
            form.reset();
            showAlert("taskAlert", "Task added!", "success");
            await loadTasks();
        } catch (error) {
            showAlert("taskAlert", error.message);
        }
    });

}

// Set up delegated task actions after task cards are drawn.
document.addEventListener("click", async (event) => {
    const completeButton = event.target.closest(".complete-task");
    if (completeButton) {
        try {
            await apiFetch(API_BASE + "complete.php", {
                method: "POST",
                body: new URLSearchParams({ task_id: completeButton.dataset.taskId })
            });
            if (document.getElementById("taskList")) await loadTasks();
            if (document.getElementById("childViewPicker")) await loadChildView();
        } catch (error) {
            showAlert(document.getElementById("taskAlert") ? "taskAlert" : "childViewAlert", error.message);
        }
    }

    const deleteButton = event.target.closest(".delete-task");
    if (deleteButton && confirm("Delete this task?")) {
        try {
            await apiFetch(API_BASE + "delete.php", {
                method: "POST",
                body: new URLSearchParams({ task_id: deleteButton.dataset.taskId })
            });
            await loadTasks();
        } catch (error) {
            showAlert("taskAlert", error.message);
        }
    }

    const editButton = event.target.closest(".edit-task");
    if (editButton) {
        document.getElementById("editTaskId").value = editButton.dataset.taskId;
        document.getElementById("editTitle").value = editButton.dataset.title;
        document.getElementById("editDescription").value = editButton.dataset.description;
        document.getElementById("editDueDate").value = editButton.dataset.due;
        document.getElementById("editPriority").value = editButton.dataset.priority;
        document.getElementById("editStatus").value = editButton.dataset.status;
        bootstrap.Modal.getOrCreateInstance(document.getElementById("editTaskModal")).show();
    }

    const reopenButton = event.target.closest(".reopen-task");
    if (reopenButton) {
        try {
            await apiFetch(API_BASE + "update.php", {
                method: "POST",
                body: new URLSearchParams({
                    task_id: reopenButton.dataset.taskId,
                    title: reopenButton.dataset.title,
                    description: reopenButton.dataset.description,
                    due_date: reopenButton.dataset.due,
                    priority: reopenButton.dataset.priority,
                    status: "pending"
                })
            });
            await loadTasks();
        } catch (error) {
            showAlert("taskAlert", error.message);
        }
    }
});

// Editing uses posted fields and prevents direct "completed" status updates.
document.getElementById("editTaskForm")?.addEventListener("submit", async (event) => {
    event.preventDefault();
    try {
        await apiFetch(API_BASE + "update.php", {
            method: "POST",
            body: new URLSearchParams(new FormData(event.currentTarget))
        });
        bootstrap.Modal.getOrCreateInstance(document.getElementById("editTaskModal")).hide();
        showAlert("taskAlert", "Task updated!", "success");
        await loadTasks();
    } catch (error) {
        showAlert("taskAlert", error.message);
    }
});

// Load today's schedule and tasks for the selected child.
async function loadChildView() {
    const picker = document.getElementById("childViewPicker");
    if (!picker) return;
    const childId = picker.value;
    if (!childId) {
        ["childGreeting", "progressSection", "scheduleSection", "tasksSection"].forEach((id) => {
            document.getElementById(id).hidden = true;
        });
        return;
    }

    try {
        const data = await apiFetch(API_BASE + `read.php?child_id=${encodeURIComponent(childId)}&scope=today&include_schedule=1`);
        const tasks = data.tasks || [];
        // A schedule with no activities comes back as one row with empty item fields.
        const schedule = (data.schedule || []).filter((item) => item.item_id);
        const childName = tasks.length
            ? tasks[0].child_first_name + " " + tasks[0].child_last_name
            : picker.options[picker.selectedIndex].textContent;

        document.getElementById("childName").textContent = childName;
        document.getElementById("childGreeting").hidden = false;

        const completed = tasks.filter((task) => task.status === "completed").length;
        const percent = tasks.length ? Math.round((completed / tasks.length) * 100) : 0;
        document.getElementById("progressText").textContent = percent + "%";
        document.getElementById("childProgressBar").style.width = percent + "%";
        document.getElementById("childProgressBar").textContent = percent + "%";
        document.getElementById("progressSection").hidden = false;

        document.getElementById("encouragement").textContent = percent === 100
            ? "Amazing work! You finished everything!"
            : completed > 0
                ? "Great job! Keep going!"
                : "You can do it! One step at a time.";

        if (schedule.length) {
            document.getElementById("todaySchedule").innerHTML = schedule.map((item) => `
                <article class="schedule-card">
                    <strong>${escapeHtml(formatTime(item.start_time))}${item.end_time ? " - " + escapeHtml(formatTime(item.end_time)) : ""}</strong>
                    <span>${item.icon ? escapeHtml(item.icon) + " " : ""}${escapeHtml(item.activity_name || "Activity")}</span>
                </article>`).join("");
        } else {
            document.getElementById("todaySchedule").innerHTML =
                '<p class="alert alert-info">No activities scheduled for today.</p>';
        }
        document.getElementById("scheduleSection").hidden = false;

        if (tasks.length) {
            document.getElementById("childTaskList").innerHTML = tasks.map((task) => `
                <article class="child-task-card ${task.status === "completed" ? "completed" : ""}">
                    <div>
                        <h3>${escapeHtml(task.title)}</h3>
                        <p>${escapeHtml(task.description || "")}</p>
                    </div>
                    ${task.status === "completed"
                        ? '<span class="done-badge" aria-label="Completed">✓ Done</span>'
                        : `<button class="btn btn-success done-button complete-task" data-task-id="${escapeHtml(task.task_id)}">Done!</button>`}
                </article>`).join("");
        } else {
            document.getElementById("childTaskList").innerHTML =
                '<p class="alert alert-info">No tasks for today. You are all done!</p>';
        }
        document.getElementById("tasksSection").hidden = false;
    } catch (error) {
        showAlert("childViewAlert", error.message);
    }
}

async function initChildView() {
    const picker = document.getElementById("childViewPicker");
    if (!picker) return;
    try {
        await loadChildren(["childViewPicker"]);
        picker.addEventListener("change", loadChildView);
        // Restore a useful selection if the URL contains a child_id.
        const requestedChild = new URLSearchParams(window.location.search).get("child_id");
        if (requestedChild) {
            picker.value = requestedChild;
            await loadChildView();
        }
    } catch (error) {
        showAlert("childViewAlert", error.message);
    }
}

initTasksPage();
initChildView();
