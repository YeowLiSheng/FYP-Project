<?php
// Start session
session_start();

// Include the database connection file
include("dataconnection.php");

// Check if the database connection exists
if (!isset($connect) || !$connect) {
    die("Database connection failed.");
}

// Retrieve FAQ data from the database grouped by `faq_type`
$query = "SELECT faq_question, faq_answer, faq_type FROM faq";
$result = mysqli_query($connect, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($connect));
}

$faq_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $faq_data[$row['faq_type']][] = $row;
}

// Define categories
$categories = [
    'Order_shipping' => 'Web Order Shipping',
    'Order_queries' => 'General Order Queries',
    'Payment' => 'Payment',
    'Order_problem' => 'Problems With My Order',
    'Product_info' => 'Product Information',
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
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .faq-container {
            width: 100%;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 30px;
            overflow: hidden;
            overflow-y: auto;
            box-sizing: border-box;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Styling for the H1 Header with Background Image */
        h1 {
            text-align: center;
            font-size: 36px;
            color: white; /* Text color */
            padding: 60px 20px;
            width: 100%;
            border-radius: 8px;
            margin-bottom: 20px;
            background-image: url('../User/images/HeadFaq.avif'); /* Add your image URL here */
            background-size: cover; /* Make the image cover the entire header */
            background-position: center; /* Center the image */
            background-repeat: no-repeat; /* Prevent the image from repeating */
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .tab-buttons {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }

        .tab-buttons button {
            flex: 1;
            padding: 15px;
            font-size: 16px;
            cursor: pointer;
            background-color: #333;
            color: #fff;
            border: 2px solid #000;
            text-transform: uppercase;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .tab-buttons button:hover,
        .tab-buttons button.active {
            background-color: #fff;
            color: #333;
            border-bottom: 3px solid #333;
        }

        .faq-category {
            flex: 1;
            display: none;
        }

        .faq-category.active,
        .faq-category.show-all {
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
            padding: 15px;
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
            height: 0; /* Initially, height is 0 */
            overflow: hidden; /* Prevent content overflow */
            transition: height 0.3s ease; /* Smooth transition effect for height */
            padding: 0 20px; /* Add horizontal padding */
            background-color: #fff;
            border-radius: 4px;
            font-size: 18px;
            color: #555;
            margin-top: 10px;
        }
        .faq-answer.open {
            height: 50px; /* Set the fixed height */
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
    <h1>FAQs</h1>

    <div class="tab-buttons">
        <button class="active" onclick="showCategory('all')">All</button>
        <?php foreach ($categories as $type => $label): ?>
            <button onclick="showCategory('<?php echo $type; ?>')"><?php echo $label; ?></button>
        <?php endforeach; ?>
    </div>

    <?php foreach ($categories as $type => $label): ?>
        <div class="faq-category" id="category-<?php echo $type; ?>">
            <h2><?php echo $label; ?></h2>
            <?php if (isset($faq_data[$type])): ?>
                <?php foreach ($faq_data[$type] as $faq): ?>
                    <div class="faq-item">
                        <div class="faq-question">
                            <span><?php echo $faq['faq_question']; ?></span>
                            <i class="fa fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer"><?php echo $faq['faq_answer']; ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No FAQs available for this category.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<script>
    function showCategory(type) {
    // Remove 'active' and 'show-all' classes from all categories
    document.querySelectorAll('.faq-category').forEach(function (category) {
        category.classList.remove('active', 'show-all');
    });

    if (type === 'all') {
        // Add 'show-all' to all categories
        document.querySelectorAll('.faq-category').forEach(function (category) {
            category.classList.add('show-all');
        });
    } else {
        // Add 'active' only to the selected category
        document.getElementById('category-' + type).classList.add('active');
    }

    // Update the active state of buttons
    document.querySelectorAll('.tab-buttons button').forEach(function (button) {
        button.classList.remove('active');
    });
    document.querySelector('.tab-buttons button[onclick="showCategory(\'' + type + '\')"]').classList.add('active');
}

document.querySelectorAll('.faq-question').forEach(item => {
    item.addEventListener('click', () => {
        const answer = item.nextElementSibling;
        answer.classList.toggle('open'); // Add or remove the 'open' class to control height
        item.classList.toggle('open'); // Rotate the arrow icon
    });
});

// Initialize all answers with the correct state for closed
window.onload = function () {
    document.querySelectorAll('.faq-answer').forEach(answer => {
        answer.classList.remove('open'); // Start with answers collapsed
    });
    showCategory('all'); // Show all categories by default
};

</script>

</body>

</html>