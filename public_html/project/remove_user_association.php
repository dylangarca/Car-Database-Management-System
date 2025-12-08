<?php
require(__DIR__ . "/../../partials/nav1.php");

if (!has_role("Admin")) {
    flash("Only administrators can access this page", "danger");
    die(header("Location: list_cars.php"));
}

$username_filter = se($_POST, "username_filter", "", false);

if (empty($username_filter)) {
    flash("No username filter provided", "danger");
    die(header("Location: all_associations.php"));
}

$db = getDB();
$stmt = $db->prepare("DELETE UserCars FROM UserCars 
                     INNER JOIN Users ON UserCars.user_id = Users.id 
                     WHERE Users.username LIKE :username");

try {
    $stmt->execute([":username" => "%" . $username_filter . "%"]);
    $deleted_count = $stmt->rowCount();
    flash("Removed $deleted_count association(s) for users matching '$username_filter'", "success");
} catch (Exception $e) {
    flash("Error removing associations", "danger");
    error_log("Error: " . var_export($e, true));
}

die(header("Location: all_associations.php"));
?>