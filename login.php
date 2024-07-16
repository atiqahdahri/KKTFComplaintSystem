<?php
// Function to display alert and redirect
function alertAndRedirect($message, $location) {
    echo "<script>alert('$message');document.location='$location'</script>";
    exit();
}

// Start session and include database connection file
session_start();
require('db.php');

// Check if login form is submitted
if (isset($_POST['login'])) {
    // Retrieve username and password from the form
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $password = mysqli_real_escape_string($db, $_POST['password']);

    // Attempt admin login
    $admin_query = mysqli_prepare($db, "SELECT username, password FROM admin WHERE username = ?");
    mysqli_stmt_bind_param($admin_query, "s", $username);
    mysqli_stmt_execute($admin_query);
    mysqli_stmt_store_result($admin_query);

    if (mysqli_stmt_num_rows($admin_query) > 0) {
        mysqli_stmt_bind_result($admin_query, $dbUsername, $dbPassword);
        mysqli_stmt_fetch($admin_query);

        // Verify plain-text password (not recommended, consider hashing)
        if ($password === $dbPassword) {
            $_SESSION["username"] = $dbUsername;
            // Redirect to admin home page
            header('Location: a_home.php');
            exit();
        } else {
            alertAndRedirect('Invalid password!', 'login.php');
        }
    } else {
        // Attempt student login
        $student_query = mysqli_prepare($db, "SELECT username, password FROM student WHERE username = ?");
        mysqli_stmt_bind_param($student_query, "s", $username);
        mysqli_stmt_execute($student_query);
        mysqli_stmt_store_result($student_query);

        if (mysqli_stmt_num_rows($student_query) > 0) {
            mysqli_stmt_bind_result($student_query, $dbUsername, $dbHashedPassword);
            mysqli_stmt_fetch($student_query);

            // Verify hashed password
            if (password_verify($password, $dbHashedPassword)) {
                $_SESSION["username"] = $dbUsername;
                alertAndRedirect('Successfully Log In', 'index.php');
            } else {
                alertAndRedirect('Invalid password!', 'login.php');
            }
        } else {
            // Attempt PPP login
            $ppp_query = mysqli_prepare($db, "SELECT username, password FROM PPP WHERE username = ?");
            mysqli_stmt_bind_param($ppp_query, "s", $username);
            mysqli_stmt_execute($ppp_query);
            mysqli_stmt_store_result($ppp_query);

            if (mysqli_stmt_num_rows($ppp_query) > 0) {
                mysqli_stmt_bind_result($ppp_query, $dbUsername, $dbHashedPassword);
                mysqli_stmt_fetch($ppp_query);

                // Verify hashed password
                if (password_verify($password, $dbHashedPassword)) {
                    $_SESSION["username"] = $dbUsername;
                    alertAndRedirect('Successfully Log In', 'p_home.php');
                } else {
                    alertAndRedirect('Invalid password!', 'login.php');
                }
            } else {
                alertAndRedirect('Invalid username or password!', 'login.php');
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Login</title>
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
              </div><!-- End Logo -->

              <div class="card mb-3">

              <div class="card-body">

<div class="pt-4 pb-2">
  <h5 class="card-title text-center pb-0 fs-4">Login to Your Account</h5>
</div>

<form class="row g-3 needs-validation" method="post" action="login.php">

  <div class="col-12">
    <div class="input-group has-validation">
      <span class="input-group-text"><i class="bi bi-person"></i></span>
      <input type="text" name="username" class="form-control" oninput="this.value = this.value.toUpperCase()" placeholder="Matric No/Staff ID" required>
      <div class="invalid-feedback">Please enter your matric number / Staff ID.</div>
    </div>
  </div>
 

  <div class="col-12">
    <div class="input-group has-validation">
      <span class="input-group-text"><i class="bi bi-lock"></i></span>
      <input type="password" name="password" class="form-control" placeholder="Password" required>
      <div class="invalid-feedback">Please enter your password!</div>
    </div>
  </div>
  
  <div class="col-12">
    <button class="btn btn-primary w-100" type="submit"  name="login" value="login">Login</button>
  </div>
  <div class="col-12">
    <p class="small mb-0">Don't remember your password?<a href="forgot_password.php">Forgot Password</a></p>
</div> 
  <div class="col-12">
    <p class="small mb-0">Don't have account? <a href="register.php">Create an account</a></p>
  </div>
</form>

</div>
</div>


              <div class="credits">
                <!-- All the links in the footer should remain intact. -->
                <!-- You can delete the links only if you purchased the pro version. -->
                <!-- Licensing information: https://bootstrapmade.com/license/ -->
                <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/ -->
                <!-- Designed by <a href="https://bootstrapmade.com/"></a> -->
              </div>

            </div>
          </div>
        </div>

      </section>

    </div>
  </main><!-- End #main -->

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