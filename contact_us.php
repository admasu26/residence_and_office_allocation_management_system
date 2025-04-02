<?php
session_start();


if (isset($_SESSION['message'])) {
    echo $_SESSION['message'];
    unset($_SESSION['message']); 
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Contact Us</title>
  <link rel="stylesheet" href="contact_us.css">
  <style>    body {
        margin: 0;
        padding: 0;
        font-family: 'Arial', sans-serif;
        background-color: #f5f5f5;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
    }

    nav {
        background: transparent;
        height: 80px;
        width: 100%;
        position: fixed;
        top: 0;
        z-index: 1000;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    label.logo {
        color: #fff;
        font-size: 30px;
        font-weight: bold;
        padding-left: 20px;
    }

    nav ul {
        display: flex;
        list-style: none;
        margin-right: 20px;
    }

    nav ul li {
        margin: 0 10px;
    }

    nav ul li a {
        color: #000;
        font-size: 17px;
        text-decoration: none;
        padding: 7px 13px;
        border-radius: 3px;
    }

    a:hover {
        color: blue;
        font-weight: 100;
        background-color: #fff;
        transition: 0.5s;
        box-shadow: 1px 1px 1px 1px rgba(2, 2, 92, 0.354);
    }

    #check {
        display: none;
    }

    @media (max-width: 1070px) {
        label.logo {
            padding: 15px;
        }

        #check {
            display: block;
            font-size: 30px;
            color: #fff;
            line-height: 80px;
            cursor: pointer;
            padding-right: 20px;
        }

        nav ul {
            display: flex;
            flex-direction: column;
            position: fixed;
            width: 100%;
            background: #fff;
            top: 80px;
            left: -100%;
            text-align: center;
            transition: all 0.5s;
            padding-top: 10px;
        }

        nav ul li {
            display: block;
            margin: 20px 0;
            line-height: 30px;
        }

        nav ul li a {
            font-size: 20px;
        }

        a:hover {
            background: none;
            color: blue;
        }

        #check:checked ~ ul {
            left: 0;
        }
    }

    .container {
      width: 50%;
      margin: 0;
      margin-top: 30px;
      padding: 50px;
      background-color: #ffffff;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    h1 {
      color: #333333;
      text-align: center;
    }

    .contact-form {
      margin-top: 30px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      font-weight: bold;
      margin-bottom: 5px;
    }

    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #dddddd;
      border-radius: 5px;
      font-size: 16px;
    }

    .form-group textarea {
      height: 120px;
    }

    .form-group button {
      background-color: #333333;
      color: #ffffff;
      border: none;
      padding: 12px 20px;
      border-radius: 5px;
      font-size: 16px;
      cursor: pointer;
    }

    .form-group button:hover {
      background-color: #555555;
    }
</style>
</head>
<body>
    <nav>
        <input type="checkbox" id="check">
        <label class="logo">AMU<i class="fa-ba-braille" aria-hidden="true"></i>SRAMS</label>
        <ul class="ul">
        <ul class="ul">
          <li><a class="active" href="index.php">Home</a></li>
          <li><a href="start.php#about">About us</a></li>
          
          <li><a href="#">Contact</a></li>
        </ul>
        </ul>
    </nav>
  <div class="container">
    <h1>Contact Us</h1>
    <form class="contact-form" method="post" action="contact.php">
      <div class="form-group">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
      </div>
      <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
      </div>
      <div class="form-group">
        <label for="message">Message:</label>
        <textarea id="message" name="message" required></textarea>
      </div>
      <div class="form-group">
        <button type="submit">Send Message</button>
      </div>
    </form>
  </div>
</body>
</html>