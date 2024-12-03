<?php 
include 'admin_sidebar.php'; 
include 'dataconnection.php'; 
?>

<head>
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

<style>
    .card {
        padding: 20px;
    }

    .error-message {
        color: red;
    }
</style>

<body>
    <div class="main p-3">
        <div class="card" style="width:100%;">
            <h1>Add FAQ</h1>
            <form action="a_faq.php" method="POST" id="s_form" name="s_form">
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

                <!-- Submit Button -->
                <button onclick="add_check(event);" class="btn btn-primary" name="add_faq">Add FAQ</button>
            </form>
        </div>

        <!-- Existing FAQs Table -->
        <hr>
        <h2>Existing FAQs</h2>
        <table class="table">
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
                            <button type='submit' name='delete_faq' value='" . $row['faq_id'] . "' class='btn btn-danger'>Delete</button>
                        </form>
                        <!-- Edit Button -->
                        <button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#editModal" . $row['faq_id'] . "'>
                            Edit
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
                                        <button type='submit' name='edit_faq' class='btn btn-primary'>Save Changes</button>
                                        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
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
</body>
