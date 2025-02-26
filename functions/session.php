<?php
//https://stackoverflow.com/a/44962660/17539426

/**
 *
 */
const SESSION_DEFAULT_COOKIE_LIFETIME = 86400;

if (!function_exists("OpenSessionReadOnly")) {
    /**
     * Open _SESSION read-only
     */
    function OpenSessionReadOnly()
    {
        session_start([
            "cookie_lifetime" => SESSION_DEFAULT_COOKIE_LIFETIME,
            "read_and_close" => true, // READ ACCESS FAST
        ]);
        // $_SESSION is now defined. Call WriteSessionValues() to write out values
    }
}

if (!function_exists("WriteSessionValues")) {
    /**
     * _SESSION is read-only by default. Call this function to save a new value
     * call this function like `WriteSessionValues(["username"=>$login_user]);`
     * to set $_SESSION["username"]
     *
     * @param array $values_assoc_array
     */
    function WriteSessionValues($values_assoc_array)
    {
        // this is required to close the read-only session and
        // not get a warning on the next line.
        session_abort();
        // now open the session with write access
        session_start(["cookie_lifetime" => SESSION_DEFAULT_COOKIE_LIFETIME]);

        foreach ($values_assoc_array as $key => $value) {
            $_SESSION[$key] = $value;
        }
        session_write_close(); // Write session data and end session

        OpenSessionReadOnly(); // now reopen the session in read-only mode.
    }
}

if (!function_exists("ActiveSessionReadOnly")) {
    /*
     * Determines whether PHP has the current visitor is a logged in user.
     *
     * @since 2.0.0
     *
     * @return bool True if user is logged in, false if not logged in.
     */
    function ActiveSessionReadOnly()
    {
        // this is required to close the read-only session and
        // not get a warning on the next line.
        session_abort();
        // now open the session with write access
        session_start(["cookie_lifetime" => SESSION_DEFAULT_COOKIE_LIFETIME]);
        
        return session_status() === PHP_SESSION_ACTIVE;
    }
}

if (!function_exists("CloseSessionReadOnly")) {
    /**
     * Open _SESSION read-only
     */
    function CloseSessionReadOnly()
    {
        // this is required to close the read-only session and
        // not get a warning on the next line.
        session_abort();
        // now open the session with write access
        session_start(["cookie_lifetime" => SESSION_DEFAULT_COOKIE_LIFETIME]);
        
        if (ActiveSessionReadOnly()) {
            session_unset();
            session_destroy();
        }
    }
}

OpenSessionReadOnly(); // start the session for whole site
?>
