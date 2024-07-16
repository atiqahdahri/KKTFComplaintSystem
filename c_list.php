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
$userQuery = "SELECT student_id, profile_image FROM student WHERE username = '$username'";
$userResult = mysqli_query($db, $userQuery);

if (!$userResult) {
    echo "Error fetching user data: " . mysqli_error($db);
    exit();
}

$row = mysqli_fetch_assoc($userResult);
$userId = $row['student_id'];

// Initialize $search variable
$search = isset($_GET['search']) ? mysqli_real_escape_string($db, $_GET['search']) : '';

// Fetch complaints from the database with status and timestamp
$complaintsQuery = "SELECT 
                        c.complaint_id,
                        s.username,
                        s.full_name,
                        c.typeofdamage,
                        c.block,
                        c.level,
                        c.room_no,
                        c.details,
                        c.date,
                        c.timestamp
                    FROM complaint AS c
                    INNER JOIN student AS s ON c.student_id = s.student_id
                    WHERE c.student_id = $userId ";

// Apply search filter if $search is not empty
if (!empty($search)) {
    $complaintsQuery .= "AND c.typeofdamage LIKE '%$search%' ";
}

// Fetch sorting option
$date = isset($_GET['date']) ? mysqli_real_escape_string($db, $_GET['date']) : '';
$dateOrder = '';

// Apply sorting based on selected option
if (!empty($date)) {
    $dateOrder = ($date == 'asc') ? 'ASC' : 'DESC';
    $complaintsQuery .= "ORDER BY c.date $dateOrder";
} else {
    $complaintsQuery .= "ORDER BY c.date DESC"; // Default sorting (newest to oldest)
}

// Pagination variables
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 6; // Number of complaints per page
$offset = ($page - 1) * $limit;

// Add pagination to the main complaints query
$complaintsQuery .= " LIMIT $limit OFFSET $offset";

$complaintsResult = mysqli_query($db, $complaintsQuery);

if (!$complaintsResult) {
    echo "Error fetching complaints: " . mysqli_error($db);
    exit();
}

// Count total complaints for pagination
$totalComplaintsQuery = "SELECT COUNT(*) AS total FROM complaint WHERE student_id = $userId";
$totalComplaintsResult = mysqli_query($db, $totalComplaintsQuery);
$totalComplaints = mysqli_fetch_assoc($totalComplaintsResult)['total'];

// Calculate total pages
$totalPages = ceil($totalComplaints / $limit);

// Fetch new complaints count associated with the logged-in user
$newComplaintsQuery = "SELECT COUNT(*) AS count 
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
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Raleway:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="assets/css/style1.css" rel="stylesheet">

    <!-- Style for disabled buttons -->
    <style>
        .disabled-button {
            pointer-events: none;
            opacity: 0.5;
        }
    </style>

</head>

