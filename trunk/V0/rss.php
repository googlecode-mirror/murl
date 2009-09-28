<?php
	$rq = 'SELECT id,url,date,cpt,title FROM '.URL_TABLE.' WHERE 1 ORDER By date DESC LIMIT 0,10';//les 10 derniers liens
	$rs = mysql_query($rq);
	if(mysql_num_rows($rs)>0)
	{
		ob_start();
		while($li = mysql_fetch_array($rs))
		{
		//id 	url 	date 	cpt 	title
		$date = date('r',strtotime($li["date"])); // date du style : Tue, 07 Jul 2009 10:22:47 +0000
		if(!$date_last_added)
			$date_last_added = $date;
		?>
		<item>
		<title><?php if($li["title"]):echo $li["title"];else:echo 'sans titre'; endif; ?></title>
		<link>http://murl.fr/<?php echo $li["id"]; ?>/stats</link>
		<pubDate><?php echo $date; ?></pubDate>
		<dc:creator>mURL</dc:creator>
		<guid isPermaLink="false">http://murl.fr/<?php echo $li["id"]; ?>/stats</guid>
		<description><?php echo $li["title"]; ?></description>
		<slash:comments><?php echo $li["clics"]; ?></slash:comments> 
		</item>
		<?php
		}
		$liste_items = ob_get_clean();
	}
header("Content-Type: text/plain; charset=UTF-8");
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:slash="http://purl.org/rss/1.0/modules/slash/" version="2.0">
<channel>
<title>mURL</title>
<link>http://murl.fr/</link>
<description>Parce que la taille compte, finalement</description>
<lastBuildDate><?php echo $date_last_added; ?></lastBuildDate>
<generator>http://murl.fr/</generator>
<language>fr</language>
<sy:updatePeriod>hourly</sy:updatePeriod>
<sy:updateFrequency>1</sy:updateFrequency>
<?php echo $liste_items; ?>
</channel>
</rss>
