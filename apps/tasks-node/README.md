# Task Manager - Node.js App

A simple task management application built with **Node.js**, **Express**, and **JSON storage**.

## Features
- ✅ Create, update, and delete tasks
- 📊 Track task status (Pending, In Progress, Completed)
- 🎯 Set priority levels (Low, Medium, High)
- 📅 Due date tracking
- 💾 JSON file for data persistence (no database setup needed!)

## Installation

1. Install dependencies:
```bash
cd apps/tasks-node
npm install
```

2. Run the app:
```bash
npm start
```

3. Open your browser to `http://localhost:3000`

## Tech Stack
- **Backend:** Node.js + Express.js
- **Storage:** JSON file (no compilation required)
- **Template Engine:** EJS
- **Styling:** Custom CSS

## Project Structure
```
tasks-node/
├── server.js          # Express server
├── package.json       # Dependencies
├── tasks.json        # JSON database (auto-created)
├── views/
│   └── index.ejs     # Main template
└── public/
    └── style.css     # Styles
```

---
**Live Demo:** Run locally on port 3000
**Note:** Works on Windows without Visual Studio Build Tools!
