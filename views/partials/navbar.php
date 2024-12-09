<nav class="navbar navbar-expand-lg bg-body-tertiary" data-bs-theme="dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="">Orah</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="add.php">Add New Product</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="order.php">Order Book</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="sales.php">Sales Book</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="rm_purchase.php">Raw Material Purchase</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="rm_master.php">Raw Material Master</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="records.php">Records</a>
                </li>
                <!-- Dropdown for Records, now clickable -->
                <li class="nav-item dropdown" style="margin-left: -15px;">
                    <a class="nav-link dropdown-toggle" href="records.php" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <!-- Optional text or icon for Records dropdown -->
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown" style="margin-top: -2px;">
                        <li><a class="dropdown-item" href="my_orders.php">My Orders</a></li>
                        <li><a class="dropdown-item" href="my_sales.php">My Sales</a></li>
                        <li><a class="dropdown-item" href="inserted_materials.php">Inserted Materials</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Welcome</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
