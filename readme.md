
---

```markdown
<h1 align="center"> 💰 MoneyMap – Personal Expense Tracker 💵 </h1>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-Core_PHP-blueviolet?style=for-the-badge&logo=php" alt="PHP Version">
  <img src="https://img.shields.io/badge/MySQL-Database-blue?style=for-the-badge&logo=mysql" alt="MySQL Version">
  <img src="https://img.shields.io/badge/Tailwind_CSS-v3-38B2AC?style=for-the-badge&logo=tailwind-css" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Visualization-Chart.js-red?style=for-the-badge&logo=chart.js" alt="Chart.js">
</p>

**MoneyMap** is a comprehensive personal expense tracker built with **Core PHP** and **MySQL**.  
It helps users monitor, analyze, and manage their finances with an intuitive dashboard, interactive charts, goal tracking, currency conversion, and secure data management.

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
- [Future Enhancements](#-future-enhancements)
- [Deployment on ByetHost](#-deployment-on-byethost)
- [Contributing](#-contributing)
- [License](#-license)
- [Developer](#-developer)
- [Acknowledgments](#-acknowledgments)
- [Support](#-support)

---

## ✨ Features

### **User Features**
- 🔐 **Secure Authentication** – Password hashing & session-based login/signup  
- 📊 **Interactive Dashboard** – Real-time summary of income, expenses & savings  
- 📈 **Data Visualization** – Dynamic charts for income vs expense & category analysis  
- 💵 **Transaction Management** – Add, view & categorize income/expense records  
- 🎯 **Goal Tracking** – Set financial goals and monitor progress  
- 💱 **Currency Converter** – Real-time AJAX-based currency conversion  
- 📝 **Personal Notes** – Keep reminders or short financial notes  
- 📄 **Export Reports** – Generate PDF/CSV reports  
- 📱 **Responsive Design** – Works seamlessly on all devices  

### **Admin Features**
- 🛡️ **Dedicated Admin Panel** – Secure login for administrators  
- 👥 **User Management** – View and manage all registered users  
- 📊 **Platform Analytics** – Visualize income, expense & goal statistics  
- 📥 **Data Export** – Export complete platform data (CSV/PDF)  

---

## 🛠️ Technologies Used

| Category | Technology |
|-----------|------------|
| **Frontend** | HTML5, CSS3, JavaScript, AJAX, Tailwind CSS |
| **Backend** | PHP (Core PHP) |
| **Database** | MySQL |
| **Security** | Session Management, Password Hashing (bcrypt) |
| **Visualization** | Chart.js |
| **Report Generation** | PDF/CSV Export (PHP Libraries) |

---

## 📁 Project Structure

```

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
├── config/
│   └── db.php
├── admin/
│   ├── admin_login.php
│   ├── admin_dashboard.php
│   ├── manage_users.php
│   └── export_data.php
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   ├── chart.js
│   │   ├── ajax.js
│   │   └── main.js
│   └── images/
│       └── logo.png
├── includes/
│   ├── header.php
│   └── footer.php
├── exports/
│   ├── user_reports/
│   └── admin_reports/
└── database/
└── moneymap.sql

````

---

## 🗄️ Database Design

The **MoneyMap** database contains **4 main tables**:

### 1. `users`
| Field | Type | Description |
|-------|------|-------------|
| id | INT (PK) | Unique user ID |
| fullname | VARCHAR(255) | User's full name |
| email | VARCHAR(255) | User email (unique) |
| password | VARCHAR(255) | Hashed password |
| created_at | DATETIME | Account creation timestamp |

### 2. `transactions`
| Field | Type | Description |
|-------|------|-------------|
| id | INT (PK) | Transaction ID |
| user_id | INT (FK) | Linked user ID |
| date | DATE | Transaction date |
| category | VARCHAR(100) | Category name |
| description | TEXT | Transaction description |
| amount | DECIMAL(10,2) | Transaction amount |
| type | ENUM('income', 'expense') | Type of transaction |
| created_at | DATETIME | Record creation timestamp |

### 3. `goals`
| Field | Type | Description |
|-------|------|-------------|
| id | INT (PK) | Goal ID |
| user_id | INT (FK) | Linked user ID |
| goal_name | VARCHAR(255) | Goal name |
| target_amount | DECIMAL(10,2) | Target saving amount |
| saved_amount | DECIMAL(10,2) | Current progress |
| start_date | DATE | Goal start date |
| target_date | DATE | Goal completion date |
| status | ENUM('active', 'achieved') | Goal status |
| created_at | DATETIME | Created timestamp |
| updated_at | DATETIME | Updated timestamp |

### 4. `user_notes`
| Field | Type | Description |
|-------|------|-------------|
| id | INT (PK) | Note ID |
| user_id | INT (FK) | Linked user ID |
| note_content | TEXT | Note content |
| created_at | DATETIME | Note creation timestamp |

---

## 🚀 Installation

### Prerequisites
- PHP ≥ 8.0  
- MySQL ≥ 8.0  
- Apache/Nginx Web Server  
- XAMPP/WAMP/LAMP (for local development)

### Steps

1. **Clone the Repository**
```bash
git clone https://github.com/yourusername/moneymap.git
cd moneymap
````

2. **Import Database**

* Open phpMyAdmin
* Create a database named `moneymap`
* Import `database/moneymap.sql`

3. **Configure Database Connection**
   Edit `config/db.php`:

```php
<?php
$host = 'localhost';
$dbname = 'moneymap';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
```

4. **Start the Server**

* Place the project in `htdocs` (for XAMPP)
* Start Apache & MySQL
* Visit: `http://localhost/moneymap`

