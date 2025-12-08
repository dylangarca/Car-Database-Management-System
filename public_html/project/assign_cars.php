<?php
require(__DIR__ . "/../../partials/nav.php");

if (!has_role("Admin")) {
    flash("Only administrators can access this page", "danger");
    die(header("Location: list_cars.php"));
}

$car_search = se($_POST, "car_search", "", false);
$user_search = se($_POST, "user_search", "", false);

$cars = [];
$users = [];

$db = getDB();

if (!empty($car_search) || !empty($user_search)) {
    if (!empty($car_search)) {
        $stmt = $db->prepare("SELECT * FROM Cars WHERE make LIKE :search OR model LIKE :search LIMIT 25");
        $stmt->execute([":search" => "%" . $car_search . "%"]);
        $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    if (!empty($user_search)) {
        $stmt = $db->prepare("SELECT id, username, email FROM Users WHERE username LIKE :search LIMIT 25");
        $stmt->execute([":search" => "%" . $user_search . "%"]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (isset($_POST["selected_cars"], $_POST["selected_users"])) {
    $selected_cars = $_POST["selected_cars"];
    $selected_users = $_POST["selected_users"];
    
    if (is_array($selected_cars) && is_array($selected_users)) {
        $added_count = 0;
        $removed_count = 0;
        
        foreach ($selected_users as $user_id) {
            foreach ($selected_cars as $car_id) {
                $check_stmt = $db->prepare("SELECT id FROM UserCars WHERE user_id = :user_id AND car_id = :car_id LIMIT 1");
                $check_stmt->execute([":user_id" => $user_id, ":car_id" => $car_id]);
                $exists = $check_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($exists) {
                    $delete_stmt = $db->prepare("DELETE FROM UserCars WHERE user_id = :user_id AND car_id = :car_id");
                    try {
                        $delete_stmt->execute([":user_id" => $user_id, ":car_id" => $car_id]);
                        $removed_count++;
                    } catch (Exception $e) {
                        error_log("Error removing association: " . var_export($e, true));
                    }
                } else {
                    $insert_stmt = $db->prepare("INSERT INTO UserCars (user_id, car_id) VALUES (:user_id, :car_id)");
                    try {
                        $insert_stmt->execute([":user_id" => $user_id, ":car_id" => $car_id]);
                        $added_count++;
                    } catch (Exception $e) {
                        error_log("Error adding association: " . var_export($e, true));
                    }
                }
            }
        }
        
        flash("Associations updated: $added_count added, $removed_count removed", "success");
    }
}
?>

<div class="container-fluid">
    <h1>Assign Cars to Users (Admin)</h1>
    <p>Search for cars and users, then select which associations to create/remove</p>
    
    <form method="POST" class="mb-4" style="border: 1px solid #ddd; padding: 1rem; border-radius: 0.5rem;">
        <h3>Search</h3>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="car_search">Car Make or Model (partial match)</label>
                <input type="text" id="car_search" name="car_search" 
                       value="<?php echo se($_POST, 'car_search', '', false); ?>" 
                       placeholder="e.g., Toyota, Camry" />
                <small>Leave blank to skip car search</small>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="user_search">Username (partial match)</label>
                <input type="text" id="user_search" name="user_search" 
                       value="<?php echo se($_POST, 'user_search', '', false); ?>" 
                       placeholder="e.g., john" />
                <small>Leave blank to skip user search</small>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Search</button>
    </form>
    
    <?php if (!empty($cars) || !empty($users)): ?>
        <form method="POST">
            <input type="hidden" name="car_search" value="<?php echo se($_POST, 'car_search', '', false); ?>" />
            <input type="hidden" name="user_search" value="<?php echo se($_POST, 'user_search', '', false); ?>" />
            
            <div class="row">
                <div class="col-md-6">
                    <h3>Cars (max 25)</h3>
                    <?php if (empty($cars)): ?>
                        <p>No cars found. Try a different search.</p>
                    <?php else: ?>
                        <div style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 1rem; border-radius: 0.5rem;">
                            <?php foreach ($cars as $car): ?>
                                <div class="mb-2">
                                    <label style="display: block; cursor: pointer;">
                                        <input type="checkbox" name="selected_cars[]" value="<?php echo se($car, 'id', ''); ?>" />
                                        <?php echo se($car, 'year', ''); ?> <?php echo se($car, 'make', ''); ?> <?php echo se($car, 'model', ''); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="col-md-6">
                    <h3>Users (max 25)</h3>
                    <?php if (empty($users)): ?>
                        <p>No users found. Try a different search.</p>
                    <?php else: ?>
                        <div style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 1rem; border-radius: 0.5rem;">
                            <?php foreach ($users as $user): ?>
                                <div class="mb-2">
                                    <label style="display: block; cursor: pointer;">
                                        <input type="checkbox" name="selected_users[]" value="<?php echo se($user, 'id', ''); ?>" />
                                        <?php echo se($user, 'username', ''); ?> (<?php echo se($user, 'email', ''); ?>)
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($cars) && !empty($users)): ?>
                <div class="mt-3">
                    <button type="submit" class="btn btn-success">Apply Associations (Toggle Selected)</button>
                    <p><small>Note: If association exists, it will be removed. If it doesn't exist, it will be created.</small></p>
                </div>
            <?php endif; ?>
        </form>
    <?php endif; ?>
</div>

<?php
require(__DIR__ . "/../../partials/flash.php");
?>