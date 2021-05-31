<?php
// Open a connection to database
include_once("includes/db.php");
$db = open_sqlite_db("db/catalog.sqlite");

// Games can only be single player, multiplayer, or both
$modes = array('multi', 'single', 'both');

// feedback message CSS classes
$title_feedback_class = "hidden";
$company_feedback_class = "hidden";
$genre_feedback_class = "hidden";
$price_feedback_class = "hidden";
$min_age_feedback_class = "hidden";
$multi_or_single_feedback_class = "hidden";

// checking if the games were inserted - additional validation constraints
$game_title_not_unique = False;
$game_inserted = False;
$game_insert_failed = False;

// default form values (search form)
$search_terms = NULL;

// default form values (add new game form)
$game_title = NULL;
$game_company = NULL;
$game_genre = NULL;
$game_price = NULL;
$game_min_age = NULL;
$game_multi_or_single = NULL;


// sticky values for my search form
$sticky_search = '';

// sticky values for adding a new record from
$sticky_title = '';
$sticky_company = '';
$sticky_genre = '';
$sticky_price = '';
$sticky_min_age = '';
$sticky_multi_or_single = NULL; // the multi/single player field is a dropdown list, thus NULL

// creating the other variables needed for query
$sql_selection_query= 'SELECT * FROM games'; // this is the base query
$sql_select_parameters= array();
$sql_have_search= False;
$sql_have_filter= False;

// search result counter
$search_result_counter= 0;


// --------------------------------- Searching -----------------------------------
// search submitted
if (isset($_GET['search'])) {

  // Trimming the leading and trailing spaces on http parameters
  $search_terms = trim($_GET['search']); // this value is untrusted

  // If the search is empty, then set value to NULL
  if (empty($search_terms)) {
    $search_terms = NULL;
  }
  $sticky_search= $search_terms; // this value is tainted

  if ($search_terms == NULL) {
    $sql_selection_query= "SELECT * FROM games";
  }

  if ($search_terms != NULL) {
    // When the user enters a terms that they want to search for throughout all of the fields
    $sql_selection_query= "SELECT * FROM games WHERE (title LIKE '%' || :userSearch || '%') OR
    (company LIKE '%' || :userSearch || '%') OR
    (genre LIKE '%' || :userSearch || '%') OR
    (price LIKE '%' || :userSearch || '%') OR
    (minimum_age_rating LIKE '%' || :userSearch || '%') OR
    (multi_or_single LIKE '%' || :userSearch || '%')";
    $sql_select_parameters= array(':userSearch' => $search_terms);
  }

  $sql_have_search = True;
}

// ----------------------------------- Filtering ----------------------------------------------

// values for filtering - using boolean
$filterMulti= (bool)$_GET['multi']; // This is untrusted
$filterSingle= (bool)$_GET['single']; // This is untrusted
$filterBoth= (bool)$_GET['both']; // This is untrusted
$filterFree= (bool)$_GET['free']; // This is untrusted
$filterLessThanThirty= (bool)$_GET['lessThanThirty']; // This is untrusted
$filterOlderThanEighteen= (bool)$_GET['olderThanEighteen']; // This is untrusted
$filterAgeLimitEighteen= (bool)$_GET['ageLimitEighteen']; // This is untrusted
$filterAgeLimitFourteen= (bool)$_GET['ageLimitFourteen']; // This is untrusted
$filterAgeLimitTen= (bool)$_GET['ageLimitTen']; // This is untrusted
$filterAgeLimitSix= (bool)$_GET['ageLimitSix']; // This is untrusted

// sticky values for my filter form
$sticky_filter_multi= ($filterMulti ? 'checked' : '');
$sticky_filter_single= ($filterSingle ? 'checked' : '');
$sticky_filter_both= ($filterBoth ? 'checked' : '');
$sticky_filter_free= ($filterFree ? 'checked' : '');
$sticky_filter_lessThanThirty= ($filterLessThanThirty ? 'checked' : '');
$sticky_filter_olderThanEighteen= ($filterOlderThanEighteen ? 'checked' : '');
$sticky_filter_ageLimitEighteen= ($filterAgeLimitEighteen ? 'checked' : '');
$sticky_filter_ageLimitFourteen= ($filterAgeLimitFourteen ? 'checked' : '');
$sticky_filter_ageLimitTen= ($filterAgeLimitTen ? 'checked' : '');
$sticky_filter_ageLimitSix= ($filterAgeLimitSix ? 'checked' : '');

