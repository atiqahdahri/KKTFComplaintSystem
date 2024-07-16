<?php
require('db.php');

if(isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete complaint</title>
    <script>
        function confirmDelete() {
            if (confirm("Are you sure you want to delete this complaint?")) {
                window.location.href = "c_deletedata.php?confirm_delete_id=<?php echo $delete_id; ?>";
            } else {
                window.location.href = "c_list.php";
            }
        }
    </script>
</head>
<body>
    <script>
        // Call the confirmDelete function when the page finishes loading
        confirmDelete();
    </script>
</body>
</html>

<?php
} elseif (isset($_GET['confirm_delete_id'])) {
    $delete_id = $_GET['confirm_delete_id'];

    // Use prepared statement to delete complaint
    $delete_query = "DELETE FROM complaint WHERE complaint_id = ?";
    $stmt = mysqli_prepare($db, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $delete_id);
    mysqli_stmt_execute($stmt);
    $rows_affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    if($rows_affected > 0) {
        // Show a success message
        echo "<script>alert('Complaint with ID $delete_id has been deleted successfully.');</script>";
    } else {
        // Show an error message if the complaint is not found
        echo "<script>alert('Complaint not found or could not be deleted.');</script>";
    }

    // Redirect to the complaint list page
    echo "<script>window.location.href = 'c_list.php';</script>";
} else {
    // Redirect to the complaint list page if delete_id is not provided
    header("Location: c_list.php");
    exit();
}
?>
