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
$newComplaintsQuery = "SELECT COUNT(*) as count FROM complaint WHERE status = 'New'";
$result = mysqli_query($db, $newComplaintsQuery);
$newComplaints = mysqli_fetch_assoc($result)['count'];

// Get status message and disabled flag from URL
$statusMsg = isset($_GET['status']) ? $_GET['status'] : '';
$disabledFlag = isset($_GET['disabled']) ? $_GET['disabled'] : 0;

// Sorting and Filtering
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'feedback_date';
$sortOrder = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';
$filterStatus = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';

// Pagination setup
$itemsPerPage = 10; // Number of items per page
$currentPage = isset($_GET['page']) ? $_GET['page'] : 1; // Default to page 1
$offset = ($currentPage - 1) * $itemsPerPage;

// Construct SQL query with sorting and filtering
$sql = "SELECT p.full_name AS officer_pic, p.email AS officer_email, p.mobile_no AS officer_mobile, cf.feedback_details, cf.feedback_date, cf.status, 
        c.block, c.room_no, c.level, c.typeofdamage, c.complaint_id 
        FROM complaint_feedback cf
        JOIN complaint c ON cf.complaint_id = c.complaint_id
        JOIN ppp p ON p.PPP_id = cf.PPP_id";



// Add filter condition
if (!empty($filterStatus)) {
    $sql .= " WHERE c.status = '$filterStatus'";
}

// Add sorting
$sql .= " ORDER BY $sortColumn $sortOrder";
$sql .= " LIMIT $itemsPerPage OFFSET $offset";

$sql_run = mysqli_query($db, $sql);

if (!$sql_run) {
    die("Query failed: " . mysqli_error($db));
}

// Count total records for pagination
$totalRecordsQuery = "SELECT COUNT(*) AS total FROM complaint_feedback cf JOIN complaint c ON cf.complaint_id = c.complaint_id";
if (!empty($filterStatus)) {
    $totalRecordsQuery .= " WHERE c.status = '$filterStatus'";
}
$totalRecordsResult = mysqli_query($db, $totalRecordsQuery);
$totalRecords = mysqli_fetch_assoc($totalRecordsResult)['total'];

// Calculate total pages
$totalPages = ceil($totalRecords / $itemsPerPage);
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
              <span>Pejabat Pembangunan dan Penyenggaraan</span>
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
        <a class="nav-link " href="p_home.php">
          <i class="bi bi-house-door-fill"></i>
          <span>Dashboard</span>
        </a>
      </li><!-- End Dashboard Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" href="p_all.php">
          <i class="bi bi-files"></i>
          <span>Complaint Feedback</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="p_index.php">
          <i class="bi bi-files"></i>
          <span>Feedback Update</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="p_reportgenerate.php">
          <i class="bi bi-file-pdf-fill"></i>
          <span>Generate Report</span>
        </a>
      </li><!-- End Blank Page Nav -->
    </ul>

  </aside><!-- End Sidebar-->

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Feedback Update</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.">Home</a></li>
          <li class="breadcrumb-item active">Feedback Update</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="inner-page">
      <div class="row">
        <div class="container">
            <div class="card">
               
                <!-- Status Filter -->
                <div class="mb-3">
                    <label for="statusFilter" class="form-label">Filter by Status:</label>
                    <select class="form-select" id="statusFilter" name="status_filter" onchange="applyFilter(this.value)">
                        <option value="">All</option>
                        <option value="In Progress" <?php echo ($filterStatus === 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                        <option value="Completed" <?php echo ($filterStatus === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                        <!-- Add more options as needed -->
                    </select>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <!-- Table headers -->
                        <thead>
                            <tr>
                                <th>Duty Officer</th>
                                <th>Email</th>
                                <th>Officer Number</th>
                                <th>Details</th>
                                <th><a href="?sort=feedback_date&order=<?php echo ($sortColumn == 'feedback_date' && $sortOrder == 'DESC') ? 'asc' : 'desc'; ?>">Complaint Repair</a></th>
                                <th><a href="?sort=block&order=<?php echo ($sortColumn == 'block' && $sortOrder == 'DESC') ? 'asc' : 'desc'; ?>">Block</a></th>
                                <th><a href="?sort=level&order=<?php echo ($sortColumn == 'level' && $sortOrder == 'DESC') ? 'asc' : 'desc'; ?>">Level</a></th>
                                <th><a href="?sort=room_no&order=<?php echo ($sortColumn == 'room_no' && $sortOrder == 'DESC') ? 'asc' : 'desc'; ?>">Room No</a></th>
                                <th><a href="?sort=typeofdamage&order=<?php echo ($sortColumn == 'typeofdamage' && $sortOrder == 'DESC') ? 'asc' : 'desc'; ?>">Type of Damage</a></th>
                                <th><a href="?sort=status&order=<?php echo ($sortColumn == 'status' && $sortOrder == 'DESC') ? 'asc' : 'desc'; ?>">Status</a></th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Fetch and display data -->
                            <?php
if (mysqli_num_rows($sql_run) > 0) {
    while ($row = mysqli_fetch_array($sql_run)) {
        $isDisabled = $row['status'] === 'Completed' ? 'disabled' : '';
        $btnClass = $row['status'] === 'Completed' ? 'btn-secondary disabled' : 'btn-primary';
?>
        <tr>
            <td><?php echo htmlspecialchars($row['officer_pic']); ?></td>
            <td><?php echo htmlspecialchars($row['officer_email']); ?></td>
            <td><?php echo htmlspecialchars($row['officer_mobile']); ?></td>
            <td><?php echo htmlspecialchars($row['feedback_details']); ?></td>
            <td><?php echo htmlspecialchars($row['feedback_date']); ?></td>
            <td><?php echo htmlspecialchars($row['block']); ?></td>
            <td><?php echo htmlspecialchars($row['level']); ?></td>
            <td><?php echo htmlspecialchars($row['room_no']); ?></td>
            <td><?php echo htmlspecialchars($row['typeofdamage']); ?></td>
            <td><?php echo htmlspecialchars($row['status']); ?></td>
            <td>
                <a class='btn <?php echo $btnClass; ?>' href='p_updatefeedback.php?complaint_id=<?php echo $row['complaint_id']; ?>' <?php echo $isDisabled; ?>>Update</a>
            </td>
        </tr>
    <?php } ?>
<?php
} else {
    echo "<tr><td colspan='11' class='text-center'>No complaints found.</td></tr>";
}
?>


                            </tbody>
                  </table>
                </div>
                <!-- Pagination links -->
                <nav aria-label="Page navigation example">
                  <ul class="pagination justify-content-center">
                    <?php
                    for ($i = 1; $i <= $totalPages; $i++) {
                        $activeClass = ($i == $currentPage) ? 'active' : '';
                        echo "<li class='page-item $activeClass'><a class='page-link' href='?page=$i'>$i</a></li>";
                    }
                    ?>
                  </ul>
                </nav>
              </div>
            </div>
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

  <script>
function applyFilter(status) {
    var url = window.location.href.split('?')[0]; // current URL without query string
    if (status) {
        url += '?status_filter=' + status;
    }
    window.location.href = url;
}
</script>

</body>

</html>
