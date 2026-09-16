<?php

require_once __DIR__ . '/../includes/functions.php';

require_admin();


/* =========================================================
   HANDLE QR SCAN REQUEST
========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    header('Content-Type: application/json');

    $studentId = (int)($_POST['student_id'] ?? 0);
    $eventId = (int)($_POST['event_id'] ?? 0);


    if (!$studentId || !$eventId) {

        echo json_encode([
            'success' => false,
            'message' => 'Invalid QR Code.'
        ]);

        exit;
    }


    /* =========================================================
       CHECK IF STUDENT IS REGISTERED FOR THE EVENT
    ========================================================= */

    $stmt = db()->prepare(
        'SELECT registration_id 
         FROM registrations 
         WHERE student_id=? AND event_id=?'
    );

    $stmt->bind_param('ii', $studentId, $eventId);

    $stmt->execute();

    $registration = $stmt->get_result()->fetch_assoc();


    if (!$registration) {

        echo json_encode([
            'success' => false,
            'message' => 'Student is not registered for this event.'
        ]);

        exit;
    }


    /* =========================================================
       CHECK EXISTING ATTENDANCE
    ========================================================= */

    $stmt = db()->prepare(
        'SELECT attendance_id 
         FROM attendance 
         WHERE student_id=? AND event_id=?'
    );

    $stmt->bind_param('ii', $studentId, $eventId);

    $stmt->execute();

    $existing = $stmt->get_result()->fetch_assoc();


    /* =========================================================
       UPDATE / INSERT ATTENDANCE
    ========================================================= */

    if ($existing) {

        $status = 'present';

        $stmt = db()->prepare(
            'UPDATE attendance 
             SET status=? 
             WHERE attendance_id=?'
        );

        $stmt->bind_param(
            'si',
            $status,
            $existing['attendance_id']
        );

        $stmt->execute();

    } else {

        $status = 'present';

        $stmt = db()->prepare(
            'INSERT INTO attendance 
             (student_id, event_id, status) 
             VALUES (?,?,?)'
        );

        $stmt->bind_param(
            'iis',
            $studentId,
            $eventId,
            $status
        );

        $stmt->execute();

        /* Award stamp */

        award_stamp($studentId, $eventId);
    }


    /* =========================================================
       GET STUDENT NAME
    ========================================================= */

    $stmt = db()->prepare(
        'SELECT name 
         FROM students 
         WHERE student_id=?'
    );

    $stmt->bind_param('i', $studentId);

    $stmt->execute();

    $student = $stmt->get_result()->fetch_assoc();


    echo json_encode([

        'success' => true,

        'message' =>
            'Attendance marked Present successfully!',

        'student_name' =>
            $student['name'] ?? 'Student'

    ]);

    exit;
}


/* =========================================================
   NORMAL PAGE
========================================================= */

$dashboardTitle = 'QR Check-in';

$activeItem = 'qr-checkin';

$role = 'admin';


require __DIR__ . '/../includes/dashboard_header.php';

?>


<h2>QR Check-in</h2>

<p>
    Scan the student's Event Pass QR code.
</p>


<!-- RESULT MESSAGE -->

<div
    id="scanResult"
    style="
        display:none;
        margin-bottom:20px;
        padding:15px;
        border-radius:8px;
        font-weight:bold;
    ">
</div>


<!-- QR CAMERA -->

<div id="reader" style="width:500px;"></div>


<script src="https://unpkg.com/html5-qrcode"></script>


<script>


let scanProcessing = false;


/* =========================================================
   QR SCAN SUCCESS
========================================================= */

function onScanSuccess(decodedText) {


    if (scanProcessing) {

        return;

    }


    scanProcessing = true;


    console.log(decodedText);


    /*
    
    EXPECTED QR FORMAT:

    PASS:SP-0001|STUDENT_ID:1|EVENT_ID:1
    
    */


    const studentMatch =
        decodedText.match(/STUDENT_ID:(\d+)/);


    const eventMatch =
        decodedText.match(/EVENT_ID:(\d+)/);


    if (!studentMatch || !eventMatch) {


        showResult(
            "Invalid SmartPass QR Code!",
            false
        );


        scanProcessing = false;

        return;

    }


    const studentId =
        studentMatch[1];


    const eventId =
        eventMatch[1];


    /*
    STOP CAMERA AFTER SUCCESSFUL SCAN
    */


    html5QrCode.stop()


    /*
    SEND DATA TO PHP
    */


    fetch("qr-checkin.php", {

        method: "POST",

        headers: {

            "Content-Type":
                "application/x-www-form-urlencoded"

        },

        body:

            "student_id=" +
            encodeURIComponent(studentId) +

            "&event_id=" +
            encodeURIComponent(eventId)

    })


    .then(response => response.json())


    .then(data => {


        if (data.success) {


            showResult(

                "✅ " +

                data.student_name +

                "<br><br>" +

                data.message +

                "<br><br>" +

                "Student ID: " +
                studentId +

                "<br>" +

                "Event ID: " +
                eventId,

                true

            );


        } else {


            showResult(

                "❌ " +
                data.message,

                false

            );

        }


    })


    .catch(error => {


        console.error(error);


        showResult(

            "❌ Something went wrong while marking attendance.",

            false

        );

    });


}


/* =========================================================
   SHOW RESULT
========================================================= */

function showResult(message, success) {


    const result =

        document.getElementById(
            "scanResult"
        );


    result.style.display =
        "block";


    result.innerHTML =
        message;


    if (success) {


        result.style.background =
            "#d1e7dd";


        result.style.color =
            "#0f5132";


        result.style.border =
            "1px solid #badbcc";


    } else {


        result.style.background =
            "#f8d7da";


        result.style.color =
            "#842029";


        result.style.border =
            "1px solid #f5c2c7";

    }

}


/* =========================================================
   START CAMERA
========================================================= */


const html5QrCode =

    new Html5Qrcode(
        "reader"
    );


Html5Qrcode.getCameras()


.then(cameras => {


    if (cameras.length) {


        html5QrCode.start(


            cameras[0].id,


            {

                fps: 10,

                qrbox: 250

            },


            onScanSuccess


        );

    }


})


.catch(error => {


    console.error(
        "Camera Error:",
        error
    );


});


</script>


<?php

require __DIR__ .
    '/../includes/dashboard_footer.php';

?>