<?php
    require_once __DIR__ . '/../database/connect.php';

    function getLogin($user, $pass) {
        global $conn;

        $strSQL = "SELECT * FROM users WHERE username = ? and password = ?";
        $stmt = mysqli_prepare($conn, $strSQL);

        if (!$stmt) {
            error_log("MySQL Prepare Error: " . mysqli_error($conn));
            return false;
        }

        mysqli_stmt_bind_param($stmt, "ss", $user, $pass);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        return false;
    }

    function getBalance($username) {
        global $conn;

        $strSQL = "SELECT balance FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $strSQL);

        if (!$stmt) {
            error_log("MySQL Prepare Error: " . mysqli_error($conn));
            return false;
        }

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['balance'];
        }
        return 0;
    }

    function checkUserExists($username) {
        global $conn;

        $strSQL = "SELECT id FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $strSQL);

        if (!$stmt) {
            error_log("MySQL Prepare Error: " . mysqli_error($conn));
            return false;
        }

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        return ($result && mysqli_num_rows($result) > 0);
    }

    function registerUser($username, $password) {
        global $conn;

        $strSQL = "INSERT INTO users (username, password, role, balance) VALUES (?, ?, 0, 0)";
        $stmt = mysqli_prepare($conn, $strSQL);

        if (!$stmt) {
            error_log("MySQL Prepare Error: " . mysqli_error($conn));
            return false;
        }

        mysqli_stmt_bind_param($stmt, "ss", $username, $password);
        if (!mysqli_stmt_execute($stmt)) {
            error_log("MySQL Execute Error: " . mysqli_error($conn));
            return false;
        }

        return true;
    }
?>
