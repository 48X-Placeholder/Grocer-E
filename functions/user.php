<?php
if (!function_exists("is_user_logged_in")) {
    /*
     * Determines whether the current visitor is a logged in user.
     *
     * @since 2.0.0
     *
     * @return bool True if user is logged in, false if not logged in.
     */
    function is_user_logged_in()
    {
        if (ActiveSessionReadOnly()) {
            if (isset($_SESSION["user_id"])) {
                return !empty($_SESSION["user_id"]);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}

if (!function_exists("user_session_create")) {
    /*
     * Sets up Session for User if one is not active.
     *
     * @since 2.0.0
     */
    function user_session_create($userID = "", $userName = "")
    {
        if (!empty($userID) && !empty($userName)) {
            if (!is_user_logged_in()) {
                // Store user ID in session (for persistent login)
                WriteSessionValues(["user_id"=>$userID]);
                // Store username in session (optional, for display)
                WriteSessionValues(["username"=>$userName]);
                return true;
            }
        }

        return false;
    }
}

if (!function_exists("cached_userid_info")) {
    /*
     * Returns user id of Current User Session
     *
     * @since 2.0.0
     */
    function cached_userid_info()
    {
        if (!empty($_SESSION["user_id"])) {
            return $_SESSION["user_id"];
        } else {
            return null;
        }
    }
}

if (!function_exists("cached_username_info")) {
    /*
     * Returns username of Current User Session
     *
     * @since 2.0.0
     */
    function cached_username_info()
    {
        if (!empty($_SESSION["username"])) {
            return $_SESSION["username"];
        } else {
            return null;
        }
    }
}
?>
