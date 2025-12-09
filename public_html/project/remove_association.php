<?php
//dg599 12/7
require(__DIR__ . "/../../partials/nav.php");

if (!has_role("Admin")) {
    flash("Only administrators can access this page", "danger");
    die(header("Location: list_cars.php"));
}

$association_id = (int)se($_POST, "association_id", 0, false);
$redirect = se($_POST, "redirect", "all_associations.php", false);

if ($association_id <= 0) {
    flash("Invalid association ID", "danger");
    die(header("Location: " . $redirect));
}

$db = getDB();
$stmt = $db->prepare("DELETE FROM UserCars WHERE id = :id");

try {
    $stmt->execute([":id" => $association_id]);
    flash("Association removed successfully", "success");
} catch (Exception $e) {
    flash("Error removing association", "danger");
    error_log("Error: " . var_export($e, true));
}

die(header("Location: " . $redirect));
?>