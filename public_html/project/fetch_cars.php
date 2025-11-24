<?php
// dg599 11/24
require(__DIR__ . "/../../partials/nav.php");
require_once(__DIR__ . "/../../lib/api_helper.php");

// Check if user is admin (only admins can fetch API data)
if (!has_role("Admin")) {
    flash("Only administrators can fetch API data", "danger");
    die(header("Location: list_cars.php"));
}

$results = [];
$fetched_count = 0;
$updated_count = 0;
$skipped_count = 0;

// Handle API fetch
if (isset($_POST["search_query"])) {
    $search_query = se($_POST, "search_query", "", false);
    
    if (empty($search_query)) {
        flash("Please enter a search term", "warning");
    } else {
        try {
            // Call the Cars API
            $data = [
                "q" => $search_query,
                "page" => "1"
            ];
            
            $endpoint = "https://cars-database-with-image.p.rapidapi.com/api/search";
            $isRapidAPI = true;
            $rapidAPIHost = "cars-database-with-image.p.rapidapi.com";
            
            $result = get($endpoint, "CAR_API_KEY", $data, $isRapidAPI, $rapidAPIHost);
            
            if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
                $api_response = json_decode($result["response"], true);
                
                // Process API
                if (isset($api_response["results"]) && is_array($api_response["results"])) {
                    $results = $api_response["results"];
                    
                    $db = getDB();
                    
                    foreach ($results as $car_data) {
                        // Extract car information from API - FIXED field names
                        $api_id = se($car_data, "id", null, false);
                        
                        // Parse the title to get make and model
                        $title = se($car_data, "title", "", false);
                        $title_parts = explode(" ", $title, 2);
                        $make = isset($title_parts[0]) ? $title_parts[0] : "";
                        $model = isset($title_parts[1]) ? $title_parts[1] : "";
                        
                        // Extract year from content 
                        $year = date("Y"); // Default to current year
                        
                        // Get type from additional field (contains "Sedan", "Hatchback", etc.)
                        $additional = se($car_data, "additional", "", false);
                        $type = "";
                        if (strpos($additional, "Sedan") !== false) {
                            $type = "Sedan";
                        } elseif (strpos($additional, "Hatchback") !== false) {
                            $type = "Hatchback";
                        } elseif (strpos($additional, "Station wagon") !== false) {
                            $type = "Station Wagon";
                        } elseif (strpos($additional, "SUV") !== false) {
                            $type = "SUV";
                        }
                        
                        $image_url = se($car_data, "image", "", false);
                        
                        // Build description from available data
                        $description = se($car_data, "content", "", false);
                        if (!empty($additional)) {
                            $description .= " | " . $additional;
                        }
                        
                        // Skip if missing required fields
                        if (empty($make) || empty($model) || empty($api_id)) {
                            $skipped_count++;
                            continue;
                        }
                        
                        // Check for duplicate by api_id
                        $check_stmt = $db->prepare("SELECT id FROM Cars WHERE api_id = :api_id LIMIT 1");
                        $check_stmt->execute([":api_id" => $api_id]);
                        $existing_car = $check_stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($existing_car) {
                            // Car already exists - UPDATE it
                            $update_stmt = $db->prepare("UPDATE Cars SET make = :make, model = :model, 
                                                         year = :year, type = :type, image_url = :image_url, 
                                                         description = :description 
                                                         WHERE api_id = :api_id");
                            try {
                                $update_stmt->execute([
                                    ":make" => $make,
                                    ":model" => $model,
                                    ":year" => $year,
                                    ":type" => $type,
                                    ":image_url" => $image_url,
                                    ":description" => $description,
                                    ":api_id" => $api_id
                                ]);
                                $updated_count++;
                            } catch (Exception $e) {
                                error_log("Error updating car from API: " . var_export($e, true));
                                $skipped_count++;
                            }
                        } else {
                            // New car - INSERT it
                            $insert_stmt = $db->prepare("INSERT INTO Cars (api_id, make, model, year, type, 
                                                         image_url, description, user_id, is_api) 
                                                         VALUES (:api_id, :make, :model, :year, :type, 
                                                         :image_url, :description, :user_id, 1)");
                            try {
                                $insert_stmt->execute([
                                    ":api_id" => $api_id,
                                    ":make" => $make,
                                    ":model" => $model,
                                    ":year" => $year,
                                    ":type" => $type,
                                    ":image_url" => $image_url,
                                    ":description" => $description,
                                    ":user_id" => get_user_id()
                                ]);
                                $fetched_count++;
                            } catch (Exception $e) {
                                error_log("Error inserting car from API: " . var_export($e, true));
                                $skipped_count++;
                            }
                        }
                    }
                    
                    flash("API Fetch Complete: $fetched_count new cars added, $updated_count cars updated, $skipped_count skipped", "success");
                } else {
                    flash("No cars found in API response", "warning");
                }
            } else {
                flash("Failed to fetch data from API", "danger");
            }
        } catch (Exception $e) {
            flash("Error fetching from API: " . $e->getMessage(), "danger");
            error_log("API Fetch Error: " . var_export($e, true));
        }
    }
}
?>

<div class="container-fluid">
    <h1>Fetch Cars from API</h1>
    
    <div class="alert alert-info">
        <strong>Admin Only:</strong> This page allows you to fetch car data from the external API and add it to your database.
        <br>
        <strong>Note:</strong> Duplicates are automatically detected and updated if data has changed.
    </div>
    
    <form method="POST">
        <div class="mb-3">
            <label for="search_query">Search for Cars</label>
            <input type="text" id="search_query" name="search_query" required
                   value="<?php echo se($_POST, 'search_query', '', false); ?>"
                   placeholder="e.g., Ford, Toyota, BMW, WRX" />
            <small>Enter a car make, model, or keyword to search the API</small>
        </div>
        
        <button type="submit" class="btn btn-primary">Fetch from API</button>
        <a href="list_cars.php" class="btn btn-secondary">Back to Car List</a>
    </form>
    
    <?php if (!empty($results)): ?>
        <hr>
        <h2>API Results Preview</h2>
        <p>Fetched: <strong><?php echo $fetched_count; ?></strong> new cars</p>
        <p>Updated: <strong><?php echo $updated_count; ?></strong> existing cars</p>
        <p>Skipped: <strong><?php echo $skipped_count; ?></strong> (missing data or errors)</p>
        
        <div class="row">
            <?php foreach (array_slice($results, 0, 6) as $car): ?>
                <div class="col-md-4 mb-3">
                    <div class="card" style="border: 1px solid #ddd; padding: 1rem; border-radius: 0.5rem;">
                        <?php if (!empty($car["image"])): ?>
                            <img src="<?php echo se($car, 'image', '', false); ?>" 
                                 alt="Car" 
                                 style="width: 100%; height: 150px; object-fit: cover; border-radius: 0.5rem;" />
                        <?php endif; ?>
                        <h4><?php echo se($car, 'title', ''); ?></h4>
                        <p><?php echo se($car, 'content', ''); ?></p>
                        <p><small><?php echo se($car, 'additional', ''); ?></small></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <p><small>Showing first 6 results. Check the <a href="list_cars.php">car list</a> to see all imported cars.</small></p>
    <?php endif; ?>
</div>

<?php
require(__DIR__ . "/../../partials/flash.php");
?>