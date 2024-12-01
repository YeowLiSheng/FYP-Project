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
            overflow-y: auto;
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
    <h1>FAQS</h1>

    <div class="tab-buttons">
        <button class="active" onclick="showCategory(0)">Web Order Shipping</button>
        <button onclick="showCategory(1)">General Order Queries</button>
        <button onclick="showCategory(2)">Payment</button>
        <button onclick="showCategory(3)">Problems With My Order</button>
        <button onclick="showCategory(4)">Product Information</button>
    </div>

    <!-- Category 1 -->
    <div class="faq-category active" id="category-0">
        <h2>Web Order Shipping</h2>
        <div class="faq-item">
            <div class="faq-question">
                <span>1. What shipping service do you use?</span>
                <i class="fa fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                We use reliable shipping services like FedEx, UPS, and DHL to ensure fast and secure delivery.
            </div>
        </div>
        <!-- Add more FAQ items here -->
    </div>

    <!-- Additional categories with placeholder content -->
    <div class="faq-category" id="category-1">
        <h2>General Order Queries</h2>
        <div class="faq-item">
            <div class="faq-question">
                <span>1. Can I modify my order after placing it?</span>
                <i class="fa fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                You can modify your order within 24 hours by contacting customer service.
            </div>
        </div>
    </div>
    <!-- Repeat for all other categories -->
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