// Filtering the SQL
if ($filterMulti || $filterSingle || $filterBoth || $filterFree || $filterLessThanThirty || $filterOlderThanEighteen ||
$filterAgeLimitEighteen || $filterAgeLimitFourteen || $filterAgeLimitTen || $filterAgeLimitSix) {
  $sql_selection_query = "SELECT * FROM games WHERE ";
  $sql_filter_expressions = '';
  $counter= 0;

  if ($filterMulti) {
    $sql_filter_expressions= $sql_filter_expressions . "(multi_or_single = 'multi')";
    $sql_have_filter= True;
  }

  if ($filterSingle) {
    $sql_filter_expressions= $sql_filter_expressions . ($sql_have_filter ? ' AND ' : '') . "(multi_or_single = 'single')";
    $sql_have_filter= True;
  }

  if ($filterBoth) {
    $sql_filter_expressions= $sql_filter_expressions . ($sql_have_filter ? ' AND ' : '') . "(multi_or_single = 'both')";
    $sql_have_filter= True;
  }

  if ($filterFree) {
    $sql_filter_expressions= $sql_filter_expressions . ($sql_have_filter ? ' AND ' : '') . "(price = 0.00)";
    $sql_have_filter= True;
  }

  if ($filterLessThanThirty) {
    $sql_filter_expressions= $sql_filter_expressions . ($sql_have_filter ? ' AND ' : '') . "(price < 30.00)";
    $sql_have_filter= True;
  }

  if ($filterOlderThanEighteen) {
    $sql_filter_expressions= $sql_filter_expressions . ($sql_have_filter ? ' AND ' : '') . "(minimum_age_rating >= 18)";
    $sql_have_filter= True;
  }

  if ($filterAgeLimitEighteen) {
    $sql_filter_expressions= $sql_filter_expressions . ($sql_have_filter ? ' AND ' : '') . "(minimum_age_rating < 18)";
    $sql_have_filter= True;
  }

  if ($filterAgeLimitFourteen) {
    $sql_filter_expressions= $sql_filter_expressions . ($sql_have_filter ? ' AND ' : '') . "(minimum_age_rating <= 14)";
    $sql_have_filter= True;
  }

  if ($filterAgeLimitTen) {
    $sql_filter_expressions= $sql_filter_expressions . ($sql_have_filter ? ' AND ' : '') . "(minimum_age_rating <= 10)";
    $sql_have_filter= True;
  }

  if ($filterAgeLimitSix) {
    $sql_filter_expressions= $sql_filter_expressions . ($sql_have_filter ? ' AND ' : '') . "(minimum_age_rating <= 6)";
    $sql_have_filter= True;
  }

  if ($sql_have_filter) {
    $sql_selection_query= $sql_selection_query . $sql_filter_expressions;
  }
}


