<?php
//dg599 12/7
require(__DIR__ . "/../../partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in", "warning");
    die(header("Location: login.php"));
}

$user_id = get_user_id();
$db = getDB();

$stmt = $db->prepare("DELETE FROM UserCars WHERE user_id = :user_id");
try {
    $stmt->execute([":user_id" => $user_id]);
    $deleted_count = $stmt->rowCount();
    flash("Removed $deleted_count car(s) from your garage", "success");
} catch (Exception $e) {
    flash("Error removing cars from garage", "danger");
    error_log("Error removing all from garage: " . var_export($e, true));
}

die(header("Location: my_garage.php"));
?>