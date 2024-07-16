<?php
include("auth.php");
include('db.php');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

// Get the username from the session
$username = $_SESSION["username"];

// Initialize status message
$statusMsg = '';

// Check if form is submitted
if (isset($_POST['submit'])) {
    // Retrieve form data
    $fullname = $_POST['full_name'];  // Corrected name
    $email = $_POST['email'];
    $phone = $_POST['mobile_no'];

    // Debug: Log the form data
    error_log("Form Data: Full Name: $fullname, Email: $email, mobile_no: $phone");

    // Check if a file is uploaded
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        // Read the file content
        $imageData = file_get_contents($_FILES['profileImage']['tmp_name']);

        // Update profile information in the student table with profile image
        $updateSql = "UPDATE student SET full_name=?, email=?, mobile_no=?, profile_image=? WHERE username=?";
        $updateStmt = mysqli_prepare($db, $updateSql);

        // Check if the statement was prepared successfully
        if ($updateStmt) {
            // Bind parameters and execute the statement
            mysqli_stmt_bind_param($updateStmt, "sssss", $fullname, $email, $phone, $imageData, $username);
            $updateResult = mysqli_stmt_execute($updateStmt);

            // Check if the update was successful
            if ($updateResult) {
                $statusMsg = "Profile updated successfully.";
            } else {
                $statusMsg = "Profile update failed: " . mysqli_stmt_error($updateStmt);
                error_log("Error executing query: " . mysqli_stmt_error($updateStmt));
            }

            // Close statement
            mysqli_stmt_close($updateStmt);
        } else {
            $statusMsg = "Error preparing statement: " . mysqli_error($db);
            error_log("Error preparing statement: " . mysqli_error($db));
        }
    } else {
        // Update profile information in the student table without profile image
        $updateSql = "UPDATE student SET full_name=?, email=?, mobile_no=? WHERE username=?";
        $updateStmt = mysqli_prepare($db, $updateSql);

        // Check if the statement was prepared successfully
        if ($updateStmt) {
            // Bind parameters and execute the statement
            mysqli_stmt_bind_param($updateStmt, "ssss", $fullname, $email, $phone, $username);
            $updateResult = mysqli_stmt_execute($updateStmt);

            // Check if the update was successful
            if ($updateResult) {
                $statusMsg = "Profile updated successfully.";
            } else {
                $statusMsg = "Profile update failed: " . mysqli_stmt_error($updateStmt);
                error_log("Error executing query: " . mysqli_stmt_error($updateStmt));
            }

            // Close statement
            mysqli_stmt_close($updateStmt);
        } else {
            $statusMsg = "Error preparing statement: " . mysqli_error($db);
            error_log("Error preparing statement: " . mysqli_error($db));
        }
    }
}

// Redirect to profile.php with status message
header("Location: profile.php?status=" . urlencode($statusMsg));
exit();
?>
