# SMART COLLEGE EVENT PASSPORT (SmartPass)

**Tagline:** Discover. Participate. Collect. Achieve.

A centralized college event participation platform where students discover events, register, attend, earn digital stamps, build a personal digital passport, unlock achievements, and compete on a leaderboard. Administrators manage events, verify attendance, and view reports.

Built as a **Web Technologies** academic project demonstrating HTML5, CSS3, JavaScript, PHP, MySQL, XML, DTD, XSLT, sessions, forms, validation, and CRUD operations.

---

## Table of Contents
1. [Folder Structure](#folder-structure)
2. [How to Run (XAMPP)](#how-to-run-xampp)
3. [Demo Login Credentials](#demo-login-credentials)
4. [ER Diagram (Description)](#er-diagram-description)
5. [Data Flow](#data-flow)
6. [WT Technology Mapping](#wt-technology-mapping)
7. [Feature List](#feature-list)
8. [Security Measures](#security-measures)
9. [Viva Questions & Answers](#viva-questions--answers)

---

## Folder Structure

```
smart-event-passport/
│
├── index.php              # Homepage (hero, stats, featured events, how-it-works)
├── about.php              # About page (problem, solution, features)
├── events.php             # Public events listing with search + category filter
├── event-details.php      # Single event details + registration
├── login.php              # Student + Admin login (role toggle)
├── register.php           # Student registration
├── logout.php             # Session destroy + redirect
├── xml-events.php         # XML→DTD→XSLT pipeline rendered via PHP XSLTProcessor
│
├── css/
│   └── style.css          # Complete stylesheet (beige/cream/brown theme)
├── js/
│   └── script.js          # Nav toggle, password toggle, tabs, toasts, validation
│
├── includes/
│   ├── db.php             # MySQLi database connection (reusable)
│   ├── auth.php           # Session helpers, role checks, login guards
│   ├── functions.php      # Shared helpers (seats, stamps, achievements, leaderboard)
│   ├── header.php         # Public site header (navbar)
│   ├── footer.php         # Public site footer
│   ├── dashboard_header.php  # Sidebar layout for student & admin dashboards
│   ├── dashboard_footer.php  # Dashboard footer
│   └── generate_hash.php  # Utility to generate bcrypt password hashes
│
├── student/
│   ├── dashboard.php      # Student dashboard (stats, upcoming, activity, progress)
│   ├── events.php         # Discover events (search + filter)
│   ├── my-events.php      # Registered events (upcoming + completed tabs)
│   ├── passport.php       # Digital Event Passport (stamps grid, locked stamps, progress)
│   ├── achievements.php   # Achievement badges with progress bars
│   ├── leaderboard.php    # Leaderboard (overall / month / department filters)
│   └── profile.php        # View + edit profile, change password
│
├── admin/
│   ├── dashboard.php      # Admin dashboard (stats, quick actions, recent activity)
│   ├── events.php         # Manage events (list + delete)
│   ├── add-event.php      # Create event form (CRUD - Create)
│   ├── edit-event.php     # Edit event form (CRUD - Update)
│   ├── students.php       # View/search student records
│   ├── registrations.php  # View all registrations (filter by event)
│   ├── attendance.php     # Verify attendance → awards stamps automatically
│   └── reports.php        # Statistics, category-wise bars, attendance rate
│
├── xml/
│   ├── events.xml         # Event data in XML format
│   ├── events.dtd         # DTD defining valid structure of events.xml
│   └── events.xsl         # XSLT stylesheet transforming XML → HTML table
│
└── database/
    └── smartpass.sql      # Complete MySQL schema + sample data
```

---

## How to Run (XAMPP)

1. **Install XAMPP** and start **Apache** and **MySQL** from the XAMPP Control Panel.

2. **Copy the project** into the htdocs folder:
   - Place the entire `smart-event-passport` folder inside `C:\xampp\htdocs\` (Windows) or `/Applications/XAMPP/htdocs/` (Mac).

3. **Create the database:**
   - Open `http://localhost/phpmyadmin`
   - Click **Import**
   - Choose `database/smartpass.sql`
   - Click **Go** — this creates the `smartpass` database with all tables and sample data.

4. **Open the app:**
   - Visit `http://localhost/smart-event-passport/index.php`

> If you placed the folder at a different path, update the `base_url()` function in `includes/auth.php` to match (e.g., `/smart-event-passport/`).

### Database Configuration
The default connection settings in `includes/db.php` are:
- Host: `localhost`
- User: `root`
- Password: `` (empty — XAMPP default)
- Database: `smartpass`

If your MySQL has a different password, edit `includes/db.php`.

---

## Demo Login Credentials

### Admin Account
| Field    | Value                  |
|----------|------------------------|
| Email    | `admin@smartpass.edu`  |
| Password | `admin123`             |

### Student Accounts (all share the same password: `password123`)
| Name           | Email                   | Department | Register No. |
|----------------|-------------------------|------------|--------------|
| Rahul Sharma   | rahul@smartpass.edu     | CSE        | CS21B001     |
| Ananya Verma   | ananya@smartpass.edu    | ECE        | EC21B014     |
| Teja Reddy     | teja@smartpass.edu      | CSE        | CS22B023     |
| Priya Nair     | priya@smartpass.edu     | IT         | IT21B009     |
| Karan Mehta    | karan@smartpass.edu     | MECH       | ME22B031     |

> You can also register new student accounts via the Register page.

---

## ER Diagram Description

```
┌──────────┐        ┌──────────────────┐        ┌────────┐
│ STUDENTS │        │  REGISTRATIONS   │        │ EVENTS │
│──────────│        │──────────────────│        │────────│
│ student_id│──┐    │ registration_id  │    ┌──│event_id│
│ name      │  └──< │ student_id (FK)  │ >──┘  │ name   │
│ register# │       │ event_id (FK)    │       │ category│
│ email     │       │ reg_date         │       │ date   │
│ dept      │       │ status           │       │ venue  │
│ year      │       └──────────────────┘       │ capacity│
│ password  │                                  └────────┘
└──────────┘                                       ▲
     │                                             │
     │    ┌──────────┐   ┌──────────────────┐     │
     ├──< │ATTENDANCE│   │     STAMPS       │>──┘
     │    │──────────│   │──────────────────│
     │    │att_id    │   │ stamp_id         │
     │    │student_id│   │ student_id (FK)  │
     │    │event_id  │   │ event_id (FK)    │
     │    │status    │   │ earned_date      │
     │    └──────────┘   └──────────────────┘
     │
     │    ┌───────────────────────┐   ┌──────────────┐
     └──< │ STUDENT_ACHIEVEMENTS  │ >─│ ACHIEVEMENTS │
          │───────────────────────│   │──────────────│
          │ id                    │   │ ach_id       │
          │ student_id (FK)       │   │ code         │
          │ achievement_id (FK)   │   │ name         │
          │ unlocked_date         │   │ description  │
          └───────────────────────┘   └──────────────┘

┌──────────┐
│  ADMINS  │   (independent table for admin login)
│──────────│
│ admin_id │
│ name     │
│ email    │
│ password │
└──────────┘
```

**Relationships:**
- One student → many registrations → one event each
- One student → many attendance records → one event each
- One student → many stamps → one event each (unique per student-event)
- One student → many student_achievements → one achievement each
- All foreign keys use `ON DELETE CASCADE`

---

## Data Flow

### Complete Workflow
```
ADMIN:  Login → Create Event → Event Published
                                    ↓
STUDENT: Register → Login → Dashboard → Discover Event → Register for Event
                                    ↓
        Event Takes Place → Admin Verifies Attendance
                                    ↓
        Stamp Automatically Awarded → Passport Updated
                                    ↓
        Achievement Progress Recomputed → Leaderboard Updated
```

### Attendance → Stamp → Achievement → Leaderboard Chain
When admin marks a student **Present** on `admin/attendance.php`:
1. An `attendance` record is inserted (or updated) with `status = 'present'`
2. `award_stamp()` inserts a `stamps` row (idempotent — prevents duplicates)
3. `recompute_achievements()` checks all 6 achievement rules and awards any newly-unlocked ones via `INSERT IGNORE`
4. The leaderboard automatically reflects the new stamp count (it's computed live via `COUNT(stamps)`)

When admin marks a student **Absent**, the stamp is removed and achievements are recomputed on next present marking.

---

## WT Technology Mapping

| Technology           | Where it is used                                              |
|----------------------|---------------------------------------------------------------|
| **HTML5**            | All `.php` pages produce HTML5 markup                         |
| **CSS3**             | `css/style.css` — flexbox, grid, gradients, transitions, media queries |
| **JavaScript**       | `js/script.js` — nav toggle, password toggle, tabs, toasts, form validation, category pills |
| **PHP**              | All backend logic — auth, CRUD, registration, attendance, stamps |
| **MySQL**            | `database/smartpass.sql` — 8 tables with foreign keys         |
| **XML**              | `xml/events.xml` — structured event data                      |
| **DTD**              | `xml/events.dtd` — validates the structure of events.xml      |
| **XSLT**             | `xml/events.xsl` — transforms XML into an HTML table; rendered server-side via `xml-events.php` |
| **Form Handling**    | Register, Login, Add/Edit Event, Profile, Password change    |
| **Client-side Validation** | JS validation on register & password forms             |
| **Server-side Validation** | PHP validation on all form submissions (register, events) |
| **Sessions**         | `$_SESSION` for student & admin login state                   |
| **Cookies**          | PHP session cookie (session ID)                               |
| **CRUD Operations**  | Events (create/read/update/delete), Students (read), Registrations (create/read), Attendance (create/update) |
| **Authentication**   | Student + Admin login with password_hash / password_verify    |
| **Database Connectivity** | MySQLi in `includes/db.php` (reusable singleton)       |
| **Search & Filtering** | Events search by name + category filter (PHP + JS)          |
| **Dynamic Content**  | All dashboard data, event cards, leaderboard, stamps pulled from DB |
| **Prepared Statements** | All SQL queries use `bind_param()` to prevent SQL injection |

---

## Feature List

### Student Features
- Register, Login, Logout (with password hashing)
- Dashboard with greeting, stat cards, upcoming events, recent activity, passport progress, achievement preview, leaderboard preview
- Discover events with search + category filter
- View event details and register (with capacity + duplicate checks)
- My Events page (upcoming + completed tabs with attendance status)
- Digital Event Passport — earned stamps grid, locked stamps, progress bar
- Achievements page — 6 achievements with unlocked/locked states and progress bars
- Leaderboard — filter by Overall / This Month / Department, highlights your rank
- Profile — view stats, edit profile, change password

### Admin Features
- Login, Logout
- Dashboard with stats, quick actions, recent events & registrations
- Event CRUD — create, view, edit, delete (with confirm dialog)
- View/search students
- View registrations (filter by event)
- Verify attendance — mark Present/Absent (auto-awards stamps)
- Reports — totals, most popular event, most active student, category-wise bars, attendance rate

---

## Security Measures
- **Password hashing:** `password_hash()` with bcrypt + `password_verify()`
- **Prepared statements:** All queries use `bind_param()` (no SQL injection)
- **Input validation:** Both client-side (JS) and server-side (PHP)
- **Session checks:** `require_student()` / `require_admin()` guards on every protected page
- **Role-based access:** Students cannot access `/admin/*`, admins cannot access `/student/*`
- **HTML escaping:** All dynamic output uses `htmlspecialchars()` via the `e()` helper
- **Duplicate prevention:** Unique constraints on registrations, attendance, stamps, and student_achievements
- **DB credentials:** Stored only in `includes/db.php`, never in frontend files

---

## Viva Questions & Answers

**Q1: What is SmartPass and what problem does it solve?**
A: SmartPass is a centralized college event participation platform. The problem is that college events are tracked across paper registers and disconnected systems — students have no record of what they attended. SmartPass digitizes the entire flow: discovery → registration → verified attendance → digital stamps → passport → achievements → leaderboard.

**Q2: What technologies are used and where?**
A: HTML5 for page structure, CSS3 for the beige/cream/brown responsive design, JavaScript for client-side interactions (nav toggle, password toggle, tabs, toasts, form validation), PHP for server-side logic (auth, CRUD, attendance processing), MySQL for the database (8 tables with foreign keys), and XML/DTD/XSLT for representing and transforming event data.

**Q3: How does the digital stamp system work?**
A: When an admin marks a student as "Present" for an event on the attendance page, PHP calls `award_stamp()` which inserts a row into the `stamps` table (with a unique constraint to prevent duplicates). Then `recompute_achievements()` runs to check if any new achievements should be unlocked. The leaderboard updates automatically because it's computed live by counting stamp rows.

**Q4: How do you prevent duplicate registrations?**
A: The `registrations` table has a `UNIQUE KEY (student_id, event_id)` constraint, and the PHP code checks for existing registration before inserting. The `INSERT IGNORE` pattern is used for stamps and achievements for the same reason.

**Q5: How is authentication implemented?**
A: PHP sessions store `student_id` or `admin_id` after login. Passwords are hashed using `password_hash()` (bcrypt) and verified with `password_verify()`. Every protected page calls `require_student()` or `require_admin()` which redirects unauthorized users to the login page.

**Q6: What is the difference between client-side and server-side validation in your project?**
A: Client-side validation (JavaScript) checks password match and length before form submission for instant feedback. Server-side validation (PHP) re-checks all fields on submission — required fields, email format, register number format, uniqueness — because client-side validation can be bypassed.

**Q7: How are prepared statements used?**
A: Every database query that accepts user input uses `$stmt = db()->prepare($sql)` followed by `$stmt->bind_param()` with type indicators (e.g., 'sssi' for string, string, string, integer). This separates the SQL structure from the data, preventing SQL injection.

**Q8: What is the purpose of the XML/DTD/XSLT files?**
A: `events.xml` stores event data in a structured XML format. `events.dtd` defines the valid structure (which elements are allowed and in what order) — this is validated server-side using `DOMDocument::validate()`. `events.xsl` is an XSLT stylesheet that transforms the XML into an HTML table. The `xml-events.php` page runs this pipeline using PHP's `XSLTProcessor` and displays the result.

**Q9: How is the leaderboard generated?**
A: A SQL query joins `students` with `stamps`, groups by student, counts stamps, and orders descending. The result is rendered as a ranked table. Filters for "This Month" add a date condition, and "Department" adds a WHERE clause on the department field.

**Q10: What are the 6 achievements and how are they triggered?**
A: (1) First Step — attend 1 event, (2) Event Explorer — attend 5, (3) Campus Champion — attend 10, (4) Tech Enthusiast — attend 5 Technical events, (5) Culture Lover — attend 5 Cultural events, (6) All-Rounder — attend events from 3 different categories. They are checked by `recompute_achievements()` after every attendance update.

**Q11: How does the responsive design work?**
A: CSS media queries at three breakpoints (980px, 768px, 480px) adjust the layout. On mobile, the navigation collapses into a toggle menu, the dashboard sidebar slides off-screen with a hamburger toggle, grids stack into single columns, and the passport stamps reduce to 2 columns.

**Q12: What is the role of cookies in your project?**
A: PHP sessions use a cookie (PHPSESSID) to store the session ID on the client. This allows the server to identify the logged-in user across page loads without re-authenticating. No other cookies are set.

**Q13: How do you handle the case where an event is full?**
A: The `seats_remaining()` function subtracts the count of registrations from the event capacity. If it reaches zero, the register button is replaced with an "Event Full" badge and the PHP backend rejects the registration.

**Q14: What happens if a student is marked Absent after being marked Present?**
A: The attendance record is updated to 'absent' and the corresponding stamp is deleted from the `stamps` table. Achievements are recomputed on the next "Present" marking. This keeps the data consistent.

**Q15: Why did you use MySQLi instead of PDO?**
A: MySQLi is well-suited for MySQL-specific projects and is commonly taught in Web Technologies courses. It supports prepared statements, parameter binding, and is lightweight. The connection is a reusable singleton in `db.php`.
