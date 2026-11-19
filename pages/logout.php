<?php
    include "../database/db.php";
    session_destroy();
    header("Location: /index.php");
?>