<?php
// dg599 11/24
require(__DIR__ . "/../../partials/nav.php");

// Check if user is logged in
if (!is_logged_in()) {
    flash("You must be logged in to delete a car", "warning");
    redirect("Location: login.php");
}

$car_id = (int)se($_GET, "id", 0, false);

if ($car_id <= 0) {
    flash("Invalid car ID", "danger");
    redirect("Location: list_cars.php");
}

$db = getDB();

// Fetch the car to check permissions
$stmt = $db->prepare("SELECT * FROM Cars WHERE id = :id LIMIT 1");
try {
    $stmt->execute([":id" => $car_id]);
    $car = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$car) {
        flash("Car not found", "danger");
        redirect("Location: list_cars.php");
    }
} catch (Exception $e) {
    flash("Error fetching car details", "danger");
    error_log("Error fetching car for delete: " . var_export($e, true));
    redirect("Location: list_cars.php");
}

// Permission check: User can only delete their own cars, OR admin can delete any
$can_delete = false;
if (has_role("Admin")) {
    $can_delete = true; // Admin can delete anything
} elseif ($car["user_id"] == get_user_id()) {
    $can_delete = true; // User can delete their own cars
}

if (!$can_delete) {
    flash("You don't have permission to delete this car", "danger");
    redirect("Location: list_cars.php");
}

// Perform deletion (hard delete)
$stmt = $db->prepare("DELETE FROM Cars WHERE id = :id");
try {
    $stmt->execute([":id" => $car_id]);
    flash("Car deleted successfully", "success");
} catch (Exception $e) {
    flash("Error deleting car", "danger");
    error_log("Error deleting car: " . var_export($e, true));
}

// Redirect back to list page, preserving filters if they exist
$redirect_url = "list_cars.php";
if (!empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'list_cars.php') !== false) {
    // Extract query string from referrer to preserve filters
    $referer_parts = parse_url($_SERVER['HTTP_REFERER']);
    if (isset($referer_parts['query'])) {
        $redirect_url .= "?" . $referer_parts['query'];
    }
}

redirect("Location: list_cars.php");
?>