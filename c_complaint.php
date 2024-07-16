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
include("auth.php");
include('db.php');

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

// Get the username from the session
$username = $_SESSION["username"];

// Query to fetch data based on the username
$sql = "SELECT * FROM student WHERE username = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Check if any rows were returned
if ($result->num_rows > 0) {
    // Fetch the first row
    $row = $result->fetch_assoc();
    $student_id = $row['student_id'];

    $status = $statusMsg = '';

    // Check if form is submitted
    if (isset($_POST["submit"])) {
        // Initialize variables for optional image upload
        $imageUploaded = false;
        $imgContent = null;

        // Check if an image is uploaded
        if (!empty($_FILES["image"]["name"])) {
            $fileName = basename($_FILES["image"]["name"]);
            $fileType = pathinfo($fileName, PATHINFO_EXTENSION);

            // Allow certain file formats
            $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
            if (in_array($fileType, $allowTypes)) {
                $imageUploaded = true;
                $image = $_FILES['image']['tmp_name'];
                $imgContent = addslashes(file_get_contents($image));
            } else {
                $statusMsg = 'Please select a valid image file (jpg, png, jpeg, gif) to upload.';
            }
        }

        // Handle form data insertion
        // You may want to add form validation and sanitation here
        if ($imageUploaded || empty($_FILES["image"]["name"])) {
            $block = $_POST['block'];
            $level = $_POST['level'];
            $room_no = $_POST['room_no'];
            $typeofdamage = $_POST['typeofdamage'];
            $details = $_POST['details'];
            $date = $_POST['date'];

            // Set the status to "New"
            $status = 'New';

            // Insert complaint data into the database
            $insert = $db->query("INSERT INTO complaint (student_id, block, level, room_no, typeofdamage, details, images, date, status) VALUES ('$student_id', '$block', '$level', '$room_no', '$typeofdamage', '$details', '$imgContent', NOW(), '$status')");

          // Check if the insertion was successful
if ($insert) {
  $status = 'success';
  $statusMsg = "Complaint submitted successfully.";
  header("Location: c_list.php");
  exit();
} else {
  $statusMsg = "Error: " . $db->error;
}
        }
    }
} else {
    // Handle the case where no matching record is found
    echo "No matching record found for username: $username";
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
        <input type="text" class="form-control" id="inputName5" value="<?php echo $row['full_name']; ?>" disabled>
    </div>
    <div class="col-md-6">
        <label for="inputName5" class="form-label">Matric No</label>
        <input type="text" class="form-control" id="inputName5" value="<?php echo $row['username']; ?>" disabled>
    </div>
    <div class="col-md-6">
        <label for="inputPassword5" class="form-label">Mobile Number</label>
        <input type="text" class="form-control" id="inputPassword5" value="<?php echo $row['mobile_no']; ?>" disabled>
    </div>

    <div class="col-md-3">
        <label for="typeofdamage" class="form-label">Type of Damage</label>
        <select id="typeofdamage" name="typeofdamage" class="form-select">
            <option selected>Choose...</option>
            <option>General Damage</option>
            <option>Electric Damage</option>
            <option>Furniture Damage</option>
        </select>
    </div>

    <div class="col-md-3">
        <label for="block" class="form-label">Block</label>
        <select id="block" name="block" class="form-select">
            <option selected>Choose...</option>
            <option>A</option>
            <option>B</option>
            <option>C</option>
            <option>D</option>
        </select>
    </div>
    <div class="col-md-3">
        <label for="level" class="form-label">Level</label>
        <select id="level" name="level" class="form-select">
            <option selected>Choose...</option>
            <option>0</option>
            <option>1</option>
            <option>2</option>
            <option>3</option>
        </select>
    </div>
    <div class="col-md-3">
        <label for="room_no" class="form-label">Room Number</label>
        <select id="room_no" name="room_no" class="form-select">
            <option selected>Choose...</option>
            <option>00</option>
            <option>01</option>
            <option>02</option>
            <option>03</option>
            <option>04</option>
            <option>05</option>
            <option>06</option>
            <option>07</option>
            <option>08</option>
            <option>09</option>
            <option>10</option>
            <option>11</option>
            <option>12</option>
            <option>13</option>
            <option>14</option>
            <option>15</option>
            <option>16</option>
        </select>
    </div>

  
    <div class="col-md-12">
                  <label for="details" class="col-sm-2 col-form-label">Details of The Damage</label>
                  <div class="col-sm-10">
                    <textarea class="form-control" style="height: 100px" id="details" name="details"></textarea>
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
                        
    <div class="col-md-6">
        <label for="inputZip" class="form-label">Select Images File</label>
        <div class="form-floating mb-3 mb-md-0"></div>
        <input type="file" name="image">
    </div>
    <br></br>
    <br></br>
    <br></br>
    <div class="col-md-6">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="gridCheck" required>
            <label class="form-check-label" for="gridCheck">
                Hereby I Agree that all the details are correct
            </label>
        </div>
    </div>
    <div class="text-center">
        <button type="submit" name="submit" class="btn btn-primary">submit</button>
        <button type="reset" class="btn btn-secondary">Reset</button>
    </div>
</form><!-- End Multi Columns Form -->

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