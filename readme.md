<h1 align="center"> 💰 MoneyMap – Personal Expense Tracker 💵 </h1>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-Core_PHP-blueviolet?style=for-the-badge&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-Database-blue?style=for-the-badge&logo=mysql" alt="MySQL">
  <img src="https://img.shields.io/badge/Tailwind_CSS-v3-38B2AC?style=for-the-badge&logo=tailwind-css" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Visualization-Chart.js-red?style=for-the-badge&logo=chart.js" alt="Chart.js">
</p>

**MoneyMap** is a powerful personal expense tracking web application built using **Core PHP** and **MySQL**. It enables users to record, analyze, and visualize their financial data effortlessly — with features like dynamic charts, goal tracking, notes, and secure authentication.

---

## 📋 Table of Contents

- [Features](#-features)
- [Technologies Used](#-technologies-used)
- [Project Structure](#-project-structure)
- [Database Design](#-database-design)
- [Installation](#-installation)
- [Usage](#-usage)
- [Modules](#-modules)
- [Admin Panel](#-admin-panel)
- [Deployment on ByetHost](#-deployment-on-byethost)
- [Future Enhancements](#-future-enhancements)
- [Contributing](#-contributing)
- [License](#-license)
- [Developer](#-developer)
- [Acknowledgments](#-acknowledgments)
- [Support](#-support)

---

## ✨ Features

### 👤 **User Features**
- 🔐 **Secure Authentication** – Password hashing with sessions
- 📊 **Interactive Dashboard** – Real-time summary of income, expenses, and savings
- 📈 **Dynamic Visualization** – Bar and Donut charts (via Chart.js)
- 💵 **Transaction Management** – Add, view, and categorize transactions
- 🎯 **Goal Tracking** – Create financial goals and monitor progress
- 💱 **Currency Converter** – Real-time AJAX-based currency conversions
- 📝 **Personal Notes** – Save personal financial reminders
- 📄 **Export Reports** – Generate reports in PDF/CSV formats
- 📱 **Responsive Design** – Optimized for all screen sizes

### 🛡️ **Admin Features**
- 🔒 **Admin Authentication** – Secure admin login
- 👥 **User Management** – View, add, and manage users
- 📊 **Analytics Dashboard** – Track global user statistics and data
- 📥 **Export Data** – Generate complete platform reports
- 📈 **Visual Insights** – Platform-wide financial data analytics

---

## 🛠️ Technologies Used

| Category | Technology |
|-----------|-------------|
| **Frontend** | HTML5, CSS3, JavaScript, AJAX, Tailwind CSS |
| **Backend** | PHP (Core PHP) |
| **Database** | MySQL |
| **Visualization** | Chart.js |
| **Security** | Password Hashing (bcrypt), Sessions |
| **Reports** | PDF/CSV Export Libraries |

---

## 📁 Project Structure

MoneyMap/
├── index.php
├── signup.php
├── login.php
├── dashboard.php
├── logout.php
├── add_transaction.php
├── view_transactions.php
├── add_goal.php
├── view_goals.php
├── notes.php
├── currency_converter.php
├── export_report.php
│
├── config/
│ └── db.php
│
├── admin/
│ ├── admin_login.php
│ ├── admin_dashboard.php
│ ├── manage_users.php
│ └── export_data.php
│
├── assets/
│ ├── css/
│ │ └── style.css
│ ├── js/
│ │ ├── chart.js
│ │ ├── ajax.js
│ │ └── main.js
│ └── images/
│ └── logo.png
│
├── includes/
│ ├── header.php
│ └── footer.php
│
├── exports/
│ ├── user_reports/
│ └── admin_reports/
│
└── database/
└── moneymap.sql

pgsql
Copy code

---

## 🗄️ Database Design

### 1. **users**
| Field | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| fullname | VARCHAR(255) | User’s full name |
| email | VARCHAR(255) | Unique email |
| password | VARCHAR(255) | Hashed password |
| created_at | DATETIME | Account creation time |

### 2. **transactions**
| Field | Type | Description |
|--------|------|-------------|
| id | INT | Transaction ID |
| user_id | INT | Linked user ID |
| date | DATE | Transaction date |
| category | VARCHAR(100) | Category |
| description | TEXT | Notes/details |
| amount | DECIMAL(10,2) | Amount |
| type | ENUM('income','expense') | Transaction type |
| created_at | DATETIME | Record creation time |

### 3. **goals**
| Field | Type | Description |
|--------|------|-------------|
| id | INT | Goal ID |
| user_id | INT | Linked user |
| goal_name | VARCHAR(255) | Goal title |
| target_amount | DECIMAL(10,2) | Target value |
| saved_amount | DECIMAL(10,2) | Current progress |
| start_date | DATE | Start date |
| target_date | DATE | Target date |
| status | ENUM('active','achieved') | Status |
| created_at | DATETIME | Created timestamp |
| updated_at | DATETIME | Last update |

### 4. **user_notes**
| Field | Type | Description |
|--------|------|-------------|
| id | INT | Note ID |
| user_id | INT | Linked user |
| note_content | TEXT | Note text |
| created_at | DATETIME | Created timestamp |

---

## 🚀 Installation

### Prerequisites
- PHP ≥ 8.0  
- MySQL ≥ 8.0  
- Apache/Nginx Web Server  
- XAMPP/WAMP/LAMP for local setup  

### Steps

1. **Clone the Repository**
   ```bash
   git clone https://github.com/yourusername/moneymap.git
   cd moneymap
Import the Database

Open phpMyAdmin

Create a database named moneymap

Import database/moneymap.sql

Configure Database

php
Copy code
<?php
$host = 'localhost';
$dbname = 'moneymap';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
Run the App

Place the folder in /htdocs (XAMPP)

Start Apache & MySQL

Open: http://localhost/moneymap

💻 Usage
For Users
Register and log in

Add and view transactions

Create financial goals

Analyze data using charts

Export PDF/CSV reports

Use built-in currency converter

Keep private financial notes

For Admin
Login via /admin/admin_login.php

View platform statistics

Manage users

Export complete data reports

📦 Modules
🔑 Authentication

📊 Dashboard

💵 Transaction Management

🎯 Goal Tracker

💱 Currency Converter

📝 Notes

📈 Visualization (Chart.js)

📄 Reports Export

🛡️ Admin Panel

🌐 Deployment on ByetHost
🏗️ Overview
MoneyMap runs on a LAMP stack and is successfully deployed on ByetHost, a free PHP + MySQL hosting platform.

1️⃣ Create Hosting Account
Sign up at ByetHost

Create a domain (e.g., money-map.byethost5.com)

Access your VistaPanel

2️⃣ Setup MySQL Database
In VistaPanel → MySQL Databases:

yaml
Copy code
Host: sql113.byethost5.com
Username: b5_40250472
Database: b5_40250472_money_map
Port: 3306
Password: (hidden for security)
3️⃣ Upload Project
Upload all project files via File Manager or FTP

Place them inside the /htdocs directory

4️⃣ Configure Database Connection
Edit config/db.php:

php
Copy code
// --- ByetHost Configuration ---
define('DB_HOST', 'sql113.byethost5.com');
define('DB_USER', 'b5_40250472');
define('DB_PASS', 'your_database_password_here'); // Hidden for security
define('DB_NAME', 'b5_40250472_money_map');
define('DB_PORT', '3306');
✅ Tip: Test connection using a small PHP file with mysqli_connect().

5️⃣ Access Live Site
🌐 https://money-map.byethost5.com/?i=1

🔐 Admin Panel → https://money-map.byethost5.com/admin/admin_login.php

🔮 Future Enhancements
📱 Mobile App (Flutter/React Native)

🤖 AI-based Expense Predictions

☁️ Cloud Data Backup

🌙 Dark Mode

📧 Email Alerts & Notifications

🏦 Bank API Integration

📊 Advanced Financial Analytics

🤝 Contributing
Fork the repository

Create a branch (feature/YourFeature)

Commit your changes

Push to your branch

Submit a Pull Request

📄 License
Licensed under the MIT License — see LICENSE for details.

👨‍💻 Developer
Jeymurugan Nadar
📧 Email: murugannadar077@gmail.com
💻 GitHub: github.com/nadarmurugan
🔗 LinkedIn: linkedin.com/in/murugannadar

🙏 Acknowledgments
HTML5 – Semantic web structure

Tailwind CSS – Modern responsive UI

Chart.js – Clean visualizations

PHP Community – Backend foundation

MySQL – Reliable data management

📞 Support
For issues or suggestions, contact
📧 murugannadar077@gmail.com
or create an issue on the repository.

⭐ If you like this project, please give it a star!
📊 Database Snapshot
Table	Rows	Engine	Size
users	3	InnoDB	48 KiB
transactions	5	InnoDB	16 KiB
goals	1	InnoDB	32 KiB
user_notes	3	InnoDB	32 KiB
Total	12	InnoDB	128 KiB

<p align="center">💡 Built & Designed by <b>Jeymurugan Nadar</b></p> ```
