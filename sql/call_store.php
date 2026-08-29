<?php

include './config/db.php';

// PDO error show করার জন্য
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $name       = trim($_POST['name'] ?? '');
        $phone      = trim($_POST['phone'] ?? '');
        $car_number = trim($_POST['car_number'] ?? '');
        $chassis    = trim($_POST['chassis_number'] ?? '');

        $total_kisti = $_POST['total_kisti'] ?? null;
        $kisti_amt   = $_POST['kisti_amount'] ?? null;

        $j_name  = trim($_POST['jabin_name'] ?? '');
        $j_phone = trim($_POST['jabin_phone'] ?? '');

        $call_status = $_POST['call_status'] ?? null;
        $j_call      = $_POST['jabin_call_status'] ?? null;

        $due = $_POST['due_amount'] ?? null;

        $followup = !empty($_POST['next_followup_date'])
            ? $_POST['next_followup_date']
            : null;

        $promise = !empty($_POST['promise_date'])
            ? $_POST['promise_date']
            : null;

        $attempt  = $_POST['call_attempt'] ?? 1;
        $category = $_POST['call_category'] ?? null;
        $note     = trim($_POST['note'] ?? '');

        $sql = "
            INSERT INTO call_stories
            (
                name,
                phone,
                car_number,
                chassis_number,
                total_kisti,
                kisti_amount,
                jabin_name,
                jabin_phone,
                call_status,
                jabin_call_status,
                due_amount,
                next_followup_date,
                promise_date,
                call_attempt,
                call_category,
                note
            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?
            )
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $name,
            $phone,
            $car_number,
            $chassis,
            $total_kisti,
            $kisti_amt,
            $j_name,
            $j_phone,
            $call_status,
            $j_call,
            $due,
            $followup,
            $promise,
            $attempt,
            $category,
            $note
        ]);

        echo "
            <div style='
                background:#d1fae5;
                color:#065f46;
                padding:15px;
                margin:15px 0;
                border-radius:8px;
                font-weight:bold;
            '>
                কল স্টোরি সফলভাবে Save হয়েছে।
            </div>
        ";

    } catch (PDOException $e) {

        echo "
            <div style='
                background:#fee2e2;
                color:#991b1b;
                padding:15px;
                margin:15px 0;
                border-radius:8px;
            '>
                <strong>Database Error:</strong><br>
                " . htmlspecialchars($e->getMessage()) . "
            </div>
        ";

    } catch (Exception $e) {

        echo "
            <div style='
                background:#fee2e2;
                color:#991b1b;
                padding:15px;
                margin:15px 0;
                border-radius:8px;
            '>
                <strong>Error:</strong><br>
                " . htmlspecialchars($e->getMessage()) . "
            </div>
        ";
    }
}
?>