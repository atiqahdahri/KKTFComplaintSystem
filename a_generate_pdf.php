<?php
require_once('tcpdf/tcpdf.php'); // Path to TCPDF library
include 'db.php'; // Include your database connection

// Determine sorting options
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'feedback_date'; // Default sort by feedback_date
$sortOrder = isset($_GET['order']) && $_GET['order'] == 'desc' ? 'DESC' : 'ASC'; // Default ascending order

// Status filter setup
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$statusCondition = '';
if ($statusFilter !== 'all') {
    $statusCondition = " AND cf.status = '$statusFilter'";
}

// Fetch all complaints with sorting and filtering
$sql = "SELECT cf.*, 
               c.block AS block, 
               c.room_no AS room_no, 
               c.level AS level, 
               c.typeofdamage AS typeofdamage,
               c.date AS date,
               c.details AS details,
               p.full_name AS officer_pic, p.email AS officer_email, p.mobile_no AS officer_mobile,
               s.full_name AS full_name
        FROM complaint_feedback cf
        JOIN complaint c ON cf.complaint_id = c.complaint_id
        LEFT JOIN student s ON c.student_id = s.student_id
        JOIN ppp p ON p.PPP_id = cf.PPP_id
        WHERE 1 $statusCondition
        ORDER BY $sortColumn $sortOrder";
$sql_run = mysqli_query($db, $sql);

// Create new PDF document
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false); // Landscape orientation
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Complaint Report');
$pdf->SetHeaderData('', 0, 'Complaint Report', 'Generated on ' . date('Y-m-d H:i:s'), array(0, 64, 255), array(0, 64, 128));
$pdf->SetMargins(15, 20, 10); // Set margins (left, top, right)
$pdf->SetFont('times', '', 11);
$pdf->AddPage();

// Check if there are complaints to display
if (mysqli_num_rows($sql_run) > 0) {
    // Add a table with updated CSS styles and width
    $html = '<style>
        table { border-collapse: collapse; width: 100%; }
        th { border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold; background-color: #f2f2f2; }
        td { border: 1px solid #000; padding: 5px; text-align: center; }
        h1 { color: #000000; } /* Title font color */
        </style>';

    $html .= '<h1>Complaint Report</h1>'; // Title

    $html .= '<table width="100%">
        <tr>
            <th style="width: 30px;">No</th> 
            <th style="width: 70px;">Duty Officer</th>
            <th style="width: 70px;">Officer Number</th>
            <th style="width: 70px;">Student Name</th>
            <th style="width: 60px;">Type of Damage</th>
            <th style="width: 40px;">Block</th>
            <th style="width: 40px;">Level</th>  
            <th style="width: 40px;">Room No</th> 
            <th style="width: 80px;">Complaint Details</th>
            <th style="width: 80px;">Feedback Details</th>
            <th style="width: 60px;">Complaint Receive</th>
            <th style="width: 60px;">Complaint Repair</th>
            <th style="width: 60px;">Status</th>
        </tr>';

    $count = 1;
    while ($row = mysqli_fetch_assoc($sql_run)) {
        $html .= '<tr>
            <td>' . $count++ . '</td>
            <td>' . htmlspecialchars($row['officer_pic']) . '</td>
            <td>' . htmlspecialchars($row['officer_mobile']) . '</td>
            <td>' . htmlspecialchars($row['full_name']) . '</td>
            <td>' . htmlspecialchars($row['typeofdamage']) . '</td>
            <td>' . htmlspecialchars($row['block']) . '</td>
            <td>' . htmlspecialchars($row['level']) . '</td>
            <td>' . htmlspecialchars($row['room_no']) . '</td>
            <td>' . mb_strimwidth(htmlspecialchars($row['details']), 0, 50, '...') . '</td> <!-- Limit to 50 characters -->
            <td>' . mb_strimwidth(htmlspecialchars($row['feedback_details']), 0, 50, '...') . '</td> <!-- Limit to 50 characters -->
            <td>' . htmlspecialchars($row['date']) . '</td>
            <td>' . htmlspecialchars($row['feedback_date']) . '</td>
            <td>' . htmlspecialchars($row['status']) . '</td>
        </tr>';
    }

    $html .= '</table>';

    $pdf->writeHTML($html, true, false, true, false, '');
} else {
    // No complaints found message
    $html = '<h1>Complaint Report</h1>';
    $html .= '<p>No complaints found.</p>';
    
    $pdf->writeHTML($html, true, false, true, false, '');
}

// Close and output PDF document
$pdf->Output('report.pdf', 'I');
?>
