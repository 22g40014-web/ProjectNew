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
?>




<!DOCTYPE html>
<html>

<head>

  <!-- Basic -->
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <!-- Mobile Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <!-- Site Metas -->
  <meta name="keywords" content="" />
  <meta name="description" content="" />
  <meta name="author" content="" />
  <link rel="shortcut icon" href="images/favicon.png" type="">

  <title> Projectss </title>

  <!-- bootstrap core css -->
  <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />

  <!--owl slider stylesheet -->
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" />
  <!-- nice select  -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/css/nice-select.min.css" integrity="sha512-CruCP+TD3yXzlvvijET8wV5WxxEh5H8P4cmz0RFbKK6FlZ2sYl3AEsKlLPHbniXKSrDdFewhbmBK5skbdsASbQ==" crossorigin="anonymous" />
  <!-- font awesome style -->
  <link href="css/font-awesome.min.css" rel="stylesheet" />

  <!-- Custom styles for this template -->
  <link href="css/style.css" rel="stylesheet" />
  <!-- responsive style -->
  <link href="css/responsive.css" rel="stylesheet" />

  
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
  width: 100%;
  aspect-ratio: 4 / 3;        /* rasio konsisten */
  overflow: hidden;
}

.menu-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;          /* isi penuh card */
  display: block;
}
/*.menu-card .img-box {
  height: 220px;
  overflow: hidden;
}

.menu-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
} */

.menu-card {
  cursor: pointer;
  transition: .3s ease;
}

.menu-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0,0,0,.15);
}

