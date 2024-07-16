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
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->
  

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

    <section class="section dashboard">
      <div class="row">

        <!-- Left side columns -->
        <div class="col-lg-8">
          <div class="row">

<!-- Recent Sales -->
<div class="col-12">
  <div class="card recent-sales overflow-auto">

    <div class="filter">
      <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
      <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
        <li class="dropdown-header text-start">
          <h6>Filter</h6>
        </li>

        <li><a class="dropdown-item" href="#">Today</a></li>
        <li><a class="dropdown-item" href="#">This Month</a></li>
        <li><a class="dropdown-item" href="#">This Year</a></li>
      </ul>
    </div>

    <div class="card-body">
      <h5 class="card-title">Complaint Status<span>| Today</span></h5>

      <table class="table table-borderless datatable">
        <thead>
          <tr>
            <th scope="col">Date</th>
            <th scope="col">Student No</th>
            <th scope="col">Type of Damage</th>
            <th scope="col">Block</th>
            <th scope="col">Level</th>
            <th scope="col">Room</th>
            <th scope="col">Status</th>
          </tr>
        </thead>
        <tbody>
        <?php
// Define the number of entries per page
$entriesPerPage = 8;

// Calculate the current page number
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the offset for the SQL query
$offset = ($page - 1) * $entriesPerPage;

// Fetch and display data
$sql = "
    SELECT c.date, s.mobile_no AS student_mobile, c.typeofdamage, c.block, c.level, c.room_no, 
           COALESCE(cf.status, c.status) AS status, cf.feedback_date AS feedback_date
    FROM complaint c
    LEFT JOIN complaint_feedback cf ON c.complaint_id = cf.complaint_id
    JOIN student s ON c.student_id = s.student_id
    ORDER BY c.date DESC, cf.feedback_date DESC
    LIMIT $entriesPerPage OFFSET $offset
";

$sql_run = mysqli_query($db, $sql);

if (!$sql_run) {
    die("Query failed: " . mysqli_error($db));
}

while ($row = mysqli_fetch_array($sql_run)) {
    ?>
    <tr>
        <td><?php echo htmlspecialchars($row['date']); ?></td>
        <td><?php echo htmlspecialchars($row['student_mobile']); ?></td>
        <td><?php echo htmlspecialchars($row['typeofdamage']); ?></td>
        <td><?php echo htmlspecialchars($row['block']); ?></td>
        <td><?php echo htmlspecialchars($row['level']); ?></td>
        <td><?php echo htmlspecialchars($row['room_no']); ?></td>
        <td>
            <?php
                if ($row['status'] == 'Completed') {
                    echo '<span class="badge bg-success">Completed</span>';
                } elseif ($row['status'] == 'In Progress') {
                    echo '<span class="badge bg-warning">In Progress</span>';
                } else {
                    echo '<span class="badge bg-primary">New</span>';
                }
            ?>
        </td>
    </tr>
    <?php
}
?>
        </tbody>

      </table>

      <!-- Pagination links -->
      <?php
      // Determine total number of pages
      $totalPagesSql = "SELECT COUNT(*) AS total FROM (
              SELECT 'complaint' AS source, c.date
              FROM complaint c
              WHERE c.status = 'New'
              UNION ALL
              SELECT 'complaint_feedback' AS source, cf.feedback_date AS date
              FROM complaint_feedback cf
              WHERE cf.status IN ('Completed', 'In Progress')
          ) AS combined";
      $totalPagesResult = mysqli_query($db, $totalPagesSql);
      $totalRows = mysqli_fetch_assoc($totalPagesResult)['total'];
      $totalPages = ceil($totalRows / $entriesPerPage);

      // Pagination links
      if ($totalPages > 1) {
          echo '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
          $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
          for ($i = 1; $i <= $totalPages; $i++) {
              echo '<li class="page-item ' . ($currentPage == $i ? 'active' : '') . '"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
          }
          echo '</ul></nav>';
      }
      ?>

    </div>

  </div>
</div><!-- End Recent Sales -->
        </div>
        </div><!-- End Left side columns -->

<!-- Right side columns -->
<div class="col-lg-4">


        <!-- Sales Card -->
        <?php
// Function to get the total number of student complaints
function getTotalStudentComplaints() {
    global $db; // Use the global database connection

    $query = "SELECT COUNT(*) AS total_complaints FROM complaint";
    $result = mysqli_query($db, $query);

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total_complaints'];
    } else {
        return 0;
    }
}

// Call the function to get the total number of complaints
$totalComplaints = getTotalStudentComplaints();
?>
<div class="card info-card sales-card">
    <div class="filter">
        <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
            <li class="dropdown-header text-start">
                <h6>Filter</h6>
            </li>
            <li><a class="dropdown-item" href="#">Today</a></li>
            <li><a class="dropdown-item" href="#">This Month</a></li>
        </ul>
    </div>
    <div class="card-body">
        <h5 class="card-title">Student Complaint <span>| Today</span></h5>
        <div class="d-flex align-items-center">
            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="ps-3">
                <h6><?php echo $totalComplaints; ?></h6>
                 <span class="text-muted small pt-2 ps-1">Complaints</span>
            </div>
        </div>
    </div>
</div><!-- End Sales Card -->

            <!-- Revenue Card -->

              <div class="card info-card revenue-card">
              <?php
// Function to get the total number of student complaints
function getTotalCompleted() {
    global $db; // Use the global database connection

    $query = "SELECT COUNT(*) AS total_completed FROM complaint_feedback WHERE status='completed'";
    $result = mysqli_query($db, $query);

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total_completed'];
    } else {
        return 0;
    }
}
// Call the function to get the total number of complaints
$total_completed = getTotalCompleted();
?>
                <div class="filter">
                  <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                      <h6>Filter</h6>
                    </li>

                    <li><a class="dropdown-item" href="#">Today</a></li>
                    <li><a class="dropdown-item" href="#">This Month</a></li>
                  </ul>
                </div>

                <div class="card-body">
                  <h5 class="card-title">Completed <span>| Today</span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-patch-check-fill"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo $total_completed; ?></h6>
                      <span class="text-muted small pt-2 ps-1">Complaints</span>

                    </div>
                  </div>
                </div>

            </div><!-- End Revenue Card -->

            <!-- Customers Card -->
            
              <div class="card info-card customers-card">
                  <?php
                  // Function to get the total number of student complaints
                  function getTotalInProgress() {
                      global $db; // Use the global database connection

                      $query = "SELECT COUNT(*) AS total_progress FROM complaint_feedback WHERE status='in progress'";
                      $result = mysqli_query($db, $query);

                      if ($result) {
                          $row = mysqli_fetch_assoc($result);
                          return $row['total_progress'];
                      } else {
                          return 0;
                      }
                  }
                  // Call the function to get the total number of complaints
                  $total_progress = getTotalInProgress();
                  ?>
                <div class="filter">
                  <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                      <h6>Filter</h6>
                    </li>

                    <li><a class="dropdown-item" href="#">Today</a></li>
                    <li><a class="dropdown-item" href="#">This Month</a></li>
                  </ul>
                </div>

                <div class="card-body">
                  <h5 class="card-title">In Progress <span>| Today</span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-patch-exclamation-fill"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo $total_progress; ?></h6>
                      <span class="text-muted small pt-2 ps-1">Complaints</span>

                    </div>
                  </div>

                </div>
              </div>

            </div><!-- End Customers Card -->
        </div>
                </main>

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