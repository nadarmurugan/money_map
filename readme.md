<h1 align="center"> 💰 MoneyMap – Personal Expense Tracker  💵 </h1>

<p align="center"> <img src="https://img.shields.io/badge/PHP-Core_PHP-blueviolet?style=for-the-badge&logo=php" alt="PHP Version"> <img src="https://img.shields.io/badge/MySQL-Database-blue?style=for-the-badge&logo=mysql" alt="MySQL Version"> <img src="https://img.shields.io/badge/Tailwind_CSS-v3-38B2AC?style=for-the-badge&logo=tailwind-css" alt="Tailwind CSS"> <img src="https://img.shields.io/badge/Visualization-Chart.js-red?style=for-the-badge&logo=chart.js" alt="Chart.js"> </p>

**MoneyMap** is a comprehensive personal expense tracking web application that helps users monitor, analyze, and manage their finances in a systematic and secure manner. Built with Core PHP and MySQL, it provides an intuitive dashboard with dynamic charts, goal tracking, currency conversion, and automated report generation.

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
- [Screenshots](#-screenshots)
- [Future Enhancements](#-future-enhancements)
- [Contributing](#-contributing)
- [License](#-license)

---

## ✨ Features

### **User Features**
- 🔐 **Secure Authentication** - Password hashing and session-based login/signup
- 📊 **Interactive Dashboard** - Real-time summary of income, expenses, balance, and savings
- 📈 **Data Visualization** - Dynamic charts (Bar Chart for Income vs Expense, Donut Chart for category-wise expenses)
- 💵 **Transaction Management** - Add, view, and categorize income/expense transactions
- 🎯 **Goal Tracking** - Set financial goals and monitor progress
- 💱 **Currency Converter** - Real-time AJAX-based multi-currency conversion
- 📝 **Personal Notes** - Maintain a personal journal for financial reminders
- 📄 **Export Reports** - Generate PDF/CSV reports of financial summaries
- 📱 **Responsive Design** - Fully optimized for all devices

### **Admin Features**
- 🛡️ **Secure Admin Panel** - Dedicated admin login with predefined credentials
- 👥 **User Management** - View total users and add new users
- 📊 **Platform Analytics** - View total income, expenses, and savings goals
- 📥 **Data Export** - Export all platform data in CSV/PDF format
- 📈 **Statistics Dashboard** - Visualize platform-wide financial data

---

## 🛠️ Technologies Used

| Category | Technology |
|----------|-----------|
| **Frontend** | HTML5, CSS3, JavaScript, AJAX, Tailwind CSS |
| **Backend** | PHP (Core PHP) |
| **Database** | MySQL |
| **Security** | Session Management, Password Hashing (bcrypt) |
| **Data Visualization** | Chart.js |
| **Report Generation** | PDF/CSV Export (PHP libraries) |

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
```

---

## 🗄️ Database Design

The MoneyMap database consists of **4 main tables**:

### 1. **users** Table
Stores user authentication and profile information.

| Field | Type | Description |
|-------|------|-------------|
| id | INT (PK) | Unique user identifier |
| fullname | VARCHAR(255) | User's full name |
| email | VARCHAR(255) | User's email (unique) |
| password | VARCHAR(255) | Hashed password |
| created_at | DATETIME | Account creation timestamp |

### 2. **transactions** Table
Stores all user financial transactions.

| Field | Type | Description |
|-------|------|-------------|
| id | INT (PK) | Unique transaction ID |
| user_id | INT (FK) | Links to users table |
| date | DATE | Transaction date |
| category | VARCHAR(100) | Transaction category |
| description | TEXT | Transaction notes |
| amount | DECIMAL(10,2) | Transaction amount |
| type | ENUM('income', 'expense') | Transaction type |
| created_at | DATETIME | Record creation time |

### 3. **goals** Table
Stores user financial goals and progress.

| Field | Type | Description |
|-------|------|-------------|
| id | INT (PK) | Unique goal ID |
| user_id | INT (FK) | Links to users table |
| goal_name | VARCHAR(255) | Goal title |
| target_amount | DECIMAL(10,2) | Target saving amount |
| saved_amount | DECIMAL(10,2) | Current progress |
| start_date | DATE | Goal start date |
| target_date | DATE | Goal completion date |
| status | ENUM('active', 'achieved') | Goal status |
| created_at | DATETIME | Created timestamp |
| updated_at | DATETIME | Last updated timestamp |

### 4. **user_notes** Table
Stores personal notes and reminders.

| Field | Type | Description |
|-------|------|-------------|
| id | INT (PK) | Unique note ID |
| user_id | INT (FK) | Links to users table |
| note_content | TEXT | Note content |
| created_at | DATETIME | Note creation time |

---

## 🚀 Installation

### Prerequisites
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache/Nginx Web Server
- XAMPP/WAMP/LAMP (for local development)

### Steps

1. **Clone the Repository**
```bash
git clone https://github.com/yourusername/moneymap.git
cd moneymap
```

2. **Import Database**
- Open phpMyAdmin
- Create a new database named `moneymap`
- Import the `database/moneymap.sql` file

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
- For XAMPP: Place project in `htdocs` folder and start Apache & MySQL
- Access the application at: `http://localhost/moneymap`

5. **Create Admin Account** (Optional)
- Default admin credentials can be set in `admin/admin_login.php`
- Or create via phpMyAdmin in the `users` table with admin privileges

---

## 💻 Usage

### For Users

1. **Sign Up** - Create a new account with email and password
2. **Login** - Access your personalized dashboard
3. **Add Transactions** - Record income and expenses with categories
4. **Set Goals** - Create financial goals and track progress
5. **View Analytics** - Check charts and reports for spending insights
6. **Export Data** - Download financial reports in PDF/CSV format
7. **Use Currency Converter** - Convert amounts across different currencies
8. **Maintain Notes** - Keep personal financial notes and reminders

### For Admin

1. **Admin Login** - Access admin panel with credentials
2. **View Statistics** - Monitor platform-wide data (users, transactions, goals)
3. **Manage Users** - Add new users or view existing ones
4. **Export Reports** - Download comprehensive platform data

---

## 📦 Modules

### 1. **User Authentication Module**
- Secure signup and login with password hashing
- Email and password validation
- Session-based authentication

### 2. **Dashboard Module**
- Personalized welcome messages
- Real-time financial summary (Income, Expense, Balance, Savings)
- Quick access to all features

### 3. **Transaction Management Module**
- Add income/expense transactions
- Categorize transactions
- View recent and all transactions
- Add notes/descriptions

### 4. **Goals Module**
- Create financial goals with target amounts and dates
- Track progress automatically
- Mark goals as achieved

### 5. **Charts & Visualization Module**
- Bar Chart: Income vs Expense comparison
- Donut Chart: Category-wise expense distribution
- Dynamic updates using Chart.js

### 6. **Currency Converter Module**
- Real-time AJAX-based conversion
- Support for multiple currencies
- No page reload required

### 7. **Personal Notes Module**
- Create and store personal financial notes
- Secure database storage
- Easy access from dashboard

### 8. **Export Report Module**
- Generate PDF reports with financial summary
- CSV export for data analysis
- User-specific and admin-level exports

### 9. **Admin Panel**
- Secure admin authentication
- Platform-wide statistics
- User management capabilities
- Comprehensive data export

---

## 🛡️ Admin Panel

The Admin Panel provides centralized control and analytics:

- **Dashboard**: View total users, income, expenses, and goals
- **User Management**: Add new users and view user list
- **Analytics**: Platform-wide financial statistics
- **Export**: Generate CSV/PDF reports of all data
- **Security**: Predefined admin credentials for secure access

---

## 🔮 Future Enhancements

- 📱 **Mobile App Integration** - Develop Android/iOS versions using Flutter or React Native
- 🤖 **AI-based Expense Prediction** - Machine learning algorithms for spending pattern prediction
- ☁️ **Cloud Backup Support** - Secure cloud storage for user data
- 👨‍👩‍👧‍👦 **Multi-User Collaboration** - Family budget sharing and collaboration features
- 🌙 **Dark Mode** - Theme customization options
- 📧 **SMS/Email Alerts** - Notifications for goals, expenses, and monthly summaries
- 🏦 **Bank API Integration** - Automatic transaction fetching from bank accounts
- 📊 **Advanced Analytics** - Predictive insights and spending recommendations

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a new branch (`git checkout -b feature/YourFeature`)
3. Commit your changes (`git commit -m 'Add some feature'`)
4. Push to the branch (`git push origin feature/YourFeature`)
5. Open a Pull Request

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 👨‍💻 Developer

**Your Name**  
📧 Email: murugannadar077@gmail.com 
🔗 GitHub: (https://github.com/nadarmurugan)  
🔗 LinkedIn: (www.linkedin.com/in/murugannadar)

)

---

## 🙏 Acknowledgments
- Html5 for structured and semantic web content.
- Chart.js for data visualization.
- Tailwind CSS for responsive design.
- PHP community for backend support
- MySQL for robust database management.

---

## 📞 Support

For support, email murugannadar077@gmail.com or create an issue in the repository.

---

**⭐ If you like this project, please give it a star!**

---

### 📊 Database Statistics

| Table | Type | Rows | Storage Engine | Size |
|-------|------|------|----------------|------|
| users | InnoDB | 3 | InnoDB | 48.0 KiB |
| transactions | InnoDB | 5 | InnoDB | 16.0 KiB |
| goals | InnoDB | 1 | InnoDB | 32.0 KiB |
| user_notes | InnoDB | 3 | InnoDB | 32.0 KiB |
| **Total** | - | **12** | **InnoDB** | **128.0 KiB** |

---

<p align="center">Made by Jeyamurugan nadar</p>




