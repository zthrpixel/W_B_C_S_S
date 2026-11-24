<?php
// Visual schedules: list, search, create, edit and delete.
require_once __DIR__ . '/../back_end/auth/auth_check.php';
require_once __DIR__ . '/../back_end/children/read.php';    // sets $children
require_once __DIR__ . '/../back_end/schedules/read.php';   // sets $schedules, $itemsBySchedule
require_once __DIR__ . '/partials/helpers.php';

$activePage = "schedule";

$messages = [
    "created"   => ["success", "Schedule created."],
    "updated"   => ["success", "Schedule updated."],
    "deleted"   => ["success", "Schedule deleted."],
    "required"  => ["danger", "Child, schedule name and date are required."],
    "too_long"  => ["danger", "The schedule name must be 150 characters or less."],
    "date"      => ["danger", "Please enter a valid schedule date."],
    "activity"  => ["danger", "Each activity needs both a time and a name (names up to 150 characters)."],
    "not_found" => ["danger", "That child or schedule could not be found."],
    "server"    => ["danger", "Something went wrong. Please try again."],
];
$flashKey = is_string($_GET["status"] ?? null) ? $_GET["status"]
          : (is_string($_GET["error"] ?? null) ? $_GET["error"] : "");
$flash = $messages[$flashKey] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Creator</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php require __DIR__ . '/partials/navbar.php'; ?>

<main class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Schedule Creator</h1>

        <?php if ($children): ?>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#scheduleModal">
                Create Schedule
            </button>
        <?php endif; ?>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash[0]) ?>" role="alert"><?= e($flash[1]) ?></div>
    <?php endif; ?>

    <?php if (!$children): ?>
        <div class="alert alert-info">
            Add a <a href="children.php">child profile</a> first, then you can create schedules for them.
        </div>
    <?php endif; ?>

    <!-- Search -->
    <label class="visually-hidden" for="searchSchedule">Search schedules</label>
    <input type="text" id="searchSchedule" class="form-control mb-4"
           placeholder="Search by schedule, child or activity">

    <!-- Schedule cards -->
    <div class="row" id="scheduleList">

        <?php foreach ($schedules as $schedule): ?>
            <?php $items = $itemsBySchedule[$schedule["schedule_id"]] ?? []; ?>
            <div class="col-md-6 mb-4 schedule-col">
                <div class="card shadow-sm h-100">
                    <div class="card-body">

                        <h2 class="card-title h5"><?= e($schedule["schedule_name"]) ?></h2>

                        <p class="mb-1"><strong>Child:</strong> <?= e($schedule["first_name"] . " " . $schedule["last_name"]) ?></p>
                        <p class="mb-2"><strong>Date:</strong> <?= e($schedule["schedule_date"]) ?></p>

                        <?php if ($schedule["description"]): ?>
                            <p class="text-muted"><?= nl2br(e($schedule["description"])) ?></p>
                        <?php endif; ?>

                        <h3 class="h6">Activities</h3>
                        <?php if ($items): ?>
                            <ul class="list-group mb-3">
                                <?php foreach ($items as $item): ?>
                                    <li class="list-group-item">
                                        <?= e(date("g:i A", strtotime($item["start_time"]))) ?>
                                        – <?= e($item["activity_name"]) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">No activities added.</p>
                        <?php endif; ?>

                        <div class="d-flex gap-2">
                            <button type="button"
                                    class="btn btn-warning btn-sm edit-schedule"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editScheduleModal"
                                    data-schedule-id="<?= e($schedule["schedule_id"]) ?>"
                                    data-name="<?= e($schedule["schedule_name"]) ?>"
                                    data-date="<?= e($schedule["schedule_date"]) ?>"
                                    data-description="<?= e($schedule["description"]) ?>">
                                Edit
                            </button>

                            <form action="../back_end/schedules/delete.php" method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this schedule?');">
                                <input type="hidden" name="schedule_id" value="<?= e($schedule["schedule_id"]) ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>

    </div>

    <?php if ($children && !$schedules): ?>
        <div class="alert alert-info">No schedules yet. Use “Create Schedule” to make the first one.</div>
    <?php endif; ?>
    <p id="noScheduleMatches" class="text-muted" hidden>No schedules match your search.</p>

</main>

<!-- Create Schedule Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="../back_end/schedules/create.php" method="POST">

                <div class="modal-header">
                    <h2 class="modal-title h5">Create Schedule</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label" for="scheduleChild">Child</label>
                        <select id="scheduleChild" name="child_id" class="form-select" required>
                            <option value="">Choose a child</option>
                            <?php foreach ($children as $child): ?>
                                <option value="<?= e($child["child_id"]) ?>">
                                    <?= e($child["first_name"] . " " . $child["last_name"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="scheduleName">Schedule name</label>
                        <input type="text" id="scheduleName" name="schedule_name" class="form-control"
                               maxlength="150" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="scheduleDate">Schedule date</label>
                        <input type="date" id="scheduleDate" name="schedule_date" class="form-control"
                               value="<?= date("Y-m-d") ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="scheduleDescription">Description</label>
                        <textarea id="scheduleDescription" name="description" class="form-control" rows="3"></textarea>
                    </div>

                    <hr>

                    <h3 class="h5">Daily Activities</h3>
                    <div id="activityRows">
                        <div class="row g-2 mb-2 activity-row">
                            <div class="col-md-4">
                                <input type="time" name="activity_time[]" class="form-control" aria-label="Activity time">
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="activity_name[]" class="form-control" maxlength="150"
                                       placeholder="Activity name" aria-label="Activity name">
                            </div>
                        </div>
                    </div>

                    <button type="button" id="addActivityRow" class="btn btn-outline-secondary mt-2">
                        Add Activity
                    </button>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Schedule</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Edit Schedule Modal -->
<div class="modal fade" id="editScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="../back_end/schedules/update.php" method="POST">
                <input type="hidden" id="editScheduleId" name="schedule_id">

                <div class="modal-header">
                    <h2 class="modal-title h5">Edit Schedule</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="editScheduleName">Schedule name</label>
                        <input type="text" id="editScheduleName" name="schedule_name" class="form-control"
                               maxlength="150" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="editScheduleDate">Schedule date</label>
                        <input type="date" id="editScheduleDate" name="schedule_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="editScheduleDescription">Description</label>
                        <textarea id="editScheduleDescription" name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <p class="form-text mb-0">Activities are set when a schedule is created.</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="javascript/schedule.js"></script>

</body>
</html>
