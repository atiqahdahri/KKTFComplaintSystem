<?php
include('db.php');

$errors = []; // Initialize an array to store errors

if (isset($_POST['submit'])) {
    $user_type = mysqli_real_escape_string($db, $_POST['user_type']);
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $full_name = mysqli_real_escape_string($db, $_POST['full_name']);
    $mobile_no = mysqli_real_escape_string($db, $_POST['mobile_no']);
    $password = mysqli_real_escape_string($db, $_POST['password']);

    // Common checks for both student and PPP users
    $checkUsernameQuery = "
        SELECT username FROM student WHERE username = '$username'
        UNION
        SELECT username FROM PPP WHERE username = '$username'
    ";
    $checkUsernameResult = mysqli_query($db, $checkUsernameQuery);

    if (mysqli_num_rows($checkUsernameResult) > 0) {
        $errors['username'] = "Username already exists. Please choose a different one.";
    }

    $checkUsermailQuery = "
        SELECT email FROM student WHERE email = '$email'
        UNION
        SELECT email FROM PPP WHERE email = '$email'
    ";
    $checkUsermailResult = mysqli_query($db, $checkUsermailQuery);

    if (mysqli_num_rows($checkUsermailResult) > 0) {
        $errors['email'] = "Email already exists. Please choose a different one.";
    }

    $checkPhoneQuery = "
        SELECT mobile_no FROM student WHERE mobile_no = '$mobile_no'
        UNION
        SELECT mobile_no FROM PPP WHERE mobile_no = '$mobile_no'
    ";
    $checkPhoneResult = mysqli_query($db, $checkPhoneQuery);

    if (mysqli_num_rows($checkPhoneResult) > 0) {
        $errors['mobile_no'] = "Phone Number already exists. Please use a different one.";
    }

    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert into the correct table based on user type
        if ($user_type == 'student') {
            $query = "INSERT INTO `student` (username, full_name, email, mobile_no, password) VALUES ('$username', '$full_name', '$email', '$mobile_no', '$hashed_password')";
        } else {
            $query = "INSERT INTO `PPP` (username, full_name, email, mobile_no, password) VALUES ('$username', '$full_name', '$email', '$mobile_no', '$hashed_password')";
        }

        $result = mysqli_query($db, $query);

        if ($result) {
            // Redirect to login page after successful registration
            header('location: login.php');
        } else {
            // Display an error message
            echo "Registration failed. Error: " . mysqli_error($db);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Pages / Register - NiceAdmin Bootstrap Template</title>
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
                    <h5 class="card-title text-center pb-0 fs-4">Create an Account</h5>
                    <p class="text-center small">Enter your personal details to create account</p>
                  </div>

                  <form class="row g-3 needs-validation" action="" method="post">
    <!-- User Type Selection -->
    <div class="col-12">
        <label for="userType" class="form-label">Register as</label>
        <select name="user_type" class="form-select" id="userType" required>
            <option value="student">Student</option>
            <option value="ppp">PPP</option>
        </select>
        <div class="invalid-feedback">Please select a user type</div>
    </div>

    <!-- Matric Number -->
    <div class="col-12">
        <label for="yourUsername" class="form-label">Matric Number (eg: DI210122)</label>
        <div class="input-group has-validation">
            <span class="input-group-text" id="inputGroupPrepend">@</span>
            <input type="text" name="username" class="form-control" id="Username" oninput="this.value = this.value.toUpperCase()" maxlength="8" pattern="[A-Za-z]{2}[0-9]{6}" required>
            <div class="invalid-feedback">Please insert your matric number (eg:di210122)</div>
        </div>
    </div>

    <!-- Full Name -->
    <div class="col-12">
        <label for="yourFullname" class="form-label">Full Name</label>
        <input type="text" name="full_name" class="form-control" id="full_name" oninput="capitalizeWords(this)" required>
        <div class="invalid-feedback">Please insert your full name</div>
    </div>

    <!-- Email -->
    <div class="col-12">
        <label for="yourEmail" class="form-label">Your Email</label>
        <input type="email" name="email" class="form-control" id="email" required>
        <div class="invalid-feedback">Please enter a valid Email address</div>
    </div>

    <!-- Mobile Number -->
    <div class="col-12">
        <label for="yourmobile_no" class="form-label">Mobile Number</label>
        <input type="text" name="mobile_no" class="form-control" id="mobile_no" required>
        <div class="invalid-feedback">Please enter a valid mobile phone number (e.g., 012-1234567).</div>
    </div>

    <!-- Password -->
    <div class="col-12">
        <label for="yourPassword" class="form-label">Password</label>
        <input type="password" name="password" class="form-control" id="password" required>
        <div class="invalid-feedback">Please enter your password!</div>
    </div>

    <!-- Submit Button -->
    <div class="col-12">
        <button name="submit" class="btn btn-primary w-100" type="submit">Create Account</button>
    </div>

    <div class="col-12">
        <p class="small mb-0">Already have an account? <a href="login.php">Log in</a></p>
    </div>

    <!-- Display Errors -->
    <?php if (!empty($errors)): ?>
        <div class="col-12 mt-3">
            <div class="alert alert-danger" role="alert">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
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
  <script>
    function capitalizeWords(input) {
    let words = input.value.toLowerCase().split(' ');
    for (let i = 0; i < words.length; i++) {
        words[i] = words[i].charAt(0).toUpperCase() + words[i].substring(1);
    }
    input.value = words.join(' ');
}
</script>
  <script>
    // Function to automatically format Malaysian mobile phone number
    document.getElementById('mobile_no').addEventListener('input', function (e) {
        var x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,7})/);
        e.target.value = !x[2] ? x[1] : x[1] + '-' + x[2];
    });
</script>

</body>

</html>