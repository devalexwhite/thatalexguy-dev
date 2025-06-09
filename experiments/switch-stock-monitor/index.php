<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>Switch 2 Stock Checker</title>
    <link rel="stylesheet" href="../../styles/style.css" />
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" href="images/favicon.png" />
    <script src="https://unpkg.com/htmx.org@2.0.4"></script>
  </head>

  <body>
    <header>
      <h1>Alex White's Blog</h1>   
    </header>

    <section id="switch-scraper">
      <button hx-get="scraper.php" hx-target="#scrape-results" hx-swap="innerHTML">Check again (limited to every 5 mins)</button>
      <div class="htmx-indicator">⧖ Loading...</div>
      <div id="scrape-results">
        <?php include './scraper.php' ?>
      </div>
    </section
  </body>
</html>
