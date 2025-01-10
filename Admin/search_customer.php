<?php
include 'dataconnection.php';

// Check if there's a search query
if (isset($_POST['search'])) {
    $searchQuery = mysqli_real_escape_string($connect, $_POST['search']);

    // Query to fetch the users matching the search query (by name, email, or contact number)
    $query = "SELECT * FROM user WHERE user_name LIKE '%$searchQuery%' OR user_email LIKE '%$searchQuery%' OR user_contact_number LIKE '%$searchQuery%'";
    
    // Execute the query
    $result = mysqli_query($connect, $query);

    // Check if any rows are returned
    if (mysqli_num_rows($result) > 0) {
        // Output the filtered rows
        while ($row = mysqli_fetch_assoc($result)) {
            ?>
            <tr onclick="window.location='customer_detail.php?ID=<?php echo $row['user_id']; ?>';">
                <th scope="row"><?php echo $row["user_id"] ?></th>
                <td><?php echo $row["user_name"]; ?></td>
                <td style="vertical-align: middle;">
                    Telephone.No: <?php echo $row["user_contact_number"] ?><br>
                    Email: <?php echo $row["user_email"] ?>
                </td>
                <td><?php echo $row["user_join_time"] ?></td>
                <td>
                    <form method="POST" action="update_user_status.php" style="margin: 0;">
                        <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                        <button 
                            type="submit" 
                            name="toggle_status" 
                            class="btn" 
                            style="background-color: <?php echo $row['user_status'] == 1 ? '#4CAF50' : '#ff4d4d'; ?>; color: white; border: none; padding: 5px 10px;">
                            <?php echo $row['user_status'] == 1 ? 'Active' : 'Deactivate'; ?>
                        </button>
                    </form>
                </td>
            </tr>
            <?php
        }
    } else {
        echo "<tr><td colspan='5' style='text-align:center'><b>No users found.</b></td></tr>";
    }
}
?>
