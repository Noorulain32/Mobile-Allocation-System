<?php
include 'conn.php';
include 'header.php';

// Function to sanitize input data
function sanitize($conn, $input) {
    return mysqli_real_escape_string($conn, htmlspecialchars($input));
}

// Handle update and delete requests via AJAX
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['action'])) {
        if ($data['action'] == 'update') {
            $record_id = sanitize($conn, $data['record_id']);
            $other_office = isset($data['other_office']) ? sanitize($conn, $data['other_office']) : '';
            $circle = isset($data['circle']) ? sanitize($conn, $data['circle']) : '';
            $circle_code = isset($data['circle_code']) ? sanitize($conn, $data['circle_code']) : '';
            $division = isset($data['division']) ? sanitize($conn, $data['division']) : '';
            $division_code = isset($data['division_code']) ? sanitize($conn, $data['division_code']) : '';
            $sub_division = isset($data['sub_division']) ? sanitize($conn, $data['sub_division']) : '';
            $sub_division_code = isset($data['sub_division_code']) ? sanitize($conn, $data['sub_division_code']) : '';
            $total_meter_reader = isset($data['total_meter_reader']) ? sanitize($conn, $data['total_meter_reader']) : '';

            // Debugging: Print out the received data
            error_log("Updating record ID: $record_id");
            error_log("other_office: $other_office");
            error_log("circle: $circle");
            error_log("circle_code: $circle_code");
            error_log("division: $division");
            error_log("division_code: $division_code");
            error_log("sub_division: $sub_division");
            error_log("sub_division_code: $sub_division_code");
            error_log("total_meter_reader: $total_meter_reader");

            // Update the record
            $sql_update = "UPDATE record SET 
                           other_office = '$other_office',
                           circle = '$circle',
                           circle_code = '$circle_code',
                           division = '$division',
                           division_code = '$division_code',
                           sub_division = '$sub_division',
                           sub_division_code = '$sub_division_code',
                           total_meter_reader = '$total_meter_reader'
                           WHERE record_id = '$record_id'";

            if ($conn->query($sql_update) === TRUE) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => $conn->error]);
            }
            exit();
        }

        if ($data['action'] == 'delete') {
            $record_id = sanitize($conn, $data['record_id']);

            // Delete the record
            $sql_delete = "DELETE FROM record WHERE record_id = '$record_id'";

            if ($conn->query($sql_delete) === TRUE) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => $conn->error]);
            }
            exit();
        }
    }
}

// Fetch record data from database
$sql_select = "SELECT * FROM record";
$result = $conn->query($sql_select);

$records = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
}

// Close database connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="images/favicon.png">
    <link rel="stylesheet" href="table.css">
    <title>View Records</title>
    <style>
        .editable {
            cursor: pointer;
        }
        .editable:hover {
            background-color: #f0f0f0;
        }
        .action-link {
            cursor: pointer;
            color: blue;
            text-decoration: underline;
        }
        .alert {
            padding: 10px;
            margin: 10px 0;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
    <script>
        function showEditForm(record_id) {
            var editForm = document.getElementById('edit-form-' + record_id);
            if (editForm.style.display === 'none' || editForm.style.display === '') {
                editForm.style.display = 'block';
            } else {
                editForm.style.display = 'none';
            }
        }

        function saveChanges(record_id) {
            const cells = document.querySelectorAll(`#row-${record_id} td[data-name]`);
            const data = { record_id: record_id, action: 'update' };

            cells.forEach(cell => {
                if (cell.isContentEditable) {
                    const value = cell.textContent.trim();
                    data[cell.dataset.name] = value === '' ? null : value; // Convert empty values to null
                }
            });

            console.log('Data to send:', data); // Debugging line

            fetch('view_record.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                console.log('Server response:', result); // Debugging line
                if (result.status === 'success') {
                    showAlert('success', 'Record updated successfully');
                    cells.forEach(cell => cell.contentEditable = false);
                } else {
                    showAlert('danger', 'Error updating record: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'Error updating record: ' + error.message);
            });
        }

        function deleteRecord(record_id) {
            if (confirm('Are you sure you want to delete this record?')) {
                fetch('view_record.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ record_id: record_id, action: 'delete' })
                })
                .then(response => response.json())
                .then(result => {
                    console.log('Server response:', result); // Debugging line
                    if (result.status === 'success') {
                        const row = document.getElementById(`row-${record_id}`);
                        if (row) {
                            row.remove(); // Remove the row from the table
                        }
                        showAlert('success', 'Record deleted successfully');
                    } else {
                        showAlert('danger', 'Error deleting record: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('danger', 'Error deleting record: ' + error.message);
                });
            }
        }

        function showAlert(type, message) {
            const alertContainer = document.getElementById('alert-container');
            alertContainer.innerHTML = `<div class='alert alert-${type}'>${message}</div>`;
            const alertDiv = alertContainer.querySelector('.alert');
            if (alertDiv) {
                alertDiv.style.display = 'block';
                setTimeout(() => alertDiv.style.display = 'none', 5000); // Hide after 5 seconds
            }
        }
    </script>
</head>
<body>
    <h1>Record Data</h1>
    <div id="alert-container"></div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Other Office</th>
                <th>Circle</th>
                <th>Circle Code</th>
                <th>Division</th>
                <th>Division Code</th>
                <th>Sub Division</th>
                <th>Sub Division Code</th>
                <th>Total Meter Reader</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php 
             $i=1; 
            foreach ($records as $record): ?>
                <tr id="row-<?php echo htmlspecialchars($record['record_id'], ENT_QUOTES, 'UTF-8'); ?>">
                    <td><?php echo htmlspecialchars($i, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td class="editable" data-name="other_office" contenteditable="true"><?php echo ucwords(htmlspecialchars($record['other_office'], ENT_QUOTES, 'UTF-8')); ?></td>
                    <td class="editable" data-name="circle" contenteditable="true"><?php echo ucwords(htmlspecialchars($record['circle'], ENT_QUOTES, 'UTF-8')); ?></td>
                    <td class="editable" data-name="circle_code" contenteditable="true"><?php echo htmlspecialchars($record['circle_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td class="editable" data-name="division" contenteditable="true"><?php echo ucwords(htmlspecialchars($record['division'], ENT_QUOTES, 'UTF-8')); ?></td>
                    <td class="editable" data-name="division_code" contenteditable="true"><?php echo htmlspecialchars($record['division_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td class="editable" data-name="sub_division" contenteditable="true"><?php echo ucwords(htmlspecialchars($record['sub_division'], ENT_QUOTES, 'UTF-8')); ?></td>
                    <td class="editable" data-name="sub_division_code" contenteditable="true"><?php echo htmlspecialchars($record['sub_division_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td class="editable" data-name="total_meter_reader" contenteditable="true"><?php echo htmlspecialchars($record['total_meter_reader'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <span class="action-link" onclick="saveChanges('<?php echo htmlspecialchars($record['record_id'], ENT_QUOTES, 'UTF-8'); ?>')">Save</span> | 
                        <span class="action-link" onclick="deleteRecord('<?php echo htmlspecialchars($record['record_id'], ENT_QUOTES, 'UTF-8'); ?>')">Delete</span>
                    </td>
                </tr>
            <?php
          $i++;
          endforeach; ?>
        </tbody>
    </table>
</body>
</html>
