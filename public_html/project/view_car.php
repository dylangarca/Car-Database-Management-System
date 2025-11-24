<?php
// dg599 11/24
require(__DIR__ . "/../../partials/nav.php");

$car_id = (int)se($_GET, "id", 0, false);

if ($car_id <= 0) {
    flash("Invalid car ID", "danger");
    die(header("Location: list_cars.php"));
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM Cars WHERE id = :id LIMIT 1");

try {
    $stmt->execute([":id" => $car_id]);
    $car = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$car) {
        flash("Car not found", "danger");
        die(header("Location: list_cars.php"));
    }
} catch (Exception $e) {
    flash("Error fetching car details", "danger");
    error_log("Error fetching car: " . var_export($e, true));
    die(header("Location: list_cars.php"));
}
?>

<div class="container-fluid">
    <h1>Car Details</h1>
    
    <div class="card" style="max-width: 800px; margin: 0 auto; padding: 2rem; border: 1px solid #dddddd04; border-radius: 0.5rem;">
        <?php if (!empty($car["image_url"])): ?>
            <img src="<?php echo se($car, 'image_url', '', false); ?>" 
                 alt="<?php echo se($car, 'make', ''); ?> <?php echo se($car, 'model', ''); ?>" 
                 style="width: 100%; max-height: 400px; object-fit: cover; border-radius: 0.5rem; margin-bottom: 1rem;" />
        <?php endif; ?>
        
        <h2><?php echo se($car, 'year', ''); ?> <?php echo se($car, 'make', ''); ?> <?php echo se($car, 'model', ''); ?></h2>
        
        <table class="table">
            <tr>
                <th>ID:</th>
                <td><?php echo se($car, 'id', ''); ?></td>
            </tr>
            <tr>
                <th>Make:</th>
                <td><?php echo se($car, 'make', ''); ?></td>
            </tr>
            <tr>
                <th>Model:</th>
                <td><?php echo se($car, 'model', ''); ?></td>
            </tr>
            <tr>
                <th>Year:</th>
                <td><?php echo se($car, 'year', ''); ?></td>
            </tr>
            <tr>
                <th>Type:</th>
                <td><?php echo se($car, 'type', 'N/A'); ?></td>
            </tr>
            <tr>
                <th>Source:</th>
                <td><?php echo (se($car, 'is_api', 0) == 1) ? " From API" : " Manual Entry"; ?></td>
            </tr>
            <?php if (!empty($car["api_id"])): ?>
            <tr>
                <th>API ID:</th>
                <td><?php echo se($car, 'api_id', ''); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <th>Description:</th>
                <td><?php echo se($car, 'description', 'No description available'); ?></td>
            </tr>
            <tr>
                <th>Date Added:</th>
                <td><?php echo se($car, 'created', ''); ?></td>
            </tr>
            <tr>
                <th>Last Modified:</th>
                <td><?php echo se($car, 'modified', ''); ?></td>
            </tr>
        </table>
        
        <div class="mt-3">
            <a href="edit_car.php?id=<?php echo se($car, 'id', ''); ?>" class="btn btn-warning">Edit</a>
            <a href="delete_car.php?id=<?php echo se($car, 'id', ''); ?>" 
               class="btn btn-danger" 
               onclick="return confirm('Are you sure you want to delete this car?')">Delete</a>
            <a href="list_cars.php" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
</div>

<?php
require(__DIR__ . "/../../partials/flash.php");
?>