<h1 align="center">Orah</h1>

<p align="center"><strong>An Inventory Manager, universally designed for all FMCG industries.</strong></p>

<p align="center">
  <a href="https://www.youtube.com/watch?v=y9v81D6gyWI" target="_blank">
    <img src="https://img.youtube.com/vi/y9v81D6gyWI/maxresdefault.jpg" alt="Watch Demo" width="600">
  </a>
</p>

## Table of Contents
<ul>
  <li><a href="#introduction">Introduction</a></li>
  <li><a href="#features">Features</a></li>
  <li><a href="#technologies-used">Technologies Used</a></li>
  <li><a href="#installation">Installation</a></li>
  <li><a href="#usage">Usage</a></li>
  <li><a href="#contributing">Contributing</a></li>
  <li><a href="#license">License</a></li>
  <li><a href="#contact">Contact</a></li>
</ul>

<h2 id="introduction">Introduction</h2>
<p>Orah is a comprehensive inventory management system tailored for Fast-Moving Consumer Goods (FMCG) industries. It streamlines the processes of tracking raw materials, managing product portfolios, monitoring sales, and handling orders, ensuring efficient operations and accurate record-keeping.</p>

<h2 id="features">Features</h2>
<ul>
  <li><strong>User Authentication</strong>: Secure login and registration system.</li>
  <li><strong>Raw Material Management</strong>: Add, update, and monitor raw materials.</li>
  <li><strong>Product Portfolio</strong>: Maintain a detailed list of finished products.</li>
  <li><strong>Order Management</strong>: Efficiently handle customer orders and track their statuses.</li>
  <li><strong>Sales Tracking</strong>: Monitor sales records and generate insightful reports.</li>
  <li><strong>Purchase Records</strong>: Keep track of raw material purchases and supplier information.</li>
</ul>

<h2 id="technologies-used">Technologies Used</h2>
<ul>
  <li><strong>Backend</strong>: PHP</li>
  <li><strong>Frontend</strong>: JavaScript, HTML, CSS</li>
  <li><strong>Database</strong>: MySQL</li>
  <li><strong>Version Control</strong>: Git</li>
</ul>

<h2 id="installation">Installation (Using XAMPP)</h2>
<ol>
  <li><strong>Download and Install XAMPP:</strong></li>
  <p>Get XAMPP from <a href="https://www.apachefriends.org/index.html">https://www.apachefriends.org/</a> and install it.</p>

  <li><strong>Clone the Repository into XAMPP's htdocs Folder:</strong></li>
  <pre><code>cd C:\xampp\htdocs
git clone https://github.com/arryaanjain/Orah.git</code></pre>

  <li><strong>Rename the Cloned Folder to "PIMS":</strong></li>
  <pre><code>rename Orah PIMS</code></pre>

  <li><strong>Start XAMPP Services:</strong></li>
  <ul>
    <li>Open XAMPP Control Panel.</li>
    <li>Start <strong>Apache</strong> and <strong>MySQL</strong>.</li>
  </ul>

  <li><strong>Set Up the Database:</strong></li>
  <ul>
    <li>Open <a href="http://localhost/phpmyadmin">phpMyAdmin</a>.</li>
    <li>Create a new database named <code>orah_db</code>.</li>
    <li>Go to the **Import** tab, select <code>database/orah_db.sql</code>, and import it.</li>
  </ul>

  <li><strong>Configure Database Connection:</strong></li>
  <p>Update the <code>functions.php</code> file inside <code>PIMS</code> with your database credentials:</p>
  <pre><code>$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'orah_db';</code></pre>

  <li><strong>Run the Application:</strong></li>
  <p>Open your browser and go to:</p>
  <pre><code>http://localhost/PIMS</code></pre>
</ol>

<h2 id="usage">Usage</h2>
<ul>
  <li><strong>Login/Register</strong>: Access the system by registering a new account or logging in with existing credentials.</li>
  <li><strong>Dashboard</strong>: View an overview of inventory statuses and recent activities.</li>
  <li><strong>Raw Materials</strong>: Add new raw materials, update existing ones, and monitor stock levels.</li>
  <li><strong>Product Portfolio</strong>: Manage finished products, including adding new products and updating details.</li>
  <li><strong>Orders</strong>: Create new orders, update order statuses, and view order histories.</li>
  <li><strong>Sales</strong>: Record new sales transactions and generate sales reports.</li>
  <li><strong>Purchases</strong>: Log raw material purchases and maintain supplier information.</li>
</ul>

<h2 id="contributing">Contributing</h2>
<p>We welcome contributions to enhance Orah's functionality and usability. To contribute:</p>
<ol>
  <li><strong>Fork the Repository</strong>: Click on the 'Fork' button at the top right of the repository page.</li>

  <li><strong>Clone Your Fork:</strong></li>
  <pre><code>git clone https://github.com/your_username/Orah.git</code></pre>

  <li><strong>Create a New Branch:</strong></li>
  <pre><code>git checkout -b feature/your_feature_name</code></pre>

  <li><strong>Make Your Changes</strong>: Implement your feature or fix.</li>

  <li><strong>Commit Your Changes:</strong></li>
  <pre><code>git commit -m "Add feature: your_feature_name"</code></pre>

  <li><strong>Push to Your Fork:</strong></li>
  <pre><code>git push origin feature/your_feature_name</code></pre>

  <li><strong>Submit a Pull Request</strong>: Navigate to the original repository and click on 'New Pull Request'.</li>
</ol>

<h2 id="license">License</h2>
<p>This project is licensed under the <a href="LICENSE">MIT License</a>.</p>

<h2 id="contact">Contact</h2>
<p>For any inquiries or feedback, please contact:</p>
<ul>
  <li><strong>Arryaan Jain</strong></li>
  <li><strong>GitHub</strong>: <a href="https://github.com/arryaanjain">arryaanjain</a></li>
  <li><strong>Email</strong>: <a href="mailto:jainarryaan@gmail.com">jainarryaan@gmail.com</a></li>
</ul>