<body>
    <header id="header" class="fixed-top d-flex align-items-center">
        <div class="container d-flex justify-content-between">
            <div class="logo">
                <h1><a href="index.html">E-Complaint KKTF</a></h1>
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
                        </a>
                        <ul class="dropdown-menu dropdown-menu-arrow notifications">
                            <li class="dropdown-header">
                                You have <?php echo $newComplaints; ?> new notification<?php echo ($newComplaints > 1) ? 's' : ''; ?>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <?php
                            if ($newComplaints > 0) {
                                $notificationsQuery = "SELECT * FROM complaint WHERE student_id = $userId AND status = 'Completed' ORDER BY date DESC LIMIT 4";
                                $notificationsResult = mysqli_query($db, $notificationsQuery);
                                if ($notificationsResult) {
                                    while ($notification = mysqli_fetch_assoc($notificationsResult)) {
                                        echo '<li class="notification-item">';
                                        echo '<div class="d-flex align-items-center justify-content-center">';
                                        if ($notification['status'] == 'Completed') {
                                            echo '<i class="bi bi-patch-check-fill text-success me-2" style="font-size: 1.8rem;"></i>';
                                        } elseif ($notification['status'] == 'In Progress') {
                                            echo '<i class="bi bi-patch-exclamation-fill text-warning me-2" style="font-size: 1.8rem;"></i>';
                                        }
                                        echo '<div>';
                                        echo '<h3 class="fs-6 m-0">' . htmlspecialchars($notification['typeofdamage']) . '</h3>';
                                        echo '<p class="fs-7 m-0">Block ' . htmlspecialchars($notification['block']) . ', Room ' . htmlspecialchars($notification['level'] . $notification['room_no']) . '</p>';
                                        echo '<p class="fs-7 m-0">' . htmlspecialchars($notification['date']) . '</p>';
                                        echo '<p class="fs-7 m-0">' . htmlspecialchars($notification['status']) . '</p>';
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
                        </ul>
                    </li>
                    <li class="nav-item dropdown pe-3">
                        <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                            <?php
                            $imageData = $row['profile_image'];
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
                        </ul>
                    </li>
                </ul>
                <i class="bi bi-list mobile-nav-toggle"></i>
            </nav>
        </div>
    </header>

    <main id="main">
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
        </section>

        <section class="inner-page">
            <div class="container">
                <h3>Your Complaints</h3>

                <!-- Search and Filter Form -->
                <form class="row mb-3" method="GET">
                    <div class="col-md-4">
                        <input type="text" class="form-control" placeholder="Search by type of damage" name="search"
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" name="date">
                            <option value="">Sort by Date</option>
                            <option value="asc" <?php echo ($date == 'asc') ? 'selected' : ''; ?>>Oldest to Newest</option>
                            <option value="desc" <?php echo ($date == 'desc') ? 'selected' : ''; ?>>Newest to Oldest</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">Apply</button>
                    </div>
                </form>

                <!-- Complaints Table -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Matric No</th>
                                <th>Full Name</th>
                                <th>Type of Damage</th>
                                <th>Location</th>
                                <th>Details</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($complaint = mysqli_fetch_assoc($complaintsResult)) : ?>
                                <tr>
                                    <!-- Output complaint details -->
                                    <td><?php echo htmlspecialchars($complaint['username']); ?></td>
                                    <td><?php echo htmlspecialchars($complaint['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($complaint['typeofdamage']); ?></td>
                                    <td>Block <?php echo htmlspecialchars($complaint['block']); ?>, Room <?php echo htmlspecialchars($complaint['level'] . $complaint['room_no']); ?></td>
                                    <td><?php echo htmlspecialchars($complaint['details']); ?></td>
                                    <td><?php echo htmlspecialchars($complaint['date']); ?></td>

                                    <td>
                                        <?php
                                        // Calculate time limit for this complaint (fixed at 60 seconds)
                                        $timeLimit = 60;

                                        // Calculate time difference in seconds
                                        $currentTime = time();
                                        $complaintTime = strtotime($complaint['date']); // Assuming 'date' field holds the complaint timestamp
                                        $timeDifference = $currentTime - $complaintTime;

                                        // Check if within time limit
                                        if ($timeDifference <= $timeLimit) {
                                            ?>
                                            <!-- Buttons for viewing, editing, and deleting -->
                                            <a class="btn btn-primary" href="c_viewcomplaint.php?complaint_id=<?php echo $complaint['complaint_id']; ?>" role="button">View</a>
                                            <a class="btn btn-primary" href="c_updatedata.php?complaint_id=<?php echo $complaint['complaint_id']; ?>" role="button" id="edit-button-<?php echo $complaint['complaint_id']; ?>">Edit</a>
                                            <a class="btn btn-danger" href="c_deletedata.php?delete_id=<?php echo $complaint['complaint_id']; ?>" role="button" id="delete-button-<?php echo $complaint['complaint_id']; ?>" onclick="return confirm('Are you sure you want to delete this complaint?')">Delete</a>
                                            <!-- Placeholder for countdown timer -->
                                            <p id="countdown-<?php echo $complaint['complaint_id']; ?>"></p>
                                            <?php
                                            // Display time limit message
                                            $timeLeft = $timeLimit - $timeDifference;
                                            $secondsLeft = $timeLeft % 60;
                                            echo "<p>You have {$secondsLeft} seconds remaining.</p>";
                                        } else {
                                            // Outside time limit, disable edit and delete buttons, and only allow viewing
                                            ?>
                                            <a class="btn btn-primary" href="c_viewcomplaint.php?complaint_id=<?php echo $complaint['complaint_id']; ?>" role="button">View</a>
                                            <a class="btn btn-primary" href="c_updatedata.php?complaint_id=<?php echo $complaint['complaint_id']; ?>" role="button" id="edit-button-<?php echo $complaint['complaint_id']; ?>">Edit</a>
                                            <a class="btn btn-danger" href="c_deletedata.php?delete_id=<?php echo $complaint['complaint_id']; ?>" role="button" id="delete-button-<?php echo $complaint['complaint_id']; ?>" onclick="return confirm('Are you sure you want to delete this complaint?')">Delete</a>
                                            <?php
                                            // Display time limit exceeded message
                                            
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination links -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i . '&' . http_build_query($_GET); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </section>
    </main>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/aos/aos.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
    <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
    <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="assets/vendor/waypoints/noframework.waypoints.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="assets/js/main.js"></script>

    <!-- Custom JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to start countdown for each complaint
        function startCountdown(complaintId, endTime) {
            var endTimestamp = endTime; // End time in milliseconds (timestamp)

            var countdownElement = document.getElementById('countdown-' + complaintId);
            var editButton = document.getElementById('edit-button-' + complaintId);
            var deleteButton = document.getElementById('delete-button-' + complaintId);

            var x = setInterval(function() {
                var now = new Date().getTime();
                var distance = endTimestamp - now;
                var seconds = Math.floor(distance / 1000);

                if (distance < 0) {
                    clearInterval(x);
                    countdownElement.innerHTML = 'Time\'s up!';
                    editButton.disabled = true;
                    deleteButton.disabled = true;
                    // Optionally, set localStorage to persist disabled state
                    localStorage.setItem('complaint_' + complaintId, 'disabled');
                } else {
                    countdownElement.innerHTML = 'Time remaining: ' + seconds + 's ';
                }
            }, 1000);
        }

        // Loop through each complaint and start countdown
        var complaints = <?php echo json_encode(mysqli_fetch_all($complaintsResult, MYSQLI_ASSOC)); ?>;
        complaints.forEach(function(complaint) {
            var endTime = new Date(complaint.date).getTime() + (60 * 1000); // Assuming 60 seconds time limit
            startCountdown(complaint.complaint_id, endTime);

            // Check if disabled state should be persisted
            var disabledState = localStorage.getItem('complaint_' + complaint.complaint_id);
            if (disabledState === 'disabled') {
                document.getElementById('edit-button-' + complaint.complaint_id).disabled = true;
                document.getElementById('delete-button-' + complaint.complaint_id).disabled = true;
            }
        });
    });
    </script>


</body>

</html>
