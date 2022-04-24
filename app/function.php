<?php

class functions  {
    public $files='';
    public $data='';
    public $code_err='';
    public $code_txt='';
    protected $layout;
    protected $sections = [];
    protected $sectionStack = [];
    protected $currentSection;
    function views(){

        $exl_filename =  explode('.',$this->files);
        $filename = APP_ROOT . '/views/'.$exl_filename[0].'.php';
      
        if(file_exists($filename)){
			
            require_once $filename;
			exit;
            }else{
            $this->http_response_codes(400);
            require_once  APP_ROOT . '/views/404.php';
            exit;
            }

    }
	

    public function section(string $name)
    {
        // Saved to prevent BC.
        $this->currentSection = $name;
        $this->sectionStack[] = $name;

        ob_start();
    }


    public function extend(string $layout)
    {
        $this->layout = $layout;
    }

    public function endSection()
    {
        $contents = ob_get_clean();

        if ($this->sectionStack === []) {
            throw new RuntimeException('View themes, no current section.');
        }

        $section = array_pop($this->sectionStack);

        // Ensure an array exists so we can store multiple entries for this.
        if (! array_key_exists($section, $this->sections)) {
            $this->sections[$section] = [];
        }

        $this->sections[$section][] = $contents;
    }

    public function renderSection(string $sectionName)
    {
        if (! isset($this->sections[$sectionName])) {
            echo '';

            return;
        }

        foreach ($this->sections[$sectionName] as $key => $contents) {
            echo $contents;
            unset($this->sections[$sectionName][$key]);
        }
    }

    function http_response_codes($code = NULL) {

        if ($code !== NULL) {

            switch ($code) {
                case 100: $text = 'Continue'; break;
                case 101: $text = 'Switching Protocols'; break;
                case 200: $text = 'OK'; break;
                case 201: $text = 'Created'; break;
                case 202: $text = 'Accepted'; break;
                case 203: $text = 'Non-Authoritative Information'; break;
                case 204: $text = 'No Content'; break;
                case 205: $text = 'Reset Content'; break;
                case 206: $text = 'Partial Content'; break;
                case 300: $text = 'Multiple Choices'; break;
                case 301: $text = 'Moved Permanently'; break;
                case 302: $text = 'Moved Temporarily'; break;
                case 303: $text = 'See Other'; break;
                case 304: $text = 'Not Modified'; break;
                case 305: $text = 'Use Proxy'; break;
                case 400: $text = 'Bad Request'; break;
                case 401: $text = 'Unauthorized'; break;
                case 402: $text = 'Payment Required'; break;
                case 403: $text = 'Forbidden'; break;
                case 404: $text = 'Not Found'; break;
                case 405: $text = 'Method Not Allowed'; break;
                case 406: $text = 'Not Acceptable'; break;
                case 407: $text = 'Proxy Authentication Required'; break;
                case 408: $text = 'Request Time-out'; break;
                case 409: $text = 'Conflict'; break;
                case 410: $text = 'Gone'; break;
                case 411: $text = 'Length Required'; break;
                case 412: $text = 'Precondition Failed'; break;
                case 413: $text = 'Request Entity Too Large'; break;
                case 414: $text = 'Request-URI Too Large'; break;
                case 415: $text = 'Unsupported Media Type'; break;
                case 500: $text = 'Internal Server Error'; break;
                case 501: $text = 'Not Implemented'; break;
                case 502: $text = 'Bad Gateway'; break;
                case 503: $text = 'Service Unavailable'; break;
                case 504: $text = 'Gateway Time-out'; break;
                case 505: $text = 'HTTP Version not supported'; break;
                default:
                    exit('Unknown http status code "' . htmlentities($code) . '"');
                break;
            }

            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

            header($protocol . ' ' . $code . ' ' . $text);

            $GLOBALS['http_response_code'] = $code;

        } else {

            $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);

        }

        $this->code_err = $code;
        $this->code_txt = $text;

    }

}

function view($files='',$data=''){
    $fc_view = new functions();
    $fc_view->files = $files;
    $fc_view->data  = $data;
    $fc_view->views();

}


// $GLOBALS['_ti_base'] = null;
// $GLOBALS['_ti_stack'] = null;


function emptyblock($name) {
	$trace = _ti_callingTrace();
	_ti_init($trace);
	_ti_insertBlock(
		_ti_newBlock($name, null, $trace)
	);
}


function startblock($name, $filters=null) {
	$trace = _ti_callingTrace();
	_ti_init($trace);
	$stack =& $GLOBALS['_ti_stack'];
	$stack[] = _ti_newBlock($name, $filters, $trace);
}


