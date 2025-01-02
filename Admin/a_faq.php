<?php
include 'dataconnection.php';
session_start(); // Start session for flash messages


// Add FAQ
if (isset($_POST['add_faq'])) {
    $question = mysqli_real_escape_string($connect, $_POST['faq_question']);
    $answer = mysqli_real_escape_string($connect, $_POST['faq_answer']);
    $type = mysqli_real_escape_string($connect, $_POST['faq_type']);

    $query = "INSERT INTO faq (faq_question, faq_answer, faq_type) VALUES ('$question', '$answer', '$type')";
    if (mysqli_query($connect, $query)) {
        $_SESSION['title'] = "FAQ Added";
        $_SESSION['text'] = "FAQ has been added successfully.";
        $_SESSION['icon'] = "success";
    } else {
        $_SESSION['title'] = "Error";
        $_SESSION['text'] = "Failed to add the FAQ.";
        $_SESSION['icon'] = "error";
    }
    header("Location: admin_faq.php");
    
    exit();
}

// Delete FAQ
if (isset($_POST['delete_faq'])) {
    $id = mysqli_real_escape_string($connect, $_POST['delete_faq']);
    $query = "DELETE FROM faq WHERE faq_id = $id";
    if (mysqli_query($connect, $query)) {
        $_SESSION['title'] = "FAQ Deleted";
        $_SESSION['text'] = "FAQ has been deleted successfully.";
        $_SESSION['icon'] = "success";
    } else {
        $_SESSION['title'] = "Error";
        $_SESSION['text'] = "Failed to delete the FAQ.";
        $_SESSION['icon'] = "error";
    }
    header("Location: admin_faq.php");
    exit();
}

// Edit FAQ
if (isset($_POST['edit_faq'])) {
    $faq_id = mysqli_real_escape_string($connect, $_POST['faq_id']);
    $question = mysqli_real_escape_string($connect, $_POST['faq_question']);
    $answer = mysqli_real_escape_string($connect, $_POST['faq_answer']);
    $type = mysqli_real_escape_string($connect, $_POST['faq_type']);

    $query = "UPDATE faq SET faq_question = '$question', faq_answer = '$answer', faq_type = '$type' WHERE faq_id = '$faq_id'";
    if (mysqli_query($connect, $query)) {
        $_SESSION['title'] = "FAQ Updated";
        $_SESSION['text'] = "FAQ details updated successfully.";
        $_SESSION['icon'] = "success";
    } else {
        $_SESSION['title'] = "Error";
        $_SESSION['text'] = "Failed to update the FAQ.";
        $_SESSION['icon'] = "error";
    }
    header("Location: admin_faq.php");
    exit();
}
?>
