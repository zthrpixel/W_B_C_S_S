<?php

include '../db.php';

if(isset($_POST['updateSchedule']))
{
    $schedule_id =
    $_POST['schedule_id'];

    $schedule_name =
    $_POST['schedule_name'];

    $schedule_date =
    $_POST['schedule_date'];

    $description =
    $_POST['description'];

    $sql = "
    UPDATE schedules

    SET

    schedule_name='$schedule_name',
    schedule_date='$schedule_date',
    description='$description'

    WHERE schedule_id='$schedule_id'
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
