<?php /* index.php ( lilURL implementation ) */

$msg .= '<div style="float:left;width:45%">';
/* Top 10 des clics du jour */
$msg .= '<h3>Top 10 du jour</h3>';
$rs = mysql_query('SELECT id,SUM(clics) as cliclic FROM '.STATS_TABLE.' WHERE date=CURRENT_DATE() AND id IN (SELECT DISTINCT id FROM '.URL_TABLE.' WHERE 1) GROUP By id ORDER By cliclic DESC LIMIT 0,10');
if(mysql_num_rows($rs))
{
	$msg .='<table class="tableau"><tr><th>URL</th><th>Clics</th></tr>';
	while($li = mysql_fetch_array($rs))
	{
		$url = $lilurl->get_url($li[0]);
		$msg .= '<tr><td><a href="http://murl.fr/'.$li[0].'/stats" title="'.$lilurl->get_url_title('',$li[0]).'">'.$url.'</a></td><td>'.$li[1].'</td></tr>';
	}
	$msg .= '</table>';
	
	/* Stats des clics par heure du jours */
	/*$rs = mysql_query("SELECT hour,SUM(clics) as cliclic FROM lil_stats WHERE date=CURRENT_DATE() GROUP By hour ORDER By hour ASC");
	if(mysql_num_rows($rs))
	{
		$msg .='<table class="tableau"><tr><th>Heure</th><th>Clics</th></tr>';
		while($li = mysql_fetch_array($rs))
			$msg .= '<tr><td>'.$li[0].'</td><td>'.$li[1].'</td></tr>';
		$msg .= '</table>';
		
	}*/
}
else
	$msg.='Aucun clic aujourd\'hui ...';

$msg .= '</div><div style="float:right;width:45%">';

/* Top 10 Fowever */
$msg .= '<h3>Top 10 global</h3>';
$rs = mysql_query('SELECT id,SUM(clics) as cliclic FROM '.STATS_TABLE.' WHERE id IN (SELECT DISTINCT id FROM '.URL_TABLE.' WHERE 1) GROUP By id ORDER By cliclic DESC LIMIT 0,10');
if(mysql_num_rows($rs))
{
	$msg .='<table class="tableau"><tr><th>URL</th><th>Clics</th></tr>';
	while($li = mysql_fetch_array($rs))
	{
		$url = $lilurl->get_url($li[0]);
		$msg .= '<tr><td><a href="http://murl.fr/'.$li[0].'/stats" title="'.$lilurl->get_url_title('',$li[0]).'">'.$url.'</a></td><td>'.$li[1].'</td></tr>';
	}
	$msg .= '</table>';
	
	/* Stats des clics par heure fowever */
	/*$rs = mysql_query("SELECT hour,SUM(clics) as cliclic FROM lil_stats WHERE 1 GROUP By hour ORDER By hour ASC");
	if(mysql_num_rows($rs))
	{
		$msg .='<table class="tableau"><tr><th>Heure</th><th>Clics</th></tr>';
		while($li = mysql_fetch_array($rs))
			$msg .= '<tr><td>'.$li[0].'</td><td>'.$li[1].'</td></tr>';
		$msg .= '</table>';
		
	}*/
	
}
$msg .= '</div><br style="clear:both" />';

/* les derniers ajouts */
$msg .= '</div><div style="float:left;width:45%">';

$msg .= '<h3>10 derniers liens</h3>';
$rs = mysql_query("SELECT id,url,title FROM ".URL_TABLE." ORDER By date DESC LIMIT 0,10");
if(mysql_num_rows($rs))
{
	$msg .='<table class="tableau"><tr><th>URL</th></tr>';
	while($li = mysql_fetch_array($rs))
		$msg .= '<tr><td><a href="http://murl.fr/'.$li[0].'/stats" title="'.$li[2].'">'.$li[1].'</a></td></tr>';
	$msg .= '</table>';
}
$msg .='</div><br style="clear:both" />';
?>
