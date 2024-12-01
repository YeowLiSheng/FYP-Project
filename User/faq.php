<?php
// Start session
session_start();

// Include the database connection file
include("dataconnection.php");

// Check if the database connection exists
if (!isset($connect) || !$connect) {
    die("Database connection failed.");
}

// Retrieve FAQ data from the database
$query = "SELECT faq_question, faq_answer FROM faq";
$result = mysqli_query($connect, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($connect));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQs</title>
    <link rel="stylesheet" href="styles.css"> <!-- Add your CSS file here -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .faq-section {
            max-width: 800px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            font-size: 36px;
            margin-bottom: 20px;
        }

        .faq-category {
            margin-bottom: 20px;
        }

        .faq-item {
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }

        .faq-question {
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            padding: 10px;
            background-color: #f1f1f1;
        }

        .faq-answer {
            display: none;
            padding: 10px;
            background-color: #fff;
        }

        .faq-question:hover {
            background-color: #e0e0e0;
        }
    </style>
</head>
<body>

<div class="faq-section">
    <h1>FAQs</h1>
    
    <div class="faq-category">
        <h2>Web Order Shipping</h2>

        <?php
        while ($row = mysqli_fetch_assoc($result)) {
            $faq_question = htmlspecialchars($row['faq_question']);
            $faq_answer = htmlspecialchars($row['faq_answer']);
            echo "
            <div class='faq-item'>
                <div class='faq-question'>$faq_question</div>
                <div class='faq-answer'>$faq_answer</div>
            </div>";
        }
        ?>
    </div>
</div>

<script>
    // Toggle FAQ answer visibility
    document.querySelectorAll('.faq-question').forEach(item => {
        item.addEventListener('click', () => {
            const answer = item.nextElementSibling;
            answer.style.display = answer.style.display === 'none' || answer.style.display === '' ? 'block' : 'none';
        });
    });
</script>

</body>
</html>
