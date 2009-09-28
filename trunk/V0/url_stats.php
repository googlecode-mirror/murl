<?php /* index.php ( lilURL implementation ) */

//infos générales sur le lien
$rs = mysql_query('SELECT * FROM '.URL_TABLE.' WHERE id="'.$id.'"');
if($liste = mysql_fetch_array($rs))
{
	$no_content = true;
    $nb = $liste["id"];
	$url = $liste["url"];
	$title = $liste["title"];
	if($title=="")
		$title=$lilurl->get_url_title($url,$id);
	
	$gain = round((((strlen($url)-strlen('http://murl.fr/'.$id))/strlen($url))*100),0);
	$diff = strlen($url)-strlen('http://murl.fr/'.$id);
	
	
	//Les informations générales
	$msg .= '<div style="float:left;width:49%"><h3>Informations</h3><p style="text-align:left;"><b>Lien court :</b> <a href="http://murl.fr/'.$id.'" title="'.$title.'">http://murl.fr/'.$id.'</a><br />
	<b>Original :</b> <a href="http://murl.fr/'.$id.'" title="'.$title.'">'.$url.'</a><br />
	<b>Titre :</b> '.$title.'
	</p>
	<em>Le lien est passé de '.strlen($url).' caractères à '.strlen('http://murl.fr/'.$id).' soit '.$diff.' caractères ('.$gain.'%)</em>
			<p class="success">Partagez-le sur : '.$lilurl->get_share_links('http://murl.fr/'.$id,$title).'</p>
			</div>';
	//$msg .= '</div>';

/*Bloc des statistiques*/

/* Clics horaires sur le lien pour ajourd'hui */
$msg .= '<div style="width:49%;float:right;"><h3>Clics horaires du jour</h3>';
$rs = mysql_query('SELECT hour,clics FROM '.STATS_TABLE.' WHERE id="'.$id.'" AND date=CURRENT_DATE() ORDER By hour ASC');
if(mysql_num_rows($rs))
{
	while($li = mysql_fetch_array($rs))
		$hour[$li[0]] = $li[1];
	for($i=0;$i<24;$i++)
	{
		if(!$hour[$i])
			$hour[$i]=0;
		$ligne_hour .= '<th>'.$i.'</th>';
		$ligne_clics .='<td>'.$hour[$i].'</td>';
		if($donnees_graph)
			$donnees_graph .= ',';
		$donnees_graph.=$hour[$i];
		$grille_x.='|'.$i;
		if($max_y<$hour[$i])
			$max_y=$hour[$i];
	}
	//$msg .= '<table><tr><th>heure</th>'.$ligne_hour.'</tr><tr><td>clics</td>'.$ligne_clics.'</tr></table>';
	// le graphique qui va bien ...
	$msg .= '<img src="http://chart.apis.google.com/chart?cht=lc&chbh=a&chs=400x200&chxl=0:'.$grille_x.'|1:|0|'.$max_y.'&chg=1,'.$max_y.',1,23&chxt=x,y&chds=0,'.$max_y.'&chd=t:'.$donnees_graph.'" />';
}
else
	$msg.='Aucun clic aujourd\'hui.';
/* Clics horaires sur le lien forever ^^ */
$msg .= '<h3>Clics horaires (global)</h3>';
$rs = mysql_query('SELECT hour,SUM(clics) as cliclics FROM '.STATS_TABLE.' WHERE id="'.$id.'" GROUP By hour ORDER By hour ASC');
if(mysql_num_rows($rs))
{
	while($li = mysql_fetch_array($rs))
		$hour[$li[0]] = $li[1];
	for($i=0;$i<24;$i++)
	{
		if(!$hour[$i])
			$hour[$i]=0;
		//$ligne_hour .= '<th>'.$i.'</th>';
		//$ligne_clics .='<td>'.$hour[$i].'</td>';
		if($donnees_graph_glob)
			$donnees_graph_glob .= ',';
		$donnees_graph_glob.=$hour[$i];
		$grille_x_glob.='|'.$i;
		if($max_y_glob<$hour[$i])
			$max_y_glob=$hour[$i];
	}
	//$msg .= '<table><tr><th>heure</th>'.$ligne_hour.'</tr><tr><td>clics</td>'.$ligne_clics.'</tr></table>';
	// le graphique qui va bien ...
	$msg .= '<img src="http://chart.apis.google.com/chart?cht=lc&chbh=a&chs=400x200&chxl=0:'.$grille_x_glob.'|1:|0|'.$max_y_glob.'&chg=1,'.$max_y_glob.',1,23&chxt=x,y&chds=0,'.$max_y_glob.'&chd=t:'.$donnees_graph_glob.'" />';
}
else
	$msg.='Ce lien n\'a jamais été cliqué</div>';

/*$msg .= '<h3>Clics quotidiens</h3>';
$rs = mysql_query('SELECT DATE_FORMAT(date , "%e/%m") as date_fr ,UNIX_TIMESTAMP(date) as date_unix,SUM(clics) as cliclics FROM '.STATS_TABLE.' WHERE id="'.$id.'" GROUP By date ORDER By date ASC');
if(mysql_num_rows($rs))
{
	$dates='0:';
	while($li = mysql_fetch_array($rs))
	{
		if($clics)
			$sep = ',';
		$clics .= $sep.$li["cliclics"];
		//$dates_unix .= $sep.$li["date_unix"];
		$dates .= '|'.$li["date_fr"];
		if($li[1]>$max_clics)
			$max_clics=$li["cliclics"];
	}
	$msg .= '<img src="http://chart.apis.google.com/chart?cht=lc&chbh=a&chs=400x200&chxl='.$dates.'|1:|0|'.$max_clics.'&chg=1,'.$max_clics.',1,23&chxt=x,y&chds=0,'.$max_clics.'&chd=t:'.$clics.'" />';
}
*/
$msg .= '</div><br style="clear:both;" />';
}
else//le lien n'existe pas...
{
	header('HTTP/1.0 404 Not Found');
    header('Status: 404 Not Found');
	$msg = '<p class="error">Désolé, mais ce code ne correspond pas à une adresse.</p>';
}
?>