5. **Create Admin Account**

* Set default credentials in `admin/admin_login.php`
* Or manually insert into the `users` table

---

## 💻 Usage

### For Users

1. Sign up and log in
2. Add income/expense transactions
3. Create and track financial goals
4. View data visualizations
5. Export reports (PDF/CSV)
6. Use currency converter
7. Manage notes and reminders

### For Admins

1. Login via the admin panel
2. View platform-wide analytics
3. Manage users
4. Export complete reports

---

## 📦 Modules Overview

| Module                 | Description                      |
| ---------------------- | -------------------------------- |
| **Authentication**     | Secure signup/login using bcrypt |
| **Dashboard**          | Real-time financial overview     |
| **Transactions**       | CRUD for income/expenses         |
| **Goals**              | Create & track saving goals      |
| **Charts**             | Dynamic charts via Chart.js      |
| **Currency Converter** | AJAX-based conversion            |
| **Notes**              | Add personal financial notes     |
| **Export Reports**     | PDF & CSV generation             |
| **Admin Panel**        | Manage users & platform data     |

---

## 🛡️ Admin Panel

The Admin Panel provides full control over platform analytics:

* Dashboard with user/goal/transaction stats
* Manage users
* Export platform data
* Secure login credentials

---

## 🔮 Future Enhancements

* 📱 Mobile App (Flutter/React Native)
* 🤖 AI-based Expense Prediction
* ☁️ Cloud Data Backup
* 👨‍👩‍👧‍👦 Multi-User Collaboration
* 🌙 Dark Mode Support
* 📧 Email/SMS Alerts
* 🏦 Bank API Integration
* 📊 Advanced Analytics

---

## 🌐 Deployment on ByetHost

### 🏗️ Overview

**MoneyMap** is successfully deployed on **ByetHost**, a free LAMP (Linux, Apache, MySQL, PHP) hosting service.

---

### 1️⃣ Create a Hosting Account

1. Visit [ByetHost](https://byet.host)
2. Sign up and create a free hosting account
3. Add a domain (e.g., `money-map.byethost5.com`)
4. Access **VistaPanel** for database & file management

---

### 2️⃣ Configure MySQL Database

| Setting           | Value                   |
| ----------------- | ----------------------- |
| **Host**          | `sql113.byethost5.com`  |
| **Username**      | `b5_40250472`           |
| **Database Name** | `b5_40250472_money_map` |
| **Port**          | `3306`                  |
| **Password**      | *(hidden for security)* |

> ⚠️ Always keep credentials private and never commit them to GitHub.

---

### 3️⃣ Upload Project Files

* Open VistaPanel’s File Manager or use **FileZilla FTP**
* Upload your entire `MoneyMap` project to `/htdocs/`
* Ensure correct folder structure (`admin/`, `config/`, `assets/`, etc.)

---

### 4️⃣ Update Database Configuration

Edit `config/db.php`:

```php
// --- Configuration for ByetHost ---
define('DB_HOST', 'sql113.byethost5.com');
define('DB_USER', 'b5_40250472');
define('DB_PASS', 'your_database_password_here'); // Hidden for security
define('DB_NAME', 'b5_40250472_money_map');
define('DB_PORT', '3306');
```

---

### 5️⃣ Access the Live Site

🌐 **Live Website:** [https://money-map.byethost5.com/?i=1](https://money-map.byethost5.com/?i=1)
🔐 **Admin Panel:** [https://money-map.byethost5.com/admin/admin_login.php](https://money-map.byethost5.com/admin/admin_login.php)

---

## 🤝 Contributing

Contributions are welcome!

1. Fork the repository
2. Create a branch (`feature/YourFeature`)
3. Commit changes (`git commit -m 'Add new feature'`)
4. Push (`git push origin feature/YourFeature`)
5. Open a Pull Request

---

## 📄 License

This project is licensed under the **MIT License**.
See the [LICENSE](LICENSE) file for more details.

---

## 👨‍💻 Developer

**Jeymurugan Nadar**
📧 Email: [murugannadar077@gmail.com](mailto:murugannadar077@gmail.com)
🔗 GitHub: [github.com/nadarmurugan](https://github.com/nadarmurugan)
🔗 LinkedIn: [linkedin.com/in/murugannadar](https://www.linkedin.com/in/murugannadar)

---

## 🙏 Acknowledgments

* HTML5 for structured and semantic design
* Chart.js for beautiful visualizations
* Tailwind CSS for modern responsive UI
* PHP community for backend guidance
* MySQL for reliable data management

---

## 📞 Support

For support or inquiries, contact:
📧 [murugannadar077@gmail.com](mailto:murugannadar077@gmail.com)
or open an issue in the repository.

---

**⭐ If you like this project, please give it a star!**

---

### 📊 Database Statistics

| Table        | Rows   | Engine     | Size          |
| ------------ | ------ | ---------- | ------------- |
| users        | 3      | InnoDB     | 48.0 KiB      |
| transactions | 5      | InnoDB     | 16.0 KiB      |
| goals        | 1      | InnoDB     | 32.0 KiB      |
| user_notes   | 3      | InnoDB     | 32.0 KiB      |
| **Total**    | **12** | **InnoDB** | **128.0 KiB** |

---

<p align="center">✨ Made with ❤️ by <b>Jeymurugan Nadar</b> ✨</p>
```

---

