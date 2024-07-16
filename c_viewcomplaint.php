<?php
// Include necessary PHP files and start session
include("auth.php");
include('db.php');

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

// Get the username from the session
$username = $_SESSION["username"];

// Fetch the user ID from the database
$userQuery = "SELECT student_id AS student_id, profile_image FROM student WHERE username = '$username'";
$userResult = mysqli_query($db, $userQuery);

if (!$userResult) {
    echo "Error fetching user data: " . mysqli_error($db);
    exit();
}

$row = mysqli_fetch_array($userResult);
$userId = $row['student_id'];

// Fetch new complaints count associated with the logged-in user
$newComplaintsQuery = "SELECT COUNT(*) as count 
                       FROM complaint_feedback AS cf
                       INNER JOIN complaint AS c ON cf.complaint_id = c.complaint_id
                       WHERE c.student_id = $userId AND cf.status = 'Completed'";
$newComplaintsResult = mysqli_query($db, $newComplaintsQuery);

if (!$newComplaintsResult) {
    echo "Error fetching new complaints: " . mysqli_error($db);
    exit();
}

$newComplaints = mysqli_fetch_assoc($newComplaintsResult)['count'];

// Fetch complaints from the database
$complaintsQuery = "SELECT * FROM complaint WHERE student_id = $userId ORDER BY date DESC";
$complaintsResult = mysqli_query($db, $complaintsQuery);

if (!$complaintsResult) {
    echo "Error fetching complaints: " . mysqli_error($db);
    exit();
}
?>
<?php

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
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Maxim Bootstrap Template - Index</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Raleway:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style1.css" rel="stylesheet">
  

  <!-- =======================================================
  * Template Name: Maxim
  * Updated: Sep 18 2023 with Bootstrap v5.3.2
  * Template URL: https://bootstrapmade.com/maxim-free-onepage-bootstrap-theme/
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body>
<?php
   include 'db.php';
   ?>

  <!-- ======= Header ======= -->
  <header id="header" class="fixed-top d-flex align-items-center">
    <div class="container d-flex justify-content-between">

      <div class="logo">
        <h1><a href="index.html">E-Complaint KKTF</a></h1>
        <!-- Uncomment below if you prefer to use an image logo -->
        <!-- <a href="index.html"><img src="assets/img/logo.png" alt="" class="img-fluid"></a>-->
      </div>

      <nav id="navbar" class="navbar">
        <ul>
          <li><a class="nav-link scrollto" href="index.php">Home</a></li>
          <li><a class="nav-link scrollto" href="c_list.php">List of Complaint</a></li>
 
          <li class="nav-item dropdown">

            <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
              <i class="bi bi-bell"></i>
              <?php if ($newComplaints > 0) : ?>
              <span class="badge bg-primary badge-number"><?php echo $newComplaints; ?></span>
              <?php endif; ?>
            </a><!-- End Notification Icon -->

            <ul class="dropdown-menu dropdown-menu-arrow notifications">
              <li class="dropdown-header">
                  You have <?php echo $newComplaints; ?> new notification<?php echo ($newComplaints > 1) ? 's' : ''; ?>
              </li>
              <li>
                  <hr class="dropdown-divider">
              </li>

              <?php
              if ($newComplaints > 0) {
                  // Fetch complaints related to the logged-in student
                  $notificationsQuery = "SELECT * FROM complaint WHERE student_id = $userId AND status = 'Completed' ORDER BY date DESC LIMIT 4";
                  $notificationsResult = mysqli_query($db, $notificationsQuery);
                  if ($notificationsResult) {
                      while ($notification = mysqli_fetch_assoc($notificationsResult)) {
                          echo '<li class="notification-item">';
                          echo '<div class="d-flex align-items-center justify-content-center">';
                          // Determine the appropriate icon based on the status of the complaint
                          if ($notification['status'] == 'Completed') {
                              echo '<i class="bi bi-patch-check-fill text-success me-2" style="font-size: 1.8rem;"></i>'; // Use a valid icon class and optionally a text color class
                          } elseif ($notification['status'] == 'In Progress') {
                              echo '<i class="bi bi-patch-exclamation-fill text-warning me-2" style="font-size: 1.8rem;"></i>'; // Use a valid icon class and optionally a text color class
                          }
                          echo '<div>';
                          echo '<h3 class="fs-6 m-0">' . htmlspecialchars($notification['typeofdamage']) . '</h3>'; // Added 'fs-6' class for smaller font size
                          echo '<p class="fs-7 m-0">Block ' . htmlspecialchars($notification['block']) . ', Room ' . htmlspecialchars($notification['level'] . $notification['room_no']) . '</p>'; // Added 'fs-7' class for smaller font size
                          echo '<p class="fs-7 m-0">' . htmlspecialchars($notification['date']) . '</p>'; // Added 'fs-7' class for smaller font size
                          echo '<p class="fs-7 m-0">' . htmlspecialchars($notification['status']) . '</p>'; // Added 'fs-7' class for smaller font size
                          echo '</div>';
                          echo '</div>';
                          echo '</li>';
                          echo '<li><hr class="dropdown-divider"></li>';
                      }
                  } else {
                      echo "Error fetching notifications: " . mysqli_error($db);
                  }
              }
              ?>

              <li class="dropdown-footer">
                  <a href="c_notification.php">Show all notifications</a>
              </li>

            </ul><!-- End Notification Dropdown Items -->
            </li><!-- End Notification Nav -->

            
          <li class="nav-item dropdown pe-3">
  
            <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                    <?php
                 
                    $iconSize = 32;
                    if (!empty($imageData)) {
                        echo '<img src="data:image/jpeg;base64,' . base64_encode($imageData) . '" alt="Profile" class="rounded-circle" style="width: ' . $iconSize . 'px; height: ' . $iconSize . 'px;">';
                    } else {
                        echo '<img src="assets/img/profile.jpg" alt="Profile" class="rounded-circle" style="width: ' . $iconSize . 'px; height: ' . $iconSize . 'px;">';
                    } ?>
                    <span class="d-none d-md-block dropdown-toggle ps-2"><?php echo $username; ?></span>
                    </a>
  
            <ul class="dropdown-menu dropdown-menu-arrow profile">
              <li class="dropdown-header">
                <h6><?php echo $username; ?></h6>
                <span>Student</span>
              </li>
              <li>
                <hr class="dropdown-divider">
              </li>
  
              <li>
                <a class="dropdown-item d-flex align-items-center" href="profile.php">
                  <i class="bi bi-person"></i>
                  <span>My Profile</span>
                </a>
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
        <i class="bi bi-list mobile-nav-toggle"></i>
      </nav><!-- .navbar -->

    </div>
  </header><!-- End Header -->

  <main id="main">
    <!-- ======= Breadcrumbs Section ======= -->
    <section class="breadcrumbs">
      <div class="container">

        <div class="d-flex justify-content-between align-items-center">
          <h2>Complaint Form</h2>
          <ol>
            <li><a href="index.php">Home</a></li>
            <li>Complaint Form</li>
          </ol>
        </div>

      </div>
    </section><!-- End Breadcrumbs Section -->

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
                                <a class='btn btn-primary' href='c_list.php' role='button'>Back</a>
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