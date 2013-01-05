<?php

class Main {
	public function __construct() {
		if( !php_Boot::$skip_constructor ) {
		$this->sources = new Hash();
		$this->readDatabase();
	}}
	public $sources;
	public function readDatabase() {
		php_Lib::println("Connecting to database...");
		$cnx = php_db_Mysql::connect(_hx_anonymous(array("host" => "localhost", "port" => 8889, "user" => "root", "pass" => "root", "socket" => null, "database" => "RODictionary")));
		php_Lib::println("Connected succesfully!");
		$sourcesResult = $cnx->request("SELECT * FROM Source");
		$startTime = haxe_Timer::stamp();
		$rset = $cnx->request("SELECT * FROM Definition LIMIT 50");
		php_Lib::println(((("Found " . $rset->getLength()) . " lexicons, and took ") . (haxe_Timer::stamp() - $startTime)) . " seconds");
		$this->writeXml($rset);
		$cnx->close();
	}
	public function writeXml($rset) {
		$fout = php_io_File::write("RomanianDictionary.xml", false);
		$fout->writeString(_hx_deref(new haxe_Template(haxe_Resource::getString("header")))->execute(_hx_anonymous(array()), null));
		$wordTemplate = new haxe_Template(haxe_Resource::getString("word"));
		$»it = $rset;
		while($»it->hasNext()) {
		$row = $»it->next();
		{
			$properties = _hx_anonymous(array("id" => $row->id, "word" => $row->lexicon, "definition" => $row->htmlRep));
			$fout->writeString($wordTemplate->execute($properties, null) . "\x0A");
			unset($properties);
		}
		}
		$fout->writeString(_hx_deref(new haxe_Template(haxe_Resource::getString("footer")))->execute(_hx_anonymous(array()), null));
		$fout->close();
	}
	public function __call($m, $a) {
		if(isset($this->$m) && is_callable($this->$m))
			return call_user_func_array($this->$m, $a);
		else if(isset($this->»dynamics[$m]) && is_callable($this->»dynamics[$m]))
			return call_user_func_array($this->»dynamics[$m], $a);
		else if('toString' == $m)
			return $this->__toString();
		else
			throw new HException('Unable to call «'.$m.'»');
	}
	static $OUTPUT = "RomanianDictionary.xml";
	static function main() {
		new Main();
	}
	function __toString() { return 'Main'; }
}