.empty-category {
  min-height: 250px;
  display: none;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  color: #999;
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
   SEARCH BAR
=============================== */
#searchProduct {
  border-radius: 30px;
  padding: 12px 20px;
  box-shadow: 0 4px 15px rgba(0,0,0,.1);
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

/* ===== FIX JARAK MENU KE SECTION BAWAH ===== */
.food_section {
    padding-bottom: 80px;
}

.about_section {
    margin-top: 80px;
    border-radius: 30px;
}

.footer_section .container-fluid {
  width: 100%;
  padding-left: 0;
  padding-right: 0;
}

.footer_section {
  max-width: 100%;
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

#client_section{
  margin-top: 80px;
}


</style>
</head>

<body>

  <div class="hero_area">
    <div class="bg-box">
      <img src="images/tampilan.jpeg" alt="">
    </div>
    <!-- header section strats -->
    <header class="header_section">
      <div class="container">
        <nav class="navbar navbar-expand-lg custom_nav-container ">
          <a class="navbar-brand" href="index.php">
            <span>
              CHABA BONSAI
            </span>
          </a>

          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class=""> </span>
          </button>

          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav  mx-auto ">
              <li class="nav-item active">
                <a class="nav-link" href="index.php">Home <span class="sr-only">(current)</span></a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="menu.php">Menu</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="about.php">About</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="book.php">Book Table</a>
              </li>
            </ul>
            <div class="user_option">
              </a>
              <a href="menu.php?order=1" class="order_online">
                Order Online
              </a>
            </div>
          </div>
        </nav>
      </div>
    </header>
    <!-- end header section -->
   <!-- slider section -->
<section class="slider_section ">
  <div id="customCarousel1" class="carousel slide" data-ride="carousel">
    <div class="carousel-inner">

      <div class="carousel-item active">
        <div class="container">
          <div class="row">
            <div class="col-md-7 col-lg-6">
              <div class="detail-box">
                <h1>Katalog Bonsai</h1>
                <p>
                  Koleksi bonsai pilihan yang menghadirkan keindahan alami dalam setiap detailnya, dirancang untuk membawa harmoni, ketenangan, dan sentuhan estetika alami ke setiap ruang, menciptakan suasana yang lebih hidup, seimbang, dan menenangkan bagi siapa pun yang menikmatinya.
                  </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="carousel-item">
        <div class="container">
          <div class="row">
            <div class="col-md-7 col-lg-6">
              <div class="detail-box">
                <h1>Katalog Bonsai</h1>
                <p>
                  Setiap bonsai dalam koleksi kami dipilih dengan penuh perhatian untuk menghadirkan keindahan alami yang autentik, memancarkan harmoni dan ketenangan, serta memberikan sentuhan estetika yang lembut dan menenangkan di setiap ruang tempat ia tumbuh dan dihargai.
                 </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="carousel-item">
        <div class="container">
          <div class="row">
            <div class="col-md-7 col-lg-6">
              <div class="detail-box">
                <h1>Katalog Bonsai</h1>
                <p>
                  Koleksi bonsai eksklusif yang memadukan keindahan alami, seni, dan filosofi keseimbangan hidup, menghadirkan nuansa harmoni dan ketenangan yang memperindah setiap ruang dengan karakter yang anggun dan berkelas.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

        <div class="container">
          <ol class="carousel-indicators">
            <li data-target="#customCarousel1" data-slide-to="0" class="active"></li>
            <li data-target="#customCarousel1" data-slide-to="1"></li>
            <li data-target="#customCarousel1" data-slide-to="2"></li>
          </ol>
        </div>
      </div>

    </section>
    <!-- end slider section -->
  </div>

  <!-- offer section -->
  <!-- OFFER SECTION -->
  <!-- OFFER SECTION -->
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
  
  // WA PROMO (JANGAN UBAH LOGIC)
    $imageUrl = "https://yourdomain.com/uploads/promos/" . $p['image'];

    $waText = urlencode(
      "Halo Admin 👋\n\n" .
      "Saya tertarik dengan tanaman berikut:\n\n" .
      "*Nama Produk:*\n" .
      $p['product_name'] . "\n\n" .
      "*Harga Normal:*\n" .
      "Rp" . number_format($priceNormal,0,',','.') . "\n\n" .
      "*Harga Promo:*\n" .
      "Rp" . number_format($priceAfter,0,',','.') . "\n\n" .
      "Mohon informasi selanjutnya 🙏"
      //"Gambar:\n" . $imageUrl
    );


  $waLink = "https://wa.me/6288239468557?text=" . $waText;
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




  >
  <div class="offer_container product-modal-trigger-promo" 
        data-name="<?= htmlspecialchars($p['product_name']) ?>"
        data-description="<?= htmlspecialchars($p['title']) ?>"
       data-image="<?= htmlspecialchars($p['image']) ?>"
       data-price="<?= htmlspecialchars($p['price_normal']) ?>">
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
  
  // WA PROMO (JANGAN UBAH LOGIC)
    $imageUrl = "https://yourdomain.com/uploads/promos/" . $p['image'];

    $waText = urlencode(
      "Halo Admin 👋\n\n" .
      "Saya tertarik dengan tanaman berikut:\n\n" .
      "*Nama Produk:*\n" .
      $p['product_name'] . "\n\n" .
      "*Harga Normal:*\n" .
      "Rp" . number_format($priceNormal,0,',','.') . "\n\n" .
      "*Harga Promo:*\n" .
      "Rp" . number_format($priceAfter,0,',','.') . "\n\n" .
      "Mohon informasi selanjutnya 🙏"
      //"Gambar:\n" . $imageUrl
    );


  $waLink = "https://wa.me/6288239468557?text=" . $waText;
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
            data-price="Rp <?= number_format($p['price_sell'],0,',','.') ?>"
            data-price-raw="<?= $p['price_sell'] ?>">
          <div class="img-box">
            <img src="<?= $image ?>">
          </div>
          <div class="p-3 text-center">
            <h5><?= htmlspecialchars($p['name']) ?></h5>
            <!-- <p><?= htmlspecialchars($p['description']) ?></p> -->
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

  <!-- about section -->

  <section class="about_section layout_padding">
    <div class="container  ">

      <div class="row">
        <div class="col-md-6 ">
          <div class="img-box">
            <img src="images/tampilan.jpeg" alt="">
          </div>
        </div>
        <div class="col-md-6">
          <div class="detail-box">
            <div class="heading_container">
              <h2>
                CHABA BONSAI
              </h2>
            </div>
            <p>
              Menjaga harmoni antara manusia dan alam melalui seni bonsai. Temukan inspirasi dan keindahan alami dari koleksi kami.
            </p>
            <a href="">
              Read More
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- end about section -->


  <!-- client section -->

  <section class="client_section layout_padding-bottom" id="client_section">
    <div class="container">
      <div class="heading_container heading_center psudo_white_primary mb_45">
        <h2>
          What Says Our Customers
        </h2>
      </div>
      <div class="carousel-wrap row ">
        <div class="owl-carousel client_owl-carousel">
          <div class="item">
            <div class="box">
              <div class="detail-box">
                <p>
                  Bonsai nya enak dipandang, dimakan juga enak
                </p>
                <h6>
                  Matthew Sebastian
                </h6>
                <p>
                  22N30009
                </p>
              </div>
              <div class="img-box">
                <img src="images/clientanonymous.jpg" alt="" class="box-img">
              </div>
            </div>
          </div>
          <div class="item">
            <div class="box">
              <div class="detail-box">
                <p>
                  Responnya sangat cepat 
                </p>
                <h6>
                  Fadhil
                </h6>
                <p>
                  Firsty
                </p>
              </div>
              <div class="img-box">
                <img src="images/clientanonymous.jpg" alt="" class="box-img">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- end client section -->

  <!-- footer section -->
  <footer class="footer_section">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-4 footer-col">
          <div class="footer_contact">
            <h4>
              Contact Us
            </h4>
            <div class="contact_link_box">
              <a href="https://maps.app.goo.gl/uhVD6XXpNy6FjFGi7">
                <i class="fa fa-map-marker" aria-hidden="true"><span> Kunjungi Kami</span></i>
              </a>
              <a href="">
                <i class="fa fa-phone" aria-hidden="true"></i>
                <span>
                  Call +62 878-6555-5340
                </span>
              </a>
              <a href="">
                <i class="fa fa-envelope" aria-hidden="true"></i>
                <span>
                  demo@gmail.com
                </span>
              </a>
            </div>
          </div>
        </div>
        <div class="col-md-4 footer-col">
          <div class="footer_detail">
            <a href="" class="footer-logo">
              CHABA BONSAI
            </a>
            <p>
              Udah Beli Bonsai dari Chaba Bonsai belum masehhh
            </p>
          </div>
        </div>
        <div class="col-md-4 footer-col">
          <h4>
            Opening Hours
          </h4>
          <p>
            Senin - Jumat
          </p>
          <p>
            17.00 - 20.00
          </p>
          <p>
            Sabtu - Minggu
          </p>
          <p>
            7.00 - 20.00
          </p>
        </div>
      </div>
      <div class="footer-info">
        <p>
          &copy; <span id="displayYear"></span> All Rights Reserved By
          <a href="">CHABABONSAI</a>
        </p>
      </div>
    </div>
  </footer>
  <!-- footer section -->

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

            <!-- TOMBOL ORDER WA (TAMBAHKAN) -->
            <a href="#"
              id="waOrderBtn"
              target="_blank"
              class="btn btn-success w-100 mt-3">
              Order via WhatsApp
            </a>
          </div>
        </div>
      </div>


    </div>
  </div>
</div>

<div class="modal fade" id="productModalPromo" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="modalProductNamePromo"></h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-md-6 text-center">
            <img id="modalProductImagePromo"
                src=""
                class="img-fluid rounded"
                alt="">
          </div>

          <div class="col-md-6">
            <h6 class="text-warning" id="modalProductPricePromo"></h6>
            <p id="modalProductDescriptionPromo"></p>

            <!-- TOMBOL ORDER WA (TAMBAHKAN) -->
            <a href="#"
              id="waOrderBtn"
              target="_blank"
              class="btn btn-success w-100 mt-3">
              Order via WhatsApp
            </a>
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

<!-- ================= MODAL + WA ORDER SCRIPT ================= -->
<script>
$(document).on('click', '.product-modal-trigger', function(){

  let name        = $(this).data('name');
  let description = $(this).data('description');
  let image       = $(this).data('image');
  let price       = $(this).data('price');

  /* ===== ISI DATA KE MODAL ===== */
  $('#modalProductName').text(name);
  $('#modalProductDescription').text(description);
  $('#modalProductImage').attr('src', image);
  $('#modalProductPrice').text(price);

  /* ===== FORMAT PESAN WHATSAPP ===== */
  let waText =
    "Halo Admin,%0A" +
    "Saya tertarik dengan produk berikut:%0A%0A" +
    "Nama: " + name + "%0A" +
    "Harga: " + price + "%0A%0A" +
    "Mohon informasi selanjutnya.";

  let waNumber = "6288239468557"; // GANTI NOMOR ADMIN

  /* ===== SET LINK KE TOMBOL WA ===== */
  $('#waOrderBtn').attr(
    'href',
    'https://wa.me/' + waNumber + '?text=' + waText
  );

  /* ===== TAMPILKAN MODAL ===== */
  $('#productModal').modal('show');
});
</script>


<!-- modal promo -->

<script>
$(document).on('click', '.product-modal-trigger-promo', function(){

  $('#modalProductNamePromo').text($(this).data('product_name'));
  $('#modalProductDescriptionPromo').text($(this).data('title'));
  $('#modalProductImagePromo').attr('src', $(this).data('image'));
  $('#modalProductPricePromo').text($(this).data('price_normal'));

  $('#productModalPromo').modal('show');
});
</script>

<script>
$(document).on('click', '.product-modal-trigger-promo', function(){

  let product_name        = $(this).data('name');
  let description = $(this).data('description');
  let image       = $(this).data('image');
  let price       = $(this).data('price_normal');

  $('#modalProductNamePromo').text(product_name);
  $('#modalProductDescriptionPromo').text(description);
  $('#modalProductImagePromo').attr('src', image);
  $('#modalProductPricePromo').text(price);

  let waText =
    "Halo Admin,%0A" +
    "Saya tertarik dengan produk berikut:%0A%0A" +
    "Nama: " + name + "%0A" +
    "Harga: " + price + "%0A%0A" +
    "Mohon informasi selanjutnya.";

  let waNumber = "6288239468557";

  $('#waOrderBtn').attr(
    'href',
    'https://wa.me/' + waNumber + '?text=' + waText
  );

  $('#productModalPromo').modal('show');
});
</script>


<!-- custom js -->
<script src="js/custom.js"></script>




</body>

</html>
