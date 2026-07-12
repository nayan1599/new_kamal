<?php
 

include './config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Safe assign (empty হলে NULL)
    $name       = $_POST['name'] ?? '';
    $phone      = $_POST['phone'] ?? '';
    $car_number = $_POST['car_number'] ?? null;
    $chassis    = $_POST['chassis_number'] ?? null;
    $total_kisti= $_POST['total_kisti'] ?? null;
    $kisti_amt  = $_POST['kisti_amount'] ?? null;
    $j_name     = $_POST['jabin_name'] ?? null;
    $j_phone    = $_POST['jabin_phone'] ?? null;
    $call_status= $_POST['call_status'] ?? null;
    $j_call     = $_POST['jabin_call_status'] ?? null;
    $due        = $_POST['due_amount'] ?? null;
    $followup   = $_POST['next_followup_date'] ?? null;
    $promise    = $_POST['promise_date'] ?? null;
    $attempt    = $_POST['call_attempt'] ?? 1;
    $category   = $_POST['call_category'] ?? null;
    $note       = $_POST['note'] ?? null;

    $stmt = $pdo->prepare("
        INSERT INTO call_stories 
        (name, phone, car_number, chassis_number, total_kisti, kisti_amount,
         jabin_name, jabin_phone, call_status, jabin_call_status, due_amount,
         next_followup_date, promise_date, call_attempt, call_category, note)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

 $stmt->execute([
       $name,
        $phone,
         $car_number,
         $chassis,
         
        $total_kisti, $kisti_amt,
        $j_name, $j_phone, $call_status, $j_call, $due,
        $followup, $promise, $attempt, $category, $note
]);   
 
  
}
?>