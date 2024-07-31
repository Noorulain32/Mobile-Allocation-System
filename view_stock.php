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
        $stock_id = sanitize($conn, $_POST['stock_id']);
        $company_name = sanitize($conn, $_POST['company_name']);
        $model_name = sanitize($conn, $_POST['model_name']);
        $quantity = sanitize($conn, $_POST['quantity']);
        $procurement_date = sanitize($conn, $_POST['procurement_date']);
        $procurement_letterno = sanitize($conn, $_POST['procurement_letterno']);
        $receiving_date = sanitize($conn, $_POST['receiving_date']);

        // Update the record
        $sql_update = "UPDATE stock SET 
                       company_name = '$company_name',
                       model_name = '$model_name',
                       quantity = '$quantity',
                       procurement_date = '$procurement_date',
                       procurement_letterno = '$procurement_letterno',
                       receiving_date = '$receiving_date'
                       WHERE stock_id = '$stock_id'";

        if ($conn->query($sql_update) === TRUE) {
            echo '<script>alert("Record updated successfully");</script>';
        } else {
            echo "Error updating record: " . $conn->error;
        }
    } elseif (isset($_POST['delete'])) {
        $stock_id = sanitize($conn, $_POST['stock_id']);

        // Delete the record
        $sql_delete = "DELETE FROM stock WHERE stock_id = '$stock_id'";

        if ($conn->query($sql_delete) === TRUE) {
            echo '<script>alert("Record deleted successfully");</script>';
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }
}

// Fetch stock data from database
$sql_select = "SELECT stock_id, company_name, model_name, quantity, procurement_date, procurement_letterno, receiving_date, procurement_letter FROM stock";
$result = $conn->query($sql_select);

$stocks = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $stocks[] = $row;
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
    <title>View Stocks</title>
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
        function showEditForm(stock_id) {
            var editForm = document.getElementById('edit-form-' + stock_id);
            if (editForm.style.display === 'none' || editForm.style.display === '') {
                editForm.style.display = 'block';
            } else {
                editForm.style.display = 'none';
            }
        }
    </script>
</head>
<body>
    <h1>Stock Data</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Company Name</th>
                <th>Model Name</th>
                <th>Quantity</th>
                <th>Procurement Date</th>
                <th>Procurement Letter No.</th>
                <th>Procurement Letter File</th>
                <th>Receiving Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $i = 1;
            foreach ($stocks as $stock): 
                // Build the correct file path
                $file_path = $base_url . htmlspecialchars($stock['procurement_letter'], ENT_QUOTES, 'UTF-8');

                // Debug: Print file path to check if it is correct
                echo "<!-- File Path: $file_path -->";
            ?>
                <tr>
                    <td><?php echo $i; ?></td>
                    <td><?php echo ucwords(htmlspecialchars($stock['company_name'], ENT_QUOTES, 'UTF-8')); ?></td>
                    <td><?php echo ucwords(htmlspecialchars($stock['model_name'], ENT_QUOTES, 'UTF-8')); ?></td>
                    <td><?php echo htmlspecialchars($stock['quantity'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($stock['procurement_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($stock['procurement_letterno'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <?php if (!empty($stock['procurement_letter'])): ?>
                        <a href="<?php echo $file_path; ?>" class="file-link" target="_blank">View File</a>
                        <?php else: ?>
                        No file
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($stock['receiving_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <a href="javascript:void(0);" onclick="showEditForm('<?php echo htmlspecialchars($stock['stock_id'], ENT_QUOTES, 'UTF-8'); ?>')" class="action-link">Edit</a>
                        <form class="delete-form" method="post" onsubmit="return confirm('Are you sure you want to delete this record?');" style="display:inline;">
                            <input type="hidden" name="stock_id" value="<?php echo htmlspecialchars($stock['stock_id'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="delete" value="1">
                            <a href="javascript:void(0);" onclick="this.closest('form').submit();" class="action-link">Delete</a>
                        </form>
                    </td>
                </tr>
                <tr>
                    <td colspan="9">
                        <form id="edit-form-<?php echo htmlspecialchars($stock['stock_id'], ENT_QUOTES, 'UTF-8'); ?>" class="edit-form" method="post">
                            <input type="hidden" name="stock_id" value="<?php echo htmlspecialchars($stock['stock_id'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="update" value="1">
                            Company Name: <input type="text" name="company_name" value="<?php echo htmlspecialchars($stock['company_name'], ENT_QUOTES, 'UTF-8'); ?>"><br>
                            Model Name: <input type="text" name="model_name" value="<?php echo htmlspecialchars($stock['model_name'], ENT_QUOTES, 'UTF-8'); ?>"><br>
                            Quantity: <input type="text" name="quantity" value="<?php echo htmlspecialchars($stock['quantity'], ENT_QUOTES, 'UTF-8'); ?>"><br>
                            Procurement Date: <input type="date" name="procurement_date" value="<?php echo htmlspecialchars($stock['procurement_date'], ENT_QUOTES, 'UTF-8'); ?>"><br>
                            Procurement Letter No.: <input type="text" name="procurement_letterno" value="<?php echo htmlspecialchars($stock['procurement_letterno'], ENT_QUOTES, 'UTF-8'); ?>"><br>
                            Receiving Date: <input type="date" name="receiving_date" value="<?php echo htmlspecialchars($stock['receiving_date'], ENT_QUOTES, 'UTF-8'); ?>"><br>
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