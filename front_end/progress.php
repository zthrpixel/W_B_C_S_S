<?php
// progress.php - shows the progress of a child (completed tasks and summary)
require_once __DIR__ . '/../back_end/auth/auth_check.php';
require_once __DIR__ . '/../back_end/config/database.php';

// get the children of the logged in user for the dropdown
$stmt = $pdo->prepare("SELECT child_id, first_name, last_name FROM children
                       WHERE user_id = :user_id ORDER BY first_name");
$stmt->execute([":user_id" => $_SESSION["user_id"]]);
$children = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Progress</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
  <style>
    body { background: #f4f8fc; font-size: 16px; }
    .btn, .form-control, .form-select { min-height: 44px; border-radius: 12px; }
    .card { border-radius: 12px; }
    .big-number { font-size: 36px; font-weight: bold; }
    .progress { height: 24px; }
  </style>
</head>
<body>

<!-- navigation bar -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background: #4A90D9;">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">Visual Schedule</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="children.php">Children</a></li>
        <li class="nav-item"><a class="nav-link" href="schedule.php">Schedule</a></li>
        <li class="nav-item"><a class="nav-link" href="task.php">Tasks</a></li>
        <li class="nav-item"><a class="nav-link" href="child_view.php">Child View</a></li>
        <li class="nav-item"><a class="nav-link active" href="progress.php">Progress</a></li>
        <li class="nav-item"><a class="nav-link" href="communication.php">Communication</a></li>
        <li class="nav-item"><a class="nav-link" href="../back_end/auth/logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-4">
  <h1 class="h3 mb-3">Progress Tracking</h1>

  <!-- messages show here -->
  <div id="message"></div>

  <?php if (count($children) === 0) { ?>
    <div class="alert alert-warning">
      You have no children yet. Add one in <a href="children.php">Children</a> first.
    </div>
  <?php } else { ?>

  <!-- filters -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <label class="form-label" for="childSelect">Child</label>
      <select class="form-select" id="childSelect">
        <?php foreach ($children as $child) { ?>
          <option value="<?php echo $child['child_id']; ?>">
            <?php echo htmlspecialchars($child['first_name'] . ' ' . $child['last_name']); ?>
          </option>
        <?php } ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label" for="range">Date Range</label>
      <select class="form-select" id="range">
        <option value="today">Today</option>
        <option value="week" selected>This Week</option>
        <option value="month">This Month</option>
      </select>
    </div>
  </div>

  <!-- summary cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card text-center"><div class="card-body">
        <div class="text-muted">Total Tasks</div>
        <div class="big-number" id="totalTasks">0</div>
      </div></div>
    </div>
    <div class="col-md-4">
      <div class="card text-center"><div class="card-body">
        <div class="text-muted">Completed</div>
        <div class="big-number text-success" id="completedTasks">0</div>
      </div></div>
    </div>
    <div class="col-md-4">
      <div class="card text-center"><div class="card-body">
        <div class="text-muted">Completion</div>
        <div class="big-number text-primary" id="percent">0%</div>
        <div class="progress mt-2">
          <div class="progress-bar bg-success" id="progressBar" style="width: 0%;"></div>
        </div>
      </div></div>
    </div>
  </div>

  <!-- chart -->
  <div class="card mb-4">
    <div class="card-body">
      <h2 class="h5">Daily Completion</h2>
      <canvas id="dailyChart" height="100"></canvas>
    </div>
  </div>

  <!-- save today's progress -->
  <div class="card mb-4">
    <div class="card-body">
      <h2 class="h5">Save Today's Progress</h2>
      <form id="saveForm" class="row g-3">
        <div class="col-md-9">
          <label class="form-label" for="remarks">Remarks (optional)</label>
          <input type="text" class="form-control" id="remarks" maxlength="255">
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100">Save</button>
        </div>
      </form>
    </div>
  </div>

  <!-- saved progress records -->
  <div class="card mb-4">
    <div class="card-body">
      <h2 class="h5">Saved Progress</h2>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Total</th>
              <th>Completed</th>
              <th>Percent</th>
              <th>Remarks</th>
            </tr>
          </thead>
          <tbody id="recordsBody"></tbody>
        </table>
      </div>
    </div>
  </div>

  <?php } ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
// the PHP files for this page are in back_end/progress/
var API = "../back_end/progress/";
var chart = null;

// makes text safe before putting it inside HTML
function safe(text) {
    var div = document.createElement("div");
    div.textContent = text == null ? "" : text;
    return div.innerHTML;
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

async function getData(file, params) {
    var query = new URLSearchParams(params || {}).toString();
    var res = await fetch(API + file + "?" + query, { credentials: "same-origin" });
    return checkResponse(res);
}

async function postData(file, data) {
    var res = await fetch(API + file, {
        method: "POST",
        body: new URLSearchParams(data),
        credentials: "same-origin"
    });
    return checkResponse(res);
}

function showMessage(text, type) {
    document.getElementById("message").innerHTML =
        '<div class="alert alert-' + type + '">' + safe(text) + "</div>";
}

// get the summary and the chart data
async function loadSummary() {
    var params = {
        child_id: document.getElementById("childSelect").value,
        range: document.getElementById("range").value
    };
    var data = await getData("summary.php", params);

    if (!data.success) {
        showMessage(data.message || "Could not load the summary", "danger");
        return;
    }

    var s = data.summary;
    document.getElementById("totalTasks").textContent = s.total_tasks;
    document.getElementById("completedTasks").textContent = s.completed_tasks;
    document.getElementById("percent").textContent = s.completion_percentage + "%";
    document.getElementById("progressBar").style.width = s.completion_percentage + "%";

    drawChart(data.daily);
}

// draw the bar chart (total vs completed per day)
function drawChart(daily) {
    var labels = [];
    var totals = [];
    var completed = [];
    daily.forEach(function (day) {
        labels.push(day.date);
        totals.push(day.total);
        completed.push(day.completed);
    });

    // remove the old chart before drawing a new one
    if (chart) {
        chart.destroy();
    }

    chart = new Chart(document.getElementById("dailyChart"), {
        type: "bar",
        data: {
            labels: labels,
            datasets: [
                { label: "Total Tasks", data: totals, backgroundColor: "#4A90D9" },
                { label: "Completed", data: completed, backgroundColor: "#7ED321" }
            ]
        },
        options: { scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });
}

// get the saved progress records for the table
async function loadRecords() {
    var params = {
        child_id: document.getElementById("childSelect").value,
        range: document.getElementById("range").value
    };
    var data = await getData("read.php", params);
    var body = document.getElementById("recordsBody");

    if (!data.success || data.records.length === 0) {
        body.innerHTML = '<tr><td colspan="5" class="text-muted">No saved progress yet.</td></tr>';
        return;
    }

    var html = "";
    data.records.forEach(function (r) {
        html += "<tr>";
        html += "<td>" + safe(r.progress_date) + "</td>";
        html += "<td>" + safe(r.total_tasks) + "</td>";
        html += "<td>" + safe(r.completed_tasks) + "</td>";
        html += "<td>" + safe(r.completion_percentage) + "%</td>";
        html += "<td>" + safe(r.remarks) + "</td>";
        html += "</tr>";
    });
    body.innerHTML = html;
}

function loadAll() {
    loadSummary();
    loadRecords();
}

// run only if there are children (the form exists)
if (document.getElementById("saveForm")) {

    document.getElementById("childSelect").addEventListener("change", loadAll);
    document.getElementById("range").addEventListener("change", loadAll);

    // save today's progress
    document.getElementById("saveForm").addEventListener("submit", async function (e) {
        e.preventDefault();
        var result = await postData("create.php", {
            child_id: document.getElementById("childSelect").value,
            remarks: document.getElementById("remarks").value
        });
        showMessage(result.message, result.success ? "success" : "danger");
        if (result.success) {
            document.getElementById("remarks").value = "";
            loadAll();
        }
    });

    loadAll();
}
</script>
</body>
</html>
