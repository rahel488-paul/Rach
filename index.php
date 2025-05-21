<?php
// DB connection
$conn = new mysqli("localhost", "root", "", "library_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create/Update logic
if (isset($_POST['submit'])) {
    $id = $_POST['id'] ?? '';
    $fullname = $_POST['fullname'];
    $regno = $_POST['regno'];
    $phone = $_POST['phone'];
    $college = $_POST['college'];
    $course = $_POST['course'];
    $bookname = $_POST['bookname'];
    $borrowdate = $_POST['borrowdate'];
    $returndate = $_POST['returndate'];
    $status = $_POST['status'];

    if ($id) {
        $stmt = $conn->prepare("UPDATE borrowers SET fullname=?, regno=?, phone=?, college=?, course=?, bookname=?, borrowdate=?, returndate=?, status=? WHERE id=?");
        $stmt->bind_param("sssssssssi", $fullname, $regno, $phone, $college, $course, $bookname, $borrowdate, $returndate, $status, $id);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO borrowers (fullname, regno, phone, college, course, bookname, borrowdate, returndate, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $fullname, $regno, $phone, $college, $course, $bookname, $borrowdate, $returndate, $status);
        $stmt->execute();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Edit logic
$editData = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM borrowers WHERE id=$id");
    $editData = $result->fetch_assoc();
}

// Delete logic
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM borrowers WHERE id=$id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Library Book Borrowing - MUST</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #eef2f3;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 960px;
            margin: 40px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
        }

        form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        form input,
        form select {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 15px;
        }

        form input[type="submit"] {
            grid-column: span 2;
            background-color: #3498db;
            color: white;
            border: none;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        form input[type="submit"]:hover {
            background-color: #2980b9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            font-size: 14px;
        }

        table th {
            background-color: #3498db;
            color: white;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .actions a {
            margin: 0 5px;
            padding: 5px 10px;
            text-decoration: none;
            color: white;
            border-radius: 4px;
            font-size: 13px;
        }

        .edit {
            background-color: #27ae60;
        }

        .delete {
            background-color: #e74c3c;
        }

        @media(max-width: 768px) {
            form {
                grid-template-columns: 1fr;
            }

            form input[type="submit"] {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Student Library Borrowing Form</h2>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
        <input type="text" name="fullname" placeholder="Full Name" value="<?= $editData['fullname'] ?? '' ?>" required>
        <input type="text" name="regno" placeholder="Registration Number" value="<?= $editData['regno'] ?? '' ?>" required>
        <input type="text" name="phone" placeholder="Phone Number" value="<?= $editData['phone'] ?? '' ?>" required>

        <select name="college" required>
            <option value="">Select College</option>
            <?php
            $colleges = ["COICT", "COACT", "CET", "COHBS"];
            foreach ($colleges as $col) {
                $selected = (isset($editData['college']) && $editData['college'] == $col) ? 'selected' : '';
                echo "<option value='$col' $selected>$col</option>";
            }
            ?>
        </select>

        <input type="text" name="course" placeholder="Course Name" value="<?= $editData['course'] ?? '' ?>" required>
        <input type="text" name="bookname" placeholder="Book Name" value="<?= $editData['bookname'] ?? '' ?>" required>
        <input type="date" name="borrowdate" value="<?= $editData['borrowdate'] ?? '' ?>" required>
        <input type="date" name="returndate" value="<?= $editData['returndate'] ?? '' ?>" required>

        <select name="status" required>
            <option value="">Select Status</option>
            <?php
            $statuses = ["borrowed", "returned", "overdue"];
            foreach ($statuses as $stat) {
                $selected = (isset($editData['status']) && $editData['status'] == $stat) ? 'selected' : '';
                echo "<option value='$stat' $selected>$stat</option>";
            }
            ?>
        </select>

        <input type="submit" name="submit" value="<?= $editData ? 'Update Record' : 'Add Record' ?>">
    </form>

    <h3 style="text-align:center; margin-top:40px;">Borrowed Books List</h3>
    <table>
        <tr>
            <th>Full Name</th>
            <th>Reg No</th>
            <th>Phone</th>
            <th>College</th>
            <th>Course</th>
            <th>Book</th>
            <th>Borrow Date</th>
            <th>Return Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php
        $result = $conn->query("SELECT * FROM borrowers ORDER BY id DESC");
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                <td>{$row['fullname']}</td>
                <td>{$row['regno']}</td>
                <td>{$row['phone']}</td>
                <td>{$row['college']}</td>
                <td>{$row['course']}</td>
                <td>{$row['bookname']}</td>
                <td>{$row['borrowdate']}</td>
                <td>{$row['returndate']}</td>
                <td>{$row['status']}</td>
                <td class='actions'>
                    <a class='edit' href='?edit={$row['id']}'>Edit</a>
                    <a class='delete' href='?delete={$row['id']}' onclick=\"return confirm('Delete this record?')\">Delete</a>
                </td>
            </tr>";
        }
        ?>
    </table>
</div>
</body>
</html>
