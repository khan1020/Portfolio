<?php
/**
 * Quiz Application with Timer
 * @author Afzal Khan
 */
session_start();
$conn = new mysqli("localhost", "root", "");
$conn->query("CREATE DATABASE IF NOT EXISTS quiz_app_db");
$conn->select_db("quiz_app_db");

$conn->query("CREATE TABLE IF NOT EXISTS quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    time_limit INT DEFAULT 300,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT,
    question TEXT NOT NULL,
    option_a VARCHAR(255),
    option_b VARCHAR(255),
    option_c VARCHAR(255),
    option_d VARCHAR(255),
    correct_answer CHAR(1),
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
)");

$conn->query("CREATE TABLE IF NOT EXISTS scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT,
    player_name VARCHAR(100),
    score INT,
    total INT,
    time_taken INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Sample data
$check = $conn->query("SELECT COUNT(*) as c FROM quizzes")->fetch_assoc();
if ($check['c'] == 0) {
    $conn->query("INSERT INTO quizzes (title, description, time_limit) VALUES ('Web Development Basics', 'Test your knowledge of HTML, CSS, and JavaScript', 300)");
    $conn->query("INSERT INTO quizzes (title, description, time_limit) VALUES ('PHP Fundamentals', 'Basics of PHP programming', 240)");
    
    $questions = [
        [1, 'What does HTML stand for?', 'Hyper Text Markup Language', 'High Tech Modern Language', 'Hyper Transfer Markup Language', 'Home Tool Markup Language', 'A'],
        [1, 'Which CSS property changes text color?', 'font-color', 'text-color', 'color', 'text-style', 'C'],
        [1, 'Which symbol is used for comments in JavaScript?', '/* */', '// or /* */', '# #', '<!-- -->', 'B'],
        [1, 'What is the correct way to link an external CSS file?', '<style src="style.css">', '<link rel="stylesheet" href="style.css">', '<css href="style.css">', '<stylesheet>style.css</stylesheet>', 'B'],
        [1, 'Which HTML tag is used for the largest heading?', '<heading>', '<h6>', '<h1>', '<head>', 'C'],
        [2, 'What does PHP stand for?', 'Personal Home Page', 'PHP: Hypertext Preprocessor', 'Private Home Page', 'Public Hypertext Protocol', 'B'],
        [2, 'Which symbol starts a PHP variable?', '@', '#', '$', '&', 'C'],
        [2, 'Which function outputs text in PHP?', 'print()', 'echo', 'output()', 'Both A and B', 'D'],
        [2, 'What is the correct way to end a PHP statement?', '.', ';', ':', ',', 'B'],
    ];
    foreach ($questions as $q) {
        $conn->query("INSERT INTO questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_answer) VALUES ({$q[0]}, '{$q[1]}', '{$q[2]}', '{$q[3]}', '{$q[4]}', '{$q[5]}', '{$q[6]}')");
    }
}

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$quizId = isset($_GET['quiz']) ? (int)$_GET['quiz'] : 0;

// Handle quiz submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    $quizId = (int)$_POST['quiz_id'];
    $name = $conn->real_escape_string($_POST['player_name'] ?? 'Anonymous');
    $timeTaken = (int)$_POST['time_taken'];
    
    $questions = $conn->query("SELECT id, correct_answer FROM questions WHERE quiz_id = $quizId");
    $score = 0;
    $total = $questions->num_rows;
    
    while ($q = $questions->fetch_assoc()) {
        $answer = isset($_POST['q_' . $q['id']]) ? $_POST['q_' . $q['id']] : '';
        if (strtoupper($answer) === $q['correct_answer']) $score++;
    }
    
    $conn->query("INSERT INTO scores (quiz_id, player_name, score, total, time_taken) VALUES ($quizId, '$name', $score, $total, $timeTaken)");
    $_SESSION['result'] = ['score' => $score, 'total' => $total, 'name' => $name];
    header("Location: index.php?page=result&quiz=$quizId");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuizMaster - Test Your Knowledge</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); min-height: 100vh; padding: 40px 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { background: white; border-radius: 20px; padding: 40px; box-shadow: 0 25px 50px rgba(0,0,0,0.2); }
        .header { text-align: center; margin-bottom: 30px; }
        .header i { font-size: 3rem; color: #6366f1; margin-bottom: 15px; }
        .header h1 { font-size: 2rem; color: #1f2937; }
        .quiz-list { display: grid; gap: 20px; margin-top: 30px; }
        .quiz-item { padding: 25px; border: 2px solid #e5e7eb; border-radius: 12px; cursor: pointer; transition: all 0.2s; }
        .quiz-item:hover { border-color: #6366f1; transform: translateY(-3px); }
        .quiz-item h3 { margin-bottom: 5px; }
        .quiz-item p { color: #6b7280; font-size: 0.9rem; }
        .quiz-meta { display: flex; gap: 15px; margin-top: 10px; font-size: 0.85rem; color: #9ca3af; }
        .timer { position: fixed; top: 20px; right: 20px; background: #ef4444; color: white; padding: 15px 25px; border-radius: 12px; font-size: 1.5rem; font-weight: 700; }
        .question-card { margin-bottom: 25px; padding: 25px; background: #f9fafb; border-radius: 12px; }
        .question-text { font-size: 1.1rem; font-weight: 600; margin-bottom: 20px; }
        .options { display: grid; gap: 12px; }
        .option { padding: 15px 20px; border: 2px solid #e5e7eb; border-radius: 10px; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 12px; }
        .option:hover { border-color: #6366f1; }
        .option input { display: none; }
        .option.selected { border-color: #6366f1; background: #eef2ff; }
        .option-letter { width: 30px; height: 30px; background: #e5e7eb; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; }
        .option.selected .option-letter { background: #6366f1; color: white; }
        .btn { padding: 15px 30px; background: #6366f1; color: white; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 10px; text-decoration: none; }
        .btn:hover { background: #4f46e5; }
        .btn-block { width: 100%; justify-content: center; }
        .result-card { text-align: center; }
        .result-score { font-size: 5rem; font-weight: 700; color: #6366f1; }
        .result-text { font-size: 1.5rem; margin: 15px 0; }
        .leaderboard { margin-top: 30px; }
        .leaderboard h3 { margin-bottom: 15px; }
        .leader-item { display: flex; justify-content: space-between; padding: 12px; background: #f9fafb; border-radius: 8px; margin-bottom: 8px; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: rgba(255,255,255,0.8); text-decoration: none; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
        .form-group input { width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($page === 'home'): ?>
            <div class="card">
                <div class="header">
                    <i class="fas fa-brain"></i>
                    <h1>QuizMaster</h1>
                    <p style="color:#6b7280; margin-top:10px;">Test your knowledge with our interactive quizzes</p>
                </div>
                <div class="quiz-list">
                    <?php 
                    $quizzes = $conn->query("SELECT q.*, (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as q_count FROM quizzes q");
                    while ($quiz = $quizzes->fetch_assoc()): ?>
                        <a href="index.php?page=start&quiz=<?php echo $quiz['id']; ?>" class="quiz-item" style="text-decoration:none; color:inherit;">
                            <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                            <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                            <div class="quiz-meta">
                                <span><i class="fas fa-question-circle"></i> <?php echo $quiz['q_count']; ?> Questions</span>
                                <span><i class="fas fa-clock"></i> <?php echo floor($quiz['time_limit']/60); ?> min</span>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php elseif ($page === 'start'): 
            $quiz = $conn->query("SELECT * FROM quizzes WHERE id = $quizId")->fetch_assoc();
        ?>
            <div class="card">
                <div class="header">
                    <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
                </div>
                <form method="POST" action="index.php?page=quiz&quiz=<?php echo $quizId; ?>">
                    <div class="form-group">
                        <label>Enter Your Name</label>
                        <input type="text" name="player_name" required placeholder="Your name">
                    </div>
                    <button type="submit" name="start" class="btn btn-block"><i class="fas fa-play"></i> Start Quiz</button>
                </form>
            </div>
        <?php elseif ($page === 'quiz'):
            $quiz = $conn->query("SELECT * FROM quizzes WHERE id = $quizId")->fetch_assoc();
            $questions = $conn->query("SELECT * FROM questions WHERE quiz_id = $quizId");
            $playerName = $_POST['player_name'] ?? 'Anonymous';
        ?>
            <div class="timer" id="timer"><?php echo floor($quiz['time_limit']/60); ?>:00</div>
            <div class="card">
                <form method="POST" id="quizForm">
                    <input type="hidden" name="quiz_id" value="<?php echo $quizId; ?>">
                    <input type="hidden" name="player_name" value="<?php echo htmlspecialchars($playerName); ?>">
                    <input type="hidden" name="time_taken" id="timeTaken" value="0">
                    
                    <?php $num = 1; while ($q = $questions->fetch_assoc()): ?>
                        <div class="question-card">
                            <div class="question-text"><?php echo $num++; ?>. <?php echo htmlspecialchars($q['question']); ?></div>
                            <div class="options">
                                <?php foreach (['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'] as $letter => $field): ?>
                                    <label class="option" onclick="this.classList.add('selected'); this.parentNode.querySelectorAll('.option').forEach(o => o !== this && o.classList.remove('selected'))">
                                        <input type="radio" name="q_<?php echo $q['id']; ?>" value="<?php echo $letter; ?>">
                                        <span class="option-letter"><?php echo $letter; ?></span>
                                        <span><?php echo htmlspecialchars($q[$field]); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <button type="submit" name="submit_quiz" class="btn btn-block"><i class="fas fa-check"></i> Submit Quiz</button>
                </form>
            </div>
            <script>
                let timeLimit = <?php echo $quiz['time_limit']; ?>;
                let elapsed = 0;
                const timer = document.getElementById('timer');
                const interval = setInterval(() => {
                    elapsed++;
                    let remaining = timeLimit - elapsed;
                    if (remaining <= 0) {
                        clearInterval(interval);
                        document.getElementById('timeTaken').value = elapsed;
                        document.getElementById('quizForm').submit();
                    }
                    let min = Math.floor(remaining / 60);
                    let sec = remaining % 60;
                    timer.textContent = `${min}:${sec.toString().padStart(2, '0')}`;
                    if (remaining < 60) timer.style.background = '#ef4444';
                }, 1000);
                document.getElementById('quizForm').addEventListener('submit', () => {
                    document.getElementById('timeTaken').value = elapsed;
                });
            </script>
        <?php elseif ($page === 'result'):
            $result = $_SESSION['result'] ?? null;
            $leaderboard = $conn->query("SELECT * FROM scores WHERE quiz_id = $quizId ORDER BY score DESC, time_taken ASC LIMIT 5");
        ?>
            <div class="card result-card">
                <i class="fas fa-trophy" style="font-size:4rem; color:#fbbf24; margin-bottom:20px;"></i>
                <?php if ($result): ?>
                    <div class="result-score"><?php echo $result['score']; ?>/<?php echo $result['total']; ?></div>
                    <div class="result-text">
                        <?php 
                        $percent = ($result['score'] / $result['total']) * 100;
                        if ($percent >= 80) echo "Excellent! 🎉";
                        elseif ($percent >= 60) echo "Good job! 👍";
                        else echo "Keep practicing! 💪";
                        ?>
                    </div>
                <?php endif; ?>
                
                <div class="leaderboard">
                    <h3><i class="fas fa-medal"></i> Leaderboard</h3>
                    <?php while ($l = $leaderboard->fetch_assoc()): ?>
                        <div class="leader-item">
                            <span><?php echo htmlspecialchars($l['player_name']); ?></span>
                            <span><?php echo $l['score']; ?>/<?php echo $l['total']; ?></span>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <a href="index.php" class="btn btn-block" style="margin-top:20px;"><i class="fas fa-redo"></i> Try Another Quiz</a>
            </div>
        <?php endif; ?>
        
        <a href="../../index.html" class="back-link"><i class="fas fa-arrow-left"></i> Back to Portfolio</a>
    </div>
</body>
</html>
