<?php

namespace App\Services;

use App\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthService
{
    protected $userModel;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    public function current_user()
    {
        if ($this->is_user_logged_in()) {
            return $_SESSION['username'];
        }
        return null;
    }

    private function is_user_active($user)
    {
        return (int) $user['active'] === 1;
    }

    public function is_admin(): bool
    {
        if ($this->is_user_logged_in()) {
            $user = $this->userModel->find_by_id($_SESSION['user_id']);
            return isset($user['is_admin']) && $user['is_admin'] == 1;
        }
        return false;
    }

    public function is_user_logged_in()
    {
        // check the session
        if (isset($_SESSION['username'])) {
            return true;
        }

        // check the remember_me in cookie
        if (isset($_COOKIE['remember_me'])) {
            $token = htmlspecialchars($_COOKIE['remember_me']);
        } else 
            $token = null;

        if ($token && $this->token_is_valid($token)) {

            $user = $this->userModel->find_user_by_token($token);

            if ($user) {
                return $this->log_user_in($user);
            }
        }
        return false;
    }

    /*************************************** Registration ***************************************/

    public function generate_activation_code(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function send_activation_email(string $email, string $activation_code): void
    {
        // create the activation link
        $activation_link = APP_URL . "/auth/activate?activation_code=$activation_code";
        
        // set email subject & body
        $subject = 'Please activate your account';
        $body = <<<MESSAGE
            Hi,
            Please click the following link to activate your account:
            $activation_link
        MESSAGE;

        $mail = new PHPMailer(true);

        // Configura il server SMTP
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = SENDER_EMAIL;  // La tua email
            $mail->Password = SENDER_PASSWORD;    // La password dell'app specifica
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Destinatario
            $mail->setFrom(SENDER_EMAIL, 'Your Name');
            $mail->addAddress($email, 'Recipient Name');
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            // Invia l'email
            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

    public function register(string $email, string $username, string $password, string $activation_code): bool
    {
        return $this->userModel->create($email, $username, $password, $activation_code);
    }

    /*************************************** Login ***************************************/

    private function log_user_in(array $user): bool
    {
        // prevent session fixation attack
        if (session_regenerate_id()) {
            // set username & id in the session
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_id'] = $user['id'];
            return true;
        }
        return false;
    }

    public function login(string $username, string $password, bool $rememberMe = false): ?array
    {
        $user = $this->userModel->find_by_username($username);
        if ($user && $this->is_user_active($user) && password_verify($password, $user['password'])) {
            $this->log_user_in($user);
            if ($rememberMe) {
                $this->remember_me($user['id']);
            }
            return $user;
        }
        return null;
    }

    /*************************************** Logout ***************************************/

    public function logout()
    {
        if ($this->is_user_logged_in())
        {
            $this->userModel->delete_user_token($_SESSION['user_id']);
            unset($_SESSION['username'], $_SESSION['user_id']);
            if (isset($_COOKIE['remember_me'])) {
                unset($_COOKIE['remember_me']);
                setcookie('remember_me', '', -1);
            }
            session_destroy();
        }
        
    }

    /*************************************** Remember Me ***************************************/

    private function parse_token(string $token): ?array
    {
        $parts = explode(':', $token);

        if ($parts && count($parts) == 2) {
            return [$parts[0], $parts[1]];
        }
        return null;
    }

    private function token_is_valid(string $token): bool
    {
        // parse the token to get the selector and validator 
        [$selector, $validator] = $this->parse_token($token);
        $tokens = $this->userModel->find_user_token_by_selector($selector);
        if (!$tokens) {
            return false;
        }

        $hashed_validator = hash_hmac('sha256', $validator, SECRET_KEY);

        return hash_equals($hashed_validator, $tokens['hashed_validator']);
    }

    private function generate_tokens(): array
    {
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        return [$selector, $validator, $selector . ':' . $validator];
    }

    private function remember_me(int $user_id, int $days = 30)
    {
        // generate a token and prevent timing attack
        [$selector, $validator, $token] = $this->generate_tokens();
        
        // remove all existing token associated with the user id
        $this->userModel->delete_user_token($user_id);
        
        // set expiration date
        $expired_seconds = time() + 60 * 60 * 24 * $days;

        // insert a token to the database
        $expiry = date('Y-m-d H:i:s', $expired_seconds);
        
        if ($this->userModel->insert_user_token($user_id, $selector, $validator, $expiry)) {
            setcookie('remember_me', $token, $expired_seconds);
        }
    }

    /*************************************** Activation ***************************************/

    public function is_user_verified(string $activation_code): bool
    {
        $user =  $this->userModel->finde_by_activation_code($activation_code);
        return $user && $this->is_user_active($user);
    }

    public function activate(string $activation_code): bool
    {

        $user = $this->userModel->find_unverified_user($activation_code);
        if ($user) {
            return $this->userModel->activate($user['id']);
        }
        return false;
    }
}
