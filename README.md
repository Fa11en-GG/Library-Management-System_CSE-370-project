# Library-Management-System_CSE-370-project
A PHP &amp; MySQL-powered Library Management System featuring real-time inventory tracking, a 12-hour smart reservation engine, and a user-driven book rating system.

## 🚀 Key Features

* **⚡ 12-Hour Smart Reservation:** A unique booking system that holds a book for 12 hours. An automated cleanup script runs on page load to release expired holds back into available inventory.
* **📊 Dynamic Stock Tracking:** Real-time visibility of physical book copies across three distinct states: `Available`, `Borrowed`, and `Reserved`.
* **👍 Interaction Engine:** A robust Like/Dislike system using relational data to ensure one-vote-per-user integrity.
* **💸 Automated Fine System:** Real-time late fee calculation based on `DATETIME` comparisons between due dates and return timestamps.
* **🔐 Dual-Role Access:** * **Users:** Browse, search, borrow, reserve, and rate books.
    * **Admins:** Manage global stock levels, restock inventory, and monitor active loans.

## 🛠️ Technology Stack

* **Backend:** PHP 8.x
* **Database:** MySQL / MariaDB
* **Frontend:** HTML5, CSS3, JavaScript (AJAX/Fetch API)
* **Server:** XAMPP / Apache

## 📂 Database Schema

The system relies on a relational database named `library_db` with the following key tables:

| Table | Purpose |
| :--- | :--- |
| `books` | Stores core metadata (ISBN, Title, Author, Rating counts). |
| `inventory` | Tracks individual physical copies and their current status. |
| `loans` | Records the lifecycle of every borrow and reservation. |
| `users` | Manages authentication and roles (User/Admin). |
| `book_ratings` | Tracks specific user feedback to prevent double-voting. |

## ⚙️ Installation

1.  **Clone the Repository:**
    ```bash
    git clone https://github.com/yourusername/library-system.git
    ```

2.  **Setup the Database:**
    * Open XAMPP and start Apache and MySQL.
    * Navigate to `http://localhost/phpmyadmin`.
    * Create a new database named `library_db`.
    * Import the `library_db.sql` file provided in the repository.

3.  **Configure Connection:**
    * Ensure your `db.php` (or connection block in `actions.php`) matches your local credentials:
    ```php
    $host = 'localhost';
    $db   = 'library_db';
    $user = 'root';
    $pass = '';
    ```

4.  **Launch:**
    * Move the project folder to `C:/xampp/htdocs/`.
    * Open your browser and go to `http://localhost/library-system/index.php`.

## 📝 Usage

* **To Login as Admin:** Use phone `0123456789` and password `admin123`.
* **To Login as User:** Use phone `1112223333` and password `pass123`.
* **Testing Reservations:** Click 'Reserve' on any available book. The status will update to 'Reserved', and you will have exactly 12 hours to check it out before the system automatically releases it.

## 📄 License
This project is open-source and available under the MIT License.
