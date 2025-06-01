<?php
// Fungsi untuk sanitasi input agar aman dari XSS
function sanitizeInput($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

