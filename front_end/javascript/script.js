// script.js - JavaScript for task.php and child_view.php
// it sends requests to the PHP files in back_end/tasks/

var API = "../back_end/tasks/";

// stores the loaded tasks so the edit popup can use them
var allTasks = {};

// makes text safe before putting it inside HTML
function safe(text) {
    var div = document.createElement("div");
    div.textContent = text == null ? "" : text;
    return div.innerHTML;
}

// send a GET request
async function getData(file, params) {
    var query = new URLSearchParams(params || {}).toString();
    var res = await fetch(API + file + "?" + query, { credentials: "same-origin" });
    return checkResponse(res);
}

// send a POST request
async function postData(file, data) {
    var res = await fetch(API + file, {
        method: "POST",
        body: new URLSearchParams(data),
        credentials: "same-origin"
    });
    return checkResponse(res);
}

// go back to login if the session expired
async function checkResponse(res) {
    if (res.status === 401) {
        window.location.href = "index.php";
        return { success: false };
    }
    try {
        return await res.json();
    } catch (e) {
        return { success: false, message: "Server error" };
    }
}

// show a message at the top of the page
function showMessage(text, type) {
    document.getElementById("message").innerHTML =
        '<div class="alert alert-' + type + '">' + safe(text) + "</div>";
}


/* ---------- task.php ---------- */

async function loadTasks() {
    var params = {
        child_id: document.getElementById("filterChild").value,
        status: document.getElementById("filterStatus").value
    };
    var data = await getData("read.php", params);
    var list = document.getElementById("taskList");

    if (!data.success) {
        list.innerHTML = "";
        showMessage(data.message || "Could not load tasks", "danger");
        return;
    }

    allTasks = {};
    if (data.tasks.length === 0) {
        list.innerHTML = '<p class="text-muted">No tasks yet.</p>';
        return;
    }

    var html = "";
    data.tasks.forEach(function (task) {
        allTasks[task.task_id] = task;
        var done = task.status === "completed";

        html += '<div class="card mb-3 ' + (done ? "task-done" : "") + '">';
        html += '<div class="card-body d-flex flex-wrap justify-content-between gap-2">';
        html += "<div>";
        html += '<div class="task-title fw-bold fs-5">' + safe(task.title) + "</div>";
        html += "<div>" + safe(task.child_name);
        if (task.due_date) {
            html += " - Due " + safe(task.due_date);
        }
        html += "</div>";
        if (task.description) {
            html += "<div>" + safe(task.description) + "</div>";
        }
        html += '<span class="badge bg-secondary me-1">' + safe(task.status.replace("_", " ")) + "</span>";
        html += '<span class="badge bg-info text-dark">' + safe(task.priority) + "</span>";
        html += "</div>";
        html += '<div class="d-flex gap-2 align-items-start">';
        if (!done) {
            html += '<button class="btn btn-success" data-action="complete" data-id="' + task.task_id + '">Complete</button>';
        }
        html += '<button class="btn btn-outline-primary" data-action="edit" data-id="' + task.task_id + '">Edit</button>';
        html += '<button class="btn btn-outline-danger" data-action="delete" data-id="' + task.task_id + '">Delete</button>';
        html += "</div></div></div>";
    });
    list.innerHTML = html;
}

