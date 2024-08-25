<?php
use League\OAuth2\Client\Provider\Google;
require 'vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$provider = new Google([
    'clientId'     => $_ENV['GOOGLE_CLIENT_ID'],
    'clientSecret' => $_ENV['GOOGLE_CLIENT_SECRET'],
    'redirectUri'  => $_ENV['GOOGLE_REDIRECT_URI'],
]);

if (!isset($_GET['code'])) {
    $authorizationUrl = $provider->getAuthorizationUrl([
        'scope' => ['https://mail.google.com/', 'https://www.googleapis.com/auth/drive'], 
        'access_type' => 'offline', 
    ]);
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authorizationUrl);
    exit;
}

if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    exit('Estado inválido');
}

try {
    $accessToken = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    $_SESSION['access_token'] = $accessToken->getToken();
    $_SESSION['refresh_token'] = $accessToken->getRefreshToken();

    echo json_encode(['success' => true, 'access_token' => $accessToken->getToken()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error obteniendo el token de acceso: ' . $e->getMessage()]);
}
?>