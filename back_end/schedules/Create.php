<?php

include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    $child_id = $_POST['child_id'];

    $schedule_name =
    $_POST['schedule_name'];

    $schedule_date =
    $_POST['schedule_date'];

    $description =
    $_POST['description'];

    $created_by = 1;

    $sql = "
    INSERT INTO schedules
    (
        child_id,
        created_by,
        schedule_name,
        schedule_date,
        description
    )
    VALUES
    (
        '$child_id',
        '$created_by',
        '$schedule_name',
        '$schedule_date',
        '$description'
    )";

    mysqli_query($conn, $sql);

    header(
        "Location: ../../front_end/schedule.php"
    );

    exit();
}

?>