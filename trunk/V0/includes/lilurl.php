<?php /* lilurl.php ( lilURL class file ) */

class lilURL
{
	function lilURL()
	{
		mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS) or die('Could not connect to database');
		mysql_select_db(MYSQL_DB) or die('Could not select database');
	}
	// return the id for a given url (or -1 if the url doesn't exist)
	function get_id($url)
	{
		$q = 'SELECT id FROM '.URL_TABLE.' WHERE (url="'.$url.'")';
		$result = mysql_query($q);

		if ( mysql_num_rows($result) )
		{
			$row = mysql_fetch_array($result);
			return $row['id'];
		}
		else
		{
			return -1;
		}
	}

	// return the url for a given id (or -1 if the id doesn't exist)
	function get_url($id)
	{
		$q = 'SELECT url FROM '.URL_TABLE.' WHERE (id="'.$id.'")';
		$result = mysql_query($q);

		if ( mysql_num_rows($result) )
		{
			$row = mysql_fetch_array($result);
			return $row['url'];
		}
		else
		{
			return -1;
		}
	}
	
	function redirect_to($id)
	{
		$this->update_stats($id);
		$location = $this->get_url($id);
		header('Location: '.$location,TRUE, 301);// ajout du code HTTP 301
		exit();
	}
	
	// add a url to the database
	function add_url($url)
	{
		// check to see if the url's already in there
		$id = $this->get_id($url);
		
		// if it is, return true
		if ( $id != -1 )
			return true;
		else // otherwise, put it in
		{
			$id = $this->get_next_id($this->get_last_id());
			$q = 'INSERT INTO '.URL_TABLE.' (id, url, date) VALUES ("'.$id.'", "'.$url.'", NOW())';
			$this->get_url_title($url,$id);
			return mysql_query($q);
		}
	}

	// return the most recent id (or -1 if no ids exist)
	function get_last_id()
	{	
		$q = 'SELECT id FROM '.URL_TABLE.' ORDER BY date DESC LIMIT 1';
		$result = mysql_query($q);

		if ( mysql_num_rows($result) )
		{
			$row = mysql_fetch_array($result);
			return $row['id'];
		}
		else
		{
			return -1;
		}
	}	

	// return the next id
	function get_next_id($last_id)
	{ 
	
		// if the last id is -1 (non-existant), start at the begining with 0
		if ( $last_id == -1 )
		{
			$next_id = 0;
		}
		else
		{
			// loop through the id string until we find a character to increment
			for ( $x = 1; $x <= strlen($last_id); $x++ )
			{
				$pos = strlen($last_id) - $x;

				if ( $last_id[$pos] != 'z' )//test by LG, before it was 'z'
				{
					$next_id = $this->increment_id($last_id, $pos);
					break; // <- kill the for loop once we've found our char
				}
			}

			// if every character was already at its max value (z),
			// append another character to the string
			if ( !isSet($next_id) )
			{
				$next_id = $this->append_id($last_id);
			}
		}

		// check to see if the $next_id we made already exists, and if it does, 
		// loop the function until we find one that doesn't
		//
		// (this is basically a failsafe to get around the potential dangers of
		//  my kludgey use of a timestamp to pick the most recent id)
		$q = 'SELECT id FROM '.URL_TABLE.' WHERE (id="'.$next_id.'")';
		$result = mysql_query($q);
		
		if ( mysql_num_rows($result) )
		{
			$next_id = $this->get_next_id($next_id);
		}

		return $next_id;
	}

	// make every character in the string 0, and then add an additional 0 to that
	function append_id($id)
	{
		for ( $x = 0; $x < strlen($id); $x++ )
		{
			$id[$x] = 0;
		}

		$id .= 0;

		return $id;
	}

	// increment a character to the next alphanumeric value and return the modified id
	function increment_id($id, $pos)
	{		
		$char = $id[$pos];
		
		// add 1 to numeric values
		if ( is_numeric($char) )
		{
			if ( $char < 9 )
			{
				$new_char = $char + 1;
			}
			else // if we're at 9, it's time to move to the alphabet
			{
				$new_char = 'A';
			}
		}
		else // move it up the alphabet
		{
			if($char=='Z')
				$new_char='a';
			else
				$new_char = chr(ord($char) + 1);
		}

		$id[$pos] = $new_char;
		
		// set all characters after the one we're modifying to 0
		if ( $pos != (strlen($id) - 1) )
		{
			for ( $x = ($pos + 1); $x < strlen($id); $x++ )
			{
				$id[$x] = 0;
			}
		}

		return $id;
	}
	
	
	//others, added by LG
	
	function update_stats($id)
	{
		//global counter, created by Seb Bilbeau
		$q = 'UPDATE '.URL_TABLE.' set cpt = cpt +1 where id = "'.$id.'"';
		mysql_query($q);
		
		//hourly stats, added by LoïcG
		//needed info
		$date=date("Y-m-d");
		$hour=date("G");
		
		$q_check = 'SELECT clics FROM '.STATS_TABLE.' WHERE id="'.$id.'" AND date="'.$date.'" AND hour="'.$hour.'"';
		$rs_check=mysql_query($q_check);
		if(mysql_num_rows($rs_check)>0)
			$q = 'UPDATE '.STATS_TABLE.' set clics = clics +1 where id = "'.$id.'" AND date="'.$date.'" AND hour="'.$hour.'"';
		else
			$q = 'INSERT INTO '.STATS_TABLE.' (id,date,hour,clics) VALUES ("'.$id.'","'.$date.'","'.$hour.'",1)';
		mysql_query($q);
		
		//Stats sur la langue du visiteur
		if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
		{
			//Récupération de la première langue acceptée
			preg_match('/([a-z]{1,8})-/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'] , $result);
			$q_check = 'SELECT clics FROM '.STATS_LANGUAGE_TABLE.' WHERE id="'.$id.'" AND language="'.$result[1].'"';
			$rs_check=mysql_query($q_check);
			if(mysql_num_rows($rs_check)>0)
				$q = 'UPDATE '.STATS_LANGUAGE_TABLE.' set clics = clics +1 where id = "'.$id.'" AND language="'.$result[1].'"';
			else
				$q = 'INSERT INTO '.STATS_LANGUAGE_TABLE.' (id,language,clics) VALUES ("'.$id.'","'.$result[1].'",1)';
			mysql_query($q);
			
		}
	}
	
	function get_url_title($url,$id=0) {
		if($id && !$url)
		{
			$rs = mysql_query('SELECT title FROM '.URL_TABLE.' WHERE id="'.$id.'"');
			if(mysql_num_rows($rs)==1)
			{
				$title=mysql_result($rs);
				$title=$title[0];
			}
		}
		else
		{
			$fh = fopen($url, "r");
			$str = fread($fh, 7500);  // read the first 7500 characters, it's gonna be near the top
			fclose($fh);
			$str2 = strtolower($str);
			$start = strpos($str2, "<title>")+7;
			$len   = strpos($str2, "</title>") - $start;
			if($len>0)
			{
				$title = trim(substr($str, $start, $len));
			
				if($title && $id)
				{
					$q = 'UPDATE '.URL_TABLE.' SET title="'.$title.'" WHERE id="'.$id.'"';
					mysql_query($q);
				}
			}
		}
		return $title;
	}
	
	/* Let's get social ^^ */
	function get_share_links($url,$title)
	{
		if(strlen($title)>100)
			$title=substr($title,0,100);
		return '<a href="http://twitter.com/home?status='.$title.' : '.$url.'"><img src="http://s.murl.fr/img/twitter.png" alt="Logo Twitter" title="Twitter" /></a>&nbsp;
		<a href="http://www.facebook.com/share.php?u='.$url.'"><img src="http://s.murl.fr/img/facebook.png" alt="Logo Facebook" title="Facebook" /></a>&nbsp;
		<a href="http://del.icio.us/post?url='.$url.'&title='.$title.'"><img src="http://s.murl.fr/img/delicious.png" alt="Logo Delicious" title="Delicious" /></a>&nbsp;
		<a href="http://identi.ca/notice/new?status_textarea='.$url.' : '.$title.'"><img src="http://s.murl.fr/img/identica.png" alt="Logo Identi.ca" title="Identi.ca" /></a>
		';
	}

}
?>
