<?php

include '../db.php';

if(isset($_POST['updateChild']))
{
    $child_id = $_POST['child_id'];

    $first_name =
    $_POST['first_name'];

    $last_name =
    $_POST['last_name'];

    $date_of_birth =
    $_POST['date_of_birth'];

    $gender =
    $_POST['gender'];

    $development_domain =
    $_POST['development_domain'];

    $notes =
    $_POST['notes'];

    $sql = "
    UPDATE children

    SET

    first_name='$first_name',
    last_name='$last_name',
    date_of_birth='$date_of_birth',
    gender='$gender',
    development_domain='$development_domain',
    notes='$notes'

    WHERE child_id='$child_id'
    ";

    mysqli_query(
        $conn,
        $sql
    );

    header(
        "Location: ../../front_end/children.php"
    );

    exit();
}

?>
