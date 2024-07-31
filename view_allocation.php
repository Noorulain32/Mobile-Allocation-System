<?php
include 'conn.php';
include 'header.php';

// Function to sanitize input data
function sanitize($conn, $input) {
    return mysqli_real_escape_string($conn, htmlspecialchars($input));
}

// Base URL for the project
$base_url = 'http://localhost/mobile_allocation_system/';

// Update or delete record if form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update'])) {
        $allocate_id = sanitize($conn, $_POST['allocate_id']);
        $other_office = sanitize($conn, $_POST['other_office']);
        $circle = sanitize($conn, $_POST['circle']);
        $division = sanitize($conn, $_POST['division']);
        $sub_division = sanitize($conn, $_POST['sub_division']);
        $quantity = sanitize($conn, $_POST['quantity']);
        $allocated_quantity = sanitize($conn, $_POST['allocated_quantity']);
        $remaining_quantity = sanitize($conn, $_POST['remaining_quantity']);
        $company_name = sanitize($conn, $_POST['company_name']);
        $model_name = sanitize($conn, $_POST['model_name']);
        $allocate_letterno = sanitize($conn, $_POST['allocate_letterno']);
        $dated = sanitize($conn, $_POST['dated']);

        // Update the record
        $sql_update = "UPDATE allocation SET 
                       other_office = '$other_office',
                       circle = '$circle',
                       division = '$division',
                       sub_division = '$sub_division',
                       quantity = '$quantity',
                       allocated_quantity = '$allocated_quantity',
                       remaining_quantity = '$remaining_quantity',
                       company_name = '$company_name',
                       model_name = '$model_name',
                       allocate_letterno = '$allocate_letterno',
                       dated = '$dated'
                       WHERE allocate_id = '$allocate_id'";

        if ($conn->query($sql_update) === TRUE) {
            echo '<script>alert("Record updated successfully");</script>';
        } else {
            echo "Error updating record: " . $conn->error;
        }
    } elseif (isset($_POST['delete'])) {
        $allocate_id = sanitize($conn, $_POST['allocate_id']);

        // Delete the record
        $sql_delete = "DELETE FROM allocation WHERE allocate_id = '$allocate_id'";

        if ($conn->query($sql_delete) === TRUE) {
            echo '<script>alert("Record deleted successfully");</script>';
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }
}

// Fetch stock data from database
$sql_select = "SELECT allocate_id, other_office, circle, division, sub_division, quantity, allocated_quantity, remaining_quantity, company_name, model_name, allocate_letterno,allocate_letterfile, dated FROM allocation";
$result = $conn->query($sql_select);

$allocation = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $allocation[] = $row;
    }
}

$conn->close(); // Close database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="images/favicon.png">
    <link rel="stylesheet" href="table.css">
    <title>View Allocations</title>
    <!-- <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        h1 {
            text-align: center;
            margin-top: 20px;
        }
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .edit-form {
            display: none;
            background-color: #f9f9f9;
            padding: 10px;
            margin-top: 10px;
        }
        .edit-form input[type="text"], 
        .edit-form input[type="date"] {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            box-sizing: border-box;
        }
        .edit-form input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
        }
        .action-link {
            color: #008CBA;
            text-decoration: none;
            cursor: pointer;
            margin-right: 10px;
        }
        .delete-form {
            display: inline;
        }
        .file-link {
            color: #0000EE;
            text-decoration: none;
        }
    </style> -->
    <script>
        function showEditForm(allocate_id) {
            var editForm = document.getElementById('edit-form-' + allocate_id);
            if (editForm.style.display === 'none' || editForm.style.display === '') {
                editForm.style.display = 'block';
            } else {
                editForm.style.display = 'none';
            }
        }
    </script>
