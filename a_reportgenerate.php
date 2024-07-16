<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION["username"];

include 'db.php';

if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch new complaints count
$newComplaintsQuery = "SELECT COUNT(*) as count FROM complaint WHERE status = 'new'";
$result = mysqli_query($db, $newComplaintsQuery);
$newComplaints = mysqli_fetch_assoc($result)['count'];

// Determine sorting options
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'feedback_date'; // Default sort by feedback_date
$sortOrder = isset($_GET['order']) && $_GET['order'] == 'desc' ? 'DESC' : 'ASC'; // Default ascending order


// Pagination setup
$entriesPerPage = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $entriesPerPage;

// Status filter setup
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$statusCondition = '';
if ($statusFilter !== 'all') {
    $statusCondition = " AND cf.status = '$statusFilter'";
}

// Fetch complaints with sorting and filtering

$sql = "SELECT cf.*, 
               c.block AS block, 
               c.room_no AS room_no, 
               c.level AS level, 
               c.typeofdamage AS typeofdamage,
               c.date AS date,
               c.details AS details,
               p.full_name AS officer_pic, p.email AS officer_email, p.mobile_no AS officer_mobile,
               s.full_name AS full_name
        FROM complaint_feedback cf
        JOIN complaint c ON cf.complaint_id = c.complaint_id
        LEFT JOIN student s ON c.student_id = s.student_id
        JOIN ppp p ON p.PPP_id = cf.PPP_id
        WHERE 1 $statusCondition
        ORDER BY $sortColumn $sortOrder
        LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($stmt, 'ii', $entriesPerPage, $offset);
mysqli_stmt_execute($stmt);
$sql_run = mysqli_stmt_get_result($stmt);

// Total pages calculation
$totalPagesSql = "SELECT CEIL(COUNT(*) / $entriesPerPage) AS total FROM complaint_feedback cf WHERE 1 $statusCondition";
$totalPagesResult = mysqli_query($db, $totalPagesSql);
$totalPages = mysqli_fetch_assoc($totalPagesResult)['total'];

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
  <div class="card">
  &nbsp;
    <div class="row mb-3">
    <div class="row">
    <div class="col-md-8"></div> <!-- Empty column to push the button to the right -->
    <div class="col-md-4 d-flex justify-content-end"> <!-- Column for the button, aligned to the end (right) -->
        <label for="generate_pdf"></label>
        <a href="generate_pdf.php?<?php echo $_SERVER['QUERY_STRING']; ?>" class="btn btn-primary">Generate PDF</a>
    </div>
</div>
    </div>
    <div class="mb-3">
      <label for="statusFilter" class="form-label">Filter by Status:</label>
      <select class="form-select" id="statusFilter" name="status" onchange="filterStatus(this)">
        <option value="all" <?php if ($statusFilter == 'all') echo 'selected'; ?>>All</option>
        <option value="In Progress" <?php if ($statusFilter == 'In Progress') echo 'selected'; ?>>In Progress</option>
        <option value="completed" <?php if ($statusFilter == 'completed') echo 'selected'; ?>>Completed</option>
      </select>
    </div>
    

    <div class="table-responsive">
      <table class="table table-hover table-striped">
        <thead class="text-center">
          <tr>
            <th><a href="?sort=officer_pic&order=<?php echo $sortOrder; ?>">Duty Officer</a></th>
            <th><a href="?sort=officer_mobile&order=<?php echo $sortOrder; ?>">Officer Phone.No</a></th>
            <th><a href="?sort=full_name&order=<?php echo $sortOrder; ?>">Student Name</a></th>
            <th><a href="?sort=typeofdamage&order=<?php echo $sortOrder; ?>">Type of Damage</a></th>
            <th><a href="?sort=block&order=<?php echo $sortOrder; ?>">Block</a></th>
            <th><a href="?sort=level&order=<?php echo $sortOrder; ?>">Level</a></th>
            <th><a href="?sort=room_no&order=<?php echo $sortOrder; ?>">Room No</a></th>
            <th><a href="?sort=details&order=<?php echo $sortOrder; ?>">Complaint Detail</a></th>
            <th><a href="?sort=feedback_details&order=<?php echo $sortOrder; ?>">Feedback Detail</a></th>
            <th><a href="?sort=date&order=<?php echo $sortOrder; ?>">Complaint Received</a></th>
            <th><a href="?sort=feedback_date&order=<?php echo $sortOrder; ?>">Complaint Repair</a></th>
            <th><a href="?sort=status&order=<?php echo $sortOrder; ?>">Status</a></th>
          </tr>
        </thead>
        <tbody>
          <?php
          if (mysqli_num_rows($sql_run) > 0) {
            while ($row = mysqli_fetch_assoc($sql_run)) {
              echo "<tr>";
              echo "<td>" . htmlspecialchars($row['officer_pic']) . "</td>";
              echo "<td>" . htmlspecialchars($row['officer_mobile']) . "</td>";
              echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
              echo "<td>" . htmlspecialchars($row['typeofdamage']) . "</td>";
              echo "<td>" . htmlspecialchars($row['block']) . "</td>";
              echo "<td>" . htmlspecialchars($row['level']) . "</td>";
              echo "<td>" . htmlspecialchars($row['room_no']) . "</td>";
              echo "<td>" . htmlspecialchars($row['details']) . "</td>";
              echo "<td>" . htmlspecialchars($row['feedback_details']) . "</td>";
              echo "<td>" . htmlspecialchars($row['date']) . "</td>";
              echo "<td>" . htmlspecialchars($row['feedback_date']) . "</td>";
              echo "<td>" . htmlspecialchars($row['status']) . "</td>";
              echo "</tr>";
            }
          } else {
            echo "<tr><td colspan='10'>No complaints found.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
      <nav aria-label='Page navigation'>
        <ul class='pagination justify-content-center'>
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class='page-item <?php echo ($page == $i) ? 'active' : ''; ?>'><a class='page-link' href='?page=<?php echo $i . '&status=' . $statusFilter . '&sort=' . $sortColumn . '&order=' . $sortOrder; ?>'><?php echo $i; ?></a></li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>
  </div>
</section>
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
  <script>
        function filterStatus(select) {
            var status = select.value;
            window.location.href = '?status=' + status;
        }
    </script>

</body>
</html>