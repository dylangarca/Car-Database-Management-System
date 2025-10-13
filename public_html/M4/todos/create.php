<?php
require_once(__DIR__ . "/../../../lib/db.php"); ?>

<?php
// don't edit - this
$expected_fields = ["task", "due", "assigned"];
$diff = array_diff($expected_fields, array_keys($_GET));

if (empty($diff)) {

    // data variables, don't edit
    $task = $_GET["task"];
    $due = $_GET["due"]; //hint: must be a valid MySQL date format
    $assigned = $_GET["assigned"]; // Must be "self" or a valid format (not empty or equivalent)

    $is_valid = true;
    // TODO Validate the incoming data for correct format based on the SQL table definition.
    // When not valid, provide a user-friendly message of what specifically was wrong and set $is_valid to false.
    // Assigned should check for "self" if a valid format/value isn't provided.
    // Start validations
    // can edit here
    //dg599 10/13

    if(empty(trim($task))){
        echo "Task cannot be empty.<br>";
        $isvalid=false;
    }
    elseif(strlen($task) > 128){
        echo "Task must be 128 charecters or less.<br>";
        $is_valid = false;
    }

    if(empty($due)){
        echo "Due Date is required. <br>";
        $is_valid =false;
    }
    else{
        $date_parts = explode( "-", $due);
        if(count($date_parts) !==3 || !checkdate($date_parts[1], $date_parts[2], $date_parts[0])){
            echo "Due date must be valid date in YYYY-MM-DD format. <br>";
            $is_valid= false;
        }
    }

    if(empty(trim($assigned))){
        $assigned = "self";
    }
    elseif(strlen($assigned) > 60){
        echo "Assigned value must be 60 charecters or less.";
        $is_valid = false;
    }


    
    // End validations

    
    if ($is_valid) {
        /*
        Design a query to insert the incoming data to the proper columns.
        Ensure valid and proper PDO named placeholders are used.
        https://phpdelusions.net/pdo
        */
        //dg599 10/13
        $query = "INSERT INTO M4_Todos (task, due, assigned) VALUES (:task, :due, :assigned)"; // edit this
        $params = [ ":task" => $task, ":due" => $due, ":assigned" => $assigned ]; // Apply the proper PDO placeholder to variable mapping here
        try {
            $db = getDB();
            $stmt = $db->prepare($query);
            $r = $stmt->execute($params);
            if ($r) {
                echo "Inserted new Todo with id " . $db->lastInsertId();
            } else {
                echo "Failed to insert";
            }
        } catch (PDOException $e) {
            // extra credit
            // check if the exception was related to a unique constraint
            // provide an appropriate user-friendly message for this scenario
            // Otherwise show the default message below
            echo "There was an error inserting the record; check the logs (terminal)";
            error_log("Insert Error: " . var_export($e, true)); // shows in the terminal
        }
    } else {
        error_log("Creation input wasn't valid");
    }
}
?>
<html>

<body>
    <?php require_once(__DIR__ . "/../nav.php"); ?>
    <section>
        <h2>Create ToDo </h2>
        <form>
            <!-- design the form with proper labels and input fields with the correct types based on the SQL table.
             Wrap each label/input pair in a div tag.
             For "Assigned" ensure the default value is "self". -->
            <!-- dg599 10/13 -->
             <div>
                <label for="task_input">Task</label>
                <input type="text" id="task_input" name="task" required maxlength="128">
             </div>

             <div>
                <label for= "due_input">Due</label>
                <input type="date" id="due_input" name="due" required>
             </div>

             <div>
                <label for="assigned_input">Assigned</label>
                <input type="text" id="assigned_input" name="assigned" value="self" maxlength="60"  >
             </div>
          
            <div>
                <input type="submit" value="Create Todo" />
            </div>
        </form>
    </section>
</body>
</body>

</html>