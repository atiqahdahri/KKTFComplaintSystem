<?php
session_start();
include('auth.php');  // Include authentication check script
include('db.php');    // Include database connection script

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

// Get the username from the session
$username = $_SESSION["username"];


// Initialize variables for officer details
$officer_email = '';
$officer_mobile = '';
$officer_name = '';

// Fetch PPP_id based on username
$fetchPPPQuery = "SELECT PPP_id FROM ppp WHERE username = ?";
$stmtPPP = mysqli_prepare($db, $fetchPPPQuery);

if ($stmtPPP) {
    mysqli_stmt_bind_param($stmtPPP, "s", $username);
    mysqli_stmt_execute($stmtPPP);
    $resultPPP = mysqli_stmt_get_result($stmtPPP);

    if ($resultPPP) {
        $pppRow = mysqli_fetch_assoc($resultPPP);
        if ($pppRow) {
            $ppp_id = $pppRow['PPP_id'];
        } else {
            $statusMsg .= "No PPP data found for username: $username";
        }
    } else {
        $statusMsg .= "Error executing PPP query: " . mysqli_error($db);
    }

    mysqli_stmt_close($stmtPPP);
} else {
    $statusMsg .= "Error preparing PPP query: " . mysqli_error($db);
}


// Fetch PPP details based on username
$fetchPPPQuery = "SELECT email, mobile_no, full_name FROM ppp WHERE username = ?";
$stmtPPP = mysqli_prepare($db, $fetchPPPQuery);

if ($stmtPPP) {
    mysqli_stmt_bind_param($stmtPPP, "s", $username);
    mysqli_stmt_execute($stmtPPP);
    $resultPPP = mysqli_stmt_get_result($stmtPPP);

    if ($resultPPP) {
        $pppRow = mysqli_fetch_assoc($resultPPP);
        if ($pppRow) {
            // Assign officer details to variables for display in form
            $officer_email = htmlspecialchars($pppRow['email']);
            $officer_mobile = htmlspecialchars($pppRow['mobile_no']);
            $officer_name = htmlspecialchars($pppRow['full_name']);
        } else {
            $statusMsg .= "No PPP data found for username: $username";
        }
    } else {
        $statusMsg .= "Error executing PPP query: " . mysqli_error($db);
    }

    mysqli_stmt_close($stmtPPP);
} else {
    $statusMsg .= "Error preparing PPP query: " . mysqli_error($db);
}

// Initialize variables to hold complaint data
$block = $level = $room_no = $typeofdamage = $details = $date = '';
$statusMsg = '';

// Check if complaint_id is set and is a valid integer
if (isset($_GET['complaint_id']) && is_numeric($_GET['complaint_id'])) {
    $complaint_id = $_GET['complaint_id'];

    // Query to fetch data based on the complaint_id and username
    $sql = "SELECT * FROM student 
            JOIN complaint ON student.student_id = complaint.student_id 
            WHERE complaint.complaint_id = ?";
    $stmt = mysqli_prepare($db, $sql);

    // Check if the statement was prepared successfully
    if ($stmt) {
        // Bind parameters
        mysqli_stmt_bind_param($stmt, "i", $complaint_id);
        // Execute the statement
        mysqli_stmt_execute($stmt);
        // Get result
        $sql_run = mysqli_stmt_get_result($stmt);

        // Check if any rows were returned
        if ($sql_run) {
            // Fetch the row
            $row = mysqli_fetch_assoc($sql_run);

            // Check if a matching record was found
            if ($row) {
                // Extract complaint details
                $block = htmlspecialchars($row['block']);
                $level = htmlspecialchars($row['level']);
                $room_no = htmlspecialchars($row['room_no']);
                $typeofdamage = htmlspecialchars($row['typeofdamage']);
                $details = htmlspecialchars($row['details']);
                $date = htmlspecialchars($row['date']);
            } else {
                $statusMsg = "No matching record found for complaint ID: $complaint_id";
            }
        } else {
            $statusMsg = "Error executing query: " . mysqli_error($db);
        }

        // Close statement
        mysqli_stmt_close($stmt);
    } else {
        $statusMsg = "Error preparing query: " . mysqli_error($db);
    }
} else {
    $statusMsg = "Invalid complaint ID.";
}