function setupTaskPage() {
    var addForm = document.getElementById("addForm");
    if (!addForm) {
        return; // no children yet
    }

    // add a task
    addForm.addEventListener("submit", async function (e) {
        e.preventDefault();
        var result = await postData("create.php", new FormData(addForm));
        showMessage(result.message, result.success ? "success" : "danger");
        if (result.success) {
            document.getElementById("title").value = "";
            document.getElementById("description").value = "";
            document.getElementById("due_date").value = "";
            loadTasks();
        }
    });

    // reload when a filter changes
    document.getElementById("filterChild").addEventListener("change", loadTasks);
    document.getElementById("filterStatus").addEventListener("change", loadTasks);

    var modal = new bootstrap.Modal(document.getElementById("editModal"));

    // buttons inside the task list (complete, edit, delete)
    document.getElementById("taskList").addEventListener("click", async function (e) {
        var button = e.target.closest("button");
        if (!button) {
            return;
        }
        var id = button.dataset.id;
        var action = button.dataset.action;
        var result;

        if (action === "complete") {
            result = await postData("complete.php", { task_id: id });
            showMessage(result.message, result.success ? "success" : "danger");
            loadTasks();
        } else if (action === "delete") {
            if (!confirm("Delete this task?")) {
                return;
            }
            result = await postData("delete.php", { task_id: id });
            showMessage(result.message, result.success ? "success" : "danger");
            loadTasks();
        } else if (action === "edit") {
            // fill the popup with the task data
            var task = allTasks[id];
            document.getElementById("edit_task_id").value = task.task_id;
            document.getElementById("edit_title").value = task.title;
            document.getElementById("edit_description").value = task.description || "";
            document.getElementById("edit_due_date").value = task.due_date || "";
            document.getElementById("edit_priority").value = task.priority;
            // completed can only be chosen if the task is already completed
            document.getElementById("edit_completed_option").disabled = task.status !== "completed";
            document.getElementById("edit_status").value = task.status;
            modal.show();
        }
    });

    // save the edited task
    document.getElementById("editForm").addEventListener("submit", async function (e) {
        e.preventDefault();
        var result = await postData("update.php", new FormData(e.target));
        showMessage(result.message, result.success ? "success" : "danger");
        if (result.success) {
            modal.hide();
            loadTasks();
        }
    });

    loadTasks();
}


/* ---------- child_view.php ---------- */

async function loadChildTasks() {
    var childId = document.body.dataset.child;
    var data = await getData("read.php", { child_id: childId, today: 1 });
    var box = document.getElementById("childTasks");

    if (!data.success) {
        box.innerHTML = "<p>Could not load tasks.</p>";
        return;
    }

    // count how many are done for the progress bar
    var total = data.tasks.length;
    var done = 0;
    data.tasks.forEach(function (task) {
        if (task.status === "completed") {
            done++;
        }
    });
    var percent = total === 0 ? 0 : Math.round((done / total) * 100);
    document.getElementById("progressBar").style.width = percent + "%";

    var text = document.getElementById("progressText");
    if (total === 0) {
        text.textContent = "No tasks today!";
    } else if (done === total) {
        text.textContent = "Great job! All done!";
    } else {
        text.textContent = done + " of " + total + " done";
    }

    var html = "";
    data.tasks.forEach(function (task) {
        var isDone = task.status === "completed";
        html += '<div class="task-box p-3 mb-3 ' + (isDone ? "done" : "") + '">';
        html += '<div class="task-title fs-3 fw-bold">' + safe(task.title) + "</div>";
        if (task.description) {
            html += '<div class="fs-5 mb-2">' + safe(task.description) + "</div>";
        }
        if (!isDone) {
            html += '<button class="btn btn-success big-btn w-100 mt-2" data-id="' + task.task_id + '">Done!</button>';
        }
        html += "</div>";
    });
    box.innerHTML = html;
}

function setupChildView() {
    // no child chosen yet, nothing to load
    if (document.body.dataset.child == 0) {
        return;
    }

    document.getElementById("childTasks").addEventListener("click", async function (e) {
        var button = e.target.closest("button");
        if (!button) {
            return;
        }
        button.disabled = true; // so it can't be tapped twice
        await postData("complete.php", { task_id: button.dataset.id });
        loadChildTasks();
    });

    loadChildTasks();
}


// run the code for the current page
document.addEventListener("DOMContentLoaded", function () {
    var page = document.body.dataset.page;
    if (page === "tasks") {
        setupTaskPage();
    }
    if (page === "child_view") {
        setupChildView();
    }
});