</head>
<body>
    <h1>Allocation Data</h1>
    <table>
        <thead>
            <tr>
            <th>ID</th>
                <th>Other Office</th>
                <th>Circle</th>
                <th>Division</th>
                <th>Sub Division</th>
                <th>Quantity</th>
                <th>Allocated Quantity</th>
                <th>Remaining Quantity</th>
                <th>Company Name</th>
                <th>Model Name</th>
                <th>Allocate Letter No.</th>
                <th>Allocate Letter File</th>
                <th>Dated</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $i = 1;
            foreach ($allocation as $allocation): 
                // Build the correct file path
                $file_path = $base_url . htmlspecialchars($allocation['allocate_letterfile'], ENT_QUOTES, 'UTF-8');

                // Debug: Print file path to check if it is correct
                echo "<!-- File Path: $file_path -->";
            ?>
                <tr>
                <td><?php echo $i; ?></td>
                    <td><?php echo ucwords(htmlspecialchars($allocation['other_office'], ENT_QUOTES, 'UTF-8')); ?></td>
                    <td><?php echo ucwords(htmlspecialchars($allocation['circle'], ENT_QUOTES, 'UTF-8')); ?></td>
                    <td><?php echo ucwords(htmlspecialchars($allocation['division'], ENT_QUOTES, 'UTF-8')); ?></td>
                    <td><?php echo ucwords(htmlspecialchars($allocation['sub_division'], ENT_QUOTES, 'UTF-8')); ?></td>
                    <td><?php echo htmlspecialchars($allocation['quantity'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($allocation['allocated_quantity'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($allocation['remaining_quantity'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo ucwords(htmlspecialchars($allocation['company_name'], ENT_QUOTES, 'UTF-8')); ?></td>
                    <td><?php echo ucwords(htmlspecialchars($allocation['model_name'], ENT_QUOTES, 'UTF-8')); ?></td>
                    <td><?php echo htmlspecialchars($allocation['allocate_letterno'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <?php if (!empty($allocation['allocate_letterfile'])): ?>
                        <a href="<?php echo $file_path; ?>" class="file-link" target="_blank">View File</a>
                        <?php else: ?>
                        No file
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($allocation['dated'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <a href="javascript:void(0);" onclick="showEditForm('<?php echo htmlspecialchars($allocation['allocate_id'], ENT_QUOTES, 'UTF-8'); ?>')" class="action-link">Edit</a>
                        <form class="delete-form" method="post" onsubmit="return confirm('Are you sure you want to delete this record?');" style="display:inline;">
                            <input type="hidden" name="allocate_id" value="<?php echo htmlspecialchars($allocation['allocate_id'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="delete" value="1">
                            <a href="javascript:void(0);" onclick="this.closest('form').submit();" class="action-link">Delete</a>
                        </form>
                    </td>
                </tr>
                <tr>
                <td colspan="13">
                        <form id="edit-form-<?php echo htmlspecialchars($allocation['allocate_id'], ENT_QUOTES, 'UTF-8'); ?>" class="edit-form" method="post">
                            <input type="hidden" name="allocate_id" value="<?php echo htmlspecialchars($allocation['allocate_id'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="update" value="1">
                            Other Office: <input type="text" name="other_office" value="<?php echo htmlspecialchars($allocation['other_office'], ENT_QUOTES, 'UTF-8'); ?>"><br>
                            Circle: <input type="text" name="circle" value="<?php echo htmlspecialchars($allocation['circle'], ENT_QUOTES, 'UTF-8'); ?>"><br>
                            Division: <input type="text" name="division" value="<?php echo htmlspecialchars($allocation['division'], ENT_QUOTES, 'UTF-8'); ?>"><br>
                            Sub Division: <input type="text" name="sub_division" value="<?php echo htmlspecialchars($allocation['sub_division'], ENT_QUOTES, 'UTF-8'); ?>"><br>
                            Quantity: <input type="text" name="quantity" value="<?php echo htmlspecialchars($allocation['quantity'], ENT_QUOTES, 'UTF-8'); ?>"><br>
                            Allocated Quantity: <input type="text" name="allocated_quantity" value="<?php echo htmlspecialchars($allocation['allocated_quantity'], ENT_QUOTES, 'UTF-8'); ?>"><br>
                            Remaining Quantity: <input type="text" name="remaining_quantity" value="<?php echo htmlspecialchars($allocation['remaining_quantity'], ENT_QUOTES, 'UTF-8'); ?>"><br>
                            Company Name: <input type="text" name="company_name" value="<?php echo htmlspecialchars($allocation['company_name'], ENT_QUOTES, 'UTF-8'); ?>"><br>
                            Model Name: <input type="text" name="model_name" value="<?php echo htmlspecialchars($allocation['model_name'], ENT_QUOTES, 'UTF-8'); ?>"><br>
                            Allocate Letter No.: <input type="text" name="allocate_letterno" value="<?php echo htmlspecialchars($allocation['allocate_letterno'], ENT_QUOTES, 'UTF-8'); ?>"><br>
                            Dated: <input type="date" name="dated" value="<?php echo htmlspecialchars($allocation['dated'], ENT_QUOTES, 'UTF-8'); ?>"><br>
                            <input type="submit" value="Update" class="action-link">
                        </form>
                    </td>
                </tr>
            <?php 
                $i++;
            endforeach; 
            ?>
        </tbody>
    </table>
</body>
</html>