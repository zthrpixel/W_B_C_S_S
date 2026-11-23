<?php
require_once __DIR__ . '/../back_end/auth/auth_check.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Progress</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .progress-bar { background: #7ED321; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="tasks.php">Task Manager</a>
            <div class="navbar-nav">
                <a class="nav-link" href="progress.php">Progress</a>
                <a class="nav-link" href="communication.php">Communication</a>
                <a class="nav-link" href="child_view.php">Child View</a>
            </div>
        </div>
    </nav>
    <main class="container py-4">
        <h1 class="h2 mb-4">Progress Tracking</h1>
        <div id="progressAlert" role="alert"></div>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label" for="progressChild">Child</label>
                <select class="form-select" id="progressChild"></select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="progressDate">Date</label>
                <input class="form-control" id="progressDate" type="date">
            </div>
        </div>
        <section class="card card-body mb-4">
            <div class="d-flex justify-content-between mb-2">
                <strong id="progressLabel">0 of 0 tasks completed</strong>
                <strong id="progressPercent">0%</strong>
            </div>
            <div class="progress" style="height: 32px">
                <div id="progressBar" class="progress-bar" style="width: 0%">0%</div>
            </div>
        </section>
        <h2 class="h4">Completion History</h2>
        <div id="completionHistory"></div>
    </main>
    <script>
        const escapeHtml = (value) => String(value ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;");

        async function apiFetch(url) {
            const response = await fetch(url);
            const data = await response.json();
            if (!response.ok || data.success === false) {
                throw new Error(data.message || "Could not load progress.");
            }
            return data;
        }

        async function loadProgress() {
            const childId = document.getElementById("progressChild").value;
            const date = document.getElementById("progressDate").value;
            const params = new URLSearchParams();
            if (childId) params.set("child_id", childId);
            if (date) params.set("date", date);

            const data = await apiFetch("../back_end/progress/summary.php?" + params.toString());
            const summary = data.summary;
            document.getElementById("progressLabel").textContent =
                `${summary.completed_tasks} of ${summary.total_tasks} tasks completed`;
            document.getElementById("progressPercent").textContent = `${summary.percentage}%`;
            document.getElementById("progressBar").style.width = `${summary.percentage}%`;
            document.getElementById("progressBar").textContent = `${summary.percentage}%`;

            const historyParams = new URLSearchParams(params);
            const history = await apiFetch("../back_end/progress/read.php?" + historyParams.toString());
            document.getElementById("completionHistory").innerHTML =
                history.progress?.length
                    ? history.progress.map((record) => `
                        <article class="card card-body mb-2">
                            <strong>${escapeHtml(record.child_first_name + " " + record.child_last_name)}</strong>
                            <span>${escapeHtml(record.progress_date)} —
                                ${escapeHtml(record.completed_tasks)} of ${escapeHtml(record.total_tasks)}
                                completed (${escapeHtml(record.completion_percentage)}%)</span>
                            ${record.remarks ? `<p class="mb-0 mt-2">${escapeHtml(record.remarks)}</p>` : ""}
                        </article>`).join("")
                    : '<p class="alert alert-info">No saved progress records for this selection.</p>';
        }

        async function initProgress() {
            document.getElementById("progressDate").value =
                new Date().toISOString().slice(0, 10);
            const childSelect = document.getElementById("progressChild");
            try {
                const data = await apiFetch("../back_end/tasks/read.php?action=children");
                data.children.forEach((child) => {
                    const option = document.createElement("option");
                    option.value = child.child_id;
                    option.textContent = `${child.first_name} ${child.last_name}`;
                    childSelect.appendChild(option);
                });
                childSelect.addEventListener("change", () => loadProgress().catch(showProgressError));
                document.getElementById("progressDate").addEventListener("change",
                    () => loadProgress().catch(showProgressError));
                await loadProgress();
            } catch (error) {
                showProgressError(error);
            }
        }

        function showProgressError(error) {
            document.getElementById("progressAlert").innerHTML =
                `<div class="alert alert-danger" role="alert">${escapeHtml(error.message)}</div>`;
        }

        initProgress();
    </script>
</body>
</html>
