<?php
session_start();
require 'database.php'; // Make sure to include your database connection

// Check if the user is logged in
if (isset($_SESSION['user_email'])) {
    $user_email = $_SESSION['user_email'];

    // Get user ID from database
    $stmt = $conn->prepare("SELECT user_id FROM user WHERE user_email = ?");
    if (!$stmt) {
        die("<script>console.error('Prepare statement failed');</script>");
    }

    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $stmt->bind_result($user_id);
    $stmt->fetch();
    $stmt->close();

    // If user_id is found, fetch their receipts
    if ($user_id) {
        // Query to fetch receipts for the logged-in user
        $receipt_stmt = $conn->prepare("SELECT receipt_product, receipt_total FROM receipt WHERE user_id = ?");
        $receipt_stmt->bind_param("i", $user_id);
        $receipt_stmt->execute();
        $receipt_stmt->store_result();
        $receipt_stmt->bind_result( $receipt_product, $receipt_total);
    } else {
        echo "<script>console.error('User ID not found.');</script>";
    }
} else {
    // If no session exists, redirect to login page
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Receipts</title>
    <link rel="stylesheet" href="styles.css">
</head>
<nav class="navbar">
  <div class="logo">
    <img src="images/LOGO3.png" alt="GoMart Logo" />
  </div>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="shop.php">Shop</a></li>
    <li><a href="receipt.php">Receipt</a></li>
  </ul>
  <div class="hamburger">
    <span></span>
    <span></span>
    <span></span>
  </div>
  <div class="shopping">
    <img src="images/shopping-bag.png" alt="shopping-bag">
    <span class="quantity">0</span>
  </div>
   <a href="logout.php" class="logoutbtn">Logout</a>
</nav>
<body class="receipt-body">
    <h1 class="receipt">Products Receipt Log</h1>
    
    <?php if (isset($user_id)): ?>
        <?php if ($receipt_stmt->num_rows > 0): ?>
            <?php 
            $overall_total = 0; 
            ?>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($receipt_stmt->fetch()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($receipt_product); ?></td>
                            <td><?php echo "₱" . number_format($receipt_total, 2); ?></td>
                        </tr>
                        <?php 
                        $overall_total += $receipt_total; 
                        ?>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div style="text-align: right; margin-right: 10%; font-weight: bold; font-size: 1.2em;">
                Overall Total: <?php echo "₱" . number_format($overall_total, 2); ?>
            </div>
        <?php else: ?>
            <p>No receipts found.</p>
        <?php endif; ?>
        <?php $receipt_stmt->close(); ?>
    <?php endif; ?>


    <script>
  
  const hamburger = document.querySelector('.hamburger');
  const navLinks = document.querySelector('.nav-links');

  
  hamburger.addEventListener('click', () => {
    navLinks.classList.toggle('open');
  });
</script>

</body>
</html>
