<?php

class haxe_Template {
	public function __construct($str) {
		if( !php_Boot::$skip_constructor ) {
		$tokens = $this->parseTokens($str);
		$this->expr = $this->parseBlock($tokens);
		if(!$tokens->isEmpty()) {
			throw new HException(("Unexpected '" . $tokens->first()->s) . "'");
		}
	}}
	public $expr;
	public $context;
	public $macros;
	public $stack;
	public $buf;
	public function execute($context, $macros) {
		$this->macros = ($macros === null ? _hx_anonymous(array()) : $macros);
		$this->context = $context;
		$this->stack = new HList();
		$this->buf = new StringBuf();
		$this->run($this->expr);
		return $this->buf->b;
	}
	public function resolve($v) {
		if(_hx_has_field($this->context, $v)) {
			return Reflect::field($this->context, $v);
		}
		$»it = $this->stack->iterator();
		while($»it->hasNext()) {
		$ctx = $»it->next();
		if(_hx_has_field($ctx, $v)) {
			return Reflect::field($ctx, $v);
		}
		}
		if($v == "__current__") {
			return $this->context;
		}
		return Reflect::field(haxe_Template::$globals, $v);
	}
	public function parseTokens($data) {
		$tokens = new HList();
		while(haxe_Template::$splitter->match($data)) {
			$p = haxe_Template::$splitter->matchedPos();
			if($p->pos > 0) {
				$tokens->add(_hx_anonymous(array("p" => _hx_substr($data, 0, $p->pos), "s" => true, "l" => null)));
			}
			if(_hx_char_code_at($data, $p->pos) === 58) {
				$tokens->add(_hx_anonymous(array("p" => _hx_substr($data, $p->pos + 2, $p->len - 4), "s" => false, "l" => null)));
				$data = haxe_Template::$splitter->matchedRight();
				continue;
			}
			$parp = $p->pos + $p->len;
			$npar = 1;
			while($npar > 0) {
				$c = _hx_char_code_at($data, $parp);
				if($c === 40) {
					$npar++;
				}
				else {
					if($c === 41) {
						$npar--;
					}
					else {
						if($c === null) {
							throw new HException("Unclosed macro parenthesis");
						}
					}
				}
				$parp++;
				unset($c);
			}
			$params = _hx_explode(",", _hx_substr($data, $p->pos + $p->len, ($parp - ($p->pos + $p->len)) - 1));
			$tokens->add(_hx_anonymous(array("p" => haxe_Template::$splitter->matched(2), "s" => false, "l" => $params)));
			$data = _hx_substr($data, $parp, strlen($data) - $parp);
			unset($parp,$params,$p,$npar,$c);
		}
		if(strlen($data) > 0) {
			$tokens->add(_hx_anonymous(array("p" => $data, "s" => true, "l" => null)));
		}
		return $tokens;
	}
	public function parseBlock($tokens) {
		$l = new HList();
		while(true) {
			$t = $tokens->first();
			if($t === null) {
				break;
			}
			if(!$t->s && ($t->p == "end" || $t->p == "else" || _hx_substr($t->p, 0, 7) == "elseif ")) {
				break;
			}
			$l->add($this->parse($tokens));
			unset($t);
		}
		if($l->length === 1) {
			return $l->first();
		}
		return haxe__Template_TemplateExpr::OpBlock($l);
	}
	public function parse($tokens) {
		$t = $tokens->pop();
		$p = $t->p;
		if($t->s) {
			return haxe__Template_TemplateExpr::OpStr($p);
		}
		if($t->l !== null) {
			$pe = new HList();
			{
				$_g = 0; $_g1 = $t->l;
				while($_g < $_g1->length) {
					$p1 = $_g1[$_g];
					++$_g;
					$pe->add($this->parseBlock($this->parseTokens($p1)));
					unset($p1);
				}
			}
			return haxe__Template_TemplateExpr::OpMacro($p, $pe);
		}
		if(_hx_substr($p, 0, 3) == "if ") {
			$p = _hx_substr($p, 3, strlen($p) - 3);
			$e = $this->parseExpr($p);
			$eif = $this->parseBlock($tokens);
			$t1 = $tokens->first();
			$eelse = null;
			if($t1 === null) {
				throw new HException("Unclosed 'if'");
			}
			if($t1->p == "end") {
				$tokens->pop();
				$eelse = null;
			}
			else {
				if($t1->p == "else") {
					$tokens->pop();
					$eelse = $this->parseBlock($tokens);
					$t1 = $tokens->pop();
					if($t1 === null || $t1->p != "end") {
						throw new HException("Unclosed 'else'");
					}
				}
				else {
					$t1->p = _hx_substr($t1->p, 4, strlen($t1->p) - 4);
					$eelse = $this->parse($tokens);
				}
			}
			return haxe__Template_TemplateExpr::OpIf($e, $eif, $eelse);
		}
		if(_hx_substr($p, 0, 8) == "foreach ") {
			$p = _hx_substr($p, 8, strlen($p) - 8);
			$e2 = $this->parseExpr($p);
			$efor = $this->parseBlock($tokens);
			$t12 = $tokens->pop();
			if($t12 === null || $t12->p != "end") {
				throw new HException("Unclosed 'foreach'");
			}
			return haxe__Template_TemplateExpr::OpForeach($e2, $efor);
		}
		if(haxe_Template::$expr_splitter->match($p)) {
			return haxe__Template_TemplateExpr::OpExpr($this->parseExpr($p));
		}
		return haxe__Template_TemplateExpr::OpVar($p);
	}
	public function parseExpr($data) {
		$l = new HList();
		$expr = $data;
		while(haxe_Template::$expr_splitter->match($data)) {
			$p = haxe_Template::$expr_splitter->matchedPos();
			$k = $p->pos + $p->len;
			if($p->pos !== 0) {
				$l->add(_hx_anonymous(array("p" => _hx_substr($data, 0, $p->pos), "s" => true)));
			}
			$p1 = haxe_Template::$expr_splitter->matched(0);
			$l->add(_hx_anonymous(array("p" => $p1, "s" => _hx_index_of($p1, "\"", null) >= 0)));
			$data = haxe_Template::$expr_splitter->matchedRight();
			unset($p1,$p,$k);
		}
		if(strlen($data) !== 0) {
			$l->add(_hx_anonymous(array("p" => $data, "s" => true)));
		}
		$e = null;
		try {
			$e = $this->makeExpr($l);
			if(!$l->isEmpty()) {
				throw new HException($l->first()->p);
			}
		}catch(Exception $»e) {
		$_ex_ = ($»e instanceof HException) ? $»e->e : $»e;
		;
		if(is_string($s = $_ex_)){
			throw new HException((("Unexpected '" . $s) . "' in ") . $expr);
		} else throw $»e; }
		return array(new _hx_lambda(array("_ex_" => &$_ex_, "data" => &$data, "e" => &$e, "expr" => &$expr, "k" => &$k, "l" => &$l, "p" => &$p, "p1" => &$p1, "s" => &$s, "»e" => &$»e), null, array(), "{
			try {
				return call_user_func_array(\$e, array());
			}catch(Exception \$»e2) {
			\$_ex_2 = (\$»e2 instanceof HException) ? \$»e2->e : \$»e2;
			;
			{ \$exc = \$_ex_2;
			{
				throw new HException(((\"Error : \" . Std::string(\$exc)) . \" in \") . \$expr);
			}}}
		}"), 'execute0');
	}
	public function makeConst($v) {
		haxe_Template::$expr_trim->match($v);
		$v = haxe_Template::$expr_trim->matched(1);
		if(_hx_char_code_at($v, 0) === 34) {
			$str = _hx_substr($v, 1, strlen($v) - 2);
			return array(new _hx_lambda(array("str" => &$str, "v" => &$v), null, array(), "{
				return \$str;
			}"), 'execute0');
		}
		if(haxe_Template::$expr_int->match($v)) {
			$i = Std::parseInt($v);
			return array(new _hx_lambda(array("i" => &$i, "str" => &$str, "v" => &$v), null, array(), "{
				return \$i;
			}"), 'execute0');
		}
		if(haxe_Template::$expr_float->match($v)) {
			$f = Std::parseFloat($v);
			return array(new _hx_lambda(array("f" => &$f, "i" => &$i, "str" => &$str, "v" => &$v), null, array(), "{
				return \$f;
			}"), 'execute0');
		}
		$me = $this;
		return array(new _hx_lambda(array("f" => &$f, "i" => &$i, "me" => &$me, "str" => &$str, "v" => &$v), null, array(), "{
			return \$me->resolve(\$v);
		}"), 'execute0');
	}
	public function makePath($e, $l) {
		$p = $l->first();
		if($p === null || $p->p != ".") {
			return $e;
		}
		$l->pop();
		$field = $l->pop();
		if($field === null || !$field->s) {
			throw new HException($field->p);
		}
		$f = $field->p;
		haxe_Template::$expr_trim->match($f);
		$f = haxe_Template::$expr_trim->matched(1);
		return $this->makePath(array(new _hx_lambda(array("e" => &$e, "f" => &$f, "field" => &$field, "l" => &$l, "p" => &$p), null, array(), "{
			return Reflect::field(call_user_func_array(\$e, array()), \$f);
		}"), 'execute0'), $l);
	}
	public function makeExpr($l) {
		return $this->makePath($this->makeExpr2($l), $l);
	}
	public function makeExpr2($l) {
		$p = $l->pop();
		if($p === null) {
			throw new HException("<eof>");
		}
		if($p->s) {
			return $this->makeConst($p->p);
		}
		switch($p->p) {
		case "(":{
			$e1 = $this->makeExpr($l);
			$p1 = $l->pop();
			if($p1 === null || $p1->s) {
				throw new HException($p1->p);
			}
			if($p1->p == ")") {
				return $e1;
			}
			$e2 = $this->makeExpr($l);
			$p2 = $l->pop();
			if($p2 === null || $p2->p != ")") {
				throw new HException($p2->p);
			}
			return eval("if(isset(\$this)) \$»this =& \$this;switch(\$p1->p) {
				case \"+\":{
					\$»r = array(new _hx_lambda(array(\"e1\" => &\$e1, \"e2\" => &\$e2, \"l\" => &\$l, \"p\" => &\$p, \"p1\" => &\$p1, \"p2\" => &\$p2, \"»r\" => &\$»r), null, array(), \"{
						return _hx_add(call_user_func_array(\\\$e1, array()), call_user_func_array(\\\$e2, array()));
					}\"), 'execute0');
				}break;
				case \"-\":{
					\$»r = array(new _hx_lambda(array(\"e1\" => &\$e1, \"e2\" => &\$e2, \"l\" => &\$l, \"p\" => &\$p, \"p1\" => &\$p1, \"p2\" => &\$p2, \"»r\" => &\$»r), null, array(), \"{
						return call_user_func_array(\\\$e1, array()) - call_user_func_array(\\\$e2, array());
					}\"), 'execute0');
				}break;
				case \"*\":{
					\$»r = array(new _hx_lambda(array(\"e1\" => &\$e1, \"e2\" => &\$e2, \"l\" => &\$l, \"p\" => &\$p, \"p1\" => &\$p1, \"p2\" => &\$p2, \"»r\" => &\$»r), null, array(), \"{
						return call_user_func_array(\\\$e1, array()) * call_user_func_array(\\\$e2, array());
					}\"), 'execute0');
				}break;
				case \"/\":{
					\$»r = array(new _hx_lambda(array(\"e1\" => &\$e1, \"e2\" => &\$e2, \"l\" => &\$l, \"p\" => &\$p, \"p1\" => &\$p1, \"p2\" => &\$p2, \"»r\" => &\$»r), null, array(), \"{
						return call_user_func_array(\\\$e1, array()) / call_user_func_array(\\\$e2, array());
					}\"), 'execute0');
				}break;
				case \">\":{
					\$»r = array(new _hx_lambda(array(\"e1\" => &\$e1, \"e2\" => &\$e2, \"l\" => &\$l, \"p\" => &\$p, \"p1\" => &\$p1, \"p2\" => &\$p2, \"»r\" => &\$»r), null, array(), \"{
						return call_user_func_array(\\\$e1, array()) > call_user_func_array(\\\$e2, array());
					}\"), 'execute0');
				}break;
				case \"<\":{
					\$»r = array(new _hx_lambda(array(\"e1\" => &\$e1, \"e2\" => &\$e2, \"l\" => &\$l, \"p\" => &\$p, \"p1\" => &\$p1, \"p2\" => &\$p2, \"»r\" => &\$»r), null, array(), \"{
						return call_user_func_array(\\\$e1, array()) < call_user_func_array(\\\$e2, array());
					}\"), 'execute0');
				}break;
				case \">=\":{
					\$»r = array(new _hx_lambda(array(\"e1\" => &\$e1, \"e2\" => &\$e2, \"l\" => &\$l, \"p\" => &\$p, \"p1\" => &\$p1, \"p2\" => &\$p2, \"»r\" => &\$»r), null, array(), \"{
						return call_user_func_array(\\\$e1, array()) >= call_user_func_array(\\\$e2, array());
					}\"), 'execute0');
				}break;
				case \"<=\":{
					\$»r = array(new _hx_lambda(array(\"e1\" => &\$e1, \"e2\" => &\$e2, \"l\" => &\$l, \"p\" => &\$p, \"p1\" => &\$p1, \"p2\" => &\$p2, \"»r\" => &\$»r), null, array(), \"{
						return call_user_func_array(\\\$e1, array()) <= call_user_func_array(\\\$e2, array());
					}\"), 'execute0');
				}break;
				case \"==\":{
					\$»r = array(new _hx_lambda(array(\"e1\" => &\$e1, \"e2\" => &\$e2, \"l\" => &\$l, \"p\" => &\$p, \"p1\" => &\$p1, \"p2\" => &\$p2, \"»r\" => &\$»r), null, array(), \"{
						return _hx_equal(call_user_func_array(\\\$e1, array()), call_user_func_array(\\\$e2, array()));
					}\"), 'execute0');
				}break;
				case \"!=\":{
					\$»r = array(new _hx_lambda(array(\"e1\" => &\$e1, \"e2\" => &\$e2, \"l\" => &\$l, \"p\" => &\$p, \"p1\" => &\$p1, \"p2\" => &\$p2, \"»r\" => &\$»r), null, array(), \"{
						return !_hx_equal(call_user_func_array(\\\$e1, array()), call_user_func_array(\\\$e2, array()));
					}\"), 'execute0');
				}break;
				case \"&&\":{
					\$»r = array(new _hx_lambda(array(\"e1\" => &\$e1, \"e2\" => &\$e2, \"l\" => &\$l, \"p\" => &\$p, \"p1\" => &\$p1, \"p2\" => &\$p2, \"»r\" => &\$»r), null, array(), \"{
						return call_user_func_array(\\\$e1, array()) && call_user_func_array(\\\$e2, array());
					}\"), 'execute0');
				}break;
				case \"||\":{
					\$»r = array(new _hx_lambda(array(\"e1\" => &\$e1, \"e2\" => &\$e2, \"l\" => &\$l, \"p\" => &\$p, \"p1\" => &\$p1, \"p2\" => &\$p2, \"»r\" => &\$»r), null, array(), \"{
						return call_user_func_array(\\\$e1, array()) || call_user_func_array(\\\$e2, array());
					}\"), 'execute0');
				}break;
				default:{
					\$»r = eval(\"if(isset(\\\$this)) \\\$»this =& \\\$this;throw new HException(\\\"Unknown operation \\\" . \\\$p1->p);
						return \\\$»r2;
					\");
				}break;
				}
				return \$»r;
			");
		}break;
		case "!":{
			$e = $this->makeExpr($l);
			return array(new _hx_lambda(array("e" => &$e, "e1" => &$e1, "e2" => &$e2, "l" => &$l, "p" => &$p, "p1" => &$p1, "p2" => &$p2, "»r" => &$»r, "»r2" => &$»r2), null, array(), "{
				\$v = call_user_func_array(\$e, array());
				return (\$v === null || _hx_equal(\$v, false));
			}"), 'execute0');
		}break;
		case "-":{
			$e3 = $this->makeExpr($l);
			return array(new _hx_lambda(array("e" => &$e, "e1" => &$e1, "e2" => &$e2, "e3" => &$e3, "l" => &$l, "p" => &$p, "p1" => &$p1, "p2" => &$p2, "»r" => &$»r, "»r2" => &$»r2), null, array(), "{
				return -call_user_func_array(\$e3, array());
			}"), 'execute0');
		}break;
		}
		throw new HException($p->p);
	}
	public function run($e) {
		$»t = ($e);
		switch($»t->index) {
		case 0:
		$v = $»t->params[0];
		{
			$this->buf->b .= Std::string($this->resolve($v));
		}break;
		case 1:
		$e1 = $»t->params[0];
		{
			$this->buf->b .= Std::string(call_user_func_array($e1, array()));
		}break;
		case 2:
		$eelse = $»t->params[2]; $eif = $»t->params[1]; $e12 = $»t->params[0];
		{
			$v2 = call_user_func_array($e12, array());
			if($v2 === null || _hx_equal($v2, false)) {
				if($eelse !== null) {
					$this->run($eelse);
				}
			}
			else {
				$this->run($eif);
			}
		}break;
		case 3:
		$str = $»t->params[0];
		{
			$this->buf->b .= $str;
		}break;
		case 4:
		$l = $»t->params[0];
		{
			$»it = $l->iterator();
			while($»it->hasNext()) {
			$e13 = $»it->next();
			$this->run($e13);
			}
		}break;
		case 5:
		$loop = $»t->params[1]; $e14 = $»t->params[0];
		{
			$v3 = call_user_func_array($e14, array());
			try {
				if(_hx_field($v3, "hasNext") === null) {
					$x = $v3->iterator();
					if(_hx_field($x, "hasNext") === null) {
						throw new HException(null);
					}
					$v3 = $x;
				}
			}catch(Exception $»e) {
			$_ex_ = ($»e instanceof HException) ? $»e->e : $»e;
			;
			{ $e2 = $_ex_;
			{
				throw new HException("Cannot iter on " . $v3);
			}}}
			$this->stack->push($this->context);
			$v1 = $v3;
			$»it2 = $v1;
			while($»it2->hasNext()) {
			$ctx = $»it2->next();
			{
				$this->context = $ctx;
				$this->run($loop);
				;
			}
			}
			$this->context = $this->stack->pop();
		}break;
		case 6:
		$params = $»t->params[1]; $m = $»t->params[0];
		{
			$v4 = Reflect::field($this->macros, $m);
			$pl = new _hx_array(array());
			$old = $this->buf;
			$pl->push(isset($this->resolve) ? $this->resolve: array($this, "resolve"));
			$»it3 = $params->iterator();
			while($»it3->hasNext()) {
			$p = $»it3->next();
			{
				$»t2 = ($p);
				switch($»t2->index) {
				case 0:
				$v12 = $»t2->params[0];
				{
					$pl->push($this->resolve($v12));
				}break;
				default:{
					$this->buf = new StringBuf();
					$this->run($p);
					$pl->push($this->buf->b);
				}break;
				}
				unset($»t2,$v12);
			}
			}
			$this->buf = $old;
			try {
				$this->buf->b .= Std::string(Reflect::callMethod($this->macros, $v4, $pl));
			}catch(Exception $»e2) {
			$_ex_2 = ($»e2 instanceof HException) ? $»e2->e : $»e2;
			;
			{ $e15 = $_ex_2;
			{
				$plstr = eval("if(isset(\$this)) \$»this =& \$this;try {
						\$»r = \$pl->join(\",\");
					}catch(Exception \$»e3) {
					\$_ex_3 = (\$»e3 instanceof HException) ? \$»e3->e : \$»e3;
					;
					{ \$e22 = \$_ex_3;
					{
						\$»r = \"???\";
					}}}
					return \$»r;
				");
				$msg = ((((("Macro call " . $m) . "(") . $plstr) . ") failed (") . Std::string($e15)) . ")";
				throw new HException($msg);
			}}}
		}break;
		}
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
	static $splitter;
	static $expr_splitter;
	static $expr_trim;
	static $expr_int;
	static $expr_float;
	static function globals() { $»args = func_get_args(); return call_user_func_array(self::$globals, $»args); }
	static $globals;
	function __toString() { return 'haxe.Template'; }
}
haxe_Template::$splitter = new EReg("(::[A-Za-z0-9_ ()&|!+=/><*.\"-]+::|\\\$\\\$([A-Za-z0-9_-]+)\\()", "");
haxe_Template::$expr_splitter = new EReg("(\\(|\\)|[ \\r\\n\\t]*\"[^\"]*\"[ \\r\\n\\t]*|[!+=/><*.&|-]+)", "");
haxe_Template::$expr_trim = new EReg("^[ ]*([^ ]+)[ ]*\$", "");
haxe_Template::$expr_int = new EReg("^[0-9]+\$", "");
haxe_Template::$expr_float = new EReg("^([+-]?)(?=\\d|,\\d)\\d*(,\\d*)?([Ee]([+-]?\\d+))?\$", "");
haxe_Template::$globals = _hx_anonymous(array());
