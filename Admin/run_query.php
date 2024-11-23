<?php
include 'databaseconnect.php';
// No need to check for 'bid' anymore since we fetch all categories
$sql = "SELECT * FROM category";
$result = mysqli_query($connect, $sql);

$out = '';
while ($row = mysqli_fetch_assoc($result)) {
    $out .= '<option value="' . $row['category_id'] . '">' . str_replace("_", " ", $row['category_name']) . '</option>';
}

// Output the generated options for the category dropdown
echo $out;

if (isset($_POST['cust'])) {
    $s = $_POST['cust'];
    $cust_result = mysqli_query($connect, "SELECT * FROM user_information WHERE user_name LIKE '%$s%'");
    $data = '';

    while ($row = mysqli_fetch_array($cust_result)) {
        $data .= '<tr onclick="window.location=\'cust_detail.php?ID=' . $row['ID'] . '\';">
            <th scope="row">' . $row["ID"] . '</th>
            <td>' . $row["user_name"] . '<br>
            </td>
            <td style="vertical-align: middle;">Telephone.No:' . $row["contactnumber"] . '<br>Email:' . $row["email"] . '</td>
            <td>' . $row["join_time"] . '</td>
        </tr>';
    }

    echo $data;
}
//check staff (admin)id
if (isset($_POST['id_r'])) {
    $id = $_POST['id_r'];
    $id_r = "SELECT * FROM staff WHERE admin_id = '$id'";
    $result = mysqli_query($connect, $id_r);

    if (mysqli_num_rows($result) > 0) {
        echo "exists";
    } else {
        echo "not_exists";
    }
}
//check peri
if (isset($_POST['p'])) {
    $p = $_POST['p'];
    $c_c = "SELECT * FROM category WHERE category = '$p'";
    $result = mysqli_query($connect, $c_c);

    if (mysqli_num_rows($result) > 0) {
        echo "exists";
    } else {
        echo "not_exists";
    }
}

//check c 
if (isset($_POST['c'])) {
    $c = $_POST['c'];
    $c_c = "SELECT * FROM category WHERE category_name = '$c'";
    $result = mysqli_query($connect, $c_c);

    if (mysqli_num_rows($result) > 0) {
        echo "exists";
    } else {
        echo "not_exists";
    }
}

//check b
if (isset($_POST['b'])) {
    $b = $_POST['b'];
    $c_c = "SELECT * FROM brand WHERE brand_name = '$b'";
    $result = mysqli_query($connect, $c_c);

    if (mysqli_num_rows($result) > 0) {
        echo "exists";
    } else {
        echo "not_exists";
    }
}

