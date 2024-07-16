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

// Determine sorting options
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'date'; // Default sort by date
$sortOrder = isset($_GET['order']) && $_GET['order'] == 'desc' ? 'DESC' : 'ASC'; // Default ascending order

// Status filter setup
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$statusCondition = '';
if ($statusFilter !== 'all') {
    $statusCondition = " AND status = '$statusFilter'";
}

// Date filter setup
$dateFilter = isset($_GET['date']) ? $_GET['date'] : 'all'; // Initialize date filter variable

// SQL query for fetching complaints with sorting and filtering
$sql = "SELECT c.*, s.username AS username, s.mobile_no AS mobile_no
        FROM complaint c
        JOIN student s ON c.student_id = s.student_id
        WHERE 1 $statusCondition";

// Adjust ORDER BY clause based on date filter
if ($dateFilter === 'newest') {
    $sql .= " ORDER BY c.date DESC";
} elseif ($dateFilter === 'oldest') {
    $sql .= " ORDER BY c.date ASC";
} else {
    // Default sorting if no date filter selected
    $sql .= " ORDER BY $sortColumn $sortOrder";
}

$sql .= " LIMIT ? OFFSET ?";

// Count new complaints
$newComplaintsQuery = "SELECT COUNT(*) as count FROM complaint WHERE status = 'new'";
$result = mysqli_query($db, $newComplaintsQuery);
if ($result) {
    $newComplaints = mysqli_fetch_assoc($result)['count'];
} else {
    die("Error counting new complaints: " . mysqli_error($db));
}

// Pagination setup
$entriesPerPage = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $entriesPerPage;

// Fetch complaints with sorting and filtering
$stmt = mysqli_prepare($db, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'ii', $entriesPerPage, $offset);
    mysqli_stmt_execute($stmt);
    $sql_run = mysqli_stmt_get_result($stmt);
    if (!$sql_run) {
        die("Error fetching complaints: " . mysqli_error($db));
    }
} else {
    die("Error preparing SQL statement: " . mysqli_error($db));
}

// Total pages calculation
$totalPagesSql = "SELECT CEIL(COUNT(*) / $entriesPerPage) AS total FROM complaint WHERE 1 $statusCondition";
$totalPagesResult = mysqli_query($db, $totalPagesSql);
if ($totalPagesResult) {
    $totalPages = mysqli_fetch_assoc($totalPagesResult)['total'];
} else {
    die("Error calculating total pages: " . mysqli_error($db));
}

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
          </a><!-- End Profile Image Icon -->

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
      <h1>Notifications</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.">Home</a></li>
          <li class="breadcrumb-item active">Notifications</li>
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
              <label for="dateFilter" class="form-label">Filter by Date:</label>
              <select class="form-select" id="dateFilter" name="date" onchange="filterDate(this)">
                <option value="all" <?php if ($dateFilter == 'all') echo 'selected'; ?>>All</option>
                <option value="newest" <?php if ($dateFilter == 'newest') echo 'selected'; ?>>Newest</option>
                <option value="oldest" <?php if ($dateFilter == 'oldest') echo 'selected'; ?>>Oldest</option>
              </select>
            </div>
            <table class="table table-hover table-striped">
              <thead>
                <tr>
                  <th>Student Phone No</th>
                  <th>Block</th>
                  <th>Level</th>
                  <th>Room No</th>
                  <th>Type of Damage</th>
                  <th>Detail of Damage</th>
                  <th>Image</th>
                  <th>Complaint Receive</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
              <?php
if (mysqli_num_rows($sql_run) > 0) {
?>
    <tbody>
        <?php
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
                <td>
                    <?php if ($row['status'] == 'New') { ?>
                        <form action="p_feedback.php?complaint_id=<?php echo $row['complaint_id']; ?>" method="post" class="disableButtonForm">
                            <button type="submit" class="btn btn-primary">Feedback</button>
                        </form>
                    <?php } else { ?>
                        <button class="btn btn-secondary" disabled>Feedback</button>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
<?php
} else {
    echo "<tbody><tr><td colspan='9' class='text-center'>No complaints found.</td></tr></tbody>";
}
?>

              </tbody>
            </table>
            <!-- Pagination -->
            <nav aria-label="Page navigation example">
              <ul class="pagination justify-content-center">
                <?php if ($page > 1) : ?>
                  <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>&sort=<?php echo $sortColumn; ?>&order=<?php echo $sortOrder; ?>&status=<?php echo $statusFilter; ?>&date=<?php echo $dateFilter; ?>">Previous</a></li>
                <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
              <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&sort=<?php echo $sortColumn; ?>&order=<?php echo $sortOrder; ?>&status=<?php echo $statusFilter; ?>&date=<?php echo $dateFilter; ?>"><?php echo $i; ?></a></li>
            <?php endfor; ?>

            <?php if ($page < $totalPages) : ?>
              <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>&sort=<?php echo $sortColumn; ?>&order=<?php echo $sortOrder; ?>&status=<?php echo $statusFilter; ?>&date=<?php echo $dateFilter; ?>">Next</a></li>
            <?php endif; ?>
          </ul>
        </nav><!-- End Pagination -->
      </div>
    </div>
  </div>
</section><!-- End category Section -->
  </main><!-- End #main -->
  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer"></footer><!-- End Footer -->
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
  <!-- Custom JS for disabling the button -->
  <script>
    document.querySelectorAll('.disableButtonForm').forEach(form => {
      form.addEventListener('submit', function(event) {
        const button = form.querySelector('button');
        button.disabled = true;
      });
    });

    function filterDate(select) {
      var dateFilter = select.value;
      var currentUrl = window.location.href.split('?')[0]; // Get current URL without query string
      var url = currentUrl + '?date=' + dateFilter;
      window.location.href = url;
    }
  </script>
</body>
</html>