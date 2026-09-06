<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Invalid Bill");
}

$id = (int)$_GET['id'];

$sql = "SELECT c.*, u.name, u.electricity_no
        FROM consumption c
        INNER JOIN users u ON c.user_id = u.id
        WHERE c.id=?";

$stmt = mysqli_prepare($conn,$sql);
mysqli_stmt_bind_param($stmt,"i",$id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0){
    die("Bill not found.");
}

$bill = mysqli_fetch_assoc($result);

$upi = "9945463812@kotak811";
$name = "Nagaraju S";
$amount = number_format($bill['total_bill'],2,'.','');

$upiLink="upi://pay?pa=".$upi.
"&pn=".urlencode($name).
"&am=".$amount.
"&cu=INR".
"&tn=".urlencode("PowerTrack Electricity Bill");

$qr="https://quickchart.io/qr?text=".urlencode($upiLink)."&size=250";
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<title>PowerTrack Payment</title>
<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Arial,sans-serif;
}

body{
background:#0f1720;
padding:40px;
}

.container{
max-width:1100px;
margin:auto;
background:#17212b;
border-radius:15px;
display:flex;
padding:30px;
gap:30px;
color:white;
}

.left{
width:50%;
}

.right{
width:50%;
text-align:center;
}

.payment-option{
padding:15px;
background:#202d39;
margin-bottom:15px;
border-radius:10px;
cursor:pointer;
font-size:18px;
}

.payment-option:hover{
background:#2b3948;
}

.amount{
font-size:36px;
color:#00ff99;
margin:20px 0;
}

button{
background:#ffc107;
padding:12px 25px;
border:none;
border-radius:8px;
font-size:16px;
cursor:pointer;
font-weight:bold;
}

.back{
display:inline-block;
margin-top:20px;
padding:12px 25px;
background:#555;
color:white;
text-decoration:none;
border-radius:8px;
}

</style>

</head>

<body>

<div class="container">

<div class="left">

<h1>💳 Electricity Bill Payment</h1>

<h2 style="margin-top:20px;">
<?php echo $bill['name']; ?>
</h2>

<p style="margin-top:10px;">
Electricity No:
<b><?php echo $bill['electricity_no']; ?></b>
</p>

<div class="amount">
₹<?php echo $amount; ?>
</div>

<h3 style="margin-bottom:20px;">
Choose Payment Method
</h3>

<div class="payment-option">
📱 Google Pay / PhonePe / Paytm / BHIM
</div>

<div class="payment-option">
💳 Debit / Credit Card
</div>

<div class="payment-option">
🏦 Net Banking
</div>

<div class="payment-option">
👛 Wallet
</div>
</div>

<div class="right">

<h2 style="color:#ffc107;">
Scan & Pay
</h2>

<img src="<?php echo $qr; ?>"
     width="250"
     style="background:white;padding:10px;border-radius:10px;">

<br><br>

<h3 style="color:#00ff99;">
₹<?php echo $amount; ?>
</h3>

<p>
<b>UPI ID</b><br>
9945463812@kotak811
</p>

<p style="margin-top:15px;">
Use Google Pay, PhonePe, Paytm or any UPI app.
</p>

<br>

<button onclick="confirmPayment()">
✅ Confirm Payment
</button>

<br><br>

<a href="print_bill.php?id=<?php echo $bill['id']; ?>"
class="back">
← Back to Bill
</a>

</div>

</div>
<script>

function confirmPayment()
{
    if(confirm("Have you completed the payment?"))
    {
        window.location.href =
        "payment_success.php?id=<?php echo $bill['id']; ?>";
    }
}

</script>

</body>
</html>