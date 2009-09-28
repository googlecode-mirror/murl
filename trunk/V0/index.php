<?php /* index.php ( lilURL implementation ) */
include $_SERVER['DOCUMENT_ROOT'].'/includes/conf.php'; // <- site-specific settings
//Si pas de requête de création, et si le script appelé n'est pas uniquement l'index (/ ou index.php)
if(isset($_REQUEST['longurl']) || $_SERVER['REQUEST_URI']!="/" )
{
	include $_SERVER['DOCUMENT_ROOT'].'/includes/lilurl.php'; // <- lilURL class file
	$lilurl = new lilURL();
}
$msg = '';
$no_content = false;

// if the form has been submitted
if(isset($_REQUEST['longurl']) && strpos($_REQUEST['longurl'],'http://murl.fr/')===false) {
  # Escape bad characters from the user's url
  $longurl = trim(mysql_escape_string($_REQUEST['longurl']));

  # Anti-spam
  $spam = 0;
  foreach($words as $word) {
    if(strstr($longurl, $word)) {
      $msg = '<p class="error">Spam detecté !<br /><b>'.$word.'</b> n\'est pas accepté ici ...</p>';
      $spam = 1;
      break;
    }
  }

  # Not a spam
  if(!$spam) {
    # set the protocol to not ok by default
    $protocol_ok = false;
	
    # if there's a list of allowed protocols, 
    # check to make sure that the user's url uses one of them
    if(count($allowed_protocols)) {
      foreach($allowed_protocols as $ap) {
        if(strtolower(substr($longurl, 0, strlen($ap))) == strtolower($ap)) {
          $protocol_ok = true;
	  break;
        }
      }

    } else {
      $protocol_ok = true;
    }
		
    # add the url to the database
    if ( $protocol_ok && $lilurl->add_url($longurl) )
	{
		$id_court = $lilurl->get_id($longurl);
		$sql = 'SELECT cpt,url,title FROM '.URL_TABLE.' where id = "'.$id_court.'"';
		$rqt = mysql_query($sql) or die('Error with '.$sql);
		while ($liste = mysql_fetch_row($rqt)) {
		    $nb = $liste[0];
			$url= $liste[1];
			$title= $liste[2];
			if($title=="")
				$title=$lilurl->get_url_title($longurl,$id_court);
	    }
		
		if (REWRITE) // mod_rewrite style link
			$url = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).$id_court;
		else // regular GET style link
			$url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'].'?id='.$id_court;
		
		//Si on souhaite une redirection vers un service de partage ...
		$shareto = $_REQUEST['shareto'];
		$location='';
		switch($shareto)
		{
			case "twitter":
				if(strlen($title)>100)
					$title=substr($title,0,100);
				$location='http://twitter.com/home?status='.$title.' : '.$url;
				break;
			case "facebook":
				$location='http://www.facebook.com/share.php?u='.$url;
				break;
			default :
				$location=$url.'/stats';
				break;
		}
		
		if($location)
		{
			header('Location: '.$location,TRUE, 301);
			exit();
		}
		
	}
	elseif ( !$protocol_ok )
		$msg = '<p class="error">Protocole invalide !</p>';
	else
		$msg = '<p class="error">La création a échoué pour une raison mystérieuse ...</p><p>'.$longurl.' n\' pas pu être réduite ...</p>';
  }
}
else // if the form hasn't been submitted, look for an id to redirect to
{
	if ( isSet($_GET['id']) ) // check GET first
	{
		$id = mysql_escape_string($_GET['id']);
	}
	elseif ( REWRITE ) // check the URI if we're using mod_rewrite
	{
		$explodo = explode('/', $_SERVER['REQUEST_URI']);
		$id = mysql_escape_string($explodo[count($explodo)-1]);
	}
	else // otherwise, just make it empty
	{
		$id = '';
	}
	
	//Si le "id" est uniquement alphanumérique, c'est bien un id
	if(preg_match('/^([A-Za-z0-9]*)$/', $id, $rs))
	{
		if(strstr($_SERVER['REQUEST_URI'], "/stats")) {
	
			$id = preg_replace("#/([a-zA-Z0-9]+)/stats(.?)#i", "$1", $_SERVER['REQUEST_URI']);
			include('url_stats.php'); //next to implement
		}
		
		// if the id isn't empty and it's not this file, redirect to it's url
		elseif ( $id != '' && $id != basename($_SERVER['PHP_SELF']))
		{	
			if ( $location != -1 )	
				$lilurl->redirect_to($id);
			else
			{
				//ajout du 404
				header('HTTP/1.0 404 Not Found');
                header('Status: 404 Not Found');
				$msg = '<p class="error">Désolé, mais ce code ne correspond pas à une adresse.</p>';
			}
		}
	}
	else
	{
		switch($id)
		{
			case "robots.txt":?>
				User-agent: *
				Allow: /
				<?php
				exit;
			case "-stats":
				include("stats.php");
				$no_content=true;
				break;
			case "-rss":
				include('rss.php');
				exit;
			case "-rand":
				$id = mysql_result(mysql_query('SELECT id FROM '.URL_TABLE.' WHERE RAND() LIMIT 1'),0);
				$lilurl->redirect_to($id);
			default:
				exit;
		}
	}
}
// print the page
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html  xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="fr">
	<head>
		<meta content="text/html; charset=UTF-8" http-equiv="Content-Type"/>
		<link rel="icon" type="image/png" href="http://s.murl.fr/img/icon.png" />
		<link rel="Shortcut Icon" href="http://s.murl.fr/img/icon.png" type="image/png" />
		<link rel="alternate" type="application/rss+xml" title="mURL.fr : les liens" href="http://murl.fr/-rss" />
		<link rel="alternate" type="application/rss+xml" title="mURL.fr : le blog" href="http://blog.murl.fr/rss" />
		<title><?php echo PAGE_TITLE; ?></title>
		<style type="text/css" media="all">
		body {
			font-size: .8em;
			text-align: center;
			color: #333;
			background-color: #fff;
			margin: 0px;
			margin-top: 2em;
		}
		h1 {
			font-size: 2em;
			padding: 0;
			margin: 0;
		}
		h2{margin-top:2px;}
		h4 {
			font-size: 1em;
			font-weight: bold;
		}
		h4,p{margin-top:0;margin-bottom:0}
		form {
			background-color: #eee;
			border: 1px solid #ccc;
			margin-bottom:10px;
			margin-left: auto;
			margin-right: auto;
			padding: 1em;
		}
		fieldset {
			border: 0;
			margin: 0;
			padding: 0;
		}
		a {
			color: #09c;
			text-decoration: none;
			font-weight: bold;
		}
		a:visited {
			color: #07a;
		}
		a:hover {
			color: #c30;
		}
		a img{
			border: none;
		}
		.error, .success {
			font-size: 1.2em;
			font-weight: bold;
		}
		.error {
			color: #f00;
		}
		.success {
			color: #000;
		}
		.top {
			margin-top: 50px;
			width: 35em;
			margin-left: auto;
			margin-right: auto;
		}
		tableau {
			border-collapse:collapse;
		}
		.tableau thead {
			border:1px solid #eee;
			background-color:#eee;
			border-collapse:collapse
		}
		.tableau td {
			padding : 0.4em ;
			border-spacing:0;
			border:0px solid #666;
			empty-cells: show;
			background-color:#fafafa;
			text-align: left;
			font-weight: normal;
		}
		.tableau td a {
			font-weight: normal;
		}
		.tableau td.name {
			text-align: left;
		}
		.tableau caption {
			font-size:2em;
			padding:1em 0 0.5em 1em;
		}
		.tableau tr.impaire td {
			background-color: #eee;
		}
		.tableau th {
			color: #666;
			padding: 0.7em;
			font-size:0.9em;
			font-weight:bold;
			text-align:center;
		}
		
		.spacer{clear:both;}
	</style>
	<style type="text/css" media="screen">
		form{width:35em;}
		#footer{width:35em;margin-left:auto;margin-right:auto;text-align:right}
		#footer h4{display:inline;float:left}
		#footer div{display:inline;margin-left:4em}
		#footer p{width:100%;text-align:center}
	</style>
	<style type="text/css" media="handheld">
		body{margin:0}
		#footer{bottom:0;position:fixed;margin-left:auto;margin-right:auto;width:98%}
		#footer div{width:33%;float:left;background-color:#ccc;vertical-align:middle;line-height:2em;border-left:1px solid #666}
		#footer div a{display:block;color:#000}
	</style>
	</head>
	<body <?php if(!$no_content): ?>onload="document.getElementById('longurl').focus()"<?php endif; ?>>
		<h1><a href="http://murl.fr/" title="mURL : parce que la taille compte, finalement">mURL.fr</a></h1>
		<h2>parce que la taille compte, finalement</h2>
		<?php if($msg) echo $msg;
