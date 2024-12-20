<?php
// At the top of your admin includes file
require_once(__DIR__ . '/../db_config.php');

$connection = mysqli_connect("localhost", "root", "", "PharmEasy");
// $connection = mysqli_connect("localhost", "id18666014_md_taha_ahmed", "bGCL0+&4qT64IM_{", "id18666014_pharmeasy");
    function post_redirect($url)
{
    ob_start();
    header('Location: ' . $url);
    // header('Location: https://md-taha-ahmed.000webhostapp.com/pharmeasy/' . $url);
    ob_end_flush();
    die();
}

function get_redirect($url)
{
    echo " <script> 
    window.location.href = '" . $url . "'; 
    </script>";
    // echo "<script>
    // window.location.href = 'https://md-taha-ahmed.000webhostapp.com/pharmeasy/" . $url . "';
    // </script>";
}
function query($query)
{
    global $connection;
    $run = mysqli_query($connection, $query);
    if ($run) {
        while ($row = $run->fetch_assoc()) {
            $data[] = $row;
        }
        if (!empty($data)) {
            return $data;
        } else {
            return "";
        }
    } else {
        return 0;
    }
}
function single_query($query)
{
    global $connection;
    if (mysqli_query($connection, $query)) {
        return "done";
    } else {
        die("no data" . mysqli_connect_error($connection));
    }
}
function login()
{
    if (isset($_POST['login'])) {

        $userEmail = trim(strtolower($_POST['userEmail']));
        $password = trim($_POST['password']);
        if (empty($userEmail) or empty($password)) {
            $_SESSION['message'] = "empty_err";
            post_redirect("login.php");
        }
        $query = "SELECT  email , user_id , user_password FROM user WHERE email= '$userEmail' ";
        $data = query($query);
        if (empty($data)) {
            $_SESSION['message'] = "loginErr";
            post_redirect("login.php");
        } elseif ($password == $data[0]['user_password'] and  $userEmail == $data[0]['email']) {
            $_SESSION['user_id'] = $data[0]['user_id'];
            post_redirect("index.php");
        } else {
            $_SESSION['message'] = "loginErr";
            post_redirect("login.php");
        }
    }
}

function singUp()
{
    if (isset($_POST['singUp'])) {
        $email  = trim(strtolower($_POST['email']));
        $fname  = trim($_POST['Fname']);
        $lname = trim($_POST['Lname']);
        $address = trim($_POST['address']);
        $passwd = trim($_POST['passwd']);
        
        // Check for empty fields
        if (empty($email) || empty($passwd) || empty($address) || empty($fname) || empty($lname)) {
            $_SESSION['message'] = "All fields are required.";
            post_redirect("signUp.php");
            return;
        }

        // Validate email format
        if (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email)) {
            $_SESSION['message'] = "Invalid email format.";
            post_redirect("signUp.php");
            return;
        }

        // Validate password strength
        if (!preg_match('/^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!@#$%]{8,30}$/', $passwd)) {
            $_SESSION['message'] = "Password must be 8-30 characters long and include at least one letter and one digit.";
            post_redirect("signUp.php");
            return;
        }

        // Check for duplicate email
        $query = "SELECT email FROM user";
        $data = query($query);
        foreach ($data as $row) {
            if ($email == $row['email']) {
                $_SESSION['message'] = "This email is already registered.";
                post_redirect("signUp.php");
                return;
            }
        }

        // Insert user data into the database
        $query = "INSERT INTO user (email, user_fname, user_lname, user_address, user_password) 
                VALUES('$email', '$fname', '$lname', '$address', '$passwd')";
        $queryStatus = single_query($query);

        if ($queryStatus == "done") {
            $_SESSION['message'] = "Registration successful. Please log in.";
            post_redirect("login.php");
        } else {
            $_SESSION['message'] = "An error occurred while creating your account. Please try again.";
            post_redirect("signUp.php");
        }
    }
}

