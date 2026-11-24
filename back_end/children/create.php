<?php

include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = 1;

    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];

    $date_of_birth = $_POST['date_of_birth'];

    $gender = $_POST['gender'];

    $development_domain =
    $_POST['development_domain'];

    $notes = $_POST['notes'];

    $sql = "
    INSERT INTO children
    (
        user_id,
        first_name,
        last_name,
        date_of_birth,
        gender,
        development_domain,
        notes
    )
    VALUES
    (
        '$user_id',
        '$first_name',
        '$last_name',
        '$date_of_birth',
        '$gender',
        '$development_domain',
        '$notes'
    )";

    mysqli_query($conn, $sql);

    header(
        "Location: ../../front_end/children.php"
    );

    exit();
}

?>
