<?php
//dg599 12/7
require(__DIR__ . "/../../partials/nav1.php");

if (!has_role("Admin")) {
    flash("Only administrators can access this page", "danger");
    die(header("Location: list_cars.php"));
}

$username = se($_GET, "username", "", false);

if (empty($username)) {
    flash("No username provided", "danger");
    die(header("Location: all_associations.php"));
}

$db = getDB();
$stmt = $db->prepare("SELECT id, username, email, created FROM Users WHERE username = :username LIMIT 1");
$stmt->execute([":username" => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    flash("User not found", "danger");
    die(header("Location: all_associations.php"));
}

$car_stmt = $db->prepare("SELECT Cars.*, UserCars.created as added_date 
                          FROM Cars 
                          INNER JOIN UserCars ON Cars.id = UserCars.car_id 
                          WHERE UserCars.user_id = :user_id");
$car_stmt->execute([":user_id" => $user["id"]]);
$cars = $car_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <h1>User Profile: <?php echo se($user, 'username', ''); ?></h1>
    
    <div class="mb-4">
        <p><strong>Email:</strong> <?php echo se($user, 'email', ''); ?></p>
        <p><strong>Member Since:</strong> <?php 
            $created = se($user, 'created', '', false);
            echo !empty($created) ? date('M d, Y', strtotime($created)) : 'Unknown';
        ?></p>
        <p><strong>Total Cars in Garage:</strong> <?php echo count($cars); ?></p>
    </div>
    
    <h2>Cars in Garage</h2>
    <?php if (empty($cars)): ?>
        <p>This user has no cars in their garage.</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($cars as $car): ?>
                <div class="col-md-4 mb-3">
                    <div class="card" style="border: 1px solid #ddd; border-radius: 0.5rem; padding: 1rem;">
                        <h3><?php echo se($car, 'year', ''); ?> <?php echo se($car, 'make', ''); ?> <?php echo se($car, 'model', ''); ?></h3>
                        <p><strong>Type:</strong> <?php echo se($car, 'type', 'N/A'); ?></p>
                        <p><small>Added: <?php 
                            $added = se($car, 'added_date', '', false);
                            echo !empty($added) ? date('M d, Y', strtotime($added)) : 'Unknown';
                        ?></small></p>
                        <a href="view_car.php?id=<?php echo se($car, 'id', ''); ?>" class="btn btn-info">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <a href="all_associations.php" class="btn btn-secondary">Back to All Associations</a>
</div>

<?php
require(__DIR__ . "/../../partials/flash.php");
?>