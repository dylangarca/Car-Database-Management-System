<?php
require(__DIR__ . "/../../partials/nav.php");
require_once(__DIR__ . "/../../lib/api_helper.php");

$result = [];
$error_message = "";

//dg599 11/17 

// This if statement will be checking if the user is searching for a car
if (isset($_GET["search"]) && !empty($_GET["search"])) {
    try {
        // This is the part of code that will be building the query parameters
        $data = [
            "q" => $_GET["search"], 
            "page" => isset($_GET["page"]) ? $_GET["page"] : "1" 
        ];
        
        // This is the database API endpoint
        $endpoint = "https://cars-database-with-image.p.rapidapi.com/api/search";
        $isRapidAPI = true;
        $rapidAPIHost = "cars-database-with-image.p.rapidapi.com";
        
        // This is where the API call will happen when all the input is gathered and is used with the api data
        $result = get($endpoint, "CAR_API_KEY", $data, $isRapidAPI, $rapidAPIHost);
        
        error_log("API Response: " . var_export($result, true));
        
        if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
            $result = json_decode($result["response"], true);
        } else {
            $result = [];
            $error_message = "Failed to fetch data from API";
        }
    } catch (Exception $e) {
        error_log("API Error: " . $e->getMessage());
        $error_message = "An error occurred while fetching car data: " . $e->getMessage();
        $result = [];
    }
}
?>

<div class="container-fluid">
    <h1>Car Database API Test</h1>
    <p>This is a testing page for the Cars Database API. Remember, we typically won't be frequently calling live data from our API - this is merely a quick sample. We'll want to cache data in our DB to save on API quota.</p>
    
    <form method="GET">
        <div class="mb-3">
            <label for="search">Search Cars (e.g., "Ford", "Toyota Camry", "BMW")</label>
            <input type="text" id="search" name="search" 
                   value="<?php echo se($_GET, 'search', '', false); ?>" 
                   placeholder="Toyota" 
                   required />
        </div>
        <div class="mb-3">
            <label for="page">Page Number (optional)</label>
            <input type="number" id="page" name="page" 
                   value="<?php echo se($_GET, 'page', '1', false); ?>" 
                   placeholder="1" 
                   min="1" />
        </div>
        <input type="submit" value="Search Cars" />
    </form>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <?php if (!empty($result)): ?>
            <h2>API Raw Response:</h2>
            <pre><?php var_export($result); ?></pre>
        <?php elseif (isset($_GET["search"])): ?>
            <p>No results found. Try a different search term.</p>
        <?php endif; ?>
    </div>
</div>

<?php
require(__DIR__ . "/../../partials/flash.php");
?>