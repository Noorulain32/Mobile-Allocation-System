# 📱 IESCO Mobile Allocation Management System

This project was developed at IESCO (Islamabad Electric Supply Company). It is a **Mobile Allocation Management System** built using **PHP, MySQL, HTML, CSS, and JavaScript**, designed to manage mobile phone allocations for officers and meter readers.

## 🔍 Features

- 📋 Add, edit, and manage stock of mobile phones
- 👤 Allocate phones to officers and meter readers (MR)
- 🧮 Track allocated, remaining, and total quantities
- 🗂 Upload and manage allocation letters (PDFs)
- 📊 Generate filtered reports (by Circle, Division, Sub-Division)
- 📤 Export report data as Excel (CSV) file
- ✅ Dynamic dropdowns for Circle → Division → Sub-Division
- 🛡 Validations for meaningful input and secure file uploads

## 🛠️ Technologies Used

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Tools:** VS Code, XAMPP

## 🗃️ Database Tables

- `allocation`: Stores mobile allocations
- `stock`: Maintains mobile stock
- `users`: for login and user control


## 🚀 How to Run Locally

1. Clone the repo
2. Start XAMPP and import `mobile_allocation_system.sql` in phpMyAdmin
3. Place the project folder in `htdocs/`
4. Navigate to `http://localhost/mobile_allocation_system/`

## 📁 Folder Structure


📁 mobile_allocation_system/
├── conn.php
├── header.php
├── stock.php
├── allocate.php
├── report.php
├── uploads/
├── js/
├── css/
└── README.md


## 📌 Future Improvements

- Role-based login system (Admin, Circle Officer)
- Pagination and filters on reports
- PDF export for report
- Full MERN stack version (in progress)

## 🙋‍♀️ About Me

I’m Noor ul Ain, a final-year Software Engineering student with a passion for frontend development and MERN stack. This project helped me understand real-world software systems and database design in a utility company setup.

📧 Email: [noorulain62423@gmail.com]  
🔗 GitHub: [https://github.com/Noorulain32]  
🔗 LinkedIn: [(https://www.linkedin.com/in/-noor-ul-ain-/)]

---

## ⭐ Star this repo if you find it helpful!
