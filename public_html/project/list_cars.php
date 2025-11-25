<?php
// dg599 11/24
require(__DIR__ . "/../../partials/nav.php");

// Pagination and filtering
$limit = (int)se($_GET, "limit", 10, false);
if ($limit < 1 || $limit > 100) {
    $limit = 10; // Default to 10 if invalid
}

$make_filter = se($_GET, "make", "", false);
$year_filter = se($_GET, "year", "", false);
$type_filter = se($_GET, "type", "", false);
$sort_by = se($_GET, "sort", "created", false); // Default sort by created date
$order = se($_GET, "order", "DESC", false); // Default descending

// Allowed sort columns 
$allowed_sorts = ["make", "model", "year", "type", "created", "modified"];
if (!in_array($sort_by, $allowed_sorts)) {
    $sort_by = "created";
}

// Allowed order directions
if (!in_array($order, ["ASC", "DESC"])) {
    $order = "DESC";
}

// Build query
$query = "SELECT * FROM Cars WHERE 1=1";
$params = [];

if (!empty($make_filter)) {
    $query .= " AND make LIKE :make";
    $params[":make"] = "%" . $make_filter . "%";
}

if (!empty($year_filter)) {
    $query .= " AND year = :year";
    $params[":year"] = $year_filter;
}

if (!empty($type_filter)) {
    $query .= " AND type LIKE :type";
    $params[":type"] = "%" . $type_filter . "%";
}

$query .= " ORDER BY $sort_by $order LIMIT :limit";

$db = getDB();
$stmt = $db->prepare($query);

// Bind limit as integer
$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);

// Bind other params
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

try {
    $stmt->execute();
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    flash("Error fetching cars: " . $e->getMessage(), "danger");
    error_log("Error fetching cars: " . var_export($e, true));
    $cars = [];
}
?>

<div class="container-fluid">
    <h1>Car List</h1>
    
    <div class="mb-3">
        <a href="create_car.php" class="btn btn-primary">Add New Car</a>
    </div>
    
    <!-- Filter/Sort Form -->
    <form method="GET" class="mb-4" style="border: 1px solid #ddd; padding: 1rem; border-radius: 0.5rem;">
        <h3>Filter & Sort</h3>
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="make">Make</label>
                <input type="text" id="make" name="make" 
                       value="<?php echo se($_GET, 'make', '', false); ?>" 
                       placeholder="e.g., Toyota" />
            </div>
            
            <div class="col-md-3 mb-3">
                <label for="year">Year</label>
                <input type="number" id="year" name="year" 
                       value="<?php echo se($_GET, 'year', '', false); ?>" 
                       placeholder="e.g., 2020" />
            </div>
            
            <div class="col-md-3 mb-3">
                <label for="type">Type</label>
                <input type="text" id="type" name="type" 
                       value="<?php echo se($_GET, 'type', '', false); ?>" 
                       placeholder="e.g., Sedan" />
            </div>
            
            <div class="col-md-3 mb-3">
                <label for="limit">Results per page</label>
                <input type="number" id="limit" name="limit" min="1" max="100"
                       value="<?php echo $limit; ?>" />
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="sort">Sort by</label>
                <select id="sort" name="sort">
                    <option value="created" <?php echo ($sort_by == "created") ? "selected" : ""; ?>>Date Added</option>
                    <option value="make" <?php echo ($sort_by == "make") ? "selected" : ""; ?>>Make</option>
                    <option value="model" <?php echo ($sort_by == "model") ? "selected" : ""; ?>>Model</option>
                    <option value="year" <?php echo ($sort_by == "year") ? "selected" : ""; ?>>Year</option>
                    <option value="type" <?php echo ($sort_by == "type") ? "selected" : ""; ?>>Type</option>
                </select>
            </div>
            
            <div class="col-md-3 mb-3">
                <label for="order">Order</label>
                <select id="order" name="order">
                    <option value="ASC" <?php echo ($order == "ASC") ? "selected" : ""; ?>>Ascending</option>
                    <option value="DESC" <?php echo ($order == "DESC") ? "selected" : ""; ?>>Descending</option>
                </select>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Apply Filters</button>
        <a href="list_cars.php" class="btn btn-secondary">Clear Filters</a>
    </form>
    
    <!-- Results -->
    <?php if (empty($cars)): ?>
        <div class="alert alert-info">
            <strong>No results available.</strong> Try different filters or add a new car.
        </div>
    <?php else: ?>
        <p>Showing <?php echo count($cars); ?> car(s)</p>
        <div class="row">
            <?php foreach ($cars as $car): ?>
                <div class="col-md-4 mb-3">
                    <div class="card" style="border: 1px solid #ddd; border-radius: 0.5rem; padding: 1rem;">
                        <?php if (!empty($car["image_url"])): ?>
                            <img src="<?php echo se($car, 'image_url', '', false); ?>" 
                                 alt="<?php echo se($car, 'make', ''); ?> <?php echo se($car, 'model', ''); ?>" 
                                 style="width: 100%; height: 200px; object-fit: cover; border-radius: 0.5rem;" />
                        <?php else: ?>
                            <div style="width: 100%; height: 200px; background: #ddd; display: flex; align-items: center; justify-content: center; border-radius: 0.5rem;">
                                No Image
                            </div>
                        <?php endif; ?>
                        
                        <h3><?php echo se($car, 'year', ''); ?> <?php echo se($car, 'make', ''); ?> <?php echo se($car, 'model', ''); ?></h3>
                        <p><strong>Type:</strong> <?php echo se($car, 'type', 'N/A'); ?></p>
                        <p><strong>Source:</strong> <?php echo (se($car, 'is_api', 0) == 1) ? " API" : " Manual"; ?></p>
                        
                        <div class="mt-2">
                            <a href="view_car.php?id=<?php echo se($car, 'id', ''); ?>" class="btn btn-info">View Details</a>
                            <a href="edit_car.php?id=<?php echo se($car, 'id', ''); ?>" class="btn btn-warning">Edit</a>
                            <a href="delete_car.php?id=<?php echo se($car, 'id', ''); ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this car?')">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
require(__DIR__ . "/../../partials/flash.php");
?>