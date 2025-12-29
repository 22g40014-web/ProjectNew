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
  height: 220px;
  overflow: hidden;
}

.menu-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
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
}

.footer_section .container-fluid {
  width: 100%;
  padding-left: 0;
  padding-right: 0;
}

.footer_section {
  max-width: 100%;
}

</style>
</head>

<body>

  <div class="hero_area">
    <div class="bg-box">
      <img src="images/hero-pg.jpg" alt="">
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
              <a href="javascript:void(0)" class="user_link" onclick="openLogin()">
                <i class="fa fa-user" aria-hidden="true"></i>
              </a>
              <a class="cart_link" href="#">
              </a>
              <form class="form-inline">
                <button class="btn  my-2 my-sm-0 nav_search-btn" type="submit">
                  <i class="fa fa-search" aria-hidden="true"></i>
                </button>
              </form>
              <a href="" class="order_online">
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
            <div class="container ">
              <div class="row">
                <div class="col-md-7 col-lg-6 ">
                  <div class="detail-box">
                    <h1>
                      Katalog Bonsai
                    </h1>
                    <p>
                      “Temukan keindahan alami dalam setiap lekuk ranting dan helai daun.
                        Katalog Bonsai menghadirkan koleksi terbaik untuk menemani keseharian Anda dengan harmoni dan ketenangan.”
                    <div class="btn-box">
                      <a href="" class="btn1">
                        Order Now
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="carousel-item ">
            <div class="container ">
              <div class="row">
                <div class="col-md-7 col-lg-6 ">
                  <div class="detail-box">
                    <h1>
                      Katalog Bonsai
                    </h1>
                    <p>
                      “Temukan keindahan alami dalam setiap lekuk ranting dan helai daun.
                        Katalog Bonsai menghadirkan koleksi terbaik untuk menemani keseharian Anda dengan harmoni dan ketenangan.”
                    </p>
                    <div class="btn-box">
                      <a href="" class="btn1">
                        Order Now
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="carousel-item">
            <div class="container ">
              <div class="row">
                <div class="col-md-7 col-lg-6 ">
                  <div class="detail-box">
                    <h1>
                      Katalog Bonsai
                    </h1>
                    <p>
                      “Temukan keindahan alami dalam setiap lekuk ranting dan helai daun.
                        Katalog Bonsai menghadirkan koleksi terbaik untuk menemani keseharian Anda dengan harmoni dan ketenangan.”
                    </p>
                    <div class="btn-box">
                      <a href="" class="btn1">
                        Order Now
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
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
<section class="offer_section layout_padding-bottom">
  <div class="offer_container">
    <div class="container">
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

  <!-- about section -->

  <section class="about_section layout_padding">
    <div class="container  ">

      <div class="row">
        <div class="col-md-6 ">
          <div class="img-box">
            <img src="images/about-img.jpg" alt="">
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

  <!-- book section -->
  <section class="book_section layout_padding">
    <div class="container">
      <div class="heading_container">
        <h2>
          Book A Table
        </h2>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="form_container">
            <form action="">
              <div>
                <input type="text" class="form-control" placeholder="Your Name" />
              </div>
              <div>
                <input type="text" class="form-control" placeholder="Phone Number" />
              </div>
              <div>
                <input type="email" class="form-control" placeholder="Your Email" />
              </div>
              <div>
                <select class="form-control nice-select wide">
                  <option value="" disabled selected>
                    How many persons?
                  </option>
                  <option value="">
                    2
                  </option>
                  <option value="">
                    3
                  </option>
                  <option value="">
                    4
                  </option>
                  <option value="">
                    5
                  </option>
                </select>
              </div>
              <div>
                <input type="date" class="form-control">
              </div>
              <div class="btn_box">
                <button>
                  Book Now
                </button>
              </div>
            </form>
          </div>
        </div>
        <div class="col-md-6">
          <div class="map_container ">
            <div id="googleMap"></div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- end book section -->

  <!-- client section -->

  <section class="client_section layout_padding-bottom">
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
              <a href="">
                <i class="fa fa-map-marker" aria-hidden="true"></i>
                <span>
                  Location
                </span>
              </a>
              <a href="">
                <i class="fa fa-phone" aria-hidden="true"></i>
                <span>
                  Call +62123456789
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
            <div class="footer_social">
              <a href="">
                <i class="fa fa-facebook" aria-hidden="true"></i>
              </a>
              <a href="">
                <i class="fa fa-twitter" aria-hidden="true"></i>
              </a>
              <a href="">
                <i class="fa fa-linkedin" aria-hidden="true"></i>
              </a>
              <a href="">
                <i class="fa fa-instagram" aria-hidden="true"></i>
              </a>
              <a href="">
                <i class="fa fa-pinterest" aria-hidden="true"></i>
              </a>
            </div>
          </div>
        </div>
        <div class="col-md-4 footer-col">
          <h4>
            Opening Hours
          </h4>
          <p>
            Everyday
          </p>
          <p>
            10.00 Am -10.00 Pm
          </p>
        </div>
      </div>
      <div class="footer-info">
        <p>
          &copy; <span id="displayYear"></span> All Rights Reserved By
          <a href="https://html.design/">Kelompok 3NewsKentir</a>
        </p>
      </div>
    </div>
  </footer>
  <!-- footer section -->

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
