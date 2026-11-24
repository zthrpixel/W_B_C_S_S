<?php

include '../db.php';

$sql = "
SELECT
    schedules.*,
    children.first_name,
    children.last_name

FROM schedules

INNER JOIN children

ON schedules.child_id =
children.child_id

ORDER BY schedule_date DESC
";

$result =
mysqli_query($conn, $sql);

$schedules = [];

while(
    $row =
    mysqli_fetch_assoc($result)
)
{
    $schedules[] = $row;
}

?>