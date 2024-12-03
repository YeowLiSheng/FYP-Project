<?php
include 'dataconnection.php';

// Add FAQ
if (isset($_POST['add_faq'])) {
    $question = mysqli_real_escape_string($connect, $_POST['faq_question']);
    $answer = mysqli_real_escape_string($connect, $_POST['faq_answer']);
    $type = mysqli_real_escape_string($connect, $_POST['faq_type']);

    $query = "INSERT INTO faq (faq_question, faq_answer, faq_type) VALUES ('$question', '$answer', '$type')";
    if (mysqli_query($connect, $query)) {
        echo "<script>alert('FAQ added successfully');</script>";
    } else {
        echo "<script>alert('Error adding FAQ');</script>";
    }
    header("Location: admin_faq.php"); // Replace with your page name
}

// Delete FAQ
if (isset($_POST['delete_faq'])) {
    $id = mysqli_real_escape_string($connect, $_POST['delete_faq']);
    $query = "DELETE FROM faq WHERE faq_id = $id";
    if (mysqli_query($connect, $query)) {
        echo "<script>alert('FAQ deleted successfully');</script>";
    } else {
        echo "<script>alert('Error deleting FAQ');</script>";
    }
    header("Location: admin_faq.php"); // Replace with your page name
}

if (isset($_POST['edit_faq'])) {
    $question = mysqli_real_escape_string($connect, $_POST['faq_question']);
    $answer = mysqli_real_escape_string($connect, $_POST['faq_answer']);
    $type = mysqli_real_escape_string($connect, $_POST['faq_type']);

    $query = "Update faq (faq_question, faq_answer, faq_type) VALUES ('$question', '$answer', '$type')";
    if (mysqli_query($connect, $query)) {
        echo "<script>alert('FAQ added successfully');</script>";
    } else {
        echo "<script>alert('Error adding FAQ');</script>";
    }
    header("Location: admin_faq.php"); // Replace with your page name
}

?>
