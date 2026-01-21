<?php
/**
 * Restaurant Booking System
 * @author Afzal Khan
 */
$conn = new mysqli("localhost", "root", "");
$conn->query("CREATE DATABASE IF NOT EXISTS booking_system_db");
$conn->select_db("booking_system_db");

$conn->query("CREATE TABLE IF NOT EXISTS time_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slot_date DATE NOT NULL,
    slot_time TIME NOT NULL,
    max_guests INT DEFAULT 4,
    available INT DEFAULT 4,
    UNIQUE KEY unique_slot (slot_date, slot_time)
)");

$conn->query("CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slot_id INT,
    guest_name VARCHAR(100) NOT NULL,
    guest_email VARCHAR(100),
    guest_phone VARCHAR(20),
    party_size INT DEFAULT 2,
    special_requests TEXT,
    status ENUM('confirmed', 'cancelled') DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (slot_id) REFERENCES time_slots(id)
)");

// Generate slots for next 7 days if not exists
for ($d = 0; $d < 7; $d++) {
    $date = date('Y-m-d', strtotime("+$d days"));
    foreach (['12:00', '13:00', '14:00', '18:00', '19:00', '20:00', '21:00'] as $time) {
        $conn->query("INSERT IGNORE INTO time_slots (slot_date, slot_time) VALUES ('$date', '$time')");
    }
}

$message = '';
$selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Handle booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book'])) {
    $slotId = (int)$_POST['slot_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $party = (int)$_POST['party_size'];
    $notes = $conn->real_escape_string($_POST['notes']);
    
    // Check availability
    $slot = $conn->query("SELECT * FROM time_slots WHERE id = $slotId AND available >= $party")->fetch_assoc();
    if ($slot) {
        $conn->query("INSERT INTO bookings (slot_id, guest_name, guest_email, guest_phone, party_size, special_requests) VALUES ($slotId, '$name', '$email', '$phone', $party, '$notes')");
        $conn->query("UPDATE time_slots SET available = available - $party WHERE id = $slotId");
        $message = 'success';
    } else {
        $message = 'error';
    }
}

