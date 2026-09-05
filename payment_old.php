<?php
include "db.php";

if (!isset($_GET['id'])) {
    die("Invalid Bill");
}

$id = $_GET['id'];

$result = mysqli_query($conn,
"SELECT c.*, u.name, u.electricity_no
FROM consumption c
JOIN users u ON c.user_id=u.id
WHERE c.id='$id'");

$bill = mysqli_fetch_assoc($result);

if (!$bill) {
    die("Bill not found");
}

$upi = "9945463812@kotak811";
$name = "PowerTrack";
$amount = number_format($bill['total_bill'],2,'.','');

$upiLink = "upi://pay?pa=".$upi.
"&pn=".urlencode($name).
"&am=".$amount.
"&cu=INR".
"&tn=".urlencode("Electricity Bill");

$qr = "https://quickchart.io/qr?text=".urlencode($upiLink)."&size=250";
?>

<!DOCTYPE html>
<html>

<head>

<title>Payment</title>

<style>

body{
background:#0f172a;
color:white;
font-family:Arial;
text-align:center;
padding:40px;
}

.card{
width:500px;
margin:auto;
background:#1e293b;
padding:30px;
border-radius:15px;
}

img{
margin:20px 0;
}

button{
padding:12px 30px;
background:#ffc107;
border:none;
border-radius:8px;
font-size:18px;
cursor:pointer;
}

</style>

</head>

<body>
<h1 style="color:red;">TEST PAYMENT PAGE</h1>
<div class="card">

<h1>💳 Electricity Bill Payment</h1>

<h3>Name</h3>

<p><?php echo $bill['name']; ?></p>

<h3>Electricity Number</h3>

<p><?php echo $bill['electricity_no']; ?></p>

<h3>Bill Amount</h3>

<h2 style="color:#00ff99;">
₹<?php echo number_format($bill['total_bill'],2); ?>
</h2>

<img src="<?php echo $qr; ?>" width="250">

<p>Scan using Google Pay / PhonePe / Paytm / BHIM</p>

<a href="print_bill.php?id=<?php echo $bill['id']; ?>&payment=success">
    <button>
        ← Return to Bill
    </button>
</a>

</div>

</body>

</html>