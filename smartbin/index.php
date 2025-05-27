<?php
session_start();
?>
<!DOCTYPE html>
<!--Coding By Abellon - abellonjesmar@gmail.com-->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Rewards</title>
    <!--Linking Font Awesome for icons-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awsome/6.6.0/css/all.min.css">
    <!--Linking Swiper CSS-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 80px;
            position: relative;
            padding: 0 3rem;
        }
        .nav-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            position: absolute;
            left: 3rem;
            top: 0;
        }
        .logo-text {
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            line-height: 80px;
            font-size: 1.5rem;
        }
        .nav-menu {
            display: flex;
            align-items: center;
            margin: 0;
            padding: 0 auto;
            height: 100%;
            margin-left: 180px;
            gap: 2.5rem;
        }
        .nav-item {
            display: flex;
            align-items: center;
            height: 100%;
            white-space: nowrap;
        }
        .nav-link {
            display: flex;
            align-items: center;
            height: 100%;
            color: #fff;
            text-decoration: none;
            padding: 0 2rem;
            font-size: 0.8rem;
            transition: color 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .nav-link:hover {
            color: #ccc;
        }
        .user-welcome {
            color: #fff;
            margin-right: 1.5rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }
        .logout-btn {
            color: #fff;
            text-decoration: none;
            padding: 0.5rem 1.2rem;
            border: 1px solid #fff;
            border-radius: 5px;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        .logout-btn:hover {
            background: #fff;
            color: #000;
        }
        .user-section {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            height: 100%;
            margin-left: 2rem;
        }
        #menu-open-button, #menu-close-button {
            display: none;
        }
    </style>
</head>
<body>
<!--Header / Navbar -->
<header>
      <nav class="navbar section-content">
         <a href="#" class="nav-logo">
            <h2 class="logo-text">SMART BIN</h2>
         </a>
         <ul class="nav-menu">
            <button id="menu-close-button" class="fas fa-times"></button>

            <li class="nav-item">
                <a href="#home" class="nav-link">HOME</a>
            </li>
            <li class="nav-item">
                <a href="#about" class="nav-link">ABOUT US</a>
            </li>
            <li class="nav-item">
                <a href="#services" class="nav-link">SERVICES</a>
            </li>
            <li class="nav-item">
                <a href="#testimonials" class="nav-link">TESTIMONIALS</a>
            </li>
            <li class="nav-item">
                <a href="#gallery" class="nav-link">GALLERY</a>
            </li>
            <li class="nav-item">
                <a href="#contact" class="nav-link">CONTACT</a>
            </li>

            <li class="nav-item">
                <a href="admin.php" class="nav-link">Dashboard</a>
            </li>

            <?php if(isset($_SESSION["user"])): ?>
                <li class="nav-item user-section">
                    <span class="user-welcome">Welcome, <?php echo htmlspecialchars($_SESSION["user_name"]); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a href="login.php" class="nav-link">Login</a>
                </li>
            <?php endif; ?>
         </ul>

         <button id="menu-open-button" class="fas fa-bars"></button>
        </nav>
   </header>

   <main>
    <!-- Hero section-->
    <section class="hero-section">
        <div class="section-content">
            <div class="hero-details">
                <h2 class="title">Eco Rewards</h2>
                <h3 class="subtitle">Smart bin with rewards points!</h3>
                <p class="description">Welcome to Eco rewards where you can connect on the internet and earn exciting rewards points!</p>
           
                <?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Home Page</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <h1>Welcome to the Download Page</h1>
    <p>If you're logged in, you can download the file below.</p>

    <a href="download.php" id="download" class="btn btn-success">Download</a>

  </div>
  </body>
</html>     

           </div>
            <div class="hero-image-wrapper">
                <img src="./images/background home.png" alt="Hero" class="hero-image">
            </div>
        </div>
    </section>
   
    <!--About section-->
  <section class="about section" id="about">
    <div class="section-content">
      <div class="about-image-wrapper">
        <img src="./images/about us 1.jpg" alt="About" class="about-image">
      </div>
      <div class="about-details">
      <h2 class="section-title">About Us</h2>
      <p class="text">At Eco Rewards, we believe that small actions lead to big changes.
         Our mission is to inspire and reward individuals and businesses for making
          eco-friendly choices that help protect our planet
        </p>
        <div class="social-link-list">
          <a href="#" class="social-link"><i class="fa-brands
             fa-facebook"></i></a>
             <a href="#" class="social-link"><i class="fa-brands
              fa-Instagram"></i></a>
              <a href="#" class="social-link"><i class="fa-brands
                fa-x-twitter"></i></a>
        </div>
      </div>
    </div>
  </section>

  <!--Services section-->
  <section class="services-section" id="services">
    <h2 class="section-title"> OUR SERVICES</h2>
    <Div class="section-content">
      <ul class="services-list">
        <li class="services-item">
          <img src="./images/promo_image-removebg-preview.png" alt="" class="services-image">
          <h3 class="name"></h3>
          <p class="text">Get free 3% Discount in every items at canteen.
          </p>
        </li>
        <li class="services-item">
          <img src="./images/promo_image-removebg-preview.png" alt="" class="services-image">
          <h3 class="name"></h3>
          <p class="text">1 bottle - 2mins free wifi
          </p>
        </li>
        <li class="services-item">
          <img src="./images/promo_image-removebg-preview.png" alt="" class="services-image">
          <h3 class="name"></h3>
          <p class="text">20 bottles - 5 pesos reward points
          </p>
        </li>
      </ul>
    </Div>
  </section>

 <!--Testimonials section-->
  <section class="testimonials-section" id="testimonials">
    <h2 class="section-title">Testimonials</h2>
    <div class="section-content">
      <div class="slider-container swiper">
        <div class="slider-wrapper">
          <ul class="testimonials-list swiper-wrapper">
            <li class="testimonial swiper slide">
              <img src="./images/q.jpg" alt="user" class="user-image">
              <h3 class="name">Michelle Davy Quiapo</h3>
              <i class="feedback"> "very convenient and usable software!"</i>
            </li><br>
            <li class="testimonial swiper slide">
              <img src="./images/p.jpg" alt="user" class="user-image">
              <h3 class="name">Prince Francis Bayona</h3>
              <i class="feedback"> "first time i use this software i love this!"</i>
            </li><br>
            <li class="testimonial swiper slide">
              <img src="./images/e.jpg" alt="user" class="user-image">
              <h3 class="name">Ezekiel John Layug</h3>
              <i class="feedback"> "i saw this before so i try it the best among the rest!"</i>
            </li>
            <li class="testimonial swiper slide">
              <img src="./images/r.jpg" alt="user" class="user-image">
              <h3 class="name">Reyniel Labitoria</h3>
              <i class="feedback"> "this software help me a lot on how to use it effectively!"</i>
            </li>
          </ul>
         
          <div class="swiper-pagination"></div>
          <div class="swiper-slide-prev"></div>
          <div class="swiper-slide-button-swiper-next"></div>
        </div>
      </div>
    </div>

  </section>

   <!--Gallery section-->
  <section class="gallery-section" id="gallery">
    <h2 class="section-title">GALLERY</h2>
    <div class="section-content">
      <ul class="gallery-list">
        <li class="gallery-item">
          <img src="./images/g 2.jpg" alt="" class="gallery-image">
        </li>
        <li class="gallery-item">
          <img src="./images/G 1.jpg" alt="" class="gallery-image">
        </li>
        <li class="gallery-item">
          <img src="./images/g 3.jpg" alt="" class="gallery-image">
        </li>
        <li class="gallery-item">
          <img src="./images/g4.jpg" alt="" class="gallery-image">
        </li>
        <li class="gallery-item">
          <img src="./images/g5.jpg" alt="" class="gallery-image">
        </li>
        <li class="gallery-item">
          <img src="./images/g6.avif" alt="" class="gallery-image">
        </li>
      </ul>
    </div>
  </section>

  <!--contact section-->
  <section class="contact-section" id="contact">
    <h2 class="section-title">CONTACT US</h2>
    <div class="section-content">
      <ul class="contact-info-list">
        <li class="contact-info">
          <i class="fa-solid fa-location-crosshairs"></i>
          <p>Cansadan-Tubudan San Jose Antique,5700</p>
        </li>
        <li class="contact-info">
          <i class="fa-regular fa-envelope"></i>
          <p>abellonjesmar@gmail.com</p>
        </li>
        <li class="contact-info">
          <i class="fa-solid fa-phone"></i>
          <p>09267856443</p>
        </li>
        <li class="contact-info">
          <i class="fa-regular fa-clock"></i>
          <p>Monday-Friday: 9:00 AM-5:00 PM</p>
        </li>
        <li class="contact-info">
          <i class="fa-regular fa-clock"></i>
          <p>Sunday: Closed</p>
        </li>
        <li class="contact-info">
          <i class="fa-solid fa-globe"></i>
          <p>www.mywebsite.com</p>
        </li>
      </ul>

      

         <a href="php/student_db.php">
          <button class="student-info-btn">
            <span class="student-name">STUDENT INFORMATION</span>
          </button>
         </a>
 
        
 
  <br><br>

  <!--footer section-->
  <footer class="footer-section">
    <div class="section-content">
      <p class="copyright-text"> 2025 Eco Rewards</p>

      <div class="social-link-list">
             <a href="#" class="social-link"><i class="fa-brands
             fa-facebook"></i></a>
             <a href="#" class="social-link"><i class="fa-brands
              fa-Instagram"></i></a>
              <a href="#" class="social-link"><i class="fa-brands
                fa-x-twitter"></i></a>
        </div>

        <p class="policy-text"></p>
        <a href="#" class="policy-link">Privacy Policy</a>
        <span class="separator">.</span>
        <a href="#" class="policy-link">Refund Policy</a>
      </div>
    </div>
  </footer>
  </main>
  
  
  <!--Linking Swiper script-->
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css.js"></script>

  <!--Linking custom script--> 
  <script src="script.js"></script>
</body>
</html>







