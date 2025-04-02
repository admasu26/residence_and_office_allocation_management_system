<!DOCTYPE html>
<html>
<head>
  <title>Lucy Bar Restaurant - Menu</title>
  <style>
    * {
      box-sizing: border-box;
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
      padding: 10px;
      background: linear-gradient(45deg, green 35%, yellow 35%, orange 35%);
      color: white;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }
    
    .navbar a {
      color: white;
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
    
    .menu {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); /* Modified to create a responsive grid */
      grid-gap: 20px;
      margin-top: 80px;
    }
    
    .menu-item {
      border: 2px solid orange;
      border-radius: 10px;
      padding: 20px;
      text-align: center;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
      transition: box-shadow 0.3s ease-in-out;
    }
    
    .menu-item:hover {
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
    }
    
    .menu-item h2 {
      margin-top: 0;
    }
    
    .menu-item p {
      margin-bottom: 0;
    }
    
    .menu-item img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 5px;
    }
    
    .menu-item p.price {
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div class="navbar">
    <a href="/" class="active">Home</a>
  </div>
  
  <h1>Lucy Bar and Restaurant - Menu</h1>

  <div class="menu">
    <div class="menu-item">
      <img src="image/im1.png" alt="Item 1">
      <h2>Item 1</h2>
      <p class="price">Price: 200 ETB</p>
    </div>
    <div class="menu-item">
      <img src="image/im11.png" alt="Item 2">
      <h2>Item 2</h2>
      <p class="price">Price: 60 ETB</p>
    </div>
    <div class="menu-item">
      <img src="image/im2.png" alt="Item 3">
      <h2>Item 3</h2>
      <p class="price">Price: 50 ETB</p>
    </div>
    <div class="menu-item">
      <img src="image/im3.png" alt="Item 4">
      <h2>Item 4</h2>
      <p class="price">Price: 150 ETB</p>
    </div>
    <div class="menu-item">
      <img src="image/im4.png" alt="Item 5">
      <h2>Item 5</h2>
      <p class="price">Price: 150 ETB</p>
    </div>
    <div class="menu-item">
      <img src="image/im5.png" alt="Item 6">
      <h2>Item 6</h2>
      <p class="price">Price: 70 ETB</p>
    </div>
    <div class="menu-item">
      <img src="image/im6.png" alt="Item 7">
      <h2>Item 7</h2>
      <p class="price">Price: 15 ETB</p>
    </div>
    <div class="menu-item">
      <img src="image/im7.png" alt="Item 8">
      <h2>Item 8</h2>
      <p class="price">Price: 55 ETB</p>
    </div>
  </div>
</body>
</html>