// ---------------------------------- Adding a new game ---------------------------------
// new game submitted
if (isset($_POST['add_game'])) {

  // Trim the leading and trailing spaces on http parameters
  $game_title = trim($_POST['game_title']); // this value is untrusted
  $game_company = trim($_POST['game_company']); // this value is untrusted
  $game_genre = trim($_POST['game_genre']); // this value is untrusted
  $game_price = trim($_POST['game_price']); // this value is untrusted
  $game_min_age = trim($_POST['game_min_age']); // this value is untrusted
  $game_multi_or_single = trim($_POST['game_multi_or_single']); // this value is untrusted

  $form_valid = True;

  // game title is required
  if (empty($game_title)) {
    $form_valid = False;
    $title_feedback_class = '';
  } else {
    // Game title should be unique
    $records = exec_sql_query(
      $db,
      "SELECT * FROM games WHERE (title = :title);",
      array(
        ':title' => $game_title
      )
    )->fetchAll();
    if (count($records) > 0) {
      $form_valid = False;
      $game_title_not_unique = True;
    }
  }

  // mode of gaming experience must be valid
  if (!in_array($game_multi_or_single, $modes)) {
    $form_valid = False;
    $multi_or_single_feedback_class = '';

  // mode was not found, do not make this into a sticky value (mode is a dropdown list)!
  $game_multi_or_single = NULL;
  }

  // game company is required
  if (empty($game_company)) {
    $form_valid = False;
    $company_feedback_class = '';
  }

  //game price is required
  if (empty($game_price)) {
    $form_valid = False;
    $price_feedback_class = '';
  }

  //game genre is required
  if (empty($game_genre)) {
    $form_valid = False;
    $genre_feedback_class = '';
  }

  //game min age is required
  if (empty($game_min_age)) {
    $form_valid = False;
    $min_age_feedback_class = '';
  }

  if ($form_valid) {
    // insert the game into the database. Replace NULL with the results of teh INSERT query

    $result = exec_sql_query(
      $db,
      "INSERT INTO games (title, company, multi_or_single, genre, price, minimum_age_rating) VALUES (:title, :company, :multi_or_single, :genre, :price, :minimum_age_rating);",
      array(
        ':title' => $game_title, // this value is tainted
        ':company' => $game_company, // this value is tainted
        ':multi_or_single' => $game_multi_or_single, // this value is tainted
        ':genre' => $game_genre, // this value is tainted
        ':price' => $game_price, // this value is tainted
        ':minimum_age_rating' => $game_min_age, // this value is tainted
      )
      );

      // check if insert query was successful
      if ($result) {
        $game_inserted = True;
      } else {
        $game_insert_failed = True;
      }
  } else {
    // form is invalid, set sticky values
    $sticky_title = $game_title; // this value is tainted
    $sticky_company = $game_company; // this value is tainted
    $sticky_price = $game_price; // this value is tainted
    $sticky_genre = $game_genre; // ainted
    $sticky_min_age = $game_min_age; // this value is tainted
    $sticky_multi_or_single = $game_multi_or_single; // this value is tainted
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title>GameTrove</title>

  <link rel="stylesheet" type="text/css" href="public/styles/site.css" media="all" />
</head>

<body>

  <div class="title_and_search">
    <div class="main_name">

      <h1><a href="/">GameTrove</a></h1>
    </div>
    <header>

      <form action="/" method="get" novalidate>
        <label class="search_label" for="search_bar">Search:</label>

        <input id="search_bar" type="text" name="search" size=40 required value="<?php echo htmlspecialchars($sticky_search); ?>" />

        <button type="submit">Search</button>
      </form>

    </header>
  </div>

  <div class="aside_and_main">
    <aside>

      <h2> Filtering </h2>
      <form action="/" method="get" id="filter_form" novalidate>

        <div class="filter_label_and_input">
          <label>
            <input type="checkbox" name="multi" value="1" required <?php echo $sticky_filter_multi; ?> /> Multiplayer (exclusive)
          </label>
        </div>

        <div class="filter_label_and_input">
          <label>
            <input type="checkbox" name="single" value="1" required <?php echo $sticky_filter_single; ?> /> Single Player (exclusive)
          </label>
        </div>

        <div class="filter_label_and_input">
          <label>
            <input type="checkbox" name="both" value="1" required <?php echo $sticky_filter_both; ?> /> Multi and Single Player (both)
          </label>
        </div>

        <div class="filter_label_and_input">
          <label>
            <input type="checkbox" name="free" value="1" required <?php echo $sticky_filter_free; ?> /> Free Games
          </label>
        </div>

        <div class="filter_label_and_input">
          <label>
            <input type="checkbox" name="lessThanThirty" value="1" required <?php echo $sticky_filter_lessThanThirty; ?> /> Games less than $30
          </label>
        </div>

        <div class="filter_label_and_input">
          <label>
            <input type="checkbox" name="olderThanEighteen" value="1" required <?php echo $sticky_filter_olderThanEighteen; ?> /> Age Limit: 18 and older
          </label>
        </div>

        <div class="filter_label_and_input">
          <label>
            <input type="checkbox" name="ageLimitEighteen" value="1" required <?php echo $sticky_filter_ageLimitEighteen; ?> /> Age Limit: Younger than 18
          </label>
        </div>

        <div class="filter_label_and_input">
          <label>
            <input type="checkbox" name="ageLimitFourteen" value="1" required <?php echo $sticky_filter_ageLimitFourteen; ?> /> Age Limit: 14 and younger
          </label>
        </div>

        <div class="filter_label_and_input">
          <label>
            <input type="checkbox" name="ageLimitTen" value="1" required <?php echo $sticky_filter_ageLimitTen; ?> /> Age Limit: 10 and younger
          </label>
        </div>

        <div class="filter_label_and_input">
          <label>
            <input type="checkbox" name="ageLimitSix" value="1" required <?php echo $sticky_filter_ageLimitSix; ?> /> Age Limit: 6 and younger
          </label>
        </div>

        <div class="align_right">
          <button type="submit">Apply Filter(s)</button>
        </div>
      </form>

      <h2>Add New Game</h2>
      <form action="/" method="post" novalidate>

        <p id="title_feedback" class="feedback <?php echo $title_feedback_class; ?>"> Please provide the name of the game.</p>

        <?php if ($game_title_not_unique) { ?>
          <p class="feedback">The Game &quot;<?php echo htmlspecialchars($game_title); ?>&quot; is already in the game list. Please input a different game title.</p>
        <?php } ?>

        <div class="label_and_input">
          <label for="game_title">Title:</label>
          <input id="game_title" type="text" name="game_title" value="<?php echo htmlspecialchars($sticky_title); ?>" required />
        </div>

        <p id="company_feedback" class="feedback <?php echo $company_feedback_class; ?>"> Please provide the name of the company.</p>
        <div class="label_and_input">
          <label for="game_company">Company:</label>
          <input id="game_company" type="text" name="game_company" value="<?php echo htmlspecialchars($sticky_company); ?>" required />
        </div>

        <p id="multi_or_single_feedback" class="feedback <?php echo $multi_or_single_feedback_class; ?>"> Game must be multiplayer, single-player, or both.</p>
        <div class="label_and_input">
          <label for="game_multi_or_single">Multi/Single Player:</label>
          <select id="game_multi_or_single" name="game_multi_or_single">
            <option label="Choose mode of game" disabled <?php echo empty($sticky_multi_or_single) ? 'selected' : ''; ?>></option>
            <?php
            foreach ($modes as $mode) {
              if ($sticky_multi_or_single == $mode) {
                $multi_or_single_selected_attribute = 'selected';
              } else {
                $multi_or_single_selected_attribute = '';
              }
              echo"<option " . $multi_or_single_selected_attribute . " value=\"" . htmlspecialchars($mode) . "\">" . htmlspecialchars($mode) . "</option>";
            } ?>
            <!-- <option value="multi" <?php echo htmlspecialchars($sticky_multi_or_single); ?>>multi</option>
            <option value="single" <?php echo htmlspecialchars($sticky_multi_or_single); ?>>single</option>
            <option value="both" <?php echo htmlspecialchars($sticky_multi_or_single); ?>>both</option> -->
          </select>
        </div>

        <p id="genre_feedback" class="feedback <?php echo $genre_feedback_class; ?>"> Please provide the genre of the game.</p>
        <div class="label_and_input">
          <label for="game_genre">Genre:</label>
          <input id="game_genre" type="text" name="game_genre" value="<?php echo htmlspecialchars($sticky_genre); ?>" required />
        </div>

        <p id="price_feedback" class="feedback <?php echo $price_feedback_class; ?>"> Please provide the price of the game.</p>
        <div class="label_and_input">
          <label for="game_price">Price:</label>
          <input id="game_price" type="number" step="0.01" name="game_price" value="<?php echo htmlspecialchars($sticky_price); ?>" required />
        </div>

        <p id="min_age_feedback" class="feedback <?php echo $min_age_feedback_class; ?>"> Please provide the minimum age rating for the game.</p>
        <div class="label_and_input">
          <label for="game_min_age">Age Rating:</label>
          <input id="game_min_age" type="number" name="game_min_age" value="<?php echo htmlspecialchars($sticky_min_age); ?>" required />
        </div>

        <div class="align_right">
          <button type="submit" name="add_game">Add Game</button>
        </div>
      </form>

    </aside>

    <!-- The main goes after the aside element because the main element is on the right  -->
    <main>

    <!-- Getting the number of search results -->
    <?php
        // Getting the records from database
        $records = exec_sql_query(
          $db,
          $sql_selection_query,
          $sql_select_parameters
        )->fetchAll();

        foreach ($records as $record) { ?>
          <?php $search_result_counter = $search_result_counter + 1; ?>

    <?php } ?>

    <!-- When the review is correctly inserted, show the confirmation message -->
    <?php if ($game_inserted) { ?>
      <p class="successful_insert"><strong>Thank you for adding the game: &quot;<?php echo htmlspecialchars($game_title); ?>&quot;</strong></p>
    <?php } ?>

    <?php if ($game_insert_failed) { ?>
      <p class="feedback_insert"><strong>Sorry, something went wrong with the game that you were adding, please try again.</strong></p>
    <?php } ?>

    <?php if ($sql_have_search && $search_terms != NULL) { ?>
        <p class="search_info"><strong><?php echo $search_result_counter; ?> results searching for:  &quot;<?php echo htmlspecialchars($search_terms); ?>&quot; </strong></p>
    <?php } ?>

    <!-- When the review is incorrectly inserted, show the error message -->
      <table>
        <tr>
          <th>Title</th>
          <th>Company</th>
          <th>Multi/Single Player</th>
          <th>Genre</th>
          <th>Price</th>
          <th>Minimum Age Rating</th>
        </tr>
        <?php
        // Getting the records from database
        $records = exec_sql_query(
          $db,
          $sql_selection_query,
          $sql_select_parameters
        )->fetchAll();

        foreach ($records as $record) { ?>
          <?php $search_result_counter = $search_result_counter + 1; ?>
          <tr>
            <td><?php echo htmlspecialchars($record["title"]); ?></td>
            <td><?php echo htmlspecialchars($record["company"]); ?></td>
            <td><?php echo htmlspecialchars($record["multi_or_single"]); ?></td>
            <td><?php echo htmlspecialchars($record["genre"]); ?></td>
            <td><?php echo htmlspecialchars($record["price"]); ?></td>
            <td><?php echo htmlspecialchars($record["minimum_age_rating"]); ?></td>
          </tr>
        <?php } ?>
      </table>

    </main>
  </div>
  <?php include("includes/footer.php"); ?>
</body>

</html>