//check add_product name
if (isset($_POST['p_n'])) {
    $p_n = $_POST['p_n'];
    $c_c = "SELECT * FROM product WHERE product_name = '$p_n'";
    $result = mysqli_query($connect, $c_c);

    if (mysqli_num_rows($result) > 0) {
        echo "exists";
    } else {
        echo "not_exists";
    }
}
//order date range filter
if (isset($_POST['order']) || isset($_POST['f1']) || isset($_POST['f2']) || isset($_POST['from']) || isset($_POST['to'])) {

    if (isset($_POST['from'])) {
        $ff = $_POST['from'];
        $f = $_POST['from'];
        $from = explode("/", $f);
        //
        //index1 = date, 0 = month; 2 = year;
        if (isset($from[2]) && isset($from[1]) && isset($from[0])) {
            $f = $from[0] . '-' . $from[1] . '-' . $from[2];
            $f = $f . " 00:00:00";
        } else {
            $f = '';
        }
    } else
        $from = '';

    if (isset($_POST['to'])) {
        $tt = $_POST['from'];
        $t = $_POST['to'];
        $to = explode("/", $t);

        if (isset($to[2]) && isset($to[1]) && isset($to[0])) {
            $t = $to[0] . '-' . $to[1] . '-' . $to[2];
            $t = $t . " 23:59:59";
        } else {
            $t = '';
        }
    } else
        $to = '';

    if (isset($_POST['order']))
        $o = $_POST['order'];
    else
        $o = '';

    if (isset($_POST['f1']))
        $f1 = $_POST['f1'];
    else
        $f1 = '';

    if (isset($_POST['f2']))
        $f2 = $_POST['f2'];
    else
        $f2 = '';

    $query = "SELECT *,user_information.user_name
    FROM order_ 
    JOIN user_information ON order_.user_id = user_information.ID WHERE 1";


    if (!empty($f))
        $query .= " AND time_status >= '$f'";
    if (!empty($t))
        $query .= " AND time_status <= '$t'";
    if (!empty($o))
        $query .= " AND user_name LIKE '%$o%'";

    if (!empty($f1))
        $query .= " AND delivery_status= '$f1'";

    if (!empty($f2)) {
        if ($f2 == 'a')
            $query .= " ORDER BY order_id DESC";
        else if ($f2 == 'b')
            $query .= " ORDER BY order_id";
        else if ($f2 == 'c') {
            $query .= " ORDER BY CAST(total_amount AS DECIMAL(10,2)) DESC";
        } else if ($f2 == 'd') {
            $query .= " ORDER BY CAST(total_amount AS DECIMAL(10,2))";
        }

    }

    $o_run = mysqli_query($connect, $query);
    $o_output = '';

    while ($row = mysqli_fetch_assoc($o_run)) {
        $user_id = $row["user_id"];
        $user = "SELECT * FROM user_information WHERE ID = '$user_id'";
        $user_run = mysqli_query($connect, $user);
        $row_user = mysqli_fetch_assoc($user_run);

        $address_id = $row["address_id"];
        $add = "SELECT * FROM user_address WHERE address_id = '$address_id'";
        $add_run = mysqli_query($connect, $add);
        $row_add = mysqli_fetch_assoc($add_run);
        $o_output .= '<tr onclick="window.location=\'order_detail.php?order_id=' . $row['order_id'] . '\';">
        <th scope="row">' . $row["order_id"] . '</th>
        <td>
            ' . $row["user_name"] . '<br>
        </td>
        <td>
           ' . $row["time_status"] . '
        </td>
        <td>
            ' . $row_add["address"] . ", " . $row_add["postcode"] . " " . $row_add["city"]
            . ", " . $row_add["state"] . '
        </td>
        <td>
            RM' . number_format($row["total_amount"],2) . '
        </td>
        <td>
            ' . $row["delivery_status"] . '
        </td>
    </tr>';
    }
    echo $o_output;
    // echo $f.$t;
    // echo $ff.$tt;
}

if (isset($_POST['from2']) && isset($_POST['to2'])) {
    $total = 0;
    if (isset($_POST['from2'])) {
        $f = $_POST['from2'];
        $from = explode("/", $f);

        if (isset($from[2]) && isset($from[1]) && isset($from[0])) {
            $f = $from[0] . '-' . $from[1] . '-' . $from[2];
            $f = $f . " 00:00:00";
        } else {
            $f = '';
        }
    } else
        $from = '';

    echo $f;
    if (isset($_POST['to2'])) {
        $t = $_POST['to2'];
        $to = explode("/", $t);

        if (isset($to[2]) && isset($to[1]) && isset($to[0])) {
            $t = $to[0] . '-' . $to[1] . '-' . $to[2];
            $t = $t . " 23:59:59";
        } else {
            $t = '';
        }
    } else
        $to = '';

    $query = "SELECT *,user_information.user_name 
    FROM order_ 
    JOIN user_information ON order_.user_id = user_information.ID WHERE 1";

    if (!empty($f))
        $query .= " AND time_status >= '$f'";
    if (!empty($t))
        $query .= " AND time_status <= '$t'";

    $o_run = mysqli_query($connect, $query);
    $o_output = '';

    while ($row = mysqli_fetch_assoc($o_run)) {
        $total += $row["total_amount"];
        $user_id = $row["user_id"];
        $user = "SELECT * FROM user_information WHERE ID = '$user_id'";
        $user_run = mysqli_query($connect, $user);
        $row_user = mysqli_fetch_assoc($user_run);

        $address_id = $row["address_id"];
        $add = "SELECT * FROM user_address WHERE address_id = '$address_id'";
        $add_run = mysqli_query($connect, $add);
        $row_add = mysqli_fetch_assoc($add_run);
        $o_output .= '<tr onclick="window.location=\'order_detail.php?order_id=' . $row['order_id'] . '\';">
        <th scope="row">' . $row["order_id"] . '</th>
        <td>
            ' . $row["user_name"] . '<br>
        </td>
        <td>
           ' . $row["time_status"] . '
        </td>
        <td>
            RM' . number_format($row["total_amount"],2). '
        </td>
        <td>
            ' . $row["delivery_status"] . '
        </td>
    </tr>' ?>
        <?php
    }
    $o_output .= '<tr>
        <td colspan="4" style="text-align:right;"><b>Total&nbsp&nbsp&nbsp&nbsp</b>' . $total . '</td>
    </tr>';
    echo $o_output;
}
?>