<?php
// part of orsee. see orsee.org

class orsee_session_handler implements SessionHandlerInterface
{
    public function open(string $aSavaPath, string $aSessionName): bool {
        return orsee_session_open($aSavaPath,$aSessionName);
    }

    public function close(): bool {
        return orsee_session_close();
    }

    public function read(string $aKey): string {
        return (string)orsee_session_read($aKey);
    }

    public function write(string $aKey, string $aVal): bool {
        return orsee_session_write($aKey,$aVal);
    }

    public function destroy(string $aKey): bool {
        return orsee_session_destroy($aKey);
    }

    public function gc(int $aMaxLifeTime): int {
        return orsee_session_gc($aMaxLifeTime);
    }
}

function orsee_session_register_handler() {
    global $settings__server_url, $settings__root_directory;
    global $site__database_host, $site__database_database, $site__database_table_prefix;

    // Fortalecer cookies de sesión
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');

    $is_secure = false;
    if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1)) {
        $is_secure = true;
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        $is_secure = true;
    }
    if ($is_secure) {
        ini_set('session.cookie_secure', 1);
    }

    $session_scope_seed=
        (string)$settings__server_url.'|'.
        (string)$settings__root_directory.'|'.
        (string)$site__database_host.'|'.
        (string)$site__database_database.'|'.
        (string)$site__database_table_prefix;
    $session_scope_hash=substr(hash('sha256',$session_scope_seed),0,16);
    session_name('ORSEE_SESSID_'.$session_scope_hash);

    session_set_save_handler(new orsee_session_handler(), true);
}

function orsee_session_open($aSavaPath, $aSessionName) {
    return true;
}

function orsee_session_close() {
    return true;
}

function orsee_session_read($aKey) {
    global $settings;
    $query = "SELECT DataValue, UNIX_TIMESTAMP(LastUpdated) as LastUpdatedTs FROM ".table('http_sessions')." WHERE SessionID=:aKey";
    $pars=array(':aKey'=>$aKey);
    $result = or_query($query,$pars);
    if (pdo_num_rows($result) == 1) {
        $r = pdo_fetch_assoc($result);
        $orsee_timeout_minutes=(isset($settings['session_timeout_minutes']) ? (int)$settings['session_timeout_minutes'] : 0);
        if ($orsee_timeout_minutes >= 15 && $orsee_timeout_minutes <= 43200) {
            $effective_lifetime=$orsee_timeout_minutes*60;
        } else {
            $effective_lifetime=max(min((int)ini_get('session.gc_maxlifetime'),43200*60),15*60);
        }
        if ((time() - (int)$r['LastUpdatedTs']) > $effective_lifetime) {
            orsee_session_gc($effective_lifetime);
            return "";
        }
        return $r['DataValue'];
    } else {
        $query = "INSERT INTO ".table('http_sessions')." (SessionID, LastUpdated, DataValue)
                       VALUES (:aKey, NOW(), '')";
        or_query($query,$pars);
        return "";
    }
}

function orsee_session_write($aKey, $aVal) {
    site__database_config();
    $pars=array(':aKey'=>$aKey, ':aVal'=>$aVal);
    $query = "UPDATE ".table('http_sessions')." SET DataValue = :aVal, LastUpdated = NOW() WHERE SessionID = :aKey";
    or_query($query,$pars);
    return true;
}

function orsee_session_destroy($aKey) {
    site__database_config();
    $pars=array(':aKey'=>$aKey);
    $query = "DELETE FROM ".table('http_sessions')." WHERE SessionID = :aKey";
    or_query($query,$pars);
    return true;
}

function orsee_session_gc($aMaxLifeTime) {
    global $settings;
    site__database_config();
    $min_timeout_minutes=15;
    $max_timeout_minutes=43200;
    $orsee_timeout_minutes=(isset($settings['session_timeout_minutes']) ? (int)$settings['session_timeout_minutes'] : 0);

    if ($orsee_timeout_minutes >= $min_timeout_minutes && $orsee_timeout_minutes <= $max_timeout_minutes) {
        $effective_lifetime=$orsee_timeout_minutes*60;
    } else {
        $effective_lifetime=(int)$aMaxLifeTime;
        if ($effective_lifetime < $min_timeout_minutes*60) {
            $effective_lifetime=$min_timeout_minutes*60;
        }
        if ($effective_lifetime > $max_timeout_minutes*60) {
            $effective_lifetime=$max_timeout_minutes*60;
        }
    }

    $pars=array(':aMaxLifeTime'=>$effective_lifetime);
    $query = "DELETE FROM ".table('http_sessions')." WHERE UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(LastUpdated) > :aMaxLifeTime";
    $done=or_query($query,$pars);
    return pdo_num_rows($done);
}


?>
