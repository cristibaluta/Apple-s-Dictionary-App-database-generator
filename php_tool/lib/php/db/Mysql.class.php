<?php

class php_db_Mysql {
	public function __construct(){}
	static function connect($params) {
		$c = mysql_connect(($params->host . (($params->port === null ? "" : ":" . $params->port))) . (($params->socket === null ? "" : ":" . $params->socket)), $params->user, $params->pass);
		if(!mysql_select_db($params->database, $c)) {
			throw new HException("Unable to connect to " . $params->database);
		}
		return new php_db__Mysql_MysqlConnection($c);
	}
	function __toString() { return 'php.db.Mysql'; }
}
