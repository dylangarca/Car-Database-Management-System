<?php
// dg599 11/24
require(__DIR__ . "/../../partials/nav1.php");

// Check if user is logged in
if (!is_logged_in()) {
    flash("You must be logged in to edit a car", "warning");
    die(header("Location: login.php"));
}

$car_id = (int)se($_GET, "id", 0, false);

if ($car_id <= 0) {
    flash("Invalid car ID", "danger");
    die(header("Location: list_cars.php"));
}

$db = getDB();

// Fetch existing car data
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

// Handle form submission
if (isset($_POST["make"], $_POST["model"], $_POST["year"])) {
    $make = se($_POST, "make", "", false);
    $model = se($_POST, "model", "", false);
    $year = se($_POST, "year", 0, false);
    $type = se($_POST, "type", "", false);
    $image_url = se($_POST, "image_url", "", false);
    $description = se($_POST, "description", "", false);
    
    $hasError = false;
    
    // Validation
    if (empty($make)) {
        flash("Make is required", "danger");
        $hasError = true;
    }
    
    if (empty($model)) {
        flash("Model is required", "danger");
        $hasError = true;
    }
    
    if (empty($year) || $year < 1900 || $year > date("Y") + 1) {
        flash("Please enter a valid year between 1900 and " . (date("Y") + 1), "danger");
        $hasError = true;
    }
    
    if (!$hasError) {
        $stmt = $db->prepare("UPDATE Cars SET make = :make, model = :model, year = :year, 
                             type = :type, image_url = :image_url, description = :description 
                             WHERE id = :id");
        try {
            $stmt->execute([
                ":make" => $make,
                ":model" => $model,
                ":year" => $year,
                ":type" => $type,
                ":image_url" => $image_url,
                ":description" => $description,
                ":id" => $car_id
            ]);
            flash("Car updated successfully!", "success");
            
            // Refresh car data to show updated values
            $stmt = $db->prepare("SELECT * FROM Cars WHERE id = :id LIMIT 1");
            $stmt->execute([":id" => $car_id]);
            $car = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            flash("Error updating car: " . $e->getMessage(), "danger");
            error_log("Error updating car: " . var_export($e, true));
        }
    }
}
?>

<div class="container-fluid">
    <h1>Edit Car</h1>
    <form method="POST" onsubmit="return validate(this)">
        <div class="mb-3">
            <label for="make">Make *</label>
            <input type="text" id="make" name="make" required maxlength="100"
                   value="<?php echo se($car, 'make', '', false); ?>" 
                   placeholder="e.g., Toyota, Ford, Honda" />
        </div>
        
        <div class="mb-3">
            <label for="model">Model *</label>
            <input type="text" id="model" name="model" required maxlength="100"
                   value="<?php echo se($car, 'model', '', false); ?>" 
                   placeholder="e.g., Camry, Mustang, Civic" />
        </div>
        
        <div class="mb-3">
            <label for="year">Year *</label>
            <input type="number" id="year" name="year" required 
                   min="1900" max="<?php echo date('Y') + 1; ?>"
                   value="<?php echo se($car, 'year', '', false); ?>" 
                   placeholder="e.g., 2020" />
        </div>
        
        <div class="mb-3">
            <label for="type">Type</label>
            <input type="text" id="type" name="type" maxlength="50"
                   value="<?php echo se($car, 'type', '', false); ?>" 
                   placeholder="e.g., Sedan, SUV, Truck" />
        </div>
        
        <div class="mb-3">
            <label for="image_url">Image URL</label>
            <input type="url" id="image_url" name="image_url"
                   value="<?php echo se($car, 'image_url', '', false); ?>" 
                   placeholder="https://example.com/image.jpg" />
        </div>
        
        <div class="mb-3">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4" 
                      placeholder="Enter car details..."><?php echo se($car, 'description', '', false); ?></textarea>
        </div>
        
        <p><small>ID: <?php echo se($car, 'id', ''); ?> | Created: <?php echo se($car, 'created', ''); ?></small></p>
        
        <input type="submit" value="Update Car" />
        <a href="view_car.php?id=<?php echo $car_id; ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
function validate(form) {
    let isValid = true;
    
    const make = form.make.value.trim();
    const model = form.model.value.trim();
    const year = parseInt(form.year.value);
    const currentYear = new Date().getFullYear();
    
    if (!make || make.length < 2) {
        alert('Make must be at least 2 characters long');
        isValid = false;
    }
    
    if (!model || model.length < 1) {
        alert('Model is required');
        isValid = false;
    }
    
    if (!year || year < 1900 || year > currentYear + 1) {
        alert('Please enter a valid year between 1900 and ' + (currentYear + 1));
        isValid = false;
    }
    
    return isValid;
}
</script>

<?php
require(__DIR__ . "/../../partials/flash.php");
?>