$slots = $conn->query("SELECT * FROM time_slots WHERE slot_date = '$selectedDate' ORDER BY slot_time");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TableBook - Restaurant Reservations</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #faf5f0; min-height: 100vh; }
        .header { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: white; padding: 20px; text-align: center; }
        .header h1 { font-family: 'Playfair Display', serif; font-size: 2rem; margin-bottom: 5px; }
        .header p { opacity: 0.8; }
        .container { max-width: 900px; margin: 0 auto; padding: 30px 20px; }
        .date-selector { display: flex; gap: 10px; overflow-x: auto; padding: 10px 0; margin-bottom: 30px; }
        .date-btn { padding: 15px 20px; background: white; border: 2px solid #e5e7eb; border-radius: 12px; cursor: pointer; text-align: center; min-width: 80px; transition: all 0.2s; text-decoration: none; color: inherit; }
        .date-btn:hover, .date-btn.active { border-color: #d4a574; background: #d4a574; color: white; }
        .date-btn .day { font-size: 0.8rem; color: #6b7280; }
        .date-btn.active .day { color: rgba(255,255,255,0.8); }
        .date-btn .num { font-size: 1.5rem; font-weight: 700; }
        .slots-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .slot-card { background: white; padding: 20px; border-radius: 12px; text-align: center; border: 2px solid transparent; cursor: pointer; transition: all 0.2s; }
        .slot-card:hover { border-color: #d4a574; transform: translateY(-2px); }
        .slot-card.selected { border-color: #d4a574; background: #fef7f0; }
        .slot-card.unavailable { opacity: 0.5; cursor: not-allowed; }
        .slot-time { font-size: 1.5rem; font-weight: 700; color: #1a1a2e; }
        .slot-avail { font-size: 0.9rem; color: #10b981; margin-top: 5px; }
        .slot-avail.low { color: #f59e0b; }
        .booking-form { background: white; padding: 30px; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: none; }
        .booking-form.show { display: block; }
        .booking-form h3 { margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 500; color: #374151; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 1rem; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #d4a574; }
        .btn { padding: 14px 28px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 1rem; }
        .btn-primary { background: #d4a574; color: white; }
        .btn-block { width: 100%; }
        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .back-link { display: block; text-align: center; margin-top: 30px; color: #6b7280; text-decoration: none; }
    </style>
</head>
<body>
    <header class="header">
        <h1><i class="fas fa-utensils"></i> TableBook</h1>
        <p>Reserve your perfect dining experience</p>
    </header>

    <div class="container">
        <?php if ($message === 'success'): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> Booking confirmed! We'll see you soon.</div>
        <?php elseif ($message === 'error'): ?>
            <div class="alert alert-error"><i class="fas fa-times-circle"></i> Sorry, this slot is no longer available.</div>
        <?php endif; ?>

        <h2 style="margin-bottom: 15px;">Select a Date</h2>
        <div class="date-selector">
            <?php for ($d = 0; $d < 7; $d++): 
                $date = date('Y-m-d', strtotime("+$d days"));
                $dayName = date('D', strtotime($date));
                $dayNum = date('j', strtotime($date));
            ?>
                <a href="?date=<?php echo $date; ?>" class="date-btn <?php echo $date === $selectedDate ? 'active' : ''; ?>">
                    <div class="day"><?php echo $dayName; ?></div>
                    <div class="num"><?php echo $dayNum; ?></div>
                </a>
            <?php endfor; ?>
        </div>

        <h2 style="margin-bottom: 15px;">Available Times</h2>
        <div class="slots-grid">
            <?php while ($slot = $slots->fetch_assoc()): 
                $available = $slot['available'];
                $availClass = $available <= 2 ? 'low' : '';
            ?>
                <div class="slot-card <?php echo $available <= 0 ? 'unavailable' : ''; ?>" 
                     onclick="<?php echo $available > 0 ? "selectSlot({$slot['id']}, '{$slot['slot_time']}')" : ''; ?>">
                    <div class="slot-time"><?php echo date('g:i A', strtotime($slot['slot_time'])); ?></div>
                    <div class="slot-avail <?php echo $availClass; ?>">
                        <?php echo $available > 0 ? "$available seats left" : 'Fully booked'; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <form method="POST" class="booking-form" id="bookingForm">
            <input type="hidden" name="slot_id" id="slotId">
            <h3><i class="fas fa-calendar-check"></i> Complete Your Reservation</h3>
            <p style="margin-bottom: 20px; color: #6b7280;">Selected time: <strong id="selectedTime"></strong></p>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" required placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label>Party Size *</label>
                    <select name="party_size" required>
                        <option value="1">1 Guest</option>
                        <option value="2" selected>2 Guests</option>
                        <option value="3">3 Guests</option>
                        <option value="4">4 Guests</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="john@example.com">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone" placeholder="+92 300 1234567">
                </div>
            </div>
            <div class="form-group">
                <label>Special Requests</label>
                <textarea name="notes" rows="3" placeholder="Allergies, occasion, seating preference..."></textarea>
            </div>
            <button type="submit" name="book" class="btn btn-primary btn-block">
                <i class="fas fa-check"></i> Confirm Reservation
            </button>
        </form>

        <a href="../../index.html" class="back-link"><i class="fas fa-arrow-left"></i> Back to Portfolio</a>
    </div>

    <script>
        function selectSlot(id, time) {
            document.querySelectorAll('.slot-card').forEach(c => c.classList.remove('selected'));
            event.target.closest('.slot-card').classList.add('selected');
            document.getElementById('slotId').value = id;
            document.getElementById('selectedTime').textContent = new Date('2000-01-01T' + time).toLocaleTimeString('en-US', {hour: 'numeric', minute: '2-digit'});
            document.getElementById('bookingForm').classList.add('show');
            document.getElementById('bookingForm').scrollIntoView({behavior: 'smooth'});
        }
    </script>
</body>
</html>
