<?php
include('db.php');

$errors = []; // Initialize an array to store errors

if (isset($_POST['submit'])) {
    // Sanitize and validate inputs
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $password = mysqli_real_escape_string($db, $_POST['password']); // Assuming password is provided to reset

    // Validate that username and password are provided
    if (empty($username)) {
        $errors[] = "Matric No is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if the username exists in the student table
        $student_query = "SELECT username FROM student WHERE username='$username'";
        $student_result = mysqli_query($db, $student_query);

        // Check if the username exists in the PPP table
        $ppp_query = "SELECT username FROM PPP WHERE username='$username'";
        $ppp_result = mysqli_query($db, $ppp_query);

        if (mysqli_num_rows($student_result) > 0) {
            // Update the password in the student table
            $update_query = "UPDATE student SET password='$hashed_password' WHERE username='$username'";
            $result = mysqli_query($db, $update_query);
        } elseif (mysqli_num_rows($ppp_result) > 0) {
            // Update the password in the PPP table
            $update_query = "UPDATE PPP SET password='$hashed_password' WHERE username='$username'";
            $result = mysqli_query($db, $update_query);
        } else {
            $errors[] = "Username not found.";
        }

        if (empty($errors) && $result) {
            // Redirect to login page after successful password update
            header('Location: login.php');
            exit();
        } else {
            // Display an error message
            echo "Password update failed. Error: " . mysqli_error($db);
        }
    } else {
        // Display validation errors
        foreach ($errors as $error) {
            echo "<p>$error</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Forgot Password</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="assets/css/style.css" rel="stylesheet">

    <!-- =======================================================
    * Template Name: NiceAdmin
    * Updated: Nov 17 2023 with Bootstrap v5.3.2
    * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
    * Author: BootstrapMade.com
    * License: https://bootstrapmade.com/license/
    ======================================================== -->
</head>

<body>
    <main>
        <div class="container">
            <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">
                            <div class="d-flex justify-content-center py-4">
                                <a href="index.html" class="logo d-flex align-items-center w-auto">
                                    <img src="assets/img/logo.png" alt="">
                                    <span class="d-none d-lg-block">E-Complaint KKTF</span>
                                </a>
                            </div>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="pt-4 pb-2">
                                        <h5 class="card-title text-center pb-0 fs-4">Forgot Password</h5>
                                    </div>
                                    <form class="row g-3 needs-validation" method="post" action="forgot_password.php">
                                        <div class="col-12">
                                            <div class="input-group has-validation">
                                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                                <input type="text" name="username" class="form-control" oninput="this.value = this.value.toUpperCase()"  placeholder="Matric No" required>
                                                <div class="invalid-feedback">Please enter your Matric no</div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="input-group has-validation">
                                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                                <input type="password" name="password" class="form-control" placeholder="New Password" required>
                                                <div class="invalid-feedback">Please enter your new password!</div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button class="btn btn-primary w-100" type="submit" name="submit">Reset Password</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="credits"></div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/chart.js/chart.umd.js"></script>
    <script src="assets/vendor/echarts/echarts.min.js"></script>
    <script src="assets/vendor/quill/quill.min.js"></script>
    <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="assets/js/main.js"></script>
</body>
</html>
