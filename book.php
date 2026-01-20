<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<title>3MasKentir - Book</title>

<link rel="shortcut icon" href="images/favicon.png">

<!-- BOOTSTRAP -->
<link rel="stylesheet" href="css/bootstrap.css">

<!-- OWL CAROUSEL -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">

<!-- FONT AWESOME -->
<link rel="stylesheet" href="css/font-awesome.min.css">

<!-- TEMPLATE CSS -->
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/responsive.css">
</head>

<body class="sub_page">

<!-- ================= HEADER ================= -->
<div class="hero_area">
  <div class="bg-box">
    <img src="images/hero-pg.jpg" alt="">
  </div>

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
</div>

<!-- ================= BOOK SECTION ================= -->
<section class="book_section layout_padding">
<div class="container">

  <div class="heading_container">
    <h2>Mau Mengunjungi?</h2>
  </div>

  <div class="row">

    <!-- FORM -->
    <div class="col-md-6">
      <div class="form_container">
        <form id="bookForm">

          <input type="text" id="name" class="form-control mb-3" placeholder="Your Name" required>
          <input type="text" id="phone" class="form-control mb-3" placeholder="Phone Number" required>
          <input type="email" id="email" class="form-control mb-3" placeholder="Your Email">
          <input type="date" id="date" class="form-control mb-3" required>

          <div class="btn_box">
            <button type="submit">Book Now</button>
          </div>

        </form>
      </div>
    </div>

    <!-- MAP -->
    <div class="col-md-6">
      <div class="map_container">
              <div class="map_container">
        <a href="https://www.google.com/maps?q=-7.005145,110.438125" 
          target="_blank"
          style="display:block; border-radius:10px; overflow:hidden;">
          
          <iframe
            src="https://www.google.com/maps?q=-7.005145,110.438125&z=15&output=embed"
            width="100%"
            height="350"
            style="border:0;"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
          </iframe>
        </a>
      </div>
      </div>
    </div>

  </div>

</div>
</section>

<!-- ================= FOOTER ================= -->
<footer class="footer_section">
<div class="container text-center">
  <p>&copy; <?= date('Y'); ?> CHABABONSAI | All Rights Reserved</p>
</div>
</footer>

<!-- ================= JS ================= -->
<script src="js/jquery-3.4.1.min.js"></script>
<script src="js/bootstrap.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<script src="js/custom.js"></script>

<!-- GOOGLE MAP -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCh39n5U-4IoWpsVGUHWdqB6puEkhRLdmI&callback=myMap"></script>

<!-- ================= WA REDIRECT SCRIPT ================= -->
<script>
document.getElementById("bookForm").addEventListener("submit", function(e) {
  e.preventDefault();

  const name  = document.getElementById("name").value;
  const phone = document.getElementById("phone").value;
  const email = document.getElementById("email").value;
  const date  = document.getElementById("date").value;

  const message =
  "Halo Admin %0A%0A" +
  "Saya ingin mengunjungi 3MasKentir dan melakukan reservasi dengan detail sebagai berikut:%0A%0A" +
  "Nama: " + name + "%0A" +
  "No HP: " + phone + "%0A" +
  "Email: " + email + "%0A" +
  "Tanggal Kunjungan: " + date;


  const waNumber = "6288239468557";
  const waURL = "https://wa.me/" + waNumber + "?text=" + message;

  window.open(waURL, "_blank");
});
</script>

</body>
</html>
