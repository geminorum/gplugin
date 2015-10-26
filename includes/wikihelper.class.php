<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginWikiHelper' ) ) { class gPluginWikiHelper extends gPluginClassCore
{

/*

http://www.labnol.org/internet/tools/using-wikipedia-api-demo-source-code-example/3076/
http://stackoverflow.com/questions/964454/how-to-use-wikipedia-api-if-it-exists
http://www.mediawiki.org/wiki/API
http://www.mediawiki.org/wiki/API:Tutorial
http://www.ibm.com/developerworks/library/x-phpwikipedia/

**/

	// http://www.barattalo.it/2010/08/29/php-bot-to-get-wikipedia-definitions/
	function wikidefinition($s)
	{
		$url = "http://fa.wikipedia.org/w/api.php?action=opensearch&search=".urlencode($s)."&format=xml&limit=1";
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
		curl_setopt($ch, CURLOPT_POST, FALSE);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_NOBODY, FALSE);
		curl_setopt($ch, CURLOPT_VERBOSE, FALSE);
		curl_setopt($ch, CURLOPT_REFERER, "");
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; he; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8");
		$page = curl_exec($ch);
		$xml = simplexml_load_string($page);
		if ( (string)$xml->Section->Item->Description) {
			return array((string)$xml->Section->Item->Text, (string)$xml->Section->Item->Description, (string)$xml->Section->Item->Url);
		} else {
			return "";
		}
	}

	// originally from : http://wordpress.org/plugins/wikipedia-search-and-display-widget/
	//	add_action('wp_ajax_nopriv_wikipedia_search', 'wikipedia_get');
	//	add_action('wp_ajax_wikipedia_search', 'wikipedia_get');
	function wikipedia_get()
	{
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$_POST['wiki_url'] . "/w/api.php?format=xml&action=opensearch&search=" . strtolower($_POST['keywords']) . "&limit=" . $_POST['no_items']);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_MAXREDIRS,10);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,100);
		curl_setopt($ch,CURLOPT_USERAGENT,"wikipedia search and display widget");
		curl_setopt($ch,CURLOPT_HTTP_VERSION,'CURLOPT_HTTP_VERSION_1_1');
		$data = curl_exec($ch);

		$xml = simplexml_load_string($data);

		$counter =0;

		if ($xml) {

			foreach($xml->Section->Item as $data => $value){

				echo "<li>";
				if (isset($value->Image[0]['source'])){
					echo "<p><span><a href='" . $value->Url . "'><img src='" . $value->Image[0]['source'] . "' /></a></p>";
				}
				echo "<p><a href='" . $value->Url . "'>" . $value->Text . "</a> | <a class='wikipedia-widget-link' title='click to expand' onclick='javascript:if(document.getElementById(\"wikipedia_widget_" . $counter. "\").style.display==\"block\"){document.getElementById(\"wikipedia_widget_" . $counter. "\").style.display=\"none\"}else{document.getElementById(\"wikipedia_widget_" . $counter. "\").style.display=\"block\"};'>+</a>";
				echo "<span id='wikipedia_widget_" . $counter++ . "'>" . $value->Description . "</span></p></li>";
			}
		}

		die(); // this is required to return a proper result
	}
} }
