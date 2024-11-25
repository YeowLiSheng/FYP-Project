<?php
include 'dataconnection.php';

if (isset($_POST['search'])) {
    $search = $_POST['search'];

    // SQL query to search for users whose name matches the search query
    $query = "SELECT * FROM user WHERE user_name LIKE '%$search%'";  
    $result = mysqli_query($connect, $query);

    // Check if there are any matching results
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr onclick=\"window.location='customer_detail.php?ID=".$row['user_id']."';\">
                    <th scope='row'>".$row['user_id']."</th>
                    <td>".$row['user_name']."<br></td>
                    <td style='vertical-align: middle;'>Telephone.No: ".$row['user_contact_number']."<br>Email: ".$row['user_email']."</td>
                    <td>".$row['user_join_time']."</td>
                  </tr>";
        }
    } else {
        echo "<td colspan='5' style='text-align:center'><b>No users found.</b></td>";
    }
}
?>
