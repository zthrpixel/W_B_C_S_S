<?php
// Small helpers shared by the front-end pages.

// Escape a value for safe output inside HTML (including attributes).
function e($value): string {
    return htmlspecialchars((string) ($value ?? ""), ENT_QUOTES, "UTF-8");
}
