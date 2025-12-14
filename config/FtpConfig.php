<?php
class FtpConfig {
    // DATOS DE CONEXIÓN AL SERVIDOR DE IMÁGENES
    private $host = '192.168.2.20';
    private $user = 'admin_archivos';
    private $pass = 'Ciber.123';
    private $port = 21;

    // Ruta raíz del usuario FTP restringido
    private $root_path = '/'; 

    // Usamos ruta relativa para que pase por el Proxy de Apache
    public $public_url = '/imagenes/';

    public function connect() {
        // Intentar conectar
        $conn = @ftp_connect($this->host, $this->port);

        // Intentar login
        if ($conn && @ftp_login($conn, $this->user, $this->pass)) {
            ftp_pasv($conn, true); // Modo pasivo OBLIGATORIO
            return $conn;
        }
        return false;
    }

    public function getRootPath() {
        return $this->root_path;
    }
}
?>
