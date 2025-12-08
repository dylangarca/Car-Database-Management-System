<?php
require(__DIR__ . "/../../partials/nav1.php");

if (!has_role("Admin")) {
    flash("Only administrators can access this page", "danger");
    die(header("Location: list_cars.php"));
}

$limit = (int)se($_GET, "limit", 10, false);
if ($limit < 1 || $limit > 100) {
    $limit = 10;
}

$username_filter = se($_GET, "username", "", false);
$make_filter = se($_GET, "make", "", false);
$sort_by = se($_GET, "sort", "UserCars.created", false);
$order = se($_GET, "order", "DESC", false);

$allowed_sorts = ["username", "make", "model", "year", "UserCars.created"];
if (!in_array($sort_by, $allowed_sorts)) {
    $sort_by = "UserCars.created";
}

if (!in_array($order, ["ASC", "DESC"])) {
    $order = "DESC";
}

$query = "SELECT UserCars.*, Cars.make, Cars.model, Cars.year, Cars.type, Cars.image_url, Users.username,
          (SELECT COUNT(*) FROM UserCars uc2 WHERE uc2.car_id = UserCars.car_id) as total_users_with_car
          FROM UserCars 
          INNER JOIN Cars ON UserCars.car_id = Cars.id
          INNER JOIN Users ON UserCars.user_id = Users.id
          WHERE 1=1";
$params = [];

if (!empty($username_filter)) {
    $query .= " AND Users.username LIKE :username";
    $params[":username"] = "%" . $username_filter . "%";
}

if (!empty($make_filter)) {
    $query .= " AND Cars.make LIKE :make";
    $params[":make"] = "%" . $make_filter . "%";
}

$count_query = str_replace("SELECT UserCars.*, Cars.make, Cars.model, Cars.year, Cars.type, Cars.image_url, Users.username,
          (SELECT COUNT(*) FROM UserCars uc2 WHERE uc2.car_id = UserCars.car_id) as total_users_with_car", "SELECT COUNT(*)", $query);

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
    $associations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    flash("Error fetching associations", "danger");
    error_log("Error: " . var_export($e, true));
    $associations = [];
}
?>

<div class="container-fluid">
    <h1>All User Associations (Admin)</h1>
    
    <div class="mb-3">
        <p><strong>Total associations: <?php echo $total_count; ?></strong></p>
        <p>Showing: <?php echo count($associations); ?> association(s)</p>
    </div>
    
    <?php if (!empty($username_filter)): ?>
        <form method="POST" action="admin_remove_user_associations.php" style="display: inline;">
            <input type="hidden" name="username_filter" value="<?php echo $username_filter; ?>" />
            <button type="submit" class="btn btn-danger" onclick="return confirm('Remove ALL associations for users matching: <?php echo $username_filter; ?>?')">
                Remove All Associations for Filtered Users
            </button>
        </form>
    <?php endif; ?>
    
    <hr>
    
    <form method="GET" class="filter-form">
    <h3>Filter & Sort</h3>
    
    <div class="mb-3">
        <label for="username">Username (partial match)</label>
        <input type="text" id="username" name="username" 
               value="<?php echo se($_GET, 'username', '', false); ?>" 
               placeholder="e.g., john" />
    </div>
    
    <div class="mb-3">
        <label for="make">Car Make</label>
        <input type="text" id="make" name="make" 
               value="<?php echo se($_GET, 'make', '', false); ?>" 
               placeholder="e.g., Toyota" />
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
            <option value="username" <?php echo ($sort_by == "username") ? "selected" : ""; ?>>Username</option>
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
    <a href="all_associations.php" class="btn btn-secondary">Clear Filters</a>
</form>
    
    <?php if (empty($associations)): ?>
        <div class="alert alert-info">
            <strong>No results available.</strong>
        </div>
    <?php else: ?>
        <table class="table" style="width: 100%; border: 1px solid #000000ff;">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Car</th>
                    <th>Type</th>
                    <th>Total Users with This Car</th>
                    <th>Date Added</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($associations as $assoc): ?>
                    <tr>
                        <td>
                            <a href="admin_user_profile.php?username=<?php echo se($assoc, 'username', ''); ?>">
                                <?php echo se($assoc, 'username', ''); ?>
                            </a>
                        </td>
                        <td><?php echo se($assoc, 'year', ''); ?> <?php echo se($assoc, 'make', ''); ?> <?php echo se($assoc, 'model', ''); ?></td>
                        <td><?php echo se($assoc, 'type', 'N/A'); ?></td>
                        <td><?php echo se($assoc, 'total_users_with_car', '0'); ?></td>
                        <td><?php 
                            $created = se($assoc, 'created', '', false);
                            echo !empty($created) ? date('M d, Y', strtotime($created)) : 'Unknown';
                        ?></td>
                        <td>
                            <a href="view_car.php?id=<?php echo se($assoc, 'car_id', ''); ?>" class="btn btn-sm btn-info">View Car</a>
                            <form method="POST" action="remove_association.php" style="display: inline; background:none; padding: 0;">
                                <input type="hidden" name="association_id" value="<?php echo se($assoc, 'id', ''); ?>" />
                                <input type="hidden" name="redirect" value="all_associations.php?<?php echo http_build_query($_GET); ?>" />
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Remove this association?')">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
require(__DIR__ . "/../../partials/flash.php");
?>