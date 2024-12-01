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

// Example categories
$categories = [
    'Web Order Shipping',
    'General Order Queries',
    'Payment',
    'Problems With My Order',
    'Product Information'
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQs - YLS Luxury Bags & Accessories</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .faq-container {
            max-width: 900px;
            margin: 50px auto;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            padding: 30px;
        }

        h1 {
            text-align: center;
            font-size: 36px;
            color: #333;
            margin-bottom: 20px;
        }

        .tab-buttons {
            display: flex;
            justify-content: space-around;
            border-bottom: 2px solid #ddd;
            margin-bottom: 20px;
        }

        .tab-buttons button {
            flex: 1;
            padding: 15px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            background-color: #f1f1f1;
            transition: background-color 0.3s ease;
            color: #555;
            border-bottom: 3px solid transparent;
        }

        .tab-buttons button:hover,
        .tab-buttons button.active {
            background-color: #fff;
            color: #333;
            border-bottom: 3px solid #333;
        }

        .faq-category {
            display: none;
        }

        .faq-category.active {
            display: block;
        }

        .faq-item {
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }

        .faq-question {
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            padding: 10px;
            background-color: #f1f1f1;
            border-radius: 4px;
            transition: background-color 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .faq-question:hover {
            background-color: #ddd;
        }

        .faq-answer {
            display: none;
            padding: 10px 20px;
            background-color: #fff;
            border-radius: 4px;
            margin-top: 10px;
            font-size: 16px;
            color: #555;
        }

        .faq-question i {
            font-size: 18px;
            transition: transform 0.3s ease;
        }

        .faq-question.open i {
            transform: rotate(180deg);
        }
    </style>
</head>

<body>

<div class="faq-container">
    <h1>Frequently Asked Questions</h1>

    <div class="tab-buttons">
        <?php foreach ($categories as $index => $category): ?>
            <button class="<?php echo $index === 0 ? 'active' : ''; ?>"
                    onclick="showCategory(<?php echo $index; ?>)">
                <?php echo $category; ?>
            </button>
        <?php endforeach; ?>
    </div>

    <?php foreach ($categories as $index => $category): ?>
        <div class="faq-category <?php echo $index === 0 ? 'active' : ''; ?>" id="category-<?php echo $index; ?>">
            <h2><?php echo $category; ?></h2>
            <?php
            mysqli_data_seek($result, 0); // Reset the result pointer for each category
            $i = 1;
            while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="faq-item">
                    <div class="faq-question">
                        <span><?php echo $i . ". " . htmlspecialchars($row['faq_question']); ?></span>
                        <i class="fa fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer"><?php echo htmlspecialchars($row['faq_answer']); ?></div>
                </div>
            <?php $i++; endwhile; ?>
        </div>
    <?php endforeach; ?>
</div>

<script>
    function showCategory(index) {
        document.querySelectorAll('.faq-category').forEach(function (category) {
            category.classList.remove('active');
        });

        document.querySelectorAll('.tab-buttons button').forEach(function (button) {
            button.classList.remove('active');
        });

        document.getElementById('category-' + index).classList.add('active');
        document.querySelectorAll('.tab-buttons button')[index].classList.add('active');
    }

    document.querySelectorAll('.faq-question').forEach(item => {
        item.addEventListener('click', () => {
            const answer = item.nextElementSibling;
            const icon = item.querySelector('i');

            if (answer.style.display === 'block') {
                answer.style.display = 'none';
                icon.classList.remove('open');
            } else {
                answer.style.display = 'block';
                icon.classList.add('open');
            }
        });
    });
</script>

</body>

</html>