if(!$no_content): ?>
		<form action="/" method="post">
		<fieldset>
			<label for="longurl">URL à raccourcir :</label>
			<input type="text" name="longurl" id="longurl" size="25" />
			<input type="submit" name="submit" id="submit" value="Ok" />
		</fieldset>
		</form>
<?php endif; ?>
		<div id="footer">
<?php if(!$no_content): ?>
			<h4>Bookmarklets</h4>
			<div><a href="javascript:void(location.href='http://murl.fr/?longurl='+encodeURIComponent(location.href))" rel="no-follow" title="Créez un lien court grâce à mURL.fr">mURL</a></div>
			<div><a href="javascript:void(location.href='http://murl.fr/?shareto=twitter&longurl='+encodeURIComponent(location.href))" rel="no-follow" title="Partagez un lien court sur Twitter grâce à mURL.fr">Twitter</a></div>
			<div><a href="javascript:void(location.href='http://murl.fr/?shareto=facebook&longurl='+encodeURIComponent(location.href))" rel="no-follow"  title="Partagez un lien court sur Facebook grâce à mURL.fr">Facebook</a></div>
			<br class="spacer" />
<?php endif; ?>
			<h4>Infos</h4>
			<div><a href="http://blog.murl.fr/" title="mURL : le blog">blog</a></div>
			<div><a href="http://api.murl.fr/" title="Développeurs, découvrez l'API de mURL.fr">api</a></div>
			<div><a href="http://murl.fr/-stats" title="Statistiques de mURL">stats</a></div>
			</ul>
			<br class="spacer" />
			<p>Un service gratuit proposé par <a href="http://blog.loicg.net" title="Loïc Gerbaud, développeur web">LoïcG</a> (alias <a href="http://twitter.com/chibani" title="profil twitter">Chibani</a>)</p>
		</div>
		<script type="text/javascript">
			var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
			document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
		</script>
		<script type="text/javascript">
			try {
				var pageTracker = _gat._getTracker("UA-2703255-12");
				pageTracker._trackPageview();
			}catch(err) {}
		</script>
	</body>
</html>
