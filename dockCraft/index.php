<?php
class Rcon
{
    private $host;
    private $port;
    private $password;
    private $timeout;

    private $socket;

    private $authorized = false;
    private $lastResponse = '';

    const PACKET_AUTHORIZE = 5;
    const PACKET_COMMAND = 6;

    const SERVERDATA_AUTH = 3;
    const SERVERDATA_AUTH_RESPONSE = 2;
    const SERVERDATA_EXECCOMMAND = 2;
    const SERVERDATA_RESPONSE_VALUE = 0;

    public function __construct($host, $port, $password, $timeout)
    {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->timeout = $timeout;
    }

    public function getResponse()
    {
        return $this->lastResponse;
    }

    public function connect()
    {
        $this->socket = fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);

        if (!$this->socket) {
            $this->lastResponse = $errstr;
            return false;
        }

        stream_set_timeout($this->socket, 3, 0);

        return $this->authorize();
    }

    public function disconnect()
    {
        if ($this->socket) {
            fclose($this->socket);
        }
    }

    public function isConnected()
    {
        return $this->authorized;
    }

    public function sendCommand($command)
    {
        if (!$this->isConnected()) {
            return false;
        }

        $this->writePacket(self::PACKET_COMMAND, self::SERVERDATA_EXECCOMMAND, $command);

        $response_packet = $this->readPacket();
        if ($response_packet['id'] == self::PACKET_COMMAND) {
            if ($response_packet['type'] == self::SERVERDATA_RESPONSE_VALUE) {
                $this->lastResponse = $response_packet['body'];

                return $response_packet['body'];
            }
        }

        return false;
    }

    private function authorize()
    {
        $this->writePacket(self::PACKET_AUTHORIZE, self::SERVERDATA_AUTH, $this->password);
        $response_packet = $this->readPacket();

        if ($response_packet['type'] == self::SERVERDATA_AUTH_RESPONSE) {
            if ($response_packet['id'] == self::PACKET_AUTHORIZE) {
                $this->authorized = true;

                return true;
            }
        }

        $this->disconnect();
        return false;
    }

    private function writePacket($packetId, $packetType, $packetBody)
    {
        $packet = pack('VV', $packetId, $packetType);
        $packet = $packet . $packetBody . "\x00";
        $packet = $packet . "\x00";

        $packet_size = strlen($packet);
        $packet = pack('V', $packet_size) . $packet;

        fwrite($this->socket, $packet, strlen($packet));
    }

    private function readPacket()
    {
        $size_data = fread($this->socket, 4);
        $size_pack = unpack('V1size', $size_data);
        $size = $size_pack['size'];

        $packet_data = fread($this->socket, $size);
        $packet_pack = unpack('V1id/V1type/a*body', $packet_data);

        return $packet_pack;
    }
}

$rconPassword = 'mdpRCON';
$rconHost = 'minecraft';
$rconPort = 25575;
$timeout = 3;

$rcon = new Rcon($rconHost, $rconPort, $rconPassword, $timeout);

$response = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $command = $_POST['command'];

    if ($rcon->connect()) {
        $response = $rcon->sendCommand($command);
        $rcon->disconnect();
    } else {
        die('Impossible de se connecter au serveur Minecraft via RCON.');
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Interface Web Minecraft</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f8f8;
            padding: 20px;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .btn {
            margin-top: 10px;
        }

        .command-output {
            background-color: #f8f8f8;
            border: 1px solid #ccc;
            padding: 10px;
            margin-top: 20px;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Interface Web Minecraft</h1>
        <form method="POST" action="">
            <div class="form-group">
                <label for="command">Commande Minecraft :</label>
                <input type="text" class="form-control" id="command" name="command" placeholder="Entrez une commande">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Envoyer</button>
        </form>

        <?php if (!empty($response)): ?>
        <div class="command-output">
            <h4>Résultat :</h4>
            <pre><?php echo htmlspecialchars($response); ?></pre>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>