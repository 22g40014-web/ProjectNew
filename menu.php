<?php
require_once 'admin/config/db.php';


/* ======================
   AMBIL DATA PROMO
====================== */
$promos = $conn->query("
    SELECT *
    FROM promos
    WHERE is_active = 1
    ORDER BY created_at DESC
    LIMIT 20
");

if (!$promos) {
    die("Query promo error: " . $conn->error);}


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

.menu-card {
  cursor: pointer;
  transition: .3s ease;
}

.menu-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0,0,0,.15);
}

/* ===============================
   PROMO / DISKON SCROLL
   =============================== */
.promo-scroll {
  max-height: 520px;      /* tinggi tetap */
  overflow-y: auto;
  overflow-x: hidden;
  padding-right: 10px;
}

/* Scrollbar promo */
.promo-scroll::-webkit-scrollbar {
  width: 6px;
}
.promo-scroll::-webkit-scrollbar-thumb {
  background: #ffbe33;
  border-radius: 10px;
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
  <section class="offer_section layout_padding-bottom">
  <div class="offer_container">
    <div class="container">
      <div class="promo-scroll">
        <div class="row">


<?php while ($p = $promos->fetch_assoc()): ?>

<?php
  $priceNormal = (float) $p['price_normal'];

  if ($p['discount_type'] === 'percent') {
      $discountPercent = (float) $p['discount_value'];
      $discountAmount  = $priceNormal * ($discountPercent / 100);
  } else {
      $discountAmount  = (float) $p['discount_value'];
      $discountPercent = round(($discountAmount / $priceNormal) * 100);
  }

  $priceAfter = $priceNormal - $discountAmount;

  // WA
  $imageUrl = "https://yourdomain.com/uploads/promos/" . $p['image'];

  $waText = urlencode(
    "Halo Admin,\n" .
    "Saya tertarik dengan tanaman ini:\n" .
    $p['product_name'] . "\n" .
    "Harga Normal: Rp" . number_format($priceNormal,0,',','.') . "\n" .
    "Harga Promo: Rp" . number_format($priceAfter,0,',','.') . "\n" .
    "Saya ingin bernego dengan anda.\n\n" .
    "Gambar:\n" . $imageUrl
  );

  $waLink = "https://wa.me/628XXXXXXXXX?text=" . $waText;
?>

<div class="col-md-6 mb-4">
  <div class="box">
    <div class="img-box">
      <img src="uploads/promos/<?= htmlspecialchars($p['image']) ?>"
           alt="<?= htmlspecialchars($p['product_name']) ?>">
    </div>

    <div class="detail-box">
      <h5><?= htmlspecialchars($p['product_name']) ?></h5>

      <h6>
        <span style="text-decoration:line-through; color:#aaa;">
          Rp<?= number_format($priceNormal,0,',','.') ?>
        </span>
        <br>

        <strong style="color:#ffc107;">
          Rp<?= number_format($priceAfter,0,',','.') ?>
        </strong>
        <small>(<?= $discountPercent ?>% OFF)</small>
      </h6>

      <a href="<?= $waLink ?>" target="_blank">
        Order Now
      </a>
    </div>
  </div>
</div>

<?php endwhile; ?>

      </div>
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
        <div class="menu-card product-modal-trigger"
     data-name="<?= htmlspecialchars($p['name']) ?>"
     data-description="<?= htmlspecialchars($p['description']) ?>"
     data-image="<?= $image ?>"
     data-price="Rp <?= number_format($p['price_sell'],0,',','.') ?>">
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

<!-- ================= MODAL PRODUK ================= -->
<div class="modal fade" id="productModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="modalProductName"></h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-md-6 text-center">
            <img id="modalProductImage"
                 src=""
                 class="img-fluid rounded"
                 alt="">
          </div>
          <div class="col-md-6">
            <h6 class="text-warning" id="modalProductPrice"></h6>
            <p id="modalProductDescription"></p>
          </div>
        </div>
      </div>

    </div>
  </div>
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

<script>
$(document).on('click', '.product-modal-trigger', function(){

  $('#modalProductName').text($(this).data('name'));
  $('#modalProductDescription').text($(this).data('description'));
  $('#modalProductImage').attr('src', $(this).data('image'));
  $('#modalProductPrice').text($(this).data('price'));

  $('#productModal').modal('show');
});
</script>


<!-- custom js -->
<script src="js/custom.js"></script>

</body>
</html>