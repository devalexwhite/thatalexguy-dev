<?php

require '../../vendor/autoload.php';

use Spatie\Browsershot\Browsershot;

$SITES_TO_MONITOR = [
  'https://www.target.com/p/nintendo-switch-2-console-mario-kart-world-bundle/-/A-94693226#lnk=sametab',
  'https://www.walmart.com/ip/Nintendo-Switch-2-Mario-Kart-World-Bundle/15928868255?classType=REGULAR&athbdg=L1103&from=/search',
  'https://www.meijer.com/shopping/product/nintendo-switch-2-mario-kart-world/4549688531.html'
];

$stock_status = [];

$db = new SQLite3('scrape.db', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
$db->query('CREATE TABLE IF NOT EXISTS "scrapes" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "has_stock" BOOLEAN,
    "url" VARCHAR,
    "time" DATETIME
)');


foreach ($SITES_TO_MONITOR as $site)
{
  try {
    $query = 'SELECT * FROM "scrapes" WHERE "url" = \'' . SQLite3::escapeString($site) . '\' LIMIT 1';
    $result = $db->querySingle($query, true);

    if ($result == NULL || new DateTime($result['time'])->diff(new DateTime())->i > 5) {
      $rendered = Browsershot::url($site)
        ->ignoreHttpsErrors()
        ->windowSize(400, 400)
        ->setChromePath('/usr/bin/chromium-browser')
        ->useCookies(['meijer-store' => '104'])
        ->userAgent('Mozilla/5.0 (Linux; Android 15) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.7151.62 Mobile Safari/537.36')
        ->bodyHtml();

      $rendered = strtolower($rendered);

      $stock = !str_contains($rendered, 'out of stock') && !str_contains($rendered, 'not available') && !str_contains($rendered, "out-of-stock");

      $stock_status[$site] = [
        'has_stock' => $stock,
        'time' => new DateTime()->format('Y-m-d H:i:s')
      ];



      if ($result == NULL) {
        $statement = $db->prepare('INSERT INTO "scrapes" ("url", "time", "has_stock") VALUES (:url, :time, :has_stock)');
        $statement->bindValue(':time', $stock_status[$site]['time']);
        $statement->bindValue(':has_stock', $stock);
        $statement->bindValue(':url', $site);
        $statement->execute();
      } else {
        $statement = $db->prepare('UPDATE "scrapes" SET "time" = :time, "has_stock" = :has_stock WHERE "id" = :id');
        $statement->bindValue(':time', $stock_status[$site]['time']);
        $statement->bindValue(':has_stock', $stock);
        $statement->bindValue(':id', $result['id']);
        $statement->execute();
      }
    } else {
      $stock_status[$site] = $result;
    }
  }
  catch (Exception $e)
  {
    $stock_status[$site] = $result;
  }
}
?>

<table>
  <thead>
    <tr>
      <td>Site</td>
      <td>Has Stock?</td>
      <td>Scraped</td>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($stock_status as $key => $status) {
        echo '<tr><td><a href="' . $key . '" target="_blank">'. substr($key,0,100) .'</a></td><td>' . ($status['has_stock'] ? 'Yes' : 'No') . '</td><td>'. new DateTime($status['time'])->diff(new DateTime())->i .' minute(s) ago</td></tr>';
      }
    ?>
  </tbody>
</table>
