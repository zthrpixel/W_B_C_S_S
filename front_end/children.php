<?php
// Child profiles: list, add, edit and delete.
require_once __DIR__ . '/../back_end/auth/auth_check.php';
require_once __DIR__ . '/../back_end/children/read.php';   // sets $children
require_once __DIR__ . '/partials/helpers.php';

$activePage = "children";

$messages = [
    "created"   => ["success", "Child profile added."],
    "updated"   => ["success", "Child profile updated."],
    "deleted"   => ["success", "Child profile deleted."],
    "required"  => ["danger", "First name, last name and date of birth are required."],
    "too_long"  => ["danger", "One of the fields is too long."],
    "dob"       => ["danger", "Please enter a valid date of birth that is not in the future."],
    "not_found" => ["danger", "That child profile could not be found."],
    "server"    => ["danger", "Something went wrong. Please try again."],
];
$flashKey = is_string($_GET["status"] ?? null) ? $_GET["status"]
          : (is_string($_GET["error"] ?? null) ? $_GET["error"] : "");
$flash = $messages[$flashKey] ?? null;

$genderOptions = ["Male", "Female", "Other", "Prefer not to say"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Child Profiles</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php require __DIR__ . '/partials/navbar.php'; ?>

<main class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Child Profiles</h1>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addChildModal">
            Add Child
        </button>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash[0]) ?>" role="alert"><?= e($flash[1]) ?></div>
    <?php endif; ?>

    <?php if (!$children): ?>
        <div class="alert alert-info">No child profiles yet. Use “Add Child” to create the first one.</div>
    <?php endif; ?>

    <?php foreach ($children as $child): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h2 class="h5"><?= e($child["first_name"] . " " . $child["last_name"]) ?></h2>
                <p class="mb-1 text-muted">
                    Born <?= e($child["date_of_birth"]) ?>
                    <?php if ($child["gender"]): ?> · <?= e($child["gender"]) ?><?php endif; ?>
                </p>
                <?php if ($child["development_domain"]): ?>
                    <p class="mb-1"><strong>Development domain:</strong> <?= e($child["development_domain"]) ?></p>
                <?php endif; ?>
                <?php if ($child["notes"]): ?>
                    <p class="mb-2"><?= nl2br(e($child["notes"])) ?></p>
                <?php endif; ?>

                <div class="d-flex gap-2 mt-2">
                    <button type="button"
                            class="btn btn-warning btn-sm edit-child"
                            data-bs-toggle="modal"
                            data-bs-target="#editChildModal"
                            data-child-id="<?= e($child["child_id"]) ?>"
                            data-first-name="<?= e($child["first_name"]) ?>"
                            data-last-name="<?= e($child["last_name"]) ?>"
                            data-dob="<?= e($child["date_of_birth"]) ?>"
                            data-gender="<?= e($child["gender"]) ?>"
                            data-domain="<?= e($child["development_domain"]) ?>"
                            data-notes="<?= e($child["notes"]) ?>">
                        Edit
                    </button>

                    <form action="../back_end/children/delete.php" method="POST"
                          onsubmit="return confirm('Delete this child and all of their tasks, schedules and progress records?');">
                        <input type="hidden" name="child_id" value="<?= e($child["child_id"]) ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

</main>

<?php
// The add and edit modals share the same fields.
function renderChildFields(string $prefix, array $genderOptions): void { ?>
    <div class="mb-2">
        <label class="form-label" for="<?= $prefix ?>FirstName">First name</label>
        <input type="text" id="<?= $prefix ?>FirstName" name="first_name" class="form-control" maxlength="100" required>
    </div>
    <div class="mb-2">
        <label class="form-label" for="<?= $prefix ?>LastName">Last name</label>
        <input type="text" id="<?= $prefix ?>LastName" name="last_name" class="form-control" maxlength="100" required>
    </div>
    <div class="mb-2">
        <label class="form-label" for="<?= $prefix ?>Dob">Date of birth</label>
        <input type="date" id="<?= $prefix ?>Dob" name="date_of_birth" class="form-control"
               max="<?= date("Y-m-d") ?>" required>
    </div>
    <div class="mb-2">
        <label class="form-label" for="<?= $prefix ?>Gender">Gender</label>
        <select id="<?= $prefix ?>Gender" name="gender" class="form-select">
            <option value="">Not specified</option>
            <?php foreach ($genderOptions as $option): ?>
                <option value="<?= e($option) ?>"><?= e($option) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-2">
        <label class="form-label" for="<?= $prefix ?>Domain">Development domain</label>
        <input type="text" id="<?= $prefix ?>Domain" name="development_domain" class="form-control" maxlength="150"
               placeholder="e.g. Communication">
    </div>
    <div class="mb-3">
        <label class="form-label" for="<?= $prefix ?>Notes">Notes</label>
        <textarea id="<?= $prefix ?>Notes" name="notes" class="form-control" rows="3"></textarea>
    </div>
<?php } ?>

<!-- Add Child Modal -->
<div class="modal fade" id="addChildModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="../back_end/children/create.php" method="POST">
                <div class="modal-header">
                    <h2 class="modal-title h5">Add Child</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php renderChildFields("add", $genderOptions); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Child</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Child Modal -->
<div class="modal fade" id="editChildModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="../back_end/children/update.php" method="POST">
                <input type="hidden" id="editChildId" name="child_id">
                <div class="modal-header">
                    <h2 class="modal-title h5">Edit Child</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php renderChildFields("edit", $genderOptions); ?>
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
<script src="javascript/children.js"></script>

</body>
</html>
