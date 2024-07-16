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

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Notifications - Maxim Bootstrap Template</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Raleway:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

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

    <!-- ======= Header ======= -->
    <header id="header" class="fixed-top d-flex align-items-center">
        <!-- Header content here -->
    </header><!-- End Header -->

    <main id="main">
        <!-- ======= Breadcrumbs Section ======= -->
        <section class="breadcrumbs">
            <div class="container">

                <div class="d-flex justify-content-between align-items-center">
                    <h2>All Notifications</h2>
                    <ol>
                        <li><a href="index.php">Home</a></li>
                        <li>All Notifications</li>
                    </ol>
                </div>

            </div>
        </section><!-- End Breadcrumbs Section -->

        <section class="inner-page">
            <div class="container">
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Type of Damage</th>
                                    <th>Block</th>
                                    <th>Level</th>
                                    <th>Room No</th>
                                    <th>Date</th>
                                    <th>Repair Detail</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetch complaints with status completed or in progress inserted by the specific student
                                $complaintsQuery = "SELECT c.typeofdamage, c.block, c.level, c.room_no, c.date, cf.feedback_details, c.status
                                FROM complaint c
                                INNER JOIN complaint_feedback cf ON c.complaint_id = cf.complaint_id
                                WHERE c.student_id = $userId
                                AND c.status IN ('Completed', 'In Progress')
                                ORDER BY c.date DESC";
                                $complaintsResult = mysqli_query($db, $complaintsQuery);
                                if ($complaintsResult) {
                                    while ($complaint = mysqli_fetch_assoc($complaintsResult)) {
                                        echo '<tr>';
                                        echo '<td>' . htmlspecialchars($complaint['typeofdamage']) . '</td>';
                                        echo '<td>' . htmlspecialchars($complaint['block']) . '</td>';
                                        echo '<td>' . htmlspecialchars($complaint['level']) . '</td>';
                                        echo '<td>' . htmlspecialchars($complaint['room_no']) . '</td>';
                                        echo '<td>' . htmlspecialchars($complaint['date']) . '</td>';
                                        echo '<td>' . htmlspecialchars($complaint['feedback_details']) . '</td>';
                                        echo '<td>' . htmlspecialchars($complaint['status']) . '</td>';
                                        echo '</tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="6">Error fetching complaints: ' . mysqli_error($db) . '</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

    </main><!-- End #main -->

    <!-- ======= Footer ======= -->
    <footer id="footer">
        <!-- Footer content here -->
    </footer><!-- End Footer -->

    <!-- Vendor JS Files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Template Main JS File -->
    <script src="assets/js/main1.js"></script>

</body>

</html>
