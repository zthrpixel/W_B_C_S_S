<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Child Profiles</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>Child Profiles</h2>

        <button
            class="btn btn-success"
            data-bs-toggle="modal"
            data-bs-target="#addChildModal">

            Add Child

        </button>

    </div>

    <!-- Child List -->

    <div class="card mb-3">

        <div class="card-body">

            <h5>Michael Doe</h5>

            <p>Communication</p>

            <button class="btn btn-warning btn-sm">
                Edit
            </button>

            <button
                class="btn btn-danger btn-sm"
                onclick="confirmDelete()">

                Delete

            </button>

        </div>

    </div>

</div>

<!-- Add Child Modal -->

<div class="modal fade" id="addChildModal">

<div class="modal-dialog">

<div class="modal-content">

<div class="modal-header">
    <h5>Add Child</h5>
</div>

<div class="modal-body">

<form
action="../back_end/children/create.php"
method="POST">

<input
type="text"
name="first_name"
class="form-control mb-2"
placeholder="First Name">

<input
type="text"
name="last_name"
class="form-control mb-2"
placeholder="Last Name">

<input
type="date"
name="date_of_birth"
class="form-control mb-2">

<select
name="gender"
class="form-control mb-2">

<option>Male</option>
<option>Female</option>

</select>

<input
type="text"
name="development_domain"
class="form-control mb-2"
placeholder="Development Domain">

<textarea
name="notes"
class="form-control mb-2"
placeholder="Notes"></textarea>

<button
type="submit"
class="btn btn-success">

Save Child

</button>

</form>

</div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/children.js"></script>

</body>
</html>
