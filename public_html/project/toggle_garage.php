<?php
//dg599 12/7
require(__DIR__ . "/../../partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in to manage your garage", "warning");
    die(header("Location: login.php"));
}

$car_id = (int)se($_POST, "car_id", 0, false);
$action = se($_POST, "action", "", false);
$redirect = se($_POST, "redirect", "list_cars.php", false);

if ($car_id <= 0) {
    flash("Invalid car ID", "danger");
    die(header("Location: $redirect"));
}

$db = getDB();
$user_id = get_user_id();

// Check if car exists
$stmt = $db->prepare("SELECT id FROM Cars WHERE id = :car_id LIMIT 1");
$stmt->execute([":car_id" => $car_id]);
$car = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$car) {
    flash("Car not found", "danger");
    die(header("Location: $redirect"));
}

// Check if already in garage
$stmt = $db->prepare("SELECT id FROM UserCars WHERE user_id = :user_id AND car_id = :car_id LIMIT 1");
$stmt->execute([":user_id" => $user_id, ":car_id" => $car_id]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($action === "add") {
    if ($existing) {
        flash("This car is already in your garage", "warning");
    } else {
        $stmt = $db->prepare("INSERT INTO UserCars (user_id, car_id) VALUES (:user_id, :car_id)");
        try {
            $stmt->execute([":user_id" => $user_id, ":car_id" => $car_id]);
            flash("Car added to your garage!", "success");
        } catch (Exception $e) {
            flash("Error adding car to garage", "danger");
            error_log("Error adding to garage: " . var_export($e, true));
        }
    }
} elseif ($action === "remove") {
    if (!$existing) {
        flash("This car is not in your garage", "warning");
    } else {
        $stmt = $db->prepare("DELETE FROM UserCars WHERE user_id = :user_id AND car_id = :car_id");
        try {
            $stmt->execute([":user_id" => $user_id, ":car_id" => $car_id]);
            flash("Car removed from your garage", "success");
        } catch (Exception $e) {
            flash("Error removing car from garage", "danger");
            error_log("Error removing from garage: " . var_export($e, true));
        }
    }
}

die(header("Location: $redirect"));
?>