<div class="top">
<table class="tableau">
<thead>
<tr>
<th>Top 10 du jour</th>
<th>Clics</th>
<th>Partager</th>
</tr>
</thead>
<?php
$sql = "SELECT id,url,cpt,title FROM ".URL_TABLE." where date LIKE '".date("Y-m-d")."%' ORDER BY cpt DESC LIMIT 0,10";
$rqt = mysql_query($sql) or die("Error with $sql");
while ($liste = mysql_fetch_row($rqt)) {

   # Data
  $shortlink = "http://murl.fr/".$liste[0];
  $url = substr($liste[1], 0, 100);
  $cpt = $liste[2];
  $title = $liste[3];

  # Print
  echo '<tr><td><a href="'.$shortlink.'" title="'.$url.'">'.$shortlink.'</a></td><td>'.$cpt.'</td>
  <td>'.$lilurl->get_share_links($shortlink,$title).'</td>
  </tr>';
}
?>
</table>
</div>
