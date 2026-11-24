<?php
// Shared navigation bar for the Bootstrap-based pages.
// Set $activePage (e.g. "tasks") before including this file.
$activePage = $activePage ?? "";

$navLinks = [
    "dashboard"     => ["dashboard.php", "Dashboard"],
    "children"      => ["children.php", "Children"],
    "schedule"      => ["schedule.php", "Schedules"],
    "tasks"         => ["tasks.php", "Tasks"],
    "progress"      => ["progress.php", "Progress"],
    "communication" => ["communication.php", "Communication"],
    "child_view"    => ["child_view.php", "Child View"],
];
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Visual Schedule</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#mainNav" aria-controls="mainNav"
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <?php foreach ($navLinks as $key => [$href, $label]): ?>
                    <li class="nav-item">
                        <a class="nav-link<?= $key === $activePage ? " active" : "" ?>"
                           href="<?= $href ?>"<?= $key === $activePage ? ' aria-current="page"' : "" ?>><?= $label ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <a class="btn btn-outline-light btn-sm" href="../back_end/auth/logout.php">Log Out</a>
        </div>
    </div>
</nav>
