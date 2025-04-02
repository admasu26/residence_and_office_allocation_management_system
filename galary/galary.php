<!DOCTYPE html>
<html>
<head>
  <title>Lucy Restaurant - Gallery</title>
  <style>
    * {
      box-sizing: 30;
    }
    
    body {
      margin: 0;
      padding: 20px;
      font-family: Arial, sans-serif;
    }
    
    h1 {
      text-align: center;
    }
    
    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      background: linear-gradient(45deg, green 40%, yellow 45%, orange 0%);
      padding: 10px;
      color: white;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }
    
    .navbar a {
      color: black;
      text-decoration: none;
      padding: 10px 15px;
      border-radius: 5px;
    }
    
    .navbar a:hover,
    .navbar a:focus {
      background-color: rgba(255, 255, 255, 0.2);
    }
    
    .navbar a.active {
      background-color: rgba(255, 255, 255, 0.2);
    }
    
    .gallery {
      margin-top: 60px; /* Add a margin to prevent the images from being hidden behind the navbar */
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      grid-gap: 20px;
    }
    
    .gallery img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border: 2px solid orange;
      border-radius: 5px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
      transition: box-shadow 0.3s ease-in-out;
    }
    
    .gallery img:hover {
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
    }
    
    .gallery img:focus {
      outline: none;
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
    }
    
    .label {
      color: #333;
    }
  </style>
</head>
<body>
 
  
  <h1>Lucy Restaurant - Gallery</h1>

  <div class="gallery">
    <img src="image/im0.jpg" alt="Food Photo 1">
    <img src="image/im2.png" alt="Food Photo 2">
    <img src="image/im3.png" alt="Food Photo 3">
    <img src="image/im4.png" alt="Food Photo 4">
    <img src="image/im5.png" alt="Food Photo 5">
    <img src="image/im6.png" alt="Food Photo 6">
    <img src="image/im7.png" alt="Food Photo 7">
    <img src="image/im8.png" alt="Food Photo 8">
    <img src="image/im9.png" alt="Food Photo 9">
    <img src="image/im10.png" alt="Food Photo 10">
    <img src="image/im11.png" alt="Food Photo 11">
    <img src="image/im1.png" alt="Food Photo 11">
    <div class="navbar">
    <a href="/" class="active">Home</a>
  </div>
  </div>
</body>
</html>