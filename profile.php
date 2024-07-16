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
?>

<?php
// Query to fetch data based on the username
$sql = "SELECT * FROM student WHERE username = '$username'";
$sql_run = mysqli_query($db, $sql);

// Check if any rows were returned
if ($sql_run) {
    // Fetch the first row
    $row = mysqli_fetch_array($sql_run);

    // Check if a matching record was found
    if (!$row) {
        // Handle the case where no matching record is found
        echo "No matching record found for username: $username";
        exit(); // Exit script if no matching record is found
    }
} else {
    // Handle the case where the query execution fails
    echo "Error executing query: " . mysqli_error($db);
    exit(); // Exit script if query execution fails
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
            // Retrieve profile image data from the database
            $imageData = $row['profile_image'];

            // Determine the desired icon size (e.g., 32px x 32px)
            $iconSize = 32;
            // Check if profile image data is available
            if (!empty($imageData)) {
            // Output the profile image with specified width and height
             echo '<img src="data:image/jpeg;base64,' . base64_encode($imageData) . '" alt="Profile" class="rounded-circle" style="width: ' . $iconSize . 'px; height: ' . $iconSize . 'px;">';
            } else {
            // If no profile image data is available, use a default image
             echo '<img src="assets/img/profile.jpg" alt="Profile" class="rounded-circle" style="width: ' . $iconSize . 'px; height: ' . $iconSize . 'px;">';
             }?> 
              <span class="d-none d-md-block dropdown-toggle ps-2"><?php echo $username; ?></span>
            </a><!-- End Profile Image Icon -->
  
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
          <h2>My Profile</h2>
          <ol>
            <li><a href="index.php">Home</a></li>
            <li>Profile</li>
          </ol>
        </div>

      </div>
    </section><!-- End Breadcrumbs Section -->

    <section class="inner-page">
      <div class="container">
        <div class="card">
            <div class="card-body">

            <section class="section profile">
      <div class="row">
        <div class="col-xl-4">

          <div class="card">
          <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
                <?php
                // Retrieve profile image data from the database
                $imageData = $row['profile_image'];

                // Check if profile image data is available
                if (!empty($imageData)) {
                    // Output the profile image with specified width and height
                    echo '<img src="data:image/jpeg;base64,' . base64_encode($imageData) . '" alt="Profile" class="rounded-circle" style="width: 150px; height: 150px;">';
                } else {
                    // If no profile image data is available, use a default image
                   echo '<img src="assets/img/profile.jpg" alt="Profile" class="rounded-circle" style="width: 150px; height: 150px;">';
                }
                ?>
                <h2><?php echo $username; ?></h2>
              <h3>Student</h3>

            </div>
          </div>

        </div>

        <div class="col-xl-8">

          <div class="card">
            <div class="card-body pt-3">
              <!-- Bordered Tabs -->
              <ul class="nav nav-tabs nav-tabs-bordered">

                <li class="nav-item">
                  <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-overview">Overview</button>
                </li>

                <li class="nav-item">
                  <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit">Edit Profile</button>
                </li>


              </ul>
              <div class="tab-content pt-2">

                <div class="tab-pane fade show active profile-overview" id="profile-overview">
          
                  <h5 class="card-title">Profile Details</h5>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Matric Number</div>
                    <div class="col-lg-9 col-md-8"><?php echo $row['username']; ?></div>
                  </div>
                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Full Name</div>
                    <div class="col-lg-9 col-md-8"><?php echo $row['full_name']; ?></div>
                  </div>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Email</div>
                    <div class="col-lg-9 col-md-8"><?php echo $row['email']; ?></div>
                  </div>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Phone</div>
                    <div class="col-lg-9 col-md-8"><?php echo $row['mobile_no']; ?></div>
                  </div>
            
                </div>

                <div class="tab-pane fade profile-edit pt-3" id="profile-edit">

                  <!-- Profile Edit Form -->
                  <form method="post" action="update_profile.php" enctype="multipart/form-data">

                  <div class="row mb-3">
                  <label for="profileImage" class="col-md-4 col-lg-3 col-form-label">Profile Image</label>
                    <div class="col-md-8 col-lg-9">
                      <input type="file" name="profileImage" id="profileImage">
                    </div>
                  </div>

                    <div class="row mb-3">
                      <label for="fullname" class="col-md-4 col-lg-3 col-form-label">Fullname</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="full_name" type="text" class="form-control" id="fullname" value="<?php echo $row['full_name']; ?>" oninput="this.value = this.value.toUpperCase()">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="fullname" class="col-md-4 col-lg-3 col-form-label">Email</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="email" type="text" class="form-control" id="email" value="<?php echo $row['email']; ?>">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="yourmobile_no" class="col-md-4 col-lg-3 col-form-label">Mobile Number</label>
                      <div class="col-md-8 col-lg-9">
                      <input type="text" name="mobile_no" class="form-control" id="mobile_no"  value="<?php echo $row['mobile_no']; ?>">
                      <div class="invalid-feedback">Please enter a valid mobile phone number (e.g., 012-1234567).</div>
                  </div>
              </div>
          
                    <div class="text-center">
                      <button type="submit" class="btn btn-primary" name="submit">Save Changes</button>
                    </div>
                  </form><!-- End Profile Edit Form -->

                </div>

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
  <script>
    // Function to automatically format Malaysian mobile phone number
    document.getElementById('mobile_no').addEventListener('input', function (e) {
        var x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,7})/);
        e.target.value = !x[2] ? x[1] : x[1] + '-' + x[2];
    });
</script>

  <!-- Template Main JS File -->
  <script src="assets/js/main1.js"></script>

</body>

</html>