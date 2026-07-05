CREATE DATABASE IF NOT EXISTS cleanpro_db;
USE cleanpro_db;

-- TABLE 1: users
CREATE TABLE users (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    name     VARCHAR(100) NOT NULL,
    email    VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone    VARCHAR(20)  NOT NULL,
    role     VARCHAR(10)  NOT NULL DEFAULT 'customer',
    verified INT          NOT NULL DEFAULT 0,
    status   VARCHAR(10)  NOT NULL DEFAULT 'active',
    document VARCHAR(255) DEFAULT NULL
);

-- TABLE 2: services
CREATE TABLE services (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)  NOT NULL,
    description VARCHAR(255)  NOT NULL,
    price       DECIMAL(10,2) NOT NULL,
    hours       DECIMAL(4,1)  NOT NULL,
    category    VARCHAR(20)   NOT NULL
);

-- TABLE 3: bookings
CREATE TABLE bookings (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT           NOT NULL,
    cleaner_id  INT           NOT NULL,
    service_id  INT           NOT NULL,
    date        DATE          NOT NULL,
    time        TIME          NOT NULL,
    address     VARCHAR(255)  NOT NULL,
    amount      DECIMAL(10,2) NOT NULL,
    status      VARCHAR(20)   NOT NULL DEFAULT 'pending',
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

-- TABLE 4: payments
CREATE TABLE payments (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT           NOT NULL,
    mpesa_code VARCHAR(50)   NOT NULL,
    amount     DECIMAL(10,2) NOT NULL,
    phone      VARCHAR(20)   NOT NULL,
    paid_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

-- TABLE 5: reviews
CREATE TABLE reviews (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    booking_id  INT  NOT NULL,
    customer_id INT  NOT NULL,
    cleaner_id  INT  NOT NULL,
    rating      INT  NOT NULL,
    comment     TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- SAMPLE DATA — ready-made accounts for testing
-- All three accounts use the password:  password

-- Admin account
INSERT INTO users (name, email, password, phone, role, verified, status) VALUES
('Admin CleanPro', 'admin@cleanpro.co.tz',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 '0700000000', 'admin', 1, 'active', NULL);

-- Test customer
INSERT INTO users (name, email, password, phone, role, verified, status) VALUES
('Amina Juma', 'amina@gmail.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 '0712345678', 'customer', 0, 'active', NULL);

-- Test cleaner 
INSERT INTO users (name, email, password, phone, role, verified, status) VALUES
('John Mwenda', 'john@gmail.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 '0755123456', 'cleaner', 1, 'active', NULL);

-- Services
INSERT INTO services (name, description, price, hours, category) VALUES
('Home Basic Cleaning',       'Cleaning of living room, bedrooms and bathroom',            25000, 3.0, 'home'),
('Office Cleaning',           'Full office cleaning including desks, chairs and floors',   40000, 4.0, 'office'),
('Deep Cleaning',             'Thorough cleaning including inside cupboards and furniture',60000, 6.0, 'deep'),
('Post-Construction Cleaning','Removing dust and debris after building or renovation',     80000, 8.0, 'deep');
