<?php


?>
<!Doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEW WORLD-ANASAYFA </title>
    <link rel="stylesheet" href="../public/css/anasayfa.css">
    <link rel='shortcut icon' type='image/x-icon' href='../public/assets/images/favicons/favicon.png' />
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" /> <!-- AOS, sayfayı reload ettiğimizde smooth animasyon yaptı -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>

<body>

<img class="image-gradient" src="../public/assets/images/gradient.png" alt="gradient">
<div class="layer-blur"></div> 

<div class="container">
    <header>
      <h1 data-aos="fade-down" data-aos-duration="1500" class="logo">NEW WORLD</h1>

     <a data-aos="fade-down" data-aos-duration="1500" class="btn-signing" href="log-header.php">
      Giriş &gt;
    </a>

    </header>

<main>
    <div class="content">
      <div data-aos="fade-zoom-in"
     data-aos-easing="ease-in-back"
     data-aos-delay="300"
     data-aos-offset="0" data-aos-duration="1500" class="tag-box">
        <div class="tag"> NEW WORLDYA HOŞGELDİNİZ &wedbar;</div>
      </div>

      <h1   data-aos="fade-zoom-in"
     data-aos-easing="ease-in-back"
     data-aos-delay="300"
     data-aos-offset="0" data-aos-duration="2000">KENDİ HİKAYENİ<br>YAZMAYA BAŞLA</h1>
      <p data-aos="fade-zoom-in"
     data-aos-easing="ease-in-back"
     data-aos-delay="300"
     data-aos-offset="0" data-aos-duration="2500"class="description">
        Herkesin kendisine ait yeni bir dünyası var,peki bu dünyayı paylaşmaya ne dersin?
      </p>

      <div   data-aos="fade-zoom-in"
     data-aos-easing="ease-in-back"
     data-aos-delay="300"
     data-aos-offset="0" data-aos-duration="3000"class="buttons">
        <a href="" class="btn-get-started">Kodlar &gt;</a>
        <a href="../Login/signing.php" class="btn-signing-main">Kayıt Ol &gt;</a>
      </div>

    </div>
   </main>
   

   <spline-viewer  data-aos="fade-zoom-in"
     data-aos-easing="ease-in-back"
     data-aos-delay="300"
     data-aos-offset="0" data-aos-duration="3000" class="robot-3d" url="https://prod.spline.design/vmXOPx31p2oqCntX/scene.splinecode"></spline-viewer>

    </div>

    <script type="module" src="https://unpkg.com/@splinetool/viewer@1.9.94/build/spline-viewer.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
  <script>
    AOS.init();
  </script>

</body>

</html>