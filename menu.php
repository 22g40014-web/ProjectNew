<?php
require_once 'admin/config/db.php';

/* ======================
   AMBIL DATA PROMO
====================== */
$promos = [];
$qPromo = $conn->query("
    SELECT 
        product_name,
        title,
        price_normal,
        discount_type,
        discount_value,
        price_after,
        image
    FROM promos
    WHERE is_active = 1
    ORDER BY created_at DESC
    LIMIT 10
");

while ($row = $qPromo->fetch_assoc()) {
    $promos[] = $row;
}


/* =====================
   AMBIL KATEGORI
===================== */
$categories = [];
$qCat = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
while ($c = $qCat->fetch_assoc()) {
    $categories[] = $c;
}

/* =====================
   AMBIL PRODUK
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
    AND p.show_in_menu = 1
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

<link rel="stylesheet" href="css/bootstrap.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
<link href="css/font-awesome.min.css" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
<link href="css/responsive.css" rel="stylesheet">

<style>
/* ===============================
   MENU STYLE
   =============================== */
.menu-nav .btn {
  border-radius: 30px;
  margin: 5px;
}

.menu-nav .btn.active {
  background: #ffbe33;
  color: #000;
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

.empty-category {
  min-height: 300px;
  display: none;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  color: #999;
}

/* ===============================
   SEARCH BAR
=============================== */
#searchProduct {
  border-radius: 30px;
  padding: 12px 20px;
  box-shadow: 0 4px 15px rgba(0,0,0,.1);
}

/* ===============================
   CAROUSEL FIX
   =============================== */
#menuCarousel {
  max-width: 1300px;
  margin: auto;
  overflow: hidden;
}

#menuCarousel .row {
  margin-left: 15px;
  margin-right: 15px;
}

#menuCarousel .owl-stage-outer {
  overflow: hidden;
}

/* ===============================
   🔥 OWL HEIGHT FIX (INTI SOLUSI)
=============================== */
#menuCarousel {
  max-width: 1300px;
  margin: auto;
}

#menuCarousel .item {
  display: flex;
  flex-direction: column;
}

@media (max-width: 768px) {
  .menu-scroll {
    min-height: 520px;
    max-height: 520px;
  }
}


#menuCarousel .owl-stage-outer {
  overflow: hidden;
}

/* AREA SCROLL PER KATEGORI */

.menu-scroll {
  min-height: 760px;   /* cukup untuk 6 card */
  max-height: 760px;
  overflow-y: auto;
  overflow-x: hidden;
}

/* SCROLLBAR */
.menu-scroll::-webkit-scrollbar {
  width: 6px;
}
.menu-scroll::-webkit-scrollbar-thumb {
  background: #ffbe33;
  border-radius: 10px;
}

.menu-scroll::-webkit-scrollbar-horizontal {
  display: none;
}

/* ===============================
   STICKY NAVBAR
   =============================== */
.header_section {
  position: sticky;
  top: 0;
  z-index: 999;
  transition: .3s ease;
}

.header_section.sticky-active {
  background: rgba(0,0,0,.9);
  box-shadow: 0 5px 20px rgba(0,0,0,.3);
}
</style>
</head>

<body class="sub_page">

<!-- ================= HEADER ================= -->
<div class="hero_area">
  <div class="bg-box">
    <img src="images/hero-pg.jpg">
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

  <!-- offer section -->
  <!-- offer section -->
<section class="offer_section layout_padding-bottom">
  <div class="offer_container">
    <div class="container">
      <div class="row">

        <?php if (!empty($promos)) : ?>
          <?php foreach ($promos as $promo) : ?>

            <div class="col-md-6">
              <div class="box">
                <div class="img-box">
                  <img src="uploads/promos/<?= htmlspecialchars($promo['image']) ?>" alt="">
                </div>

                <div class="detail-box">
                  <h5>
                    <?= htmlspecialchars($promo['product_name']) ?>
                  </h5>

                  <h6>
                    <?php if ($promo['discount_type'] == 'percent') : ?>
                      <span><?= (int)$promo['discount_value'] ?>%</span> Off
                    <?php else : ?>
                      <span>Rp <?= number_format($promo['discount_value'], 0, ',', '.') ?></span> Off
                    <?php endif; ?>
                  </h6>

                  <a href="https://wa.me/6288239468557?text=Saya%20tertarik%20promo%20<?= urlencode($promo['product_name']) ?>">
                    Order Now
                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 456.029 456.029">
                    </svg>
                  </a>
                </div>
              </div>
            </div>

          <?php endforeach; ?>
        <?php else : ?>
          <p style="text-align:center; width:100%;">Promo belum tersedia</p>
        <?php endif; ?>

      </div>
    </div>
  </div>
