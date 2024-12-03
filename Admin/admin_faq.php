<?php 
include 'admin_sidebar.php'; 
include 'dataconnection.php'; 
?>

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .main {
            padding: 30px;
        }

        .card {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .table-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .table-container h2 {
            color: #333;
            font-weight: bold;
        }

        .btn-success,
        .btn-primary {
            border-radius: 20px;
            padding: 10px 20px;
        }

        .btn-success:hover {
            background-color: #28a745;
            color: #fff;
        }

        .table {
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
        }

        .table th {
            background: #6c757d;
            color: #fff;
            text-align: center;
        }

        .table tbody tr:nth-child(odd) {
            background: #f9f9f9;
        }

        .table tbody tr:nth-child(even) {
            background: #ffffff;
        }

        .table tbody tr:hover {
            background: #f1f1f1;
        }

        .error-message {
            color: #ff4d4f;
            font-size: 0.9rem;
        }

        .modal-content {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: #fff;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .modal-footer .btn-primary {
            background: #007bff;
            border: none;
        }

        .modal-footer .btn-primary:hover {
            background: #0056b3;
        }

        .modal-footer .btn-secondary:hover {
            background: #6c757d;
        }

        .btn-danger {
            border-radius: 20px;
            padding: 5px 15px;
        }

        .btn-danger:hover {
            background: #dc3545;
        }

        @media (max-width: 768px) {
            .table-container {
                flex-direction: column;
                align-items: flex-start;
            }

            .table-container h2 {
                margin-bottom: 10px;
            }
        }
    </style>

    <script>
        function add_check(event) {
            event.preventDefault(); // Prevent form submission
            var no_error = true;

            var fq = document.s_form.faq_question.value.trim();
            var fa = document.s_form.faq_answer.value.trim();
            var ft = document.s_form.faq_type.value;

            // FAQ Question validation
            if (fq === "") {
                document.getElementById("check_fq").innerHTML = "Please enter a question";
                no_error = false;
            } else {
                document.getElementById("check_fq").innerHTML = "";
            }

            // FAQ Answer validation
            if (fa === "") {
                document.getElementById("check_fa").innerHTML = "Please enter an answer";
                no_error = false;
            } else {
                document.getElementById("check_fa").innerHTML = "";
            }

            // FAQ Type validation
            if (ft === "") {
                document.getElementById("check_ft").innerHTML = "Please select a type";
                no_error = false;
            } else {
                document.getElementById("check_ft").innerHTML = "";
            }

            // Submit the form if no errors are found
            if (no_error) {
                document.getElementById("s_form").submit();
            }
        }
    </script>
</head>

<body>
    <div class="main">
        <div class="card">
            <!-- Page Title -->
            <div class="table-container">
                <h2><i class="fas fa-question-circle"></i> Existing FAQs</h2>
                <!-- Add FAQ Button -->
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addFaqModal">
                    <i class="fas fa-plus"></i> Add FAQ
                </button>
            </div>

            <!-- Add FAQ Modal -->
            <div class="modal fade" id="addFaqModal" tabindex="-1" aria-labelledby="addFaqModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h5 class="modal-title" id="addFaqModalLabel">Add FAQ</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <!-- Modal Body -->
                        <form action="a_faq.php" method="POST" id="s_form" name="s_form">
                            <div class="modal-body">
                                <!-- FAQ Question -->
                                <label for="faq_question">FAQ Question</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="faq_question" name="faq_question" required>
                                </div>
                                <div>
                                    <span id="check_fq" class="error-message"></span>
                                </div>

                                <!-- FAQ Answer -->
                                <label for="faq_answer">FAQ Answer</label>
                                <div class="input-group mb-3">
                                    <textarea class="form-control" id="faq_answer" name="faq_answer" required></textarea>
                                </div>
                                <div>
                                    <span id="check_fa" class="error-message"></span>
                                </div>

                                <!-- FAQ Type -->
                                <label for="faq_type">FAQ Type</label>
                                <div class="input-group mb-3">
                                    <select class="form-control" id="faq_type" name="faq_type" required>
                                        <option value="" disabled selected>Select Type</option>
                                        <?php
                                        // Fetch unique FAQ types from the database
                                        $query = "SELECT DISTINCT faq_type FROM faq";
                                        $result = mysqli_query($connect, $query);

                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo "<option value='" . $row['faq_type'] . "'>" . $row['faq_type'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div>
                                    <span id="check_ft" class="error-message"></span>
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div class="modal-footer">
                                <button onclick="add_check(event);" class="btn btn-primary" name="add_faq">
                                    <i class="fas fa-save"></i> Add FAQ
                                </button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <hr>
            <!-- FAQ Table -->
            <table class="table table-bordered text-center">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Question</th>
                        <th scope="col">Answer</th>
                        <th scope="col">Type</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $faq_query = "SELECT * FROM faq";
                    $faq_result = mysqli_query($connect, $faq_query);

                    while ($row = mysqli_fetch_assoc($faq_result)) {
                        echo "<tr>";
                        echo "<td>" . $row['faq_id'] . "</td>";
                        echo "<td>" . $row['faq_question'] . "</td>";
                        echo "<td>" . $row['faq_answer'] . "</td>";
                        echo "<td>" . $row['faq_type'] . "</td>";
                        echo "<td>
                            <!-- Delete Button -->
                            <form action='a_faq.php' method='POST' style='display:inline;'>
                                <button type='submit' name='delete_faq' value='" . $row['faq_id'] . "' class='btn btn-danger'>
                                    <i class='fas fa-trash-alt'></i> Delete
                                </button>
                            </form>
                            <!-- Edit Button -->
                            <button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#editModal" . $row['faq_id'] . "'>
                                <i class='fas fa-edit'></i> Edit
                            </button>
                        </td>";
                        echo "</tr>";

                        // Edit Modal
                        echo "
                        <div class='modal fade' id='editModal" . $row['faq_id'] . "' tabindex='-1' aria-labelledby='editModalLabel" . $row['faq_id'] . "' aria-hidden='true'>
                            <div class='modal-dialog'>
                                <div class='modal-content'>
                                    <!-- Modal Header -->
                                    <div class='modal-header'>
                                        <h5 class='modal-title' id='editModalLabel" . $row['faq_id'] . "'>Edit FAQ: " . $row['faq_question'] . "</h5>
                                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                    </div>
                                    <!-- Modal Body -->
                                    <form action='a_faq.php' method='POST'>
                                        <div class='modal-body'>
                                            <input type='hidden' name='faq_id' value='" . $row['faq_id'] . "'>

                                            <label for='faq_question'>FAQ Question</label>
                                            <input type='text' class='form-control' id='faq_question' name='faq_question' value='" . $row['faq_question'] . "' required><br>

                                            <label for='faq_answer'>FAQ Answer</label>
                                            <textarea class='form-control' id='faq_answer' name='faq_answer' required>" . $row['faq_answer'] . "</textarea><br>

                                            <label for='faq_type'>FAQ Type</label>
                                            <select class='form-control' id='faq_type' name='faq_type' required>
                                                <option value='" . $row['faq_type'] . "' selected>" . $row['faq_type'] . "</option>";

                                                // Fetch other types for dropdown
                                                $type_query = "SELECT DISTINCT faq_type FROM faq";
                                                $type_result = mysqli_query($connect, $type_query);
                                                while ($type_row = mysqli_fetch_assoc($type_result)) {
                                                    if ($type_row['faq_type'] !== $row['faq_type']) {
                                                        echo "<option value='" . $type_row['faq_type'] . "'>" . $type_row['faq_type'] . "</option>";
                                                    }
                                                }

                        echo "
                                            </select>
                                        </div>
                                        <!-- Modal Footer -->
                                        <div class='modal-footer'>
                                            <button type='submit' name='edit_faq' class='btn btn-primary'>
                                                <i class='fas fa-save'></i> Save Changes
                                            </button>
                                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>
                                                <i class='fas fa-times'></i> Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
