<?php
// ==========================================
// CABECERAS DE SEGURIDAD OBLIGATORIAS
// ==========================================

// Evita que tu página se cargue dentro de un iframe (Protección contra Clickjacking) [cite: 142]
header("X-Frame-Options: SAMEORIGIN");

// Obliga al navegador a respetar los tipos MIME declarados (Evita MIME Sniffing) [cite: 144]
header("X-Content-Type-Options: nosniff");

// Política de Seguridad de Contenido (CSP) [cite: 141]
// Define qué fuentes de contenido son seguras. Permitimos 'self' y CDNs de Bootstrap.
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' https://cdn.jsdelivr.net 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' https://cdn.jsdelivr.net;");

// Política de Referencia (Privacidad)
header("Referrer-Policy: strict-origin-when-cross-origin");
?>