function message()
{
    if (isset($_SESSION['message'])) {
        if ($_SESSION['message'] == "signup_err_password") {
            echo "   <div class='alert alert-danger' role='alert'>
        please enter the password in correct form !!!
      </div>";
            unset($_SESSION['message']);
        } elseif ($_SESSION['message'] == "loginErr") {
            echo "   <div class='alert alert-danger' role='alert'>
        The email or the password is incorrect !!!
      </div>";
            unset($_SESSION['message']);
        } elseif ($_SESSION['message'] == "usedEmail") {
            echo "   <div class='alert alert-danger' role='alert'>
        This email is already used !!!
      </div>";
            unset($_SESSION['message']);
        } elseif ($_SESSION['message'] == "wentWrong") {
            echo "   <div class='alert alert-danger' role='alert'>
        Something went wrong !!!
      </div>";
            unset($_SESSION['message']);
        } elseif ($_SESSION['message'] == "empty_err") {
            echo "   <div class='alert alert-danger' role='alert'>
        please don't leave anything empty !!!
      </div>";
            unset($_SESSION['message']);
        } elseif ($_SESSION['message'] == "signup_err_email") {
            echo "   <div class='alert alert-danger' role='alert'>
        please enter the email in the correct form !!!
      </div>";
            unset($_SESSION['message']);
        }
    }
}
function search()
{
    if (isset($_GET['search'])) {
        $search_text = $_GET['search_text'];
        if ($search_text == "") {
            return;
        }
        $query = "SELECT * FROM item WHERE item_tags LIKE '%$search_text%'";
        $data = query($query);
        return $data;
    } elseif (isset($_GET['cat'])) {
        $cat = $_GET['cat'];
        $query = "SELECT * FROM item WHERE item_cat='$cat' ORDER BY RAND()";
        $data = query($query);
        return $data;
    }
}
function all_products()
{
    $query = "SELECT * FROM item ORDER BY RAND()";
    $data = query($query);
    return $data;
}
function total_price($data)
{
    $sum = 0;
    $num = sizeof($data);
    for ($i = 0; $i < $num; $i++) {
        // Corrected the line below to accumulate the correct sum
        $sum += ($data[$i][0]['item_price'] * $_SESSION['cart'][$i]['quantity']);
    }
    return $sum;
}
function get_item()
{
    if (isset($_GET['product_id'])) {
        $_SESSION['item_id'] = $_GET['product_id'];
        $id = $_GET['product_id'];
        $query = "SELECT * FROM item WHERE item_id='$id'";
        $data = query($query);
        return $data;
    }
}function add_cart($item_id)
{
    $user_id = $_SESSION['user_id'];
    $quantity = isset($_GET['quantity']) ? $_GET['quantity'] : 1; // Default quantity to 1 if not set

    if (empty($user_id)) {
        get_redirect("login.php");
    } else {
        if (isset($_SESSION['cart'])) {
            $item_exists = false;
            foreach ($_SESSION['cart'] as &$cart_item) {
                if ($cart_item['item_id'] == $item_id) {
                    $cart_item['quantity'] += $quantity;
                    $item_exists = true;
                    break;
                }
            }
            if (!$item_exists) {
                $_SESSION['cart'][] = array('user_id' => $user_id, 'item_id' => $item_id, 'quantity' => $quantity);
            }
        } else {
            $_SESSION['cart'] = array(array('user_id' => $user_id, 'item_id' => $item_id, 'quantity' => $quantity));
        }

        if (isset($_GET['cart'])) {
            get_redirect("product.php?product_id=" . $item_id);
        } elseif (isset($_GET['buy'])) {
            get_redirect("cart.php");
        }
    }
}

function get_cart()
{
    $num = sizeof($_SESSION['cart']);
    if (isset($num)) {
        for ($i = 0; $i < $num; $i++) {
            $item_id = $_SESSION['cart'][$i]['item_id'];
            $query = "SELECT item_id, item_image ,item_title  ,item_quantity ,item_price ,item_brand FROM item WHERE item_id='$item_id'";
            $data[$i] = query($query);
        }
        return $data;
    }
}
function delete_from_cart()
{
    if (isset($_GET['delete'])) {
        $item_id = $_GET['delete'];
        $num = sizeof($_SESSION['cart']);
        for ($i = 0; $i < $num; $i++) {
            if ($_SESSION['cart'][$i]['item_id'] == $item_id) {
                unset($_SESSION['cart'][$i]);
                $_SESSION['cart'] = array_values($_SESSION['cart']);
                break;
            }
        }
        get_redirect("cart.php");
    } elseif (isset($_GET['delete_all'])) {
        unset($_SESSION['cart']);
        get_redirect("cart.php");
    }
}
function add_order()
{
    if (isset($_GET['order'])) {
        $num = sizeof($_SESSION['cart']);
        date_default_timezone_set("Asia/Kolkata");
        $date = date("Y-m-d");
        for ($i = 0; $i < $num; $i++) {
            $item_id = $_SESSION['cart'][$i]['item_id'];
            $user_id = $_SESSION['cart'][$i]['user_id'];
            $quantity = $_SESSION['cart'][$i]['quantity'];
            if ($quantity == 0) {
                return;
            } else {
                $query = "INSERT INTO orders (user_id,item_id,order_quantity,order_date) 
                VALUES('$user_id','$item_id','$quantity','$date')";
                $data =   single_query($query);
                $item = get_item_id($item_id);
                $new_quantity = $item[0]['item_quantity'] - $quantity;
                $query = "UPDATE item SET item_quantity='$new_quantity' WHERE item_id = '$item_id'";
                single_query($query);
            }
        }
        unset($_SESSION['cart']);
        get_redirect("final.php");
    }
}
function check_user($id)
{
    $query = "SELECT user_id FROM user where user_id='$id'";
    $row = query($query);
    if (empty($row)) {
        return 0;
    } else {
        return 1;
    }
}
function get_user($id)
{
    $query = "SELECT user_id ,user_fname ,user_lname ,email ,user_address FROM user WHERE user_id=$id";
    $data = query($query);
    return $data;
}
function get_item_id($id)
{
    $query = "SELECT * FROM item WHERE item_id= '$id'";
    $data = query($query);
    return $data;
}
