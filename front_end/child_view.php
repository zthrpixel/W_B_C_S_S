<?php
// child_view.php - simple page for the child
// the caregiver picks a child first, then the child sees today's schedule and tasks
require_once __DIR__ . '/../back_end/auth/auth_check.php';
require_once __DIR__ . '/../back_end/config/database.php';

// get the children of the logged in user
$stmt = $pdo->prepare("SELECT child_id, first_name FROM children
                       WHERE user_id = :user_id ORDER BY first_name");
$stmt->execute([":user_id" => $_SESSION["user_id"]]);
$children = $stmt->fetchAll();

// find the selected child (must be one of this user's children)
$child = null;
$child_id = $_GET["child_id"] ?? 0;
foreach ($children as $c) {
    if ($c["child_id"] == $child_id) {
        $child = $c;
    }
}

// get today's schedule of the selected child
$schedule = [];
if ($child) {
    $stmt = $pdo->prepare("SELECT si.start_time, a.activity_name, a.icon
                           FROM schedules s
                           JOIN schedule_items si ON si.schedule_id = s.schedule_id
                           JOIN activities a ON a.activity_id = si.activity_id
                           WHERE s.child_id = :child_id AND s.schedule_date = CURDATE()
                           ORDER BY si.item_order, si.start_time");
    $stmt->execute([":child_id" => $child["child_id"]]);
    $schedule = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Child View</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
  <style>
    body { background: #fffbea; font-size: 18px; }
    .task-box { background: white; border: 4px solid #4A90D9; border-radius: 16px; }
    .task-box.done { border-color: #7ED321; background: #f1fbe3; }
    .task-box.done .task-title { text-decoration: line-through; }
    .big-btn { min-height: 64px; font-size: 24px; border-radius: 12px; }
    .progress { height: 28px; }
  </style>
</head>
<body data-page="child_view" data-child="<?php echo $child ? $child['child_id'] : 0; ?>">

<div class="container py-3" style="max-width: 720px;">

  <div class="d-flex justify-content-between mb-3">
    <a href="dashboard.php" class="btn btn-outline-secondary">Dashboard</a>
    <?php if ($child) { ?>
      <a href="child_view.php" class="btn btn-outline-primary">Switch Child</a>
    <?php } ?>
  </div>

  <?php if (!$child) { ?>

    <!-- step 1: choose a child -->
    <h1 class="h2 text-center mb-4">Who is using the planner?</h1>
    <?php if (count($children) === 0) { ?>
      <div class="alert alert-warning">
        No children yet. Add one in <a href="children.php">Children</a> first.
      </div>
    <?php } ?>
    <?php foreach ($children as $c) { ?>
      <a href="child_view.php?child_id=<?php echo $c['child_id']; ?>"
         class="btn btn-primary big-btn w-100 mb-3">
        <?php echo htmlspecialchars($c['first_name']); ?>
      </a>
    <?php } ?>

  <?php } else { ?>

    <!-- step 2: the child's day -->
    <h1 class="h2 text-center">Hi, <?php echo htmlspecialchars($child['first_name']); ?>!</h1>
    <p class="text-center text-muted"><?php echo date("l, F j"); ?></p>

    <?php if (count($schedule) > 0) { ?>
      <h2 class="h4">My Day</h2>
      <ul class="list-group mb-4">
        <?php foreach ($schedule as $item) { ?>
          <li class="list-group-item d-flex justify-content-between py-3">
            <span><?php echo htmlspecialchars($item['icon'] . ' ' . $item['activity_name']); ?></span>
            <span class="text-muted"><?php echo date("g:i A", strtotime($item['start_time'])); ?></span>
          </li>
        <?php } ?>
      </ul>
    <?php } ?>

    <h2 class="h4">My Tasks</h2>
    <div class="progress mb-2">
      <div class="progress-bar bg-success" id="progressBar" style="width: 0%;"></div>
    </div>
    <p class="text-center fw-bold" id="progressText"></p>

    <!-- the tasks are filled in by script.js -->
    <div id="childTasks"></div>

  <?php } ?>
</div>

<script src="javascript/script.js"></script>
</body>
</html>
