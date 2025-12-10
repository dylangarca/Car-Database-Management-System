<?php
// dg599 11/24
require(__DIR__ . "/../../partials/nav1.php");

// Check if user is logged in
if (!is_logged_in()) {
    flash("You must be logged in to create a car entry", "warning");
    redirect("Location: login.php");
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
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO Cars (make, model, year, type, image_url, description, user_id, is_api) 
                             VALUES (:make, :model, :year, :type, :image_url, :description, :user_id, 0)");
        try {
            $stmt->execute([
                ":make" => $make,
                ":model" => $model,
                ":year" => $year,
                ":type" => $type,
                ":image_url" => $image_url,
                ":description" => $description,
                ":user_id" => get_user_id()
            ]);
            flash("Car added successfully!", "success");
            // Redirect to list page after successful creation
            redirect("Location: list_cars.php");
        } catch (Exception $e) {
            flash("Error creating car entry: " . $e->getMessage(), "danger");
            error_log("Error creating car: " . var_export($e, true));
        }
    }
}
?>

<div class="container-fluid">
    <h1>Add New Car</h1>
    <form method="POST" onsubmit="return validate(this)">
        <div class="mb-3">
            <label for="make">Make *</label>
            <input type="text" id="make" name="make" required maxlength="100"
                   value="<?php echo se($_POST, 'make', '', false); ?>" 
                   placeholder="e.g., Toyota, Ford, Honda" />
        </div>
        
        <div class="mb-3">
            <label for="model">Model *</label>
            <input type="text" id="model" name="model" required maxlength="100"
                   value="<?php echo se($_POST, 'model', '', false); ?>" 
                   placeholder="e.g., Camry, Mustang, Civic" />
        </div>
        
        <div class="mb-3">
            <label for="year">Year *</label>
            <input type="number" id="year" name="year" required 
                   min="1900" max="<?php echo date('Y') + 1; ?>"
                   value="<?php echo se($_POST, 'year', '', false); ?>" 
                   placeholder="e.g., 2020" />
        </div>
        
        <div class="mb-3">
            <label for="type">Type</label>
            <input type="text" id="type" name="type" maxlength="50"
                   value="<?php echo se($_POST, 'type', '', false); ?>" 
                   placeholder="e.g., Sedan, SUV, Truck" />
        </div>
        
        <div class="mb-3">
            <label for="image_url">Image URL</label>
            <input type="url" id="image_url" name="image_url"
                   value="<?php echo se($_POST, 'image_url', '', false); ?>" 
                   placeholder="https://example.com/image.jpg" />
        </div>
        
        <div class="mb-3">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4" 
                      placeholder="Enter car details..."><?php echo se($_POST, 'description', '', false); ?></textarea>
        </div>
        
        <input type="submit" value="Add Car" />
        <a href="list_cars.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
function validate(form) {
    let isValid = true;
    
    // Get form values
    const make = form.make.value.trim();
    const model = form.model.value.trim();
    const year = parseInt(form.year.value);
    const currentYear = new Date().getFullYear();
    
    // Validate make
    if (!make || make.length < 2) {
        flash('Make must be at least 2 characters long', 'warning');
        isValid = false;
    }
    
    // Validate model
    if (!model || model.length < 1) {
        flash('Model is required', 'warning');
        isValid = false;
    }
    
    // Validate year
    if (!year || year < 1900 || year > currentYear + 1) {
        flash('Please enter a valid year between 1900 and ' + (currentYear + 1), 'warning');
        isValid = false;
    }
    
    return isValid;
}
</script>

<?php
require(__DIR__ . "/../../partials/flash.php");
?>