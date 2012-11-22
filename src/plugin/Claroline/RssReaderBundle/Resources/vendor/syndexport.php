<?php
/**
 * Syndication Export
 * Export RSS and atom feed to PHP array or JSON
 * PHP 5
 *
 * @author     MILLET Maxime <maxime@milletmaxime.net>
 * @license    CC-BY-SA see http://www.milletmaxime.net
 * @version    1.1 (07/15/2012)
 * @doc		   http://milletmaxime.net/142510-php-class-syndexport-parser-flux-atom-et-flux-rss.html
 * Thanks to : quent1.fr
 */

class SyndExport
{
	private $p_feed;
	private $p_type;
	private $p_count;
	/*
		Constructor : 2 parameters whose the second is optional
		the first parameter is feed content
		the second is type of the feed : RSS or ATOM. If empty, he will be automatically detected by detector()
	*/
	public function __construct($feed,$type=NULL)
	{
		$this->p_feed = new SimpleXMLElement($feed);
		if($type==NULL) $this->p_type = $this->detector();
		else $this->p_type =$type;
		unset($feed,$type);
		if($this->p_type == "RSS") $this->p_count = count($this->p_feed->channel->item);
		elseif($this->p_type == "ATOM") $this->p_count = count($this->p_feed->entry);
	}
	/* ********************************************** *\
	\* functions which detect and return type of feed */
	private function detector()
	{
		$racine=$this->p_feed->getName();
		if($racine == "rss")		return "RSS";
		elseif($racine == "feed")	return "ATOM";
		else						return false;	//It isn't a feed
	}
	public function returnType()
	{
		return $this->p_type;//Returns the type of feed
	}
	/* ********************************************** */
	public function countItems() //Returns the number of items
	{
		return $this->p_count; //Calculated by the constructor
	}
	private function extractAtomInfos($other=NULL) //Extracts ATOM information
	{
		if(!empty($this->p_feed->title))		 	$infos["title"] 	  =	(string)$this->p_feed->title;
		if(!empty($this->p_feed->logo))		 		$infos["titleImage"]  =	(string)$this->p_feed->logo;
		if(!empty($this->p_feed->icon))		 		$infos["icon"] 		  =	(string)$this->p_feed->icon;
		if(!empty($this->p_feed->subtitle)) 		$infos["description"] = (string)$this->p_feed->subtitle;
		if(!empty($this->p_feed->link[href])) 		$infos["link"]		  = (string)$this->p_feed->link[href];
		if(!empty($this->p_feed->language))			$infos["language"]	  = (string)$this->p_feed->language;
		if(!empty($this->p_feed->author->name)) 	$infos["author"]	  =	(string)$this->p_feed->author->name;
		if(!empty($this->p_feed->author->email))	$infos["email"] 	  = (string)$this->p_feed->author->email;
		if(!empty($this->p_feed->updated))			$infos["last"] 		  = (string)$this->p_feed->updated;
		if(!empty($this->p_feed->rights))			$infos["copyright"]	  = (string)$this->p_feed->rights;
		if(!empty($this->p_feed->generator))		$infos["generator"]	  = (string)$this->p_feed->generator;
		if(!empty($this->p_feed->$other))			$infos["other"]	 	  = (string)$this->p_feed->$other;
		return $infos;
	}
	private function extractRssInfos($other=NULL) //Extracts RSS information
	{
		$ns = $this->p_feed->getNamespaces(true);
		if(!empty($this->p_feed->channel->title))		  $infos["title"]		 = (string)$this->p_feed->channel->title;
		if(!empty($this->p_feed->channel->image->url))	  $infos["titleImage"]	 = (string)$this->p_feed->channel->image->url;
		if(!empty($this->p_feed->channel->description))	  $infos["description"]	 = (string)$this->p_feed->channel->description;
		if(!empty($this->p_feed->channel->link))	 	  $infos["link"]		 = (string)$this->p_feed->channel->link;
		if(!empty($this->p_feed->channel->language))	  $infos["language"]	 = (string)$this->p_feed->channel->language;
		if(!empty($this->p_feed->channel->author))		  $infos["email"]		 = (string)$this->p_feed->channel->author;
		if(!empty($this->p_feed->channel->managingEditor))$infos["author"]	 	 = (string)$this->p_feed->channel->managingEditor;
		if(!empty($this->p_feed->channel->lastBuildDate)) $infos["last"] 	 	 = (string)$this->p_feed->channel->lastBuildDate;
		if(!empty($this->p_feed->channel->copyright))	  $infos["copyright"] 	 = (string)$this->p_feed->channel->copyright;
		if(!empty($this->p_feed->channel->generator))	  $infos["generator"] 	 = (string)$this->p_feed->channel->generator;
		if(!empty($this->p_feed->channel->$other))	  	  $infos["other"] 		 = (string)$this->p_feed->channel->$other;
		return $infos;
	}
	private function extractAtomItems($max,$other=NULL)//Extracts ATOM items
	{
		$ns = $this->p_feed->getNamespaces(true);
		$this->p_feed->registerXPathNamespace('atom', $ns[""]);
		for($i=0;$i!=$max;$i++)
		{
			if(!empty($this->p_feed->entry[$i]->title))		  $items[$i]["title"] = (string)$this->p_feed->entry[$i]->title;
			if(!empty($this->p_feed->entry[$i]->summary)) 	  $items[$i]["description"]=(string)$this->p_feed->entry[$i]->summary;
			elseif(!empty($this->p_feed->entry[$i]->content)) $items[$i]["description"] = (string)$this->p_feed->entry[$i]->content;
			if(!empty($this->p_feed->entry[$i]->link[href]))  $items[$i]["link"] = (string)$this->p_feed->entry[$i]->link[href];
			if(!empty($this->p_feed->entry[$i]->author)) 	  $items[$i]["author"] = (string)$this->p_feed->entry[$i]->author;
			if(!empty($this->p_feed->entry[$i]->updated)) 	  $items[$i]["date"] = (string)$this->p_feed->entry[$i]->updated;
			if(!empty($this->p_feed->entry[$i]->id))		  $items[$i]["guid"] = (string)$this->p_feed->entry[$i]->id;
			/****Enclosure****/
			$j = $i+1;
			$enclosure = $this->p_feed->xpath("atom:entry[$j]/atom:link[@rel='enclosure']");
			if(!empty($enclosure[0][href]))
			{
				$items[$i]["media"]["url"]   =	(string)$enclosure[0][href];
				if(!empty($enclosure[0][type]))	 $items[$i]["media"]["type"]  =	(string)$enclosure[0][type];
				if(!empty($enclosure[0][length]))$items[$i]["media"]["length"]=	(string)$enclosure[0][length];
			}
			/****Enclosure****/
			if(!empty($this->p_feed->entry[$i]->$other))	 $items[$i]["other"] = (string)$this->p_feed->entry[$i]->$other;
		}
		return $items;
	}
	private function extractRssItems($max,$other=NULL)//Extracts RSS items
	{
		$ns = $this->p_feed->getNamespaces(true);
		for($i=0;$i!=$max;$i++)
		{
			if(!empty($this->p_feed->channel->item[$i]->title))		  $items[$i]["title"] = (string)$this->p_feed->channel->item[$i]->title;
			if(!empty($this->p_feed->channel->item[$i]->description)) $items[$i]["description"]=(string)$this->p_feed->channel->item[$i]->description;
			elseif(isset($ns['content']))if(!empty($content_encoded)) $items[$i]["description"]=(string)$this->p_feed->channel->item[$i]->children($ns['content']);//namespace content
			if(!empty($this->p_feed->channel->item[$i]->link))		  $items[$i]["link"] = (string)$this->p_feed->channel->item[$i]->link;
			if(!empty($this->p_feed->channel->item[$i]->author)) 	  $items[$i]["author"] = (string)$this->p_feed->channel->item[$i]->author;
			if(!empty($this->p_feed->channel->item[$i]->pubDate)) 	  $items[$i]["date"] = (string)$this->p_feed->channel->item[$i]->pubDate;
			if(!empty($this->p_feed->channel->item[$i]->guid))		  $items[$i]["guid"] = (string)$this->p_feed->channel->item[$i]->guid;
			/****Start Enclosure****/
			 	if(!empty($this->p_feed->channel->item[$i]->enclosure['url']))
				{
			  		$items[$i]["media"]["url"] =(string)$this->p_feed->channel->item[$i]->enclosure[url];

					if(!empty($this->p_feed->channel->item[$i]->enclosure[type])){
			  			$items[$i]["media"]["type"]=(string)$this->p_feed->channel->item[$i]->enclosure[type]; }

					if(!empty($this->p_feed->channel->item[$i]->enclosure[length])){
			  			$items[$i]["media"]["length"]=(int)$this->p_feed->channel->item[$i]->enclosure[length]; }
				}
			/****End Enclosure****/
			if(!empty($this->p_feed->channel->item[$i]->guid))		$items[$i]["guid"] = (string)$this->p_feed->channel->item[$i]->guid;
			if(!empty($this->p_feed->channel->item[$i]->$other))	$items[$i]["guid"] = (string)$this->p_feed->channel->item[$i]->$other;
		}
		return $items;
	}
	public function exportInfos($type="array",$other=NULL)//Function to exports feed information
	{
		if($type=="json")
		{
			if($this->p_type == "RSS") 		return json_encode($this->extractRssInfos($other));
			elseif($this->p_type == "ATOM") return json_encode($this->extractAtomInfos($other));
		}
		else
		{
			if($this->p_type == "RSS") 		return $this->extractRssInfos($other);
			elseif($this->p_type == "ATOM") return $this->extractAtomInfos($other);
		}
	}
	// Exports entries
	public function exportItems($max=20,$type="array",$other=NULL) //Function to exports feed items
													               //$max unmetered when is equal -1
	{
		if($max > $this->p_count || $max==-1) $max = $this->p_count;
		if($type=="json")
		{
			if($this->p_type == "RSS") 		return json_encode($this->extractRssItems($max,$other));
			elseif($this->p_type == "ATOM") return json_encode($this->extractAtomItems($max,$other));
		}
		else
		{
			if($this->p_type == "RSS") 		return $this->extractRssItems($max,$other);
			elseif($this->p_type == "ATOM") return $this->extractAtomItems($max,$other);
		}
	}
	
	public function exportOtherInfo($info)
	{
		if($this->p_type == "RSS")
		{
			if(!empty($this->p_feed->channel->$info))$export = (string)$this->p_feed->channel->$info;
			else $export = NULL;
			return $export;
		}
		if($this->p_type == "ATOM")
		{
			if(!empty($this->p_feed->$info))$export = (string)$this->p_feed->$info;
			else $export = NULL;
			return $export;
		}
	}
}
// Sorry for my bad english ;) \\
?>