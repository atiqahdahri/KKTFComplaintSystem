<?php
include("auth.php");
include('db.php');

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

// Get the username from the session
$username = $_SESSION["username"];

// Fetch new complaints count
$newComplaintsQuery = "SELECT COUNT(*) as count FROM complaint WHERE status = 'new'";
$result = mysqli_query($db, $newComplaintsQuery);
$newComplaints = mysqli_fetch_assoc($result)['count'];

// Initialize variables to hold complaint data
$office_pic = $office_email = $office_mobile = $feedback_details = $feedback_date  = $status  = $block = $level = $room_no = $typeofdamage = $details = $date = '';

// Check if complaint_id is set and is a valid integer
if (isset($_GET['complaint_id']) && is_numeric($_GET['complaint_id'])) {
    $complaint_id = $_GET['complaint_id'];

    // Query to fetch data based on the complaint_id and username using a prepared statement
    $sql = "SELECT cf.*, 
               c.block AS block, 
               c.room_no AS room_no, 
               c.level AS level, 
               c.typeofdamage AS typeofdamage,
               c.date AS date,
               c.details AS details,
               p.full_name AS officer_pic, 
               p.email AS officer_email, 
               p.mobile_no AS officer_mobile,
               s.full_name AS full_name
        FROM complaint_feedback cf
        JOIN complaint c ON cf.complaint_id = c.complaint_id
        LEFT JOIN student s ON c.student_id = s.student_id
        JOIN ppp p ON p.PPP_id = cf.PPP_id
        WHERE cf.complaint_id = ?";


    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $complaint_id);
    mysqli_stmt_execute($stmt);
    $sql_run = mysqli_stmt_get_result($stmt);

    // Check if any rows were returned
    if ($sql_run) {
        // Fetch the row
        $row = mysqli_fetch_assoc($sql_run);

        // Check if a matching record was found
        if ($row) {
            // Extract complaint details
            $office_pic = $row['officer_pic'];
            $office_email = $row['officer_email'];
            $office_mobile = $row['officer_mobile'];
            $feedback_details = $row['feedback_details'];
            $feedback_date = $row['feedback_date'];
            $status = $row['status'];
            $block = $row['block'];
            $level = $row['level'];
            $room_no = $row['room_no'];
            $typeofdamage = $row['typeofdamage'];
            $details = $row['details'];
            $date = $row['date'];
        } else {
            echo "No matching record found for complaint ID: $complaint_id";
        }
    } else {
        echo "Error executing query: " . mysqli_error($db);
    }
} else {
    // If complaint_id is not set or is not a valid integer, handle accordingly
    echo "Invalid complaint ID";
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Dashboard - NiceAdmin Bootstrap Template</title>
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

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
      <a class="logo d-flex align-items-center">
        <img src="assets/img/logo.png" alt="">
        <span class="d-none d-lg-block">NiceAdmin</span>
      </a>
    </div><!-- End Logo -->
    <i class="bi bi-list toggle-sidebar-btn"></i>

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">
        <li class="nav-item dropdown">
          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-bell"></i>
            <?php if ($newComplaints > 0) : ?>
            <span class="badge bg-primary badge-number"><?php echo $newComplaints; ?></span>
            <?php endif; ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
            <li class="dropdown-header">
              You have <?php echo $newComplaints; ?> new notification<?php echo ($newComplaints > 1) ? 's' : ''; ?>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <?php
              if ($newComplaints > 0) {
                $notificationsQuery = "SELECT * FROM complaint WHERE status = 'new' ORDER BY date DESC LIMIT 4";
                $notificationsResult = mysqli_query($db, $notificationsQuery);
                while ($notification = mysqli_fetch_assoc($notificationsResult)) {
                    echo '<li class="notification-item">';
                    echo '<i class="bi bi-exclamation-circle text-warning"></i>';
                    echo '<div>';
                    echo '<h4>' . htmlspecialchars($notification['typeofdamage']) . '</h4>';
                    echo '<p>Block ' . htmlspecialchars($notification['block']) . ', Room ' . htmlspecialchars($notification['level'] . $notification['room_no']) . '</p>';
                    echo '<p>' . htmlspecialchars($notification['date']) . '</p>';
                    echo '</div>';
                    echo '</li>';
                    echo '<li><hr class="dropdown-divider"></li>';
                }
            }
            ?>
            <li class="dropdown-footer">
              <a href="a_view_all_notifications.php">Show all notifications</a>
            </li>

          </ul><!-- End Notification Dropdown Items -->

        </li><!-- End Notification Nav -->

        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <img src="assets/img/profile-img.jpg" alt="Profile" class="rounded-circle">
            <span class="d-none d-md-block dropdown-toggle ps-2"><?php echo $username; ?></span>
          </a><!-- End Profile Iamge Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?php echo $username; ?></h6>
              <span>Admin</span>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>          

            <li>
              <a class="dropdown-item d-flex align-items-center" href="logout.php">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sign Out</span>
              </a>
            </li>

          </ul><!-- End Profile Dropdown Items -->
        </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->

  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

    <li class="nav-item">
        <a class="nav-link " href="a_home.php">
          <i class="bi bi-house-door-fill"></i>
          <span>Dashboard</span>
        </a>
      </li><!-- End Dashboard Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" href="a_studentcomplaint.php">
          <i class="bi bi-files"></i>
          <span>Student Complaint</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="a_complaintfeedback.php">
          <i class="bi bi-files"></i>
          <span>Complaint Feedback</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="a_reportgenerate.php">
          <i class="bi bi-file-pdf-fill"></i>
          <span>Generate Report</span>
        </a>
      </li><!-- End Blank Page Nav -->
    </ul>

  </aside><!-- End Sidebar-->

  <main id="main" class="main">
  <?php
   include 'db.php';
   ?>
    <div class="pagetitle">
      <h1>Dashboard</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.">Home</a></li>
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">

      <div class="card">
            <div class="card-body">
              <h5 class="card-title">Feedback Form</h5>
             
             <!-- Multi Columns Form -->
             <form class="row g-3" action="" method="post" enctype= "multipart/form-data">

             
             <div class="col-md-6">
                        <label for="status" class="form-label">Status of Repair</label>
                        <input type="status" class="form-control" id="status" name="status" value="<?php echo $status; ?>" disabled>
                          </div>

                        <div class="col-md-12">
                            <label for="inputName5" class="form-label">Officer In Charge</label>
                            <input type="text" class="form-control" id="inputName5" name="officer_pic" value="<?php echo htmlspecialchars($row['officer_pic']); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label for="inputEmail5" class="form-label">Email</label>
                            <input type="email" class="form-control" id="inputEmail5" name="officer_email" value="<?php echo htmlspecialchars($row['officer_email']); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label for="inputMobileNumber" class="form-label">Mobile Number</label>
                            <input type="text" class="form-control" id="inputMobileNumber" name="officer_mobile"  value="<?php echo htmlspecialchars($row['officer_mobile']); ?>"disabled>
                        </div>

                        <div class="col-md-12">
                            <label for="floatingTextarea" class="form-label">Details of the Repair</label>
                            <input type="text" class="form-control" id="inputDetails" name="details" value="<?php echo htmlspecialchars($feedback_details); ?>" disabled>
                        </div>

                        <div class="col-md-5">
                            <label for="date" class="form-label">Date of Repair</label>
                            <input type="date" class="form-control" id="date" name="feedback_date" value="<?php echo htmlspecialchars($row['feedback_date']); ?>" disabled>
                        </div>

                        <div class="col-md-5">
                            <label for="date" class="form-label">Date of Complaint Receive </label>
                            <input type="date" class="form-control" id="date" name="feedback_date" value="<?php echo htmlspecialchars($row['date']); ?>" disabled>
                        </div>

                        <div class="col-md-3">
                          <label for="typeofdamage" class="form-label">Type of Damage</label>
                          <input type="text" class="form-control" id="inputTypeOfDamage" value="<?php echo htmlspecialchars($typeofdamage); ?>" disabled>
                      </div>
                        <div class="col-md-3">
                          <label for="block" class="form-label">Block</label>
                          <input type="text" class="form-control" id="inputBlock" value="<?php echo htmlspecialchars($block); ?>" disabled>
                        </div>

                        <div class="col-md-3">
                          <label for="level" class="form-label">Level</label>
                          <input type="text" class="form-control" id="inputLevel" value="<?php echo htmlspecialchars($level); ?>" disabled>
                          </div>

                        <div class="col-md-3">
                          <label for="room_no" class="form-label">Room Number</label>
                          <input type="text" class="form-control" id="inputRoomNo" value="<?php echo htmlspecialchars($room_no); ?>" disabled>
                           </div>

                        

                        
                        <br></br>
                       <br></br>

                        <div class="text-center">
                        
                        <a class='btn btn-primary' href='a_complaintfeedback.php' role='button'>Back </a>
                            

                        </div>
                    </form><!-- End Multi Columns Form -->
            </div>
          </div>

        </div>

            </div>
          </div>
      </div>
    </section>
</div>
</div>
</section><!-- End all recipe Section -->

  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
  </footer><!-- End Footer -->

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