<?php
require_once 'admin/config/db.php';

/* =====================
   AMBIL KATEGORI
===================== */
$categories = [];
$qCat = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
while ($c = $qCat->fetch_assoc()) {
    $categories[] = $c;
}

/* =====================
   AMBIL PRODUK (GROUP PER KATEGORI)
===================== */
$products = [];
$qProd = $conn->query("
    SELECT 
        p.id, p.name, p.description, p.price_sell,
        c.id AS category_id,
        pi.image
    FROM products p
    JOIN categories c ON c.id = p.category_id
    LEFT JOIN product_images pi ON pi.product_id = p.id
    WHERE p.is_active = 1
    ORDER BY p.created_at DESC
");

while ($p = $qProd->fetch_assoc()) {
    $products[$p['category_id']][] = $p;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Menu</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- CSS ASLI TEMPLATE -->
<link rel="stylesheet" href="css/bootstrap.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
<link href="css/font-awesome.min.css" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
<link href="css/responsive.css" rel="stylesheet">

<!-- STYLE KHUSUS MENU -->
<style>
.menu-nav .btn {
  border-radius: 30px;
  margin: 5px;
  transition: .3s ease;
}
.menu-nav .btn.active {
  background: #ffbe33;
  color: #000;
  box-shadow: 0 5px 15px rgba(255,190,51,.4);
  transform: translateY(-2px);
}

.menu-card {
  border-radius: 15px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 4px 15px rgba(0,0,0,.08);
  height: 100%;
}

.menu-card .img-box {
  height: 220px;
  overflow: hidden;
}

.menu-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.owl-item {
  min-height: 420px;
}

.empty-category {
  min-height: 300px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  color: #999;
}

.empty-category i {
  font-size: 48px;
  color: #ffbe33;
  margin-bottom: 10px;
}
</style>
</head>

<body class="sub_page">

<!-- ================= HERO + NAVBAR (ASLI) ================= -->
<div class="hero_area">
  <div class="bg-box">
    <img src="images/hero-pg.jpg" alt="">
  </div>

  <header class="header_section">
    <div class="container">
      <nav class="navbar navbar-expand-lg custom_nav-container">
        <a class="navbar-brand" href="index.php">
          <span>CHABA BONSAI</span>
        </a>

        <div class="collapse navbar-collapse">
          <ul class="navbar-nav mx-auto">
            <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
            <li class="nav-item active"><a class="nav-link" href="menu.php">Menu</a></li>
            <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
            <li class="nav-item"><a class="nav-link" href="book.php">Book Table</a></li>
          </ul>
          <div class="user_option">
            <a href="#" class="user_link"><i class="fa fa-user"></i></a>
            <a class="order_online" href="#">Order Online</a>
          </div>
        </div>
      </nav>
    </div>
  </header>
</div>

<!-- ================= MENU ================= -->
<section class="food_section layout_padding">
<div class="container">

  <div class="heading_container heading_center mb-4">
    <h2>Our Menu</h2>
  </div>

  <!-- NAV KATEGORI -->
  <div class="text-center menu-nav mb-4">
    <?php foreach ($categories as $i => $c): ?>
      <button class="btn btn-outline-warning <?= $i === 0 ? 'active' : '' ?>"
              data-index="<?= $i; ?>">
        <?= htmlspecialchars($c['name']); ?>
      </button>
    <?php endforeach; ?>
  </div>

  <!-- CAROUSEL -->
  <div class="owl-carousel owl-theme" id="menuCarousel">

    <?php foreach ($categories as $c): ?>
    <div class="item">
      <h3 class="text-center mb-4 fw-bold"><?= htmlspecialchars($c['name']); ?></h3>

      <div class="row justify-content-center">
        <?php if (!empty($products[$c['id']])): ?>
          <?php foreach ($products[$c['id']] as $p):

            $image = 'assets/images/no-image.png';
            if (!empty($p['image'])) {
              $file = basename($p['image']);
              $path = 'admin/uploads/products/' . $file;
              if (file_exists(__DIR__ . '/' . $path)) {
                $image = $path;
              }
            }
          ?>
          <div class="col-sm-6 col-lg-4 mb-4">
            <div class="menu-card">
              <div class="img-box">
                <img src="<?= htmlspecialchars($image); ?>">
              </div>
              <div class="p-3 text-center">
                <h5><?= htmlspecialchars($p['name']); ?></h5>
                <p><?= htmlspecialchars($p['description']); ?></p>
                <h6 class="text-warning fw-bold">
                  Rp <?= number_format($p['price_sell'],0,',','.'); ?>
                </h6>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-category">
            <i class="fa fa-dropbox"></i>
            <h5>Produk belum tersedia</h5>
            <p>Silakan pilih kategori lain</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>

  </div>
</div>
</section>

<!-- ================= FOOTER (ASLI) ================= -->
<footer class="footer_section">
  <div class="container text-center">
    <p class="text-white mb-0">
      &copy; <?= date('Y'); ?> CHABA BONSAI
    </p>
  </div>
</footer>

<!-- JS -->
<script src="js/jquery-3.4.1.min.js"></script>
<script src="js/bootstrap.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>

<script>
$(function () {

  const owl = $('#menuCarousel').owlCarousel({
    items: 1,
    loop: false,
    dots: false,
    nav: false,
    smartSpeed: 600
  });

  $('.menu-nav .btn').click(function () {
    let index = $(this).data('index');
    owl.trigger('to.owl.carousel', [index, 500]);

    $('.menu-nav .btn').removeClass('active');
    $(this).addClass('active');
  });

  owl.on('changed.owl.carousel', function (event) {
    let index = event.item.index;
    $('.menu-nav .btn').removeClass('active');
    $('.menu-nav .btn').eq(index).addClass('active');
  });

});
</script>

</body>
</html>
