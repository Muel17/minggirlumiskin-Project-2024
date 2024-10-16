<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
};

if(isset($_POST['add_to_wishlist'])){

   $pid = $_POST['pid'];
   $pid = filter_var($pid, FILTER_SANITIZE_STRING);
   $p_name = $_POST['p_name'];
   $p_name = filter_var($p_name, FILTER_SANITIZE_STRING);
   $p_price = $_POST['p_price'];
   $p_price = filter_var($p_price, FILTER_SANITIZE_STRING);
   $p_image = $_POST['p_image'];
   $p_image = filter_var($p_image, FILTER_SANITIZE_STRING);
   $p_qty = $_POST['p_qty'];
   $p_qty = filter_var($p_qty, FILTER_SANITIZE_STRING);

   $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
   $check_wishlist_numbers->execute([$p_name, $user_id]);

   if($check_wishlist_numbers->rowCount() > 0){
      $message[] = 'already added to wishlist!';
   }else{

      $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
      $check_wishlist_numbers->execute([$p_name, $user_id]);

      if($check_wishlist_numbers->rowCount() > 0){
         $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE name = ? AND user_id = ?");
         $delete_wishlist->execute([$p_name, $user_id]);
      }

      $insert_wishlist = $conn->prepare("INSERT INTO `wishlist`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
      $insert_wishlist->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
      $message[] = 'added to wishlist!';
   }

}

if(isset($_GET['delete'])){

   $delete_id = $_GET['delete'];
   $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE id = ?");
   $delete_wishlist_item->execute([$delete_id]);
   header('location:wishlist.php');

}

if(isset($_GET['delete_all'])){

   $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?");
   $delete_wishlist_item->execute([$user_id]);
   header('location:wishlist.php');

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>wishlist</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<style>
            @import url('https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400..700&display=swap');
    :root {
  font-size: 16px;
  font-family: 'Pixelify Sans';
  --transition-speed: 600ms;
}
.wishlist {
    padding: 50px 0;
}

.wishlist .title {
    text-align: center;
    margin-bottom: 50px;
    color: #333;
}

.wishlist .box-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.wishlist .box {
    position: relative;
    background-color: #fff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.4);
}

.wishlist .box img {
    width: 30%;
    height: auto;
    border-radius: 5px;
    margin-bottom: 20px;
}

.wishlist .box .name {
    font-weight: bold;
    margin-bottom: 10px;
}

.wishlist .box .price {
    margin-bottom: 10px;
}

.wishlist .box .qty {
    width: 50px;
    margin-bottom: 10px;
}

.wishlist .box .option-btn {
    padding: 5px 10px;
    background-color: #333;
    color: #fff;
    text-decoration: none;
    border-radius: 5px;
    cursor: pointer;
    margin-top: 10px;
    position: absolute;
    bottom: 20px;
    right: 20px;
}

.wishlist .box .option-btn:hover {
    background-color: #555;
}

.wishlist .box .sub-total {
    margin-top: 20px;
    font-weight: bold;
}

.wishlist .wishlist-total {
    margin-top: 30px;
    text-align: center;
}

.wishlist .wishlist-total p {
    margin-bottom: 30px;
    font-size: 30px;
}

.wishlist .wishlist-total .btn,
.wishlist .wishlist-total .delete-btn, 
.wishlist .wishlist-total .option-btn {
    padding: 10px 20px;
    background-color: #333;
    color: #fff;
    text-decoration: none;
    border-radius: 5px;
    cursor: pointer;
    margin-right: 10px;
}

.wishlist .wishlist-total .btn:hover,
.wishlist .wishlist-total .delete-btn:hover,
.wishlist .wishlist-total .option-btn:hover {
    background-color: #555;
}

.wishlist .wishlist-total .disabled {
    pointer-events: none;
    opacity: 0.5;
}

   </style>
<body>
   
<?php include 'header.php'; ?>

<section class="wishlist">

   <h1 class="title">products added</h1>

   <div class="box-container">

   <?php
      $grand_total = 0;
      $select_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
      $select_wishlist->execute([$user_id]);
      if($select_wishlist->rowCount() > 0){
         while($fetch_wishlist = $select_wishlist->fetch(PDO::FETCH_ASSOC)){ 
   ?>
   <form action="" method="POST" class="box">
      <a href="wishlist.php?delete=<?= $fetch_wishlist['id']; ?>" class="fas fa-times" onclick="return confirm('delete this from wishlist?');"></a>
      <a href="view_page.php?pid=<?= $fetch_wishlist['pid']; ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= $fetch_wishlist['image']; ?>" alt="">
      <div class="name"><?= $fetch_wishlist['name']; ?></div>
      <div class="price">$<?= $fetch_wishlist['price']; ?>/-</div>
      <input type="number" min="1" value="1" class="qty" name="p_qty">
      <input type="hidden" name="pid" value="<?= $fetch_wishlist['pid']; ?>">
      <input type="hidden" name="p_name" value="<?= $fetch_wishlist['name']; ?>">
      <input type="hidden" name="p_price" value="<?= $fetch_wishlist['price']; ?>">
      <input type="hidden" name="p_image" value="<?= $fetch_wishlist['image']; ?>">
      <input type="submit" value="add to wishlist" name="add_to_wishlist" class="btn">
   </form>
   <?php
      $grand_total += $fetch_wishlist['price'];
      }
   }else{
      echo '<p class="empty">your wishlist is empty</p>';
   }
   ?>
   </div>

   <div class="wishlist-total">
      <p>grand total : <span>$<?= $grand_total; ?>/-</span></p>
      <a href="shop.php" class="option-btn">continue shopping</a>
      <a href="wishlist.php?delete_all" class="delete-btn <?= ($grand_total > 1)?'':'disabled'; ?>">delete all</a>
   </div>

</section>








<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>