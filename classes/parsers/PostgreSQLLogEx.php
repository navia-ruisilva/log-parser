<?php

require_once __DIR__ . '/../Parser.php';

class PostgreSQLLog extends Parser {
    
    protected $_username_pattern = "[a-zA-Z0-9_.-]+";
    protected $_database_pattern = "[a-zA-Z0-9_.-]+";

    public function __construct() {
        parent::__construct();
    
        $this->pattern = $this->_build_pattern();
    }

    private function _build_pattern() {
	// 2025-10-29 10:18:46.910 UTC [1733629] navia@navia LOG:  disconnection: session time: 0:00:00.010 user=navia database=navia host=172.27.1.127 port=50226
	// 2025-10-29 10:18:46.911 UTC [1733564] navia@navia LOG:  disconnection: session time: 0:00:01.846 user=navia database=navia host=172.27.1.127 port=49694
	// 2025-10-29 10:18:46.915 UTC [1733630] [unknown]@[unknown] LOG:  connection received: host=172.27.1.127 port=50228
	// 2025-10-29 10:18:46.918 UTC [1733630] navia@navia LOG:  connection authorized: user=navia database=navia SSL enabled (protocol=TLSv1.3, cipher=TLS_AES_256_GCM_SHA384, bits=256, compression=off)
	// 2025-10-29 10:18:46.920 UTC [1733631] [unknown]@[unknown] LOG:  connection received: host=172.27.1.127 port=50244
	// 2025-10-29 10:18:46.923 UTC [1733631] navia@navia LOG:  connection authorized: user=navia database=navia SSL enabled (protocol=TLSv1.3, cipher=TLS_AES_256_GCM_SHA384, bits=256, compression=off)
	// 2025-10-29 10:18:46.930 UTC [1733631] navia@navia LOG:  disconnection: session time: 0:00:00.011 user=navia database=navia host=172.27.1.127 port=50244

        $patt_ip = $this->ip_patt;
        $patt_num = "[0-9]+";

	    $fields['date'] = "[0-9]{4}(-[0-9]{2}){2} [0-9]{2}(:[0-9]{2}){2}\.[0-9]{1,3} [A-Za-z]+";
	    $fields['pid'] = ['prefix' => '\[', 'pattern' => '[0-9]+', 'suffix' => '\]'];
        $fields['seq'] = ['prefix' => '\[', 'pattern' => '[0-9]+', 'suffix' => '\]', 'optional' => true];
        $fields['database'] = ['prefix' => 'db=', 'pattern' => $this->_database_pattern, 'optional' => true];
        $fields['user'] = ['prefix' => 'user=', 'pattern' => $this->_username_pattern, 'optional' => true];
        $fields['app'] = ['prefix' => 'app="', 'pattern' => '[^"]*', 'suffix' => '"', 'optional' => true];
        $fields['client'] = ['prefix' => 'client=', 'pattern' => $patt_ip . ":" . $patt_num, 'optional' => true];
	    
        $fields_log_conn_recv = [];
        $fields_log_conn_recv['cr_host'] = ['prefix' => 'host=', 'pattern' => $patt_ip];
        $fields_log_conn_recv['cr_port'] = ['prefix' => 'port=', 'pattern' => $patt_num];
        $patt_log_conn_recv = $this->build_pattern($fields_log_conn_recv, false);
        
        $fields_log_conn_auth = [];
        $fields_log_conn_auth['ca_user'] = ['prefix' => 'user=', 'pattern' => $this->_username_pattern];
        $fields_log_conn_auth['ca_database'] = ['prefix' => 'database=', 'pattern' => $this->_database_pattern];
        $fields_log_conn_auth['ca_conn_settings'] = [ 'pattern' =>'.*', 'optional' => true ];
        $patt_log_conn_auth = $this->build_pattern($fields_log_conn_auth, false);

        $fields_log_disconn = [];
        $fields_log_disconn['dc_session_time'] = ['prefix' => 'session time: ', 'pattern' => '[0-9:\.]+'];
        $fields_log_disconn['dc_user'] = ['prefix' => 'user=', 'pattern' => $this->_username_pattern];
        $fields_log_disconn['dc_database'] = ['prefix' => 'database=', 'pattern' => $this->_database_pattern];
        $fields_log_disconn['dc_host'] = ['prefix' => 'host=', 'pattern' => $patt_ip];
        $fields_log_disconn['dc_port'] = ['prefix' => 'port=', 'pattern' => $patt_num];
        $patt_log_disconn = $this->build_pattern($fields_log_disconn, false);
        
        $fields['log'] = [
            'prefix' => 'LOG:\s+',
            'pattern' => '(' . 
                $this->build_patt_field('connection_received', ['prefix'=>'connection received:\s+', 'pattern' => $patt_log_conn_recv ]). '|' . 
                $this->build_patt_field('connection_authorized', ['prefix' => 'connection authorized:\s+', 'pattern' => $patt_log_conn_auth ]) . '|' . 
                $this->build_patt_field('disconnection', ['prefix' => 'disconnection:\s+', 'pattern' => $patt_log_disconn ]) . '|' .
                $this->build_patt_field('other_log', '.*') .
                ')'
        ];       

        return $this->build_pattern($fields);

    }

    public function parseLine($line) {
        if (empty($line)) {
            return [];
        }

        $matches = [];
        if (!preg_match($this->pattern, $line, $matches)) {
            throw new Exception("Line does not match the expected format: {$line}");
        }

        // Return the parsed data
        return $matches;
    }
}