</section>


<!-- ================= MENU ================= -->
  <section class="food_section layout_padding" style="padding:0px 0px" >
  <div class="container-fluid">

  <h2 class="text-center mb-4">Our Menu</h2>

  <!-- NAV KATEGORI -->
  <div class="text-center menu-nav mb-3">
  <?php foreach ($categories as $i => $c): ?>
    <button class="btn btn-outline-warning <?= $i==0?'active':'' ?>" data-index="<?= $i ?>">
      <?= htmlspecialchars($c['name']) ?>
    </button>
  <?php endforeach; ?>
  </div>

  <!-- SEARCH -->
  <div class="row justify-content-center mb-4">
    <div class="col-md-5">
      <input type="text" id="searchProduct" class="form-control text-center"
            placeholder="Cari produk berdasarkan nama...">
    </div>
  </div>

  <!-- CAROUSEL -->
  <div class="owl-carousel owl-theme" id="menuCarousel">

<?php foreach ($categories as $c): ?>
<div class="item">
  <h3 class="text-center mb-4"><?= htmlspecialchars($c['name']) ?></h3>

  <!-- 🔥 SCROLL INTERNAL -->
  <div class="menu-scroll">
    <div class="row justify-content-center">

    <?php if (!empty($products[$c['id']])): ?>
    <?php foreach ($products[$c['id']] as $p):
      $image = 'assets/images/no-image.png';
      if (!empty($p['image'])) {
        $path = 'admin/uploads/products/'.basename($p['image']);
        if (file_exists($path)) $image = $path;
      }
    ?>
      <div class="col-sm-6 col-lg-4 mb-4 product-item"
           data-name="<?= strtolower($p['name']) ?>">
        <div class="menu-card">
          <div class="img-box">
            <img src="<?= $image ?>">
          </div>
          <div class="p-3 text-center">
            <h5><?= htmlspecialchars($p['name']) ?></h5>
            <p><?= htmlspecialchars($p['description']) ?></p>
            <h6 class="text-warning">
              Rp <?= number_format($p['price_sell'],0,',','.') ?>
            </h6>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <div class="empty-category">
      <i class="fa fa-search fa-3x text-warning mb-2"></i>
      <h6>Produk tidak ditemukan</h6>
    </div>

    </div>
  </div>
</div>
<?php endforeach; ?>

</div>

<!-- ================= CORE JS (WAJIB URUT) ================= -->
<script src="js/jquery-3.4.1.min.js"></script>
<script src="js/bootstrap.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/js/jquery.nice-select.min.js"></script>

<!-- ================= MENU SCRIPT ================= -->
<script>
$(document).ready(function(){

  /* INIT OWL */
  const owl = $('#menuCarousel').owlCarousel({
    items: 1,
    dots: false,
    nav: false,
    smartSpeed: 600,
    autoHeight: false
  });

  /* NAV KATEGORI */
  $('.menu-nav .btn').on('click', function(){
    const index = $(this).data('index');

    owl.trigger('to.owl.carousel', [index, 400]);

    $('.menu-nav .btn').removeClass('active');
    $(this).addClass('active');
  });

  /* SEARCH PRODUK */
  $('#searchProduct').on('keyup', function(){
    let key = $(this).val().toLowerCase();

    $('#menuCarousel .item').each(function(){
      let found = false;

      $(this).find('.product-item').each(function(){
        if($(this).data('name').includes(key)){
          $(this).show();
          found = true;
        } else {
          $(this).hide();
        }
      });

      $(this).find('.empty-category').toggle(!found);
    });
  });

  /* STICKY HEADER */
  $(window).on('scroll', function(){
    $('.header_section').toggleClass(
      'sticky-active',
      $(this).scrollTop() > 50
    );
  });

});
</script>

<!-- custom js -->
<script src="js/custom.js"></script>

</body>
</html>