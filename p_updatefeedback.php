<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

// Include database connection
include 'db.php';

// Initialize the disabled status
$disabled = false;

// Check if the complaint ID is provided in the URL
if (isset($_GET['complaint_id']) && is_numeric($_GET['complaint_id'])) {
    $complaint_id = $_GET['complaint_id'];

    // Check if form is submitted for updating details
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get the current date
        $current_date = date("Y-m-d");

        // Update the status to "Completed" and the feedback date to the current date for the specific complaint ID
        $update_query = "UPDATE complaint_feedback SET status = 'Completed', feedback_date = ? WHERE complaint_id = ?";
        $update_stmt = mysqli_prepare($db, $update_query);

        if ($update_stmt) {
            mysqli_stmt_bind_param($update_stmt, "si", $current_date, $complaint_id);
            $update_result = mysqli_stmt_execute($update_stmt);

            if ($update_result) {
                // Update the feedback details if provided
                if (isset($_POST['updateDetails'])) {
                    $update_details = $_POST['updateDetails'];

                    // Update the feedback details in complaint_feedback table
                    $update_details_query = "UPDATE complaint_feedback SET feedback_details = ? WHERE complaint_id = ?";
                    $update_details_stmt = mysqli_prepare($db, $update_details_query);

                    if ($update_details_stmt) {
                        mysqli_stmt_bind_param($update_details_stmt, "si", $update_details, $complaint_id);
                        $update_details_result = mysqli_stmt_execute($update_details_stmt);

                        if ($update_details_result) {
                            $statusMsg = "Status, date, and feedback details updated successfully";
                        } else {
                            $statusMsg = "Error updating feedback details: " . mysqli_error($db);
                        }

                        mysqli_stmt_close($update_details_stmt);
                    } else {
                        $statusMsg = "Error preparing update statement for feedback details: " . mysqli_error($db);
                    }
                } else {
                    // If no update details provided, update status and date only
                    $statusMsg = "Status and date updated successfully";
                }

                // Update the status in the complaint table
                $complaint_update_query = "UPDATE complaint SET status = 'Completed' WHERE complaint_id = ?";
                $complaint_update_stmt = mysqli_prepare($db, $complaint_update_query);

                if ($complaint_update_stmt) {
                    mysqli_stmt_bind_param($complaint_update_stmt, "i", $complaint_id);
                    $complaint_update_result = mysqli_stmt_execute($complaint_update_stmt);

                    if (!$complaint_update_result) {
                        $statusMsg = "Error updating status in complaint table: " . mysqli_error($db);
                    }

                    mysqli_stmt_close($complaint_update_stmt);
                } else {
                    $statusMsg = "Error preparing update statement for complaint table: " . mysqli_error($db);
                }

                // Send notifications to students and officials
                // Your code to send notifications goes here

                // Set the disabled status for the "Update" button
                $disabled = true;

                // Redirect back to the previous page with the status message and disabled flag
                $disabledFlag = $disabled ? 1 : 0;
                header("Location: p_index.php?complaint_id=$complaint_id&status=" . urlencode($statusMsg) . "&disabled=$disabledFlag");
                exit();
            } else {
                $statusMsg = "Error updating status and date: " . mysqli_error($db);
            }

            mysqli_stmt_close($update_stmt);
        } else {
            $statusMsg = "Error preparing update statement: " . mysqli_error($db);
        }
    } else {
        // Fetch existing complaint details to populate the form for editing
        $fetch_query = "SELECT * FROM complaint_feedback WHERE complaint_id = ?";
        $fetch_stmt = mysqli_prepare($db, $fetch_query);

        if ($fetch_stmt) {
            mysqli_stmt_bind_param($fetch_stmt, "i", $complaint_id);
            mysqli_stmt_execute($fetch_stmt);
            $result = mysqli_stmt_get_result($fetch_stmt);

            if ($row = mysqli_fetch_assoc($result)) {
                // Display form for updating details
                ?>
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Update Complaint Details</title>
                    <!-- Include Bootstrap CSS for styling -->
                    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
                </head>
                <body>
                    <div class="container mt-4">
                        <h2>Update Complaint Details</h2>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?complaint_id=" . $complaint_id; ?>" method="POST">
                            <div class="form-group">
                                <label for="updateDetails">Update Details:</label>
                                <textarea class="form-control" id="updateDetails" name="updateDetails" rows="5"><?php echo htmlspecialchars($row['feedback_details']); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Details</button>
                        </form>
                    </div>

                    <!-- Include Bootstrap JS and jQuery for modal functionality -->
                    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
                    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
                    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
                </body>
                </html>
                <?php
            } else {
                $statusMsg = "No complaint found with ID $complaint_id";
            }

            mysqli_stmt_close($fetch_stmt);
        } else {
            $statusMsg = "Error fetching complaint details: " . mysqli_error($db);
        }
    }
} else {
    $statusMsg = "Complaint ID is missing or invalid";
}

if (isset($statusMsg)) {
    // Display error message if any
    echo '<p>' . $statusMsg . '</p>';
}
?>
