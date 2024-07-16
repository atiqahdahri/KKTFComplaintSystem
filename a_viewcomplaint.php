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
$full_name = $matric_no = $mobile_no = $block = $level = $room_no = $typeofdamage = $details = $date = $status = $images = '';

// Check if complaint_id is set and is a valid integer
if (isset($_GET['complaint_id']) && is_numeric($_GET['complaint_id'])) {
    $complaint_id = $_GET['complaint_id'];

    // Query to fetch data based on the complaint_id and username using a prepared statement
    $sql = "SELECT student.full_name, student.username, student.mobile_no, 
                   complaint.block, complaint.level, complaint.room_no, 
                   complaint.typeofdamage, complaint.details, complaint.date,complaint.status, complaint.images 
            FROM student 
            JOIN complaint ON student.student_id = complaint.student_id 
            WHERE student.username = ? AND complaint.complaint_id = ?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "si", $username, $complaint_id);
    mysqli_stmt_execute($stmt);
    $sql_run = mysqli_stmt_get_result($stmt);

    // Check if any rows were returned
    if ($sql_run) {
        // Fetch the row
        $row = mysqli_fetch_assoc($sql_run);

        // Check if a matching record was found
        if ($row) {
            // Extract complaint details
            $full_name = $row['full_name'];
            $matric_no = $row['username'];
            $mobile_no = $row['mobile_no'];
            $block = $row['block'];
            $level = $row['level'];
            $room_no = $row['room_no'];
            $typeofdamage = $row['typeofdamage'];
            $details = $row['details'];
            $date = $row['date'];
            $status = $row['status'];
            $images = $row['images'];
        } else {
            echo "No matching record found for complaint ID: $complaint_id and username: $username";
        }
    } else {
        echo "Error executing query: " . mysqli_error($db);
    }
} else {
    // If complaint_id is not set or is not a valid integer, handle accordingly
    echo "Invalid complaint ID";
}
// Check if complaint_id is set and is a valid integer
if (isset($_GET['complaint_id']) && is_numeric($_GET['complaint_id'])) {
  $complaint_id = $_GET['complaint_id'];

  // Query to fetch data based on the complaint_id
  $sql = "SELECT student.full_name, student.username, student.mobile_no, 
                 complaint.block, complaint.level, complaint.room_no, 
                 complaint.typeofdamage, complaint.details, complaint.date, complaint.status, complaint.images 
          FROM student 
          JOIN complaint ON student.student_id = complaint.student_id 
          WHERE complaint.complaint_id = ?";
  $stmt = mysqli_prepare($db, $sql);
  mysqli_stmt_bind_param($stmt, "i", $complaint_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  if ($result) {
      $row = mysqli_fetch_assoc($result);

      if ($row) {
          // Extract complaint details
          $full_name = $row['full_name'];
          $matric_no = $row['username'];
          $mobile_no = $row['mobile_no'];
          $block = $row['block'];
          $level = $row['level'];
          $room_no = $row['room_no'];
          $typeofdamage = $row['typeofdamage'];
          $details = $row['details'];
          $date = $row['date'];
          $status = $row['status'];
          $images = base64_encode($row['images']);
      } else {
          echo "No matching record found for complaint ID: $complaint_id";
      }
  } else {
      echo "Error executing query: " . mysqli_error($db);
  }
} else {
  echo "Invalid complaint ID";
}


// Query for notifications
$notificationsQuery = "
    SELECT 'complaint' AS type, complaint_id, typeofdamage, block, level, room_no, date 
    FROM complaint 
    WHERE status = 'new'
    UNION ALL
    SELECT 'feedback' AS type, complaint_id, feedback_details AS typeofdamage, '' AS block, '' AS level, '' AS room_no, feedback_date AS date 
    FROM complaint_feedback 
    ORDER BY date DESC 
    LIMIT 4";

$notificationsResult = mysqli_query($db, $notificationsQuery);
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

    <div class="pagetitle">
      <h1>Dashboard</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.html">Home</a></li>
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="inner-page">
            <div class="container">
                <div class="card">
                    <div class="card-body">
                        <form class="row g-3" action="" method="post" enctype="multipart/form-data">
                            <div class="col-md-12">
                                <label for="inputName5" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="inputName5" value="<?php echo htmlspecialchars($full_name); ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label for="inputMatricNo" class="form-label">Matric No</label>
                                <input type="text" class="form-control" id="inputMatricNo" value="<?php echo htmlspecialchars($matric_no); ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label for="inputMobileNo" class="form-label">Mobile Number</label>
                                <input type="text" class="form-control" id="inputMobileNo" value="<?php echo htmlspecialchars($mobile_no); ?>" disabled>
                            </div>
                            <div class="col-md-3">
                                <label for="inputTypeOfDamage" class="form-label">Type of Damage</label>
                                <input type="text" class="form-control" id="inputTypeOfDamage" value="<?php echo htmlspecialchars($typeofdamage); ?>" disabled>
                            </div>
                            <div class="col-md-3">
                                <label for="inputBlock" class="form-label">Block</label>
                                <input type="text" class="form-control" id="inputBlock" value="<?php echo htmlspecialchars($block); ?>" disabled>
                            </div>
                            <div class="col-md-3">
                                <label for="inputLevel" class="form-label">Level</label>
                                <input type="text" class="form-control" id="inputLevel" value="<?php echo htmlspecialchars($level); ?>" disabled>
                            </div>
                            <div class="col-md-3">
                                <label for="inputRoomNo" class="form-label">Room Number</label>
                                <input type="text" class="form-control" id="inputRoomNo" value="<?php echo htmlspecialchars($room_no); ?>" disabled>
                            </div>
                            <div class="col-md-12">
                                <label for="inputDetails" class="form-label">Details of The Damage</label>
                                <input type="text" class="form-control" id="inputDetails" name="details" value="<?php echo htmlspecialchars($details); ?>" disabled>
                            </div>
                            <div class="col-md-4">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="date" name="date" value="<?php echo $date; ?>" disabled>
                            </div>
                            <div class="col-md-4">
                                <label for="status" class="form-label">Complaint Status</label>
                                <input type="status" class="form-control" id="status" name="status" value="<?php echo $status; ?>" disabled>
                            </div>
                            <div class="col-md-4">
                            <label for="image" class="form-label">Image</label>
                            <img src="data:image/jpeg;base64,<?php echo $images; ?>" alt="Complaint Image" class="img-fluid" />
                          </div>
                            <div class="col-md-6"></div>
                            <div class="text-center">
                                <a class='btn btn-primary' href='a_studentcomplaint.php' role='button'>Back</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

  </main><!-- End #main -->

  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <footer id="footer">

    <div class="container">
      <div class="copyright">
        &copy; Copyright <strong><span>Maxim</span></strong>. All Rights Reserved
      </div>
      <div class="credits">
        <!-- All the links in the footer should remain intact. -->
        <!-- You can delete the links only if you purchased the pro version. -->
        <!-- Licensing information: https://bootstrapmade.com/license/ -->
        <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/maxim-free-onepage-bootstrap-theme/ -->
        Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>
      </div>
    </div>
  </footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main1.js"></script>

</body>

</html>