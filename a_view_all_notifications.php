<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

// Retrieve the username from the session
$username = $_SESSION["username"];

// Include database connection
include 'db.php';

// Fetch new complaints count
$newComplaintsQuery = "SELECT COUNT(*) as count FROM complaint WHERE status = 'new'";
$result = mysqli_query($db, $newComplaintsQuery);
$newComplaints = mysqli_fetch_assoc($result)['count'];

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
      <h1>Notifications</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.">Home</a></li>
          <li class="breadcrumb-item active">Notifications</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">

       <!-- ======= All recipe Section ======= -->
     <section class="category-section">
      <div class="container" data-aos="fade-up">

      <div class="row">
        <div class="col-lg-12 text-center mb-5">
        <table class="table table-hover table-striped">
            <tr>
              <th>Student Phone No</th>
              <th>Block</th>
              <th>Level</th>
              <th>Room No</th>
              <th>Type of Damage</th>
              <th>Detail of Damage</th>
              <th>Image</th>
              <th>Date</th>
            </tr>
                                                       
            <?php
                    $sql = "SELECT c.*, s.mobile_no AS mobile_no
                            FROM complaint c
                            JOIN student s ON c.student_id = s.student_id";
                    $sql_run = mysqli_query($db, $sql);
                    if (!$sql_run) {
                        die("Query failed: " . mysqli_error($db));
                    }
                    while ($row = mysqli_fetch_array($sql_run)) {
                        ?>
                        <tr>
                          <td><?php echo htmlspecialchars($row['mobile_no']); ?></td>
                          <td><?php echo htmlspecialchars($row['block']); ?></td>
                          <td><?php echo htmlspecialchars($row['level']); ?></td>
                          <td><?php echo htmlspecialchars($row['room_no']); ?></td>
                          <td><?php echo htmlspecialchars($row['typeofdamage']); ?></td>
                          <td><?php echo htmlspecialchars($row['details']); ?></td>
                          <td><img style="width:100px" src="data:image/jpg;charset=utf8;base64,<?php echo base64_encode($row['images']); ?>" /></td>
                          <td><?php echo htmlspecialchars($row['date']); ?></td>
                        </tr>
                    <?php } ?>
                                        
                                
                          
                             
  </table>
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