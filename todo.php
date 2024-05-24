<?php
    session_start() or trigger_error("", E_USER_ERROR);
    echo $_SESSION['userid'];
    echo $_SESSION['username'];
    session_abort();
?>