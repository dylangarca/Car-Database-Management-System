<?php

require_once "base.php";

$ucid = "dg599"; // <-- set your ucid

// Don't edit the arrays below, they are used to test your code
$array1 = ["hello world!", "php programming", "special@#$%^&characters", "numbers 123 456", "mIxEd CaSe InPut!"];
$array2 = ["hello world", "php programming", "this is a title case test", "capitalize every word", "mixEd CASE input"];
$array3 = ["  hello   world  ", "php    programming  ", "  extra    spaces  between   words   ",
    "      leading and trailing spaces      ", "multiple      spaces"];
$array4 = ["hello world", "php programming", "short", "a", "even"];


function transformText($arr, $arrayNumber) {
    // Only make edits between the designated "Start" and "End" comments
    printArrayInfoBasic($arr, $arrayNumber);

    // Challenge 1: Remove non-alphanumeric characters except spaces
    // Challenge 2: Convert text to Title Case
    // Challenge 3: Trim leading/trailing spaces and remove duplicate spaces
    // Result 1-3: Assign final phrase to `$placeholderForModifiedPhrase`
    // Challenge 4 (extra credit): Extract up to the middle 3 characters (middle index and +/- 1 if it's not the first/last character),
    // Do not include the first or last character of the phrase/word. (e.g., oven should show as ve)
    // assign the result to `$placeholderForMiddleCharacters`
    // If the phrase is shorter than 3 characters, return "Not enough characters"

    // Step 1: sketch out plan using comments (include ucid and date)
    // Step 2: Add/commit your outline of comments (required for full credit)
    // Step 3: Add code to solve the problem (add/commit as needed)
    $placeholderForModifiedPhrase = "";
    $placeholderForMiddleCharacters = "";
    foreach ($arr as $index => $text) {
        // Start Solution Edits

        //dg599 9/29 for challenge 1 the way im going to remove everything that isn't a number, letter or space is with the preg replace function
        //for challenge 2 i am going to use the uppercase function that lets me capitalizes the first letter but first i would have to make every letter into lowercase with the lowercase function
        // for challenge 3 i can use the trim function to remove spaces at the front and end and then i will use preg replace again but this time to get rid of multiple spaces

        $placeholderForModifiedPhrase = preg_replace('/[^a-zA-Z0-9 ]/', '', $text);


        $placeholderForModifiedPhrase = strtolower($placeholderForModifiedPhrase);
        $placeholderForModifiedPhrase = ucwords($placeholderForModifiedPhrase);

        $placeholderForModifiedPhrase = trim($placeholderForModifiedPhrase);
        $placeholderForModifiedPhrase = preg_replace('/\s+/', ' ', $placeholderForModifiedPhrase);

        

        // End Solution Edits
        echo "<div>";
        printStringTransformations($index, $placeholderForModifiedPhrase, $placeholderForMiddleCharacters);
        echo "</div>";
    }

    echo "<br>______________________________________<br>";
}

// Run the problem
printHeader($ucid, 4);
transformText($array1, 1);
transformText($array2, 2);
transformText($array3, 3);
transformText($array4, 4);
printFooter($ucid, 4);

?>