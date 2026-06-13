<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Middleware\Session;
use App\Models\User;

// Google OAuth configuration from .env
$client_id = getenv('GOOGLE_CLIENT_ID');
$client_secret = getenv('GOOGLE_CLIENT_SECRET');
$redirect_uri = getenv('GOOGLE_REDIRECT_URL');

if (!isset($_GET['code'])) {
    // Stage 1: Redirect to Google's OAuth 2.0 server
    $auth_url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'response_type' => 'code',
        'scope' => 'email profile',
        'access_type' => 'online'
    ]);
    header('Location: ' . $auth_url);
    exit;
} else {
    // Stage 2: Exchange authorization code for tokens
    $code = $_GET['code'];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'code' => $code,
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code'
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if (isset($data['access_token'])) {
        $access_token = $data['access_token'];
        
        // Stage 3: Get user info from Google
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $access_token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $user_info_raw = curl_exec($ch);
        curl_close($ch);
        
        $google_user = json_decode($user_info_raw, true);
        
        if (isset($google_user['email'])) {
            $email = $google_user['email'];
            $name = $google_user['name'] ?? explode('@', $email)[0];
            
            $userModel = new User();
            $user = $userModel->findByEmail($email);
            
            if (!$user) {
                // If user doesn't exist, create a new one
                $data = [
                    'username' => $name,
                    'email' => $email,
                    'password_hash' => password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT),
                    'role_id' => 2, // Default to Student
                    'verification_token' => null
                ];
                $userModel->create($data);
                $user = $userModel->findByEmail($email);
            }
            
            // Log the user in
            Session::set('user_id', $user['id']);
            Session::set('username', $user['username']);
            Session::set('user_role', $user['role_name']);
            
            header('Location: index.php');
            exit;
        }
    }
    
    // If something fails, redirect back to login
    header('Location: login.php?error=google_login_failed');
    exit;
}
