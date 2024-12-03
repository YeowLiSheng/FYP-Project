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
    $faq_id = mysqli_real_escape_string($connect, $_POST['faq_id']); // Assuming you have the FAQ ID to identify the row
    $question = mysqli_real_escape_string($connect, $_POST['faq_question']);
    $answer = mysqli_real_escape_string($connect, $_POST['faq_answer']);
    $type = mysqli_real_escape_string($connect, $_POST['faq_type']);

    // Correct UPDATE query
    $query = "UPDATE faq SET faq_question = '$question', faq_answer = '$answer', faq_type = '$type' WHERE faq_id = '$faq_id'";

    // Execute the query and provide feedback
    if (mysqli_query($connect, $query)) {
        echo "<script>alert('FAQ updated successfully');</script>";
    } else {
        echo "<script>alert('Error updating FAQ: " . mysqli_error($connect) . "');</script>";
    }
    header("Location: admin_faq.php"); // Redirect to FAQ management page
    exit(); // Ensure script stops execution after redirection
}


?>
