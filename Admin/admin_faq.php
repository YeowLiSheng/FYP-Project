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
                        <form action='a_faq.php' method='POST' style='display:inline;'>
                            <button type='submit' name='delete_faq' value='" . $row['faq_id'] . "' class='btn btn-danger'>Delete</button>
                        </form>
                    </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