if (isset($_POST['submit'])) {
// Retrieve and sanitize form data
$feedback_details = mysqli_real_escape_string($db, $_POST['feedback_details']);
$feedback_date = mysqli_real_escape_string($db, $_POST['feedback_date']);
$status = mysqli_real_escape_string($db, $_POST['status']);

// Insert data into the feedback table
$insert_query = "INSERT INTO complaint_feedback (complaint_id, feedback_details, feedback_date, status, PPP_id) 
                 VALUES (?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($db, $insert_query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "isssi", $complaint_id, $feedback_details, $feedback_date, $status, $ppp_id);
    $insert_result = mysqli_stmt_execute($stmt);

    if ($insert_result) {
        $statusMsg = "Feedback data inserted successfully";

        // Update status in complaint table
        $update_status_query = "UPDATE complaint SET status = ? WHERE complaint_id = ?";
        $stmt_update = mysqli_prepare($db, $update_status_query);
        mysqli_stmt_bind_param($stmt_update, "si", $status, $complaint_id);
        $update_result = mysqli_stmt_execute($stmt_update);

        if ($update_result) {
            $statusMsg .= " and Complaint status updated successfully";
        } else {
            $statusMsg .= ". Error updating complaint status: " . mysqli_error($db);
        }

        mysqli_stmt_close($stmt_update);
    } else {
        $statusMsg = "Error inserting feedback data: " . mysqli_error($db);
    }

    mysqli_stmt_close($stmt);
} else {
    $statusMsg = "Error preparing insert statement: " . mysqli_error($db);
}

// Redirect to p_index.php
header("Location: p_index.php");
exit(); // Ensure script stops executing after redirection

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

// Fetch count of new complaints
$countNewComplaintsQuery = "SELECT COUNT(*) AS newComplaints FROM complaint WHERE status = 'new'";
$countNewComplaintsResult = mysqli_query($db, $countNewComplaintsQuery);

if ($countNewComplaintsResult) {
    $countNewComplaintsRow = mysqli_fetch_assoc($countNewComplaintsResult);
    $newComplaints = $countNewComplaintsRow['newComplaints'];
} else {
    // Handle query error if needed
    $newComplaints = 0; // Default to 0 if there's an error
}

// Initialize status message variable
$statusMsg = '';
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
<?php
   include 'db.php';
   ?>
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
                        <select id="status" name="status" class="form-select">
                            <option selected>Choose...</option>
                             <option>Completed</option>
                             <option>In Progress</option>
                        </select>
                          </div>

                          <div class="col-md-6">
                              <label for="inputName5" class="form-label">Officer In Charge</label>
                              <input type="text" class="form-control" id="inputName5" name="officer_name" value="<?php echo $officer_name; ?>" readonly>
                          </div>
                          <div class="col-md-6">
                              <label for="inputEmail5" class="form-label">Email</label>
                              <input type="email" class="form-control" id="inputEmail5" name="officer_email" value="<?php echo $officer_email; ?>" readonly>
                          </div>
                          <div class="col-md-6">
                              <label for="inputMobileNumber" class="form-label">Mobile Number</label>
                              <input type="text" class="form-control" id="officer_mobile" name="officer_mobile" value="<?php echo $officer_mobile; ?>" readonly>
                          </div>


                        <div class="col-md-8">
                            <label for="floatingTextarea" class="form-label">Details of the Repair</label>
                            <div class="form-floating mb-3">
                                <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextarea" name="feedback_details" style="height: 100px;" ></textarea>
                                <label for="floatingTextarea">Comments</label>
                            </div>
                        </div>

                                                <div class="col-md-4">
                            <label for="date" class="form-label">Date of Complaint Repair</label>
                            <input type="date" class="form-control" id="date" name="feedback_date" required>
                        </div>

                        <script>
                            // Get today's date in the format YYYY-MM-DD
                            var today = new Date().toISOString().substr(0, 10);

                            // Set the value of the input field to today's date
                            document.getElementById("date").value = today;
                        </script>

                        <div class="col-md-3">
                            <label for="date" class="form-label">Date of Complaint Receive </label>
                            <input type="date" class="form-control" id="date" name="feedback_date" value="<?php echo htmlspecialchars($row['date']); ?>" disabled>
                        </div>

                        <div class="col-md-3">
                          <label for="typeofdamage" class="form-label">Type of Damage</label>
                          <select id="typeofdamage" name="typeofdamage" class="form-select" disabled>
                              <option value="General Damage" <?php if ($row['typeofdamage'] == 'General Damage') echo 'selected'; ?>>General Damage</option>
                              <option value="Electric Damage" <?php if ($row['typeofdamage'] == 'Electric Damage') echo 'selected'; ?>>Electric Damage</option>
                              <option value="Furniture Damage" <?php if ($row['typeofdamage'] == 'Furniture Damage') echo 'selected'; ?>>Furniture Damage</option>
                          </select>
                      </div>
                        <div class="col-md-2">
                          <label for="block" class="form-label">Block</label>
                            <select id="block" name="block" class="form-select" disabled>
                                 <option value="A" <?php if ($block == 'A') echo 'selected'; ?>>A</option>
                                  <option value="B" <?php if ($block == 'B') echo 'selected'; ?>>B</option>
                                  <option value="C" <?php if ($block == 'C') echo 'selected'; ?>>C</option>
                                  <option value="D" <?php if ($block == 'D') echo 'selected'; ?>>D</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                          <label for="level" class="form-label">Level</label>
                            <select id="level" name="level" class="form-select" disabled>
                              <option <?php if ($level == '0') echo 'selected'; ?>>0</option>
                              <option <?php if ($level == '1') echo 'selected'; ?>>1</option>
                              <option <?php if ($level == '2') echo 'selected'; ?>>2</option>
                              <option <?php if ($level == '3') echo 'selected'; ?>>3</option>
                            </select>
                          </div>

                        <div class="col-md-2">
                          <label for="room_no" class="form-label">Room Number</label>
                           <select id="room_no" name="room_no" class="form-select" disabled>
                           <?php
                             for ($i = 0; $i <= 16; $i++) {
                            echo '<option ';
                            if ($room_no == str_pad($i, 2, '0', STR_PAD_LEFT)) {
                            echo 'selected';
                            }
                            echo '>' . str_pad($i, 2, '0', STR_PAD_LEFT) . '</option>';
                           }?>
                           </select>
                           </div>

                        

                        
                        <br></br>
                       <br></br>

                        <div class="text-center">
                            <button name="submit" type="submit" class="btn btn-primary">Submit</button>
                            <button type="reset" class="btn btn-secondary">Reset</button>
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