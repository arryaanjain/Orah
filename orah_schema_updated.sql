-- Companies (existing - keep as is)
CREATE TABLE companies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Users (existing - keep as is)  
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_email (company_id, email)
);

-- Raw Materials Master (improved)
CREATE TABLE rm_master (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    user_id INT NOT NULL,
    material VARCHAR(255) NOT NULL,
    description TEXT,
    base_unit VARCHAR(50) NOT NULL, -- kg, liters, pieces, etc.
    minimum_stock DECIMAL(10,3) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_material (company_id, user_id, material),
    INDEX idx_company_user (company_id, user_id)
);

-- Units (normalized units table)
CREATE TABLE rm_master_units (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    user_id INT NOT NULL,
    material_id INT NOT NULL,
    unit_name VARCHAR(50) NOT NULL,
    conversion_factor DECIMAL(10,4) NOT NULL DEFAULT 1, -- conversion to base unit
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES rm_master(id) ON DELETE CASCADE,
    UNIQUE KEY unique_material_unit (company_id, user_id, material_id, unit_name)
);

-- Raw Material Purchases (improved with better tracking)
CREATE TABLE rm_purchase (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    user_id INT NOT NULL,
    material_id INT NOT NULL,
    supplier_name VARCHAR(255),
    purchase_date DATE NOT NULL,
    qty DECIMAL(10,3) NOT NULL,
    unit_id INT NOT NULL,
    rate DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(12,2) DEFAULT 0,
    batch_number VARCHAR(100),
    expiry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES rm_master(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES rm_master_units(id) ON DELETE CASCADE,
    INDEX idx_company_user_material (company_id, user_id, material_id)
);

-- Finished Products (improved)
CREATE TABLE finished_products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    user_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    description TEXT,
    base_unit VARCHAR(50) NOT NULL DEFAULT 'pieces',
    selling_price DECIMAL(10,2) DEFAULT 0,
    minimum_stock DECIMAL(10,3) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product (company_id, user_id, product_name),
    INDEX idx_company_user (company_id, user_id)
);

-- Product Bill of Materials (NEW - replaces dynamic tables)
CREATE TABLE product_bom (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    material_id INT NOT NULL,
    qty_required DECIMAL(10,4) NOT NULL, -- quantity per unit of finished product
    unit_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES finished_products(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES rm_master(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES rm_master_units(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_material (company_id, user_id, product_id, material_id),
    INDEX idx_product (product_id)
);

-- Customers (improved)
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_company_user (company_id, user_id)
);

-- Order Book (improved)
CREATE TABLE order_book (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    user_id INT NOT NULL,
    customer_id INT,
    product_id INT NOT NULL,
    qty DECIMAL(10,3) NOT NULL,
    unit_price DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(12,2) DEFAULT 0,
    order_date DATE NOT NULL,
    expected_delivery_date DATE,
    status ENUM('pending', 'confirmed', 'in_production', 'ready', 'delivered', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (product_id) REFERENCES finished_products(id) ON DELETE CASCADE,
    INDEX idx_company_user (company_id, user_id),
    INDEX idx_order_date (order_date),
    INDEX idx_status (status)
);

-- Sales Book (improved)
CREATE TABLE sales_book (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT,
    customer_id INT,
    product_id INT NOT NULL,
    qty DECIMAL(10,3) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(12,2) NOT NULL,
    sale_date DATE NOT NULL,
    payment_status ENUM('pending', 'partial', 'paid') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES order_book(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (product_id) REFERENCES finished_products(id) ON DELETE CASCADE,
    INDEX idx_company_user (company_id, user_id),
    INDEX idx_sale_date (sale_date)
);

-- Stock Movements (NEW - for better inventory tracking)
CREATE TABLE stock_movements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    user_id INT NOT NULL,
    material_id INT NOT NULL,
    movement_type ENUM('purchase', 'consumption', 'adjustment', 'return') NOT NULL,
    reference_type ENUM('purchase', 'sale', 'production', 'manual') NOT NULL,
    reference_id INT, -- ID of the related record (purchase_id, sale_id, etc.)
    qty_change DECIMAL(10,3) NOT NULL, -- positive for inbound, negative for outbound
    unit_id INT NOT NULL,
    notes TEXT,
    movement_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES rm_master(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES rm_master_units(id) ON DELETE CASCADE,
    INDEX idx_material_date (material_id, movement_date),
    INDEX idx_company_user (company_id, user_id)
);