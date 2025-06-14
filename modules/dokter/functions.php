<?php
function sanitizeInput($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

