<?php
require_once __DIR__ . '/../includes/functions.php';

require_student();

$student = current_student();
$sid = (int)$student['student_id'];

/*
|--------------------------------------------------------------------------
| Get registered events
|--------------------------------------------------------------------------
*/

$passes = db()->query(
    "SELECT 
        r.registration_id,
        r.registration_date,
        e.event_id,
        e.event_name,
        e.event_date,
        e.event_time,
        e.venue,
        e.category
     FROM registrations r
     JOIN events e ON r.event_id = e.event_id
     WHERE r.student_id = $sid
     ORDER BY e.event_date ASC"
)->fetch_all(MYSQLI_ASSOC);


/*
|--------------------------------------------------------------------------
| Page settings
|--------------------------------------------------------------------------
*/

$dashboardTitle = 'Event Passes';
$activeItem = 'event-passes';
$role = 'student';

require __DIR__ . '/../includes/dashboard_header.php';
?>

<div class="event-passes-page">

    <?php if ($passes): ?>

        <div class="event-pass-grid">

            <?php foreach ($passes as $index => $event): ?>

                <?php

                /*
                |--------------------------------------------------------------------------
                | Generate unique pass ID
                |--------------------------------------------------------------------------
                */

                $passId = 'SP-' . str_pad(
                    $event['registration_id'],
                    4,
                    '0',
                    STR_PAD_LEFT
                );


                /*
                |--------------------------------------------------------------------------
                | QR Code data
                |--------------------------------------------------------------------------
                */

               $qrData =
                    'PASS:' . $passId .
                    '|STUDENT_ID:' . $sid .
                    '|EVENT_ID:' . $event['event_id'];


                /*
                |--------------------------------------------------------------------------
                | Category
                |--------------------------------------------------------------------------
                */

                $category = strtoupper(
                    $event['category'] ?? 'EVENT'
                );

                ?>

                <div class="event-pass-card">

                    <!-- PASS HEADER -->

                    <div class="event-pass-header">

                        <div class="pass-brand">

                            <div class="pass-logo">
                                SP
                            </div>

                            <div>

                                <h2>
                                    SMARTPASS
                                </h2>

                                <span>
                                    EVENT ENTRY PASS
                                </span>

                            </div>

                        </div>


                        <div class="pass-category">

                            <?= e($category) ?>

                        </div>

                    </div>


                    <!-- PASS BODY -->

                    <div class="event-pass-body">


                        <!-- PASS INFORMATION -->

                        <div class="pass-information">

                            <div class="pass-field">

                                <span>STUDENT</span>

                                <strong>
                                    <?= e($student['name']) ?>
                                </strong>

                            </div>


                            <div class="pass-field">

                                <span>REGISTER NO.</span>

                                <strong>

                                    <?= e(
                                        $student['register_no']
                                        ?? $student['student_id']
                                    ) ?>

                                </strong>

                            </div>


                            <div class="pass-field">

                                <span>EVENT</span>

                                <strong>

                                    <?= e($event['event_name']) ?>

                                </strong>

                            </div>


                            <div class="pass-field">

                                <span>DATE</span>

                                <strong>

                                    <?= date(
                                        'd M Y',
                                        strtotime($event['event_date'])
                                    ) ?>

                                    <?php if (!empty($event['event_time'])): ?>

                                        ·
                                        <?= date(
                                            'h:i A',
                                            strtotime($event['event_time'])
                                        ) ?>

                                    <?php endif; ?>

                                </strong>

                            </div>


                            <div class="pass-field">

                                <span>VENUE</span>

                                <strong>

                                    <?= e($event['venue']) ?>

                                </strong>

                            </div>


                            <div class="pass-field">

                                <span>PASS ID</span>

                                <strong class="pass-id">

                                    <?= e($passId) ?>

                                </strong>

                            </div>

                        </div>


                        <!-- QR CODE -->

                        <div class="pass-qr-section">

                            <div class="qr-box">

                                <img
                                    src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=<?= urlencode($qrData) ?>"
                                    alt="Event QR Code"
                                >

                            </div>

                            <span class="qr-text">

                                Scan at entry

                            </span>

                        </div>

                    </div>


                    <!-- PASS FOOTER -->

                    <div class="event-pass-footer">


                        <span class="pass-status">

                            Awaiting Check-in

                        </span>


                        <a
                            href="<?= e(base_url()) ?>event-details.php?id=<?= (int)$event['event_id'] ?>"
                            class="pass-details-link"
                        >

                            Event details →

                        </a>


                    </div>

                </div>

            <?php endforeach; ?>

        </div>


    <?php else: ?>


        <div class="no-passes">

            <div class="no-passes-icon">

                🎫

            </div>

            <h2>
                No Event Passes Yet
            </h2>

            <p>
                Register for an event to receive your event pass.
            </p>

            <a
                href="<?= e(base_url()) ?>student/events.php"
                class="discover-events-btn"
            >

                Discover Events →

            </a>

        </div>


    <?php endif; ?>

</div>


<?php require __DIR__ . '/../includes/dashboard_footer.php'; ?>