function endblock($name=null) {
	$trace = _ti_callingTrace();
	_ti_init($trace);
	$stack =& $GLOBALS['_ti_stack'];
	if ($stack) {
		$block = array_pop($stack);
		if ($name && $name != $block['name']) {
			_ti_warning("startblock('{$block['name']}') does not match endblock('$name')", $trace);
		}
		_ti_insertBlock($block);
	}else{
		_ti_warning(
			$name ? "orphan endblock('$name')" : "orphan endblock()",
			$trace
		);
	}
}


function superblock() {
	if ($GLOBALS['_ti_stack']) {
		echo getsuperblock();
	}else{
		_ti_warning(
			"superblock() call must be within a block",
			_ti_callingTrace()
		);
	}
}


function getsuperblock() {
	$stack =& $GLOBALS['_ti_stack'];
	if ($stack) {
		$hash =& $GLOBALS['_ti_hash'];
		$block = end($stack);
		if (isset($hash[$block['name']])) {
			return implode(
				_ti_compile(
					$hash[$block['name']]['block'],
					ob_get_contents()
				)
			);
		}
	}else{
		_ti_warning(
			"getsuperblock() call must be within a block",
			_ti_callingTrace()
		);
	}
	return '';
}


function flushblocks() {
	$base =& $GLOBALS['_ti_base'];
	if ($base) {
		$stack =& $GLOBALS['_ti_stack'];
		$level =& $GLOBALS['_ti_level'];
		while ($block = array_pop($stack)) {
			_ti_warning(
				"missing endblock() for startblock('{$block['name']}')",
				_ti_callingTrace(),
				$block['trace']
			);
		}
		while (ob_get_level() > $level) {
			ob_end_flush(); // will eventually trigger bufferCallback
		}
		$base = null;
		$stack = null;
	}
}


function blockbase() {
	_ti_init(_ti_callingTrace());
}


function _ti_init($trace) {
	$base =& $GLOBALS['_ti_base'];
	if ($base && !_ti_inBaseOrChild($trace)) {
		flushblocks(); // will set $base to null
	}
	if (!$base) {
		$base = array(
			'trace' => $trace,
			'filters' => null, // purely for compile
			'children' => array(),
			'start' => 0, // purely for compile
			'end' => null
		);
		$GLOBALS['_ti_level'] = ob_get_level();
		$GLOBALS['_ti_stack'] = array();
		$GLOBALS['_ti_hash'] = array();
		$GLOBALS['_ti_end'] = null;
		$GLOBALS['_ti_after'] = '';
		ob_start('_ti_bufferCallback');
	}
}


function _ti_newBlock($name, $filters, $trace) {
	$base =& $GLOBALS['_ti_base'];
	$stack =& $GLOBALS['_ti_stack'];
	while ($block = end($stack)) {
		if (_ti_isSameFile($block['trace'], $trace)) {
			break;
		}else{
			array_pop($stack);
			_ti_insertBlock($block);
			_ti_warning(
				"missing endblock() for startblock('{$block['name']}')",
				_ti_callingTrace(),
				$block['trace']
			);
		}
	}
	if ($base['end'] === null && !_ti_inBase($trace)) {
		$base['end'] = ob_get_length();
	}
	if ($filters) {
		if (is_string($filters)) {
			$filters = preg_split('/\s*[,|]\s*/', trim($filters));
		}
		else if (!is_array($filters)) {
			$filters = array($filters);
		}
		foreach ($filters as $i => $f) {
			if ($f && !is_callable($f)) {
				_ti_warning(
					is_array($f) ?
						"filter " . implode('::', $f) . " is not defined":
						"filter '$f' is not defined", // TODO: better messaging for methods
					$trace
				);
				$filters[$i] = null;
			}
		}
	}
	return array(
		'name' => $name,
		'trace' => $trace,
		'filters' => $filters,
		'children' => array(),
		'start' => ob_get_length()
	);
}


function _ti_insertBlock($block) { // at this point, $block is done being modified
	$base =& $GLOBALS['_ti_base'];
	$stack =& $GLOBALS['_ti_stack'];
	$hash =& $GLOBALS['_ti_hash'];
	$end =& $GLOBALS['_ti_end'];
	$block['end'] = $end = ob_get_length();
	$name = $block['name'];
	if ($stack || _ti_inBase($block['trace'])) {
		$block_anchor = array(
			'start' => $block['start'],
			'end' => $end,
			'block' => $block
		);
		if ($stack) {
			// nested block
			$stack[count($stack)-1]['children'][] =& $block_anchor;
		}else{
			// top-level block in base
			$base['children'][] =& $block_anchor;
		}
		$hash[$name] =& $block_anchor; // same reference as children array
	}
	else if (isset($hash[$name])) {
		if (_ti_isSameFile($hash[$name]['block']['trace'], $block['trace'])) {
			_ti_warning(
				"cannot define another block called '$name'",
				_ti_callingTrace(),
				$block['trace']
			);
		}else{
			// top-level block in a child template; override the base's block
			$hash[$name]['block'] = $block;
		}
	}
}


