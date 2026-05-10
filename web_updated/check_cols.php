<?php
require 'd:/ProgramFile/XAMPP/htdocs/database/connect.php';
$r = mysqli_query($conn, 'DESCRIBE products');
while ($row = mysqli_fetch_assoc($r)) {
    echo $row['Field'] . ': ' . $row['Type'] . "\n";
}
