<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title>Page Not Found</title>

  <link rel="stylesheet" type="text/css" href="public/styles/site.css" media="all" />
</head>

<body>

  <div class="title_and_search">
    <div class="main_name">

      <h1><a href="/">GameTrove</a></h1>
    </div>
  </div>

  <div class="not_found_body">

    <div class="error_message_not_found">
      <h2 class="black_text">We are sorry, the page: <em>&quot;<?php echo htmlspecialchars($request_uri); ?>&quot;</em>, does not exist.</h2>
    </div>

    <h3 class="black_text">Please click on this link to get to the main  web page:  <a href="/">GameTrove main page</a></h3>

    <h2 class="black_text"> OR </h2>

    <h3 class="black_text">Please click on the logo on the top left of the this web page</h3>

  </div>

  <?php include("includes/footer.php"); ?>
</body>

</html>
