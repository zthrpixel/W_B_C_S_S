<?php

include '../db.php';

if(isset($_GET['id']))
{
    $id = $_GET['id'];

    $sql = "
    DELETE FROM schedules
    WHERE schedule_id='$id'
    ";

    mysqli_query(
        $conn,
        $sql
    );

    header(
        "Location: ../../front_end/schedule.php"
    );

    exit();
}

?>