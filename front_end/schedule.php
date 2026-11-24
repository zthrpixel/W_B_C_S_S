<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Creator</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>Schedule Creator</h2>

        <button
            class="btn btn-success"
            data-bs-toggle="modal"
            data-bs-target="#scheduleModal">

            <i class="bi bi-plus-circle"></i>
            Create Schedule

        </button>

    </div>

    <!-- Search -->

    <input
        type="text"
        id="searchSchedule"
        class="form-control mb-4"
        placeholder="Search Schedule">

    <!-- Schedule Cards -->

    <div class="row">

        <div class="col-md-6 mb-4">

            <div class="card shadow-sm">

                <div class="card-body">

                    <h5 class="card-title">
                        Morning Routine
                    </h5>

                    <p>
                        <strong>Child:</strong>
                        Michael Doe
                    </p>

                    <p>
                        <strong>Date:</strong>
                        2026-11-23
                    </p>

                    <h6>Activities</h6>

                    <ul class="list-group mb-3">

                        <li class="list-group-item">
                            08:00 AM - Breakfast
                        </li>

                        <li class="list-group-item">
                            09:00 AM - Reading
                        </li>

                        <li class="list-group-item">
                            10:00 AM - Therapy
                        </li>

                    </ul>

                    <button
                        class="btn btn-warning btn-sm">

                        Edit

                    </button>

                    <button
                        class="btn btn-danger btn-sm delete-btn">

                        Delete

                    </button>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- Create Schedule Modal -->

<div class="modal fade" id="scheduleModal">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Create Schedule
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <form
                    action="../back_end/schedules/create.php"
                    method="POST">

                    <div class="mb-3">

                        <label class="form-label">
                            Child
                        </label>

                        <select
                            name="child_id"
                            class="form-select">

                            <option>
                                Michael Doe
                            </option>

                            <option>
                                Sarah Cruz
                            </option>

                        </select>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Schedule Name
                        </label>

                        <input
                            type="text"
                            name="schedule_name"
                            class="form-control">

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Schedule Date
                        </label>

                        <input
                            type="date"
                            name="schedule_date"
                            class="form-control">

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Description
                        </label>

                        <textarea
                            name="description"
                            class="form-control"
                            rows="3"></textarea>

                    </div>

                    <hr>

                    <h5>Daily Activities</h5>

                    <div class="row">

                        <div class="col-md-4">

                            <input
                                type="time"
                                class="form-control">

                        </div>

                        <div class="col-md-8">

                            <input
                                type="text"
                                class="form-control"
                                placeholder="Activity Name">

                        </div>

                    </div>

                    <button
                        type="button"
                        class="btn btn-outline-secondary mt-3">

                        Add Activity

                    </button>

                    <div class="mt-4">

                        <button
                            type="submit"
                            class="btn btn-success">

                            Save Schedule

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/schedule.js"></script>

</body>
</html>
