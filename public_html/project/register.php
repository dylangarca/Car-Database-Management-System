<?php
require(__DIR__ . "/../../partials/nav.php");

//dg599 11/5
?>
<h3>Register</h3>
<form onsubmit="return validate(this)" method="POST">
    <div>
        <label for="email">Email</label>
        <input id="email" type="email" name="email" required 
                value ="<?php echo se($_POST, 'email', ' ', false); ?>" />
    </div>
    <div>
        <label for="username">Username</label>
        <input type="text" name="username" required maxlength="30" 
                value ="<?php echo se($_POST, 'username', ' ', false); ?>" />
    </div>
    <div>
        <label for="pw">Password</label>
        <input type="password" id="pw" name="password" required minlength="8" />
    </div>
    <div>
        <label for="confirm">Confirm</label>
        <input type="password" name="confirm" required minlength="8" />
    </div>
    <input type="submit" value="Register" />
</form>
<script>
    function validate(form) {
        //TODO 1: implement JavaScript validation (you'll do this on your own towards the end of Milestone1)
        //ensure it returns false for an error and true for success

        //dg599 11/10 
        // the code will check whether each input of email, username, and password is valid and if it is not valid it will give a warning with the error message

        let isValid = true;
        const email = form.email.value.trim();
        const username = form.username.value.trim();
        const password = form.password.value;
        const confirm = form.confirm.value;

        if(!email || !email.includes('@')){
            flash('Please enter a valid email address', 'warning');
            isValid = false;
        }

        if(!username || username.length < 3){
            flash('Username must be at least 3 characters', 'warning');
            isValid = false;
        }

        if(password.length < 8){
            flash('Password must be at least 8 characters', 'warning');
            isValid = false;
        }

        if(password !== confirm){
            flash('Passwords do not match', 'warning');
            isValid = false;
        }

        return isValid;
    }
</script>
<?php
//TODO 2: add PHP Code

//dg599 11/5
if (isset($_POST["email"], $_POST["password"], $_POST["confirm"], $_POST["username"])) {

    $email = se($_POST, "email", "", false);
    $password = se($_POST, "password", "", false);
    $confirm = se($_POST, "confirm", "", false);
    $username = se($_POST, "username", "", false);
    // TODO 3: validate/use
    $hasError = false;

    if (empty($email)) {
        flash("Email must not be empty.", "danger");
        $hasError = true;
    }
    // Sanitize and validate email
    $email = sanitize_email($email);
    if (!is_valid_email($email)) {
        flash("Invalid email address.", "danger");
        $hasError = true;
    }
    if (!is_valid_username($username)) {
        flash("Username must be lowercase, alphanumerical, and can only contain _ or -", "danger");
        $hasError = true;
    }
    if (empty($password)) {
        flash("Password must not be empty.", "danger");
        $hasError = true;
    }

    if (empty($confirm)) {
        flash("Confirm password must not be empty.", "danger");
        $hasError = true;
    }

    if (!is_valid_password($password)) {
        flash("Password must be at least 8 characters long.", "danger");
        $hasError = true;
    }

    if (!is_valid_confirm($password, $confirm)) {
        flash("Passwords must match.", "danger");
        $hasError = true;
    }

    if (!$hasError) {
        // TODO 4: Hash password and store record in DB
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $db = getDB(); // available due to the `require()` of `functions.php`
        // Code for inserting user data into the database
        $stmt = $db->prepare("INSERT INTO Users (email, password, username) VALUES (:email, :password, :username)");
        try {
            $stmt->execute([':email' => $email, ':password' => $hashed_password, ':username' => $username]);
   
            flash("Successfully registered! You can now log in.", "success");
        } catch(PDOException $e) {
            // Handle duplicate email/username
            users_check_duplicate($e);
        }
        catch (Exception $e) {
            flash("There was an error registering. Please try again.", "danger");
            error_log("Registration Error: " . var_export($e, true)); // log the technical error for debugging
        }
    }
}
?>
<?php
require(__DIR__ . "/../../partials/flash.php");
reset_session();
?>