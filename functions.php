<?php
/**
 * Shared helper functions.
 */
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

const EVENT_CATEGORIES = ['Technical','Cultural','Sports','Workshop','Seminar','Club','Competition'];

/** Count how many seats remain for an event. */
function seats_remaining(int $eventId): int {
    $stmt = db()->prepare('SELECT capacity FROM events WHERE event_id = ?');
    $stmt->bind_param('i', $eventId);
    $stmt->execute();
    $cap = (int)($stmt->get_result()->fetch_assoc()['capacity'] ?? 0);

    $stmt = db()->prepare('SELECT COUNT(*) c FROM registrations WHERE event_id = ?');
    $stmt->bind_param('i', $eventId);
    $stmt->execute();
    $used = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
    return max(0, $cap - $used);
}

/** Number of events a student has attended (stamps). */
function student_stamp_count(int $studentId): int {
    $stmt = db()->prepare('SELECT COUNT(*) c FROM stamps WHERE student_id = ?');
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    return (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
}

/** Distinct categories attended by a student. */
function student_categories_attended(int $studentId): array {
    $stmt = db()->prepare(
        'SELECT DISTINCT e.category FROM stamps s JOIN events e ON s.event_id = e.event_id WHERE s.student_id = ?'
    );
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    return array_column($rows, 'category');
}

/** Distinct categories a student is registered for. */
function student_categories_registered(int $studentId): array {
    $stmt = db()->prepare(
        'SELECT DISTINCT e.category FROM registrations r JOIN events e ON r.event_id = e.event_id WHERE r.student_id = ?'
    );
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    return array_column($rows, 'category');
}

/** Leaderboard rank of a student (1-based). 0 if no stamps. */
function student_rank(int $studentId): int {
    $conn = db();
    $res = $conn->query(
        'SELECT student_id, COUNT(*) AS stamps FROM stamps GROUP BY student_id ORDER BY stamps DESC, student_id ASC'
    );
    $rank = 0;
    if ($res) {
        $r = 1;
        while ($row = $res->fetch_assoc()) {
            if ((int)$row['student_id'] === $studentId) { $rank = $r; break; }
            $r++;
        }
    }
    return $rank;
}

/**
 * Recompute and award achievements for a student.
 * Called after attendance/stamp is granted.
 */
function recompute_achievements(int $studentId): void {
    $stampCount = student_stamp_count($studentId);
    $cats = student_categories_attended($studentId);

    // category-specific counts
    $techCount = 0; $cultCount = 0;
    foreach ($cats as $c) {
        if ($c === 'Technical') $techCount++;
    }
    $stmt = db()->prepare(
        'SELECT COUNT(*) c FROM stamps s JOIN events e ON s.event_id=e.event_id
         WHERE s.student_id=? AND e.category="Cultural"'
    );
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $cultCount = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);

    $stmt = db()->prepare(
        'SELECT COUNT(*) c FROM stamps s JOIN events e ON s.event_id=e.event_id
         WHERE s.student_id=? AND e.category="Technical"'
    );
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $techCount = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);

    $rules = [
        'first_step'      => $stampCount >= 1,
        'event_explorer'  => $stampCount >= 5,
        'campus_champion' => $stampCount >= 10,
        'tech_enthusiast' => $techCount >= 5,
        'culture_lover'   => $cultCount >= 5,
        'all_rounder'     => count($cats) >= 3,
    ];

    foreach ($rules as $code => $met) {
        if (!$met) continue;
        $ach = db()->query("SELECT achievement_id FROM achievements WHERE code='" . db()->real_escape_string($code) . "'")->fetch_assoc();
        if (!$ach) continue;
        $stmt = db()->prepare('INSERT IGNORE INTO student_achievements (student_id, achievement_id) VALUES (?, ?)');
        $stmt->bind_param('ii', $studentId, $ach['achievement_id']);
        $stmt->execute();
    }
}

/** Award a stamp (idempotent) when attendance marked present. */
function award_stamp(int $studentId, int $eventId): void {
    $stmt = db()->prepare('INSERT IGNORE INTO stamps (student_id, event_id) VALUES (?, ?)');
    $stmt->bind_param('ii', $studentId, $eventId);
    $stmt->execute();
    recompute_achievements($studentId);
}

/** Total student count. */
function total_students(): int {
    $r = db()->query('SELECT COUNT(*) c FROM students');
    return $r ? (int)$r->fetch_assoc()['c'] : 0;
}

/** Total event count. */
function total_events(): int {
    $r = db()->query('SELECT COUNT(*) c FROM events');
    return $r ? (int)$r->fetch_assoc()['c'] : 0;
}

/** Total registrations. */
function total_registrations(): int {
    $r = db()->query('SELECT COUNT(*) c FROM registrations');
    return $r ? (int)$r->fetch_assoc()['c'] : 0;
}

/** Total attendance records marked present. */
function total_attendance(): int {
    $r = db()->query("SELECT COUNT(*) c FROM attendance WHERE status='present'");
    return $r ? (int)$r->fetch_assoc()['c'] : 0;
}

/** Total stamps. */
function total_stamps(): int {
    $r = db()->query('SELECT COUNT(*) c FROM stamps');
    return $r ? (int)$r->fetch_assoc()['c'] : 0;
}

/** Fetch featured upcoming events (limit). */
function featured_events(int $limit = 4): array {
    $stmt = db()->prepare('SELECT * FROM events WHERE status="upcoming" ORDER BY event_date ASC LIMIT ?');
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/** Get all achievements. */
function all_achievements(): array {
    $r = db()->query('SELECT * FROM achievements ORDER BY achievement_id ASC');
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

/** Achievements unlocked by a student (codes). */
function student_unlocked_codes(int $studentId): array {
    $stmt = db()->prepare(
        'SELECT a.code FROM student_achievements sa JOIN achievements a ON sa.achievement_id=a.achievement_id WHERE sa.student_id=?'
    );
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    return array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'code');
}

/** Progress for an achievement code for a student. Returns [current, target]. */
function achievement_progress(string $code, int $studentId): array {
    $stamps = student_stamp_count($studentId);
    switch ($code) {
        case 'first_step':      return [min($stamps, 1), 1];
        case 'event_explorer':  return [min($stamps, 5), 5];
        case 'campus_champion': return [min($stamps, 10), 10];
        case 'tech_enthusiast':
            $stmt = db()->prepare('SELECT COUNT(*) c FROM stamps s JOIN events e ON s.event_id=e.event_id WHERE s.student_id=? AND e.category="Technical"');
            $stmt->bind_param('i', $studentId); $stmt->execute();
            $c = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
            return [min($c, 5), 5];
        case 'culture_lover':
            $stmt = db()->prepare('SELECT COUNT(*) c FROM stamps s JOIN events e ON s.event_id=e.event_id WHERE s.student_id=? AND e.category="Cultural"');
            $stmt->bind_param('i', $studentId); $stmt->execute();
            $c = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
            return [min($c, 5), 5];
        case 'all_rounder':
            return [min(count(student_categories_attended($studentId)), 3), 3];
    }
    return [0, 1];
}

/** Build a small icon glyph per category (emoji-free unicode). */
function category_icon(string $cat): string {
    return match ($cat) {
        'Technical'   => '⚙',
        'Cultural'    => '♪',
        'Sports'      => '⚑',
        'Workshop'    => '✎',
        'Seminar'     => '◆',
        'Club'        => '★',
        'Competition' => '🏆',
        default       => '•',
    };
}
