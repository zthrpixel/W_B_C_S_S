<?php

include '../db.php';

$sql =
"SELECT * FROM children
ORDER BY child_id DESC";

$result =
mysqli_query($conn, $sql);

$children = [];

while (
    $row =
    mysqli_fetch_assoc($result)
) {

    $children[] = $row;

}

?>
