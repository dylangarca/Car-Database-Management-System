<?php
require(__DIR__ . "/../../partials/nav1.php");

if (!is_logged_in()) {
    flash("You must be logged in to view your garage", "warning");
    die(header("Location: login.php"));
}

$user_id = get_user_id();

// Pagination and filtering
$limit = (int)se($_GET, "limit", 10, false);
if ($limit < 1 || $limit > 100) {
    $limit = 10;
}

$make_filter = se($_GET, "make", "", false);
$year_filter = se($_GET, "year", "", false);
$type_filter = se($_GET, "type", "", false);
$sort_by = se($_GET, "sort", "UserCars.created", false);
$order = se($_GET, "order", "DESC", false);

$allowed_sorts = ["make", "model", "year", "type", "UserCars.created"];
if (!in_array($sort_by, $allowed_sorts)) {
    $sort_by = "UserCars.created";
}

if (!in_array($order, ["ASC", "DESC"])) {
    $order = "DESC";
}

// Build query
$query = "SELECT Cars.*, UserCars.created as added_date 
          FROM Cars 
          INNER JOIN UserCars ON Cars.id = UserCars.car_id 
          WHERE UserCars.user_id = :user_id";
$params = [":user_id" => $user_id];

if (!empty($make_filter)) {
    $query .= " AND Cars.make LIKE :make";
    $params[":make"] = "%" . $make_filter . "%";
}

if (!empty($year_filter)) {
    $query .= " AND Cars.year = :year";
    $params[":year"] = $year_filter;
}

if (!empty($type_filter)) {
    $query .= " AND Cars.type LIKE :type";
    $params[":type"] = "%" . $type_filter . "%";
}

// Get total count
$count_query = str_replace("SELECT Cars.*, UserCars.created as added_date", "SELECT COUNT(*)", $query);
$db = getDB();
$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_count = $count_stmt->fetchColumn();

// Add sorting and limit
$query .= " ORDER BY $sort_by $order LIMIT :limit";

$stmt = $db->prepare($query);
$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

try {
    $stmt->execute();
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    flash("Error fetching your garage", "danger");
    error_log("Error fetching garage: " . var_export($e, true));
    $cars = [];
}
?>

<div class="container-fluid">
    <h1>My Garage</h1>
    
    <div class="mb-3">
        <p><strong>Total cars in garage: <?php echo $total_count; ?></strong></p>
        <p>Showing: <?php echo count($cars); ?> car(s)</p>
    </div>
    
    <?php if ($total_count > 0): ?>
        <form method="POST" action="remove_all_garage.php" style="display: inline; background:none; padding: 0; ">
            <button type="submit" class="btn btn-danger"  onclick="return confirm('Are you sure you want to remove ALL cars from your garage?')">
                Remove All Cars from Garage
            </button>
        </form>
    <?php endif; ?>
    
    <hr>
    
    <form method="GET" class="filter-form">
    <h3>Filter & Sort</h3>
    
    <div class="mb-3">
        <label for="make">Make</label>
        <input type="text" id="make" name="make" 
               value="<?php echo se($_GET, 'make', '', false); ?>" 
               placeholder="e.g., Toyota" />
    </div>
    
    <div class="mb-3">
        <label for="year">Year</label>
        <input type="number" id="year" name="year" 
               value="<?php echo se($_GET, 'year', '', false); ?>" 
               placeholder="e.g., 2020" />
    </div>
    
    <div class="mb-3">
        <label for="type">Type</label>
        <input type="text" id="type" name="type" 
               value="<?php echo se($_GET, 'type', '', false); ?>" 
               placeholder="e.g., Sedan" />
    </div>
    
    <div class="mb-3">
        <label for="limit">Results per page</label>
        <input type="number" id="limit" name="limit" min="1" max="100"
               value="<?php echo $limit; ?>" />
    </div>
    
    <div class="mb-3">
        <label for="sort">Sort by</label>
        <select id="sort" name="sort" class="form-select">
            <option value="UserCars.created" <?php echo ($sort_by == "UserCars.created") ? "selected" : ""; ?>>Date Added</option>
            <option value="make" <?php echo ($sort_by == "make") ? "selected" : ""; ?>>Make</option>
            <option value="model" <?php echo ($sort_by == "model") ? "selected" : ""; ?>>Model</option>
            <option value="year" <?php echo ($sort_by == "year") ? "selected" : ""; ?>>Year</option>
            <option value="type" <?php echo ($sort_by == "type") ? "selected" : ""; ?>>Type</option>
        </select>
    </div>
    
    <div class="mb-3">
        <label for="order">Order</label>
        <select id="order" name="order" class="form-select">
            <option value="ASC" <?php echo ($order == "ASC") ? "selected" : ""; ?>>Ascending</option>
            <option value="DESC" <?php echo ($order == "DESC") ? "selected" : ""; ?>>Descending</option>
        </select>
    </div>
    
    <button type="submit" class="btn btn-primary">Apply Filters</button>
    <a href="my_garage.php" class="btn btn-secondary">Clear Filters</a>
</form>
    
    <?php if (empty($cars)): ?>
        <div class="alert alert-info">
            <strong>No results available.</strong> 
            <?php if ($total_count > 0): ?>
                Try adjusting your filters.
            <?php else: ?>
                Start adding cars to your garage from the <a href="list_cars.php">car list</a>!
            <?php endif; ?>
        </div>
    <?php else: ?>
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
                        <p><small>Added: <?php 
                            $added_date = se($car, 'added_date', '', false);
                            echo !empty($added_date) ? date('M d, Y', strtotime($added_date)) : 'Unknown';
                        ?></small></p>
                        
                        <div class="mt-2">
                            <a href="view_car.php?id=<?php echo se($car, 'id', ''); ?>" class="btn btn-info">View Details</a>
                            
                            <form method="POST" action="toggle_garage.php" style="display: inline;">
                                <input type="hidden" name="car_id" value="<?php echo se($car, 'id', ''); ?>" />
                                <input type="hidden" name="action" value="remove" />
                                <input type="hidden" name="redirect" value="my_garage.php?<?php echo http_build_query($_GET); ?>" />
                                <button type="submit" class="btn btn-danger" >Remove from Garage</button>
                            </form>
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