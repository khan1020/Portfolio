# Task Manager

A full-featured task management application with PHP/MySQL backend.

## 🌟 Features

- **Full CRUD** - Create, read, update, delete tasks
- **Categories** - Organize tasks by category (Work, Personal, Study, etc.)
- **Priority Levels** - High, Medium, Low with visual badges
- **Status Tracking** - Pending, In Progress, Completed
- **Due Dates** - Track deadlines with overdue highlighting
- **Quick Toggle** - One-click complete/uncomplete
- **Filters** - Filter by category, status, or priority
- **Statistics** - Dashboard showing task counts

## 🚀 Quick Start

1. **Start XAMPP** - Enable Apache and MySQL
2. **Visit**: `http://localhost/Portfolio/apps/03-task-manager/`
3. **Database** auto-creates with sample tasks

## 📁 Structure

```
03-task-manager/
├── index.php         # Main application
├── database.sql      # Schema + sample data
├── includes/
│   └── db.php        # Database connection
├── css/
│   └── style.css     # Styles
├── js/
│   └── app.js        # Enhancements
└── README.md
```

## 💾 Database Schema

### Categories
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| name | VARCHAR | Category name |
| color | VARCHAR | Hex color code |
| icon | VARCHAR | Font Awesome icon |

### Tasks
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| title | VARCHAR | Task title |
| description | TEXT | Task details |
| category_id | INT | Foreign key |
| priority | ENUM | low/medium/high |
| status | ENUM | pending/in_progress/completed |
| due_date | DATE | Due date |

## ⌨️ Shortcuts

- **Ctrl+N** - Focus on "Add Task" input

---

Built by **Afzal Khan** | January 2026
