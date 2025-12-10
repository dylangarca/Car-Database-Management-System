<?php
//dg599 12/7
require(__DIR__ . "/../../partials/nav1.php");

if (!has_role("Admin")) {
    flash("Only administrators can access this page", "danger");
    redirect("Location: list_cars.php");
}

$limit = (int)se($_GET, "limit", 10, false);
if ($limit < 1 || $limit > 100) {
    $limit = 10;
}

$make_filter = se($_GET, "make", "", false);
$year_filter = se($_GET, "year", "", false);
$sort_by = se($_GET, "sort", "created", false);
$order = se($_GET, "order", "DESC", false);

$allowed_sorts = ["make", "model", "year", "type", "created"];
if (!in_array($sort_by, $allowed_sorts)) {
    $sort_by = "created";
}

if (!in_array($order, ["ASC", "DESC"])) {
    $order = "DESC";
}

$query = "SELECT Cars.* FROM Cars 
          LEFT JOIN UserCars ON Cars.id = UserCars.car_id 
          WHERE UserCars.id IS NULL";
$params = [];

if (!empty($make_filter)) {
    $query .= " AND Cars.make LIKE :make";
    $params[":make"] = "%" . $make_filter . "%";
}

if (!empty($year_filter)) {
    $query .= " AND Cars.year = :year";
    $params[":year"] = $year_filter;
}

$count_query = str_replace("SELECT Cars.*", "SELECT COUNT(*)", $query);
$db = getDB();
$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_count = $count_stmt->fetchColumn();

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
    flash("Error fetching unassociated cars", "danger");
    error_log("Error: " . var_export($e, true));
    $cars = [];
}
?>

<div class="container-fluid">
    <h1>Unassociated Cars (Admin)</h1>
    <p>These cars have no user associations</p>
    
    <div class="mb-3">
        <p><strong>Total unassociated cars: <?php echo $total_count; ?></strong></p>
        <p>Showing: <?php echo count($cars); ?> car(s)</p>
    </div>
    
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
        <label for="limit">Results per page</label>
        <input type="number" id="limit" name="limit" min="1" max="100"
               value="<?php echo $limit; ?>" />
    </div>
    
    <div class="mb-3">
        <label for="sort">Sort by</label>
        <select id="sort" name="sort" class="form-select">
            <option value="created" <?php echo ($sort_by == "created") ? "selected" : ""; ?>>Date Added</option>
            <option value="make" <?php echo ($sort_by == "make") ? "selected" : ""; ?>>Make</option>
            <option value="year" <?php echo ($sort_by == "year") ? "selected" : ""; ?>>Year</option>
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
    <a href="unassociated_cars.php" class="btn btn-secondary">Clear Filters</a>
</form>
    
    <?php if (empty($cars)): ?>
        <div class="alert alert-info">
            <strong>No results available.</strong> All cars have at least one user association!
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($cars as $car): ?>
                <div class="col-md-4 mb-3">
                    <div class="card" style="border: 1px solid #ddd; border-radius: 0.5rem; padding: 1rem;">
                        <?php if (!empty($car["image_url"])): ?>
                            <img src="<?php echo se($car, 'image_url', '', false); ?>" 
                                 alt="<?php echo se($car, 'make', ''); ?> <?php echo se($car, 'model', ''); ?>" 
                                 style="width: 100%; height: 200px; object-fit: contain; border-radius: 0.5rem; background: #f5f5f5;" />
                        <?php else: ?>
                            <div style="width: 100%; height: 200px; background: #ddd; display: flex; align-items: center; justify-content: center; border-radius: 0.5rem;">
                                No Image
                            </div>
                        <?php endif; ?>
                        
                        <h3><?php echo se($car, 'year', ''); ?> <?php echo se($car, 'make', ''); ?> <?php echo se($car, 'model', ''); ?></h3>
                        <p><strong>Type:</strong> <?php echo se($car, 'type', 'N/A'); ?></p>
                        
                        <div class="mt-2">
                            <a href="view_car.php?id=<?php echo se($car, 'id', ''); ?>" class="btn btn-info">View Details</a>
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