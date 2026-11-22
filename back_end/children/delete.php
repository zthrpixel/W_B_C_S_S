<?php

include '../db.php';

if(isset($_GET['id']))
{
    $id =
    $_GET['id'];

    $sql =
    "DELETE FROM children
    WHERE child_id='$id'";

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
