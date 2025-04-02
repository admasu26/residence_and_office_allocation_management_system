<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in</title>
    <link rel="icon" href="logo.png">
    <link rel="stylesheet" type="text/css" href="Start.css">
    <script src="new index.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700,800&display=swap" rel="stylesheet">
    
</head>
<body>

<nav>
    <input type="checkbox" id="check">
      <i class="fas-ba-bars"></i>
    
    <img src="logo.png" class="logo" alt="abrehot_logo">
    <ul class="ul">
      <li><a class="active" href="index.php">Home</a></li>
      <li><a href="start.php#about">About us</a></li>
     
      <li><a href="contact_us.php">Contact</a></li>
    </ul>
</nav>

<div class="content">
    <div class="login">
        <img id="my-image" src="a.jpg" alt="Image description" >
        <h1>Dear customer & Guest.</h1> 
        <p>"Experience seamless organization and efficiency like never before."</p>
        <button class="link-button" onclick="redirectToLoginPage()"><span></span>Sign in as user</button>
        <script>
    function redirectToLoginPage() {
        window.location.href = 'admin/Log.php';
    }
</script>
    </div>
</div>

<section id="about">
    <div class="about-us">
        <h2>About Us</h2><br><br>

        <p>
        Welcome to the Arba Minch Staff Residence and Office Allocation Management System!

At Arba Minch University, we understand the challenges staff members and administrators face when it comes to securing residences and office spaces. To streamline this process, we have developed a modern web-based platform designed to ensure fairness, efficiency, and transparency in resource allocation.

Gone are the days of manual paperwork and delays! Our system simplifies requests, automates allocation, and provides real-time updates, making the entire process more convenient for everyone involved. Whether you're applying for a staff residence or office space, our user-friendly interface ensures a smooth and hassle-free experience.

With a commitment to innovation and excellence, we aim to foster a more connected and well-managed university environment. Join us in transforming resource management at Arba Minch University—where efficiency meets transparency!.
        </p>
        <img src="admin/main.jpg">
    </div>
</section>

</body>
</html>
