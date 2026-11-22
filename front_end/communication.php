<?php
// communication.php - talking board with picture cards
// tap a card and it speaks the word out loud
require_once __DIR__ . '/../back_end/auth/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Communication Board</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
  <style>
    body { background: #fffdf5; font-size: 18px; }
    .btn, .form-control, .form-select { min-height: 44px; border-radius: 12px; }
    .card { border-radius: 12px; }
    .picture-card {
      min-height: 150px; border: 3px solid #333; border-radius: 16px;
      background: white; font-weight: bold; font-size: 20px;
    }
    .picture-card:hover { background: #fff6d6; }
    .picture { font-size: 64px; line-height: 1.2; }
    .picture img { max-width: 80px; max-height: 80px; }
    .sentence-bar {
      min-height: 80px; border: 3px solid #4A90D9; border-radius: 16px;
      background: #eaf2fd; padding: 10px;
    }
    .sentence-word { background: white; border: 2px solid #333; border-radius: 10px; padding: 4px 10px; }
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
        <li class="nav-item"><a class="nav-link" href="progress.php">Progress</a></li>
        <li class="nav-item"><a class="nav-link active" href="communication.php">Communication</a></li>
        <li class="nav-item"><a class="nav-link" href="../back_end/auth/logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">My Talking Board</h1>
    <div class="btn-group">
      <button class="btn btn-primary" id="childModeBtn">Child</button>
      <button class="btn btn-outline-primary" id="caregiverModeBtn">Caregiver</button>
    </div>
  </div>

  <!-- messages show here -->
  <div id="message"></div>

  <!-- ===== child mode ===== -->
  <div id="childMode">

    <!-- category buttons -->
    <div id="categoryTabs" class="d-flex flex-wrap gap-2 mb-3"></div>

    <!-- sentence bar -->
    <div class="sentence-bar d-flex flex-wrap align-items-center gap-2 mb-3">
      <span class="sentence-word">I want</span>
      <span id="sentenceWords" class="d-flex flex-wrap gap-2"></span>
      <span class="ms-auto d-flex gap-2">
        <button class="btn btn-success" id="speakBtn">Speak</button>
        <button class="btn btn-danger" id="clearBtn">Clear</button>
      </span>
    </div>

    <!-- picture cards -->
    <div class="row g-3" id="cardGrid"></div>
  </div>

  <!-- ===== caregiver mode ===== -->
  <div id="caregiverMode" style="display: none;">
    <div class="row g-4">

      <div class="col-lg-6">
        <!-- add category -->
        <div class="card mb-4"><div class="card-body">
          <h2 class="h5">New Category</h2>
          <form id="categoryForm">
            <div class="mb-3">
              <label class="form-label" for="category_name">Category name</label>
              <input type="text" class="form-control" id="category_name" maxlength="100" placeholder="e.g. Toys" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Add Category</button>
          </form>
        </div></div>

        <!-- add card -->
        <div class="card mb-4"><div class="card-body">
          <h2 class="h5">Add Picture Card</h2>
          <form id="cardForm">
            <div class="mb-3">
              <label class="form-label" for="cardCategory">Category</label>
              <select class="form-select" id="cardCategory" required></select>
            </div>
            <div class="mb-3">
              <label class="form-label" for="card_name">Word</label>
              <input type="text" class="form-control" id="card_name" maxlength="100" placeholder="e.g. Apple" required>
            </div>
            <div class="mb-3">
              <label class="form-label" for="image_path">Picture (an emoji or an image path)</label>
              <input type="text" class="form-control" id="image_path" maxlength="255" placeholder="e.g. 🍎 or assets/images/apple.png">
            </div>
            <div class="mb-3">
              <label class="form-label" for="spoken_text">Spoken text (optional)</label>
              <input type="text" class="form-control" id="spoken_text" maxlength="255" placeholder="what the board says">
            </div>
            <button type="submit" class="btn btn-primary w-100">Add Card</button>
          </form>
        </div></div>

        <!-- voice settings -->
        <div class="card mb-4"><div class="card-body">
          <h2 class="h5">Voice</h2>
          <div class="mb-3">
            <label class="form-label" for="voiceLang">Language</label>
            <select class="form-select" id="voiceLang">
              <option value="en-US">English</option>
              <option value="fil-PH">Filipino</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="voiceSpeed">Speed</label>
            <input type="range" class="form-range" id="voiceSpeed" min="0.5" max="1.5" step="0.1" value="1">
          </div>
          <button class="btn btn-primary w-100" id="testVoiceBtn">Test Voice</button>
        </div></div>
      </div>

      <!-- list of categories and cards -->
      <div class="col-lg-6">
        <div class="card"><div class="card-body">
          <h2 class="h5">Categories and Cards</h2>
          <div id="manageList"></div>
        </div></div>
      </div>

    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="javascript/communication.js"></script>
</body>
</html>