function _ti_bufferCallback($buffer) {
	$base =& $GLOBALS['_ti_base'];
	$stack =& $GLOBALS['_ti_stack'];
	$end =& $GLOBALS['_ti_end'];
	$after =& $GLOBALS['_ti_after'];
	if ($base) {
		while ($block = array_pop($stack)) {
			_ti_insertBlock($block);
			_ti_warning(
				"missing endblock() for startblock('{$block['name']}')",
				_ti_callingTrace(),
				$block['trace']
			);
		}
		if ($base['end'] === null) {
			$base['end'] = strlen($buffer);
			$end = null; // todo: more explanation
			// means there were no blocks other than the base's
		}
		$parts = _ti_compile($base, $buffer);
		// remove trailing whitespace from end
		$i = count($parts) - 1;
		$parts[$i] = rtrim($parts[$i]);
		// if there are child template blocks, preserve output after last one
		if ($end !== null) {
			$parts[] = substr($buffer, $end);
		}
		// for error messages
		$parts[] = $after;
		return implode($parts);
	}else{
		return '';
	}
}


function _ti_compile($block, $buffer) {
	$parts = array();
	$previ = $block['start'];
	foreach ($block['children'] as $child_anchor) {
		$parts[] = substr($buffer, $previ, $child_anchor['start'] - $previ);
		$parts = array_merge(
			$parts,
			_ti_compile($child_anchor['block'], $buffer)
		);
		$previ = $child_anchor['end'];
	}
	if ($previ != $block['end']) {
		// could be a big buffer, so only do substr if necessary
		$parts[] = substr($buffer, $previ, $block['end'] - $previ);
	}
	if ($block['filters']) {
		$s = implode($parts);
		foreach ($block['filters'] as $filter) {
			if ($filter) {
				$s = call_user_func($filter, $s);
			}
		}
		return array($s);
	}
	return $parts;
}


function _ti_warning($message, $trace, $warning_trace=null) {
	if (error_reporting() & E_USER_WARNING) {
		if (defined('STDIN')) {
			// from command line
			$format = "\nWarning: %s in %s on line %d\n";
		}else{
			// from browser
			$format = "<br />\n<b>Warning</b>:  %s in <b>%s</b> on line <b>%d</b><br />\n";
		}
		if (!$warning_trace) {
			$warning_trace = $trace;
		}
		$s = sprintf($format, $message, $warning_trace[0]['file'], $warning_trace[0]['line']);
		if (!$GLOBALS['_ti_base'] || _ti_inBase($trace)) {
			echo $s;
		}else{
			$GLOBALS['_ti_after'] .= $s;
		}
	}
}


/* backtrace utilities
------------------------------------------------------------------------*/


function _ti_callingTrace() {
	$trace = debug_backtrace();
	foreach ($trace as $i => $location) {
		if ($location['file'] !== __FILE__) {
			return array_slice($trace, $i);
		}
	}
}


function _ti_inBase($trace) {
	return _ti_isSameFile($trace, $GLOBALS['_ti_base']['trace']);
}


function _ti_inBaseOrChild($trace) {
	$base_trace = $GLOBALS['_ti_base']['trace'];
	return
		$trace && $base_trace &&
		_ti_isSubtrace(array_slice($trace, 1), $base_trace) &&
		$trace[0]['file'] === $base_trace[count($base_trace)-count($trace)]['file'];
}


function _ti_isSameFile($trace1, $trace2) {
	return
		$trace1 && $trace2 &&
		$trace1[0]['file'] === $trace2[0]['file'] &&
		array_slice($trace1, 1) === array_slice($trace2, 1);
}


function _ti_isSubtrace($trace1, $trace2) { // is trace1 a subtrace of trace2
	$len1 = count($trace1);
	$len2 = count($trace2);
	if ($len1 > $len2) {
		return false;
	}
	for ($i=0; $i<$len1; $i++) {
		if ($trace1[$len1-1-$i] !== $trace2[$len2-1-$i]) {
			return false;
		}
	}
	return true;
}


