const express = require('express');
const fs = require('fs');
const path = require('path');

const app = express();
const PORT = 3000;
const DB_FILE = path.join(__dirname, 'tasks.json');

// Helper functions for JSON "database"
function readDB() {
    if (!fs.existsSync(DB_FILE)) {
        const initialData = {
            tasks: [
                { id: 1, title: 'Finish portfolio project', description: 'Complete the Node.js task manager', status: 'in-progress', priority: 'high', created_at: new Date().toISOString(), due_date: '2026-01-30' },
                { id: 2, title: 'Learn Express.js', description: 'Deep dive into Express framework', status: 'pending', priority: 'medium', created_at: new Date().toISOString(), due_date: '2026-02-05' },
                { id: 3, title: 'Code review', description: 'Review pull requests from team', status: 'completed', priority: 'low', created_at: new Date().toISOString(), due_date: null }
            ],
            nextId: 4
        };
        fs.writeFileSync(DB_FILE, JSON.stringify(initialData, null, 2));
        return initialData;
    }
    return JSON.parse(fs.readFileSync(DB_FILE, 'utf8'));
}

function writeDB(data) {
    fs.writeFileSync(DB_FILE, JSON.stringify(data, null, 2));
}

// Middleware
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));
app.use(express.static('public'));
app.use(express.urlencoded({ extended: true }));
app.use(express.json());

// Routes
app.get('/', (req, res) => {
    const db = readDB();
    res.render('index', { tasks: db.tasks });
});

app.post('/tasks', (req, res) => {
    const { title, description, priority, due_date } = req.body;
    const db = readDB();

    const newTask = {
        id: db.nextId++,
        title,
        description: description || null,
        status: 'pending',
        priority: priority || 'medium',
        created_at: new Date().toISOString(),
        due_date: due_date || null
    };

    db.tasks.push(newTask);
    writeDB(db);
    res.redirect('/');
});

app.post('/tasks/:id/status', (req, res) => {
    const { status } = req.body;
    const db = readDB();
    const task = db.tasks.find(t => t.id === parseInt(req.params.id));
    if (task) task.status = status;
    writeDB(db);
    res.json({ success: true });
});

app.delete('/tasks/:id', (req, res) => {
    const db = readDB();
    db.tasks = db.tasks.filter(t => t.id !== parseInt(req.params.id));
    writeDB(db);
    res.json({ success: true });
});

app.listen(PORT, () => {
    console.log(`✅ Task Manager running on http://localhost:${PORT}`);
});
