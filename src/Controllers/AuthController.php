<?php

require_once __DIR__ .'/../Models/User.php';
require_once __DIR__ . '/../bootstrap.php';

class AuthController {
    protected $userModel;
    protected $db;
    protected $filter;
    protected $logger;

    public function __construct(ServiceContainer $container)
    {
        $this->db = $container->getLazy('db');
        $this->userModel = new User($this->db);
        $this->filter = $container->getLazy('filter');
        $this->logger = $container->getLazy('logger') ?? null;
    }

    public function current_user()
    {
        if ($this->is_user_logged_in()) {
            return $_SESSION['username'];
        }
        return null;
    }

    private function load_view(string $view, array $data = []): void
    {
        // Estrai le variabili per renderle disponibili nella vista
        extract($data);

        // Carica la vista dalla cartella src/Views
        require_once __DIR__ . '/../Views/' . $view;
    }

    private function load_view_message(string $view, string $message, string $type = FLASH_SUCCESS): void
    {
        flash('flash_' . uniqid(), $message, $type);

        $this->load_view($view, []);
    }

    private function back_home(): void
    {
        header('Location: /');
    }

    /*************************************** Registration ***************************************/

    private function generate_activation_code(): string
    {
        return bin2hex(random_bytes(16));
    }

    private function send_activation_email(string $email, string $activation_code): void
    {
        // create the activation link
        $activation_link = APP_URL . "/auth/activate?email=$email&activation_code=$activation_code";

        // set email subject & body
        $subject = 'Please activate your account';
        $message = <<<MESSAGE
                Hi,
                Please click the following link to activate your account:
                $activation_link
                MESSAGE;
        // email header
        $header = "From:" . SENDER_EMAIL_ADDRESS;

        // send the email
        mail($email, $subject, nl2br($message), $header);
    }

    public function register()
    {
        if ($this->is_user_logged_in()) {
            $this->back_home();
        }

        $errors = [];
        $inputs = [];

        if (is_post_request()) {
            $fields = [
                'username' => 'string | required | alphanumeric | between: 3, 25 | unique: users, username',
                'email' => 'email | required | email | unique: users, email',
                'password' => 'string | required | secure',
                'password2' => 'string | required | same: password',
                'agree' => 'string | required'
            ];

            // custom messages
            $messages = [
                'password2' => [
                    'required' => 'Please enter the password again',
                    'same' => 'The password does not match'
                ],
                'agree' => [
                    'required' => 'You need to agree to the term of services to register'
                ]
            ];

            [$inputs, $errors] = $this->filter->filter($_POST, $fields, $messages);

            if ($errors) {
                return $this->load_view('register.php', compact('errors', 'inputs'));

            }

            $activation_code = $this->generate_activation_code();

            if ($this->userModel->create($inputs['email'], $inputs['username'], $inputs['password'], $activation_code)) {

                // send the activation email
                $this->send_activation_email($inputs['email'], $activation_code);

                return $this->load_view_message(
                    'login.php',
                    'Please check your email to activate your account before signing in'
                );

            }

        } else if (is_get_request()) {
            [$errors, $inputs] = session_flash('errors', 'inputs');

            return $this->load_view('register.php', compact('errors', 'inputs'));
        }
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

    public function login()
    {
        if ($this->is_user_logged_in()) {
            $this->back_home();
        }

        $inputs = [];
        $errors = [];

        if (is_post_request()) {
            $fields = [
                'username' => 'string | required',
                'password' => 'string | required',
                'remember_me' => 'string'
            ];

            // custom messages
            $messages = [
                'username' => [
                    'required' => 'Please enter your username'
                ],
                'password' => [
                    'required' => 'Please enter your password'
                ]
            ];

            [$inputs, $errors] = $this->filter->filter($_POST, $fields, $messages);

            if ($errors) {
                return $this->load_view('login.php', compact('errors', 'inputs'));
            }

            // if login fails
            $user = $this->userModel->find_by_username($inputs['username']);

            if (!$user || !$this->is_user_active($user) || !password_verify($inputs['password'], $user['password'])) {

                $errors['login'] = 'Invalid username or password';

                return $this->load_view('login.php', compact('errors', 'inputs'));

            }

            $this->log_user_in($user);

            if ($inputs['remember_me']) {
                $this->remember_me($user['id']);
            }

            // login successfully
            $this->back_home();


        } else if (is_get_request()) {
            [$errors, $inputs] = session_flash('errors', 'inputs');

            return $this->load_view('login.php', compact('errors', 'inputs'));
        }
    }

    /*************************************** Logout ***************************************/

    public function logout()
    {
        if ($this->is_user_logged_in()) {

            // delete the user token
            $this->userModel->delete_user_token($_SESSION['user_id']);

            // delete session
            unset($_SESSION['username'], $_SESSION['user_id']);

            // remove the remember_me cookie
            if (isset($_COOKIE['remember_me'])) {
                unset($_COOKIE['remember_me']);
                setcookie('remember_me', '', -1);
            }

            // remove all session data
            session_destroy();
        }

        // redirect to the login page
        $this->back_home();
    }

    /*************************************** Remember Me ***************************************/

    private function generate_tokens(): array
    {
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
    
        return [$selector, $validator, $selector . ':' . $validator];
    }

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

        return password_verify($validator, $tokens['hashed_validator']);
    }

    public function remember_me(int $user_id, int $day = 30)
    {
        [$selector, $validator, $token] = $this->generate_tokens();

        // remove all existing token associated with the user id
        $this->userModel->delete_user_token($user_id);

        // set expiration date
        $expired_seconds = time() + 60 * 60 * 24 * $day;

        // insert a token to the database
        $hashed_validator = password_hash($validator, PASSWORD_DEFAULT);
        $expiry = date('Y-m-d H:i:s', $expired_seconds);

        if ($this->userModel->insert_user_token($user_id, $selector, $hashed_validator, $expiry)) {
            setcookie('remember_me', $token, $expired_seconds);
        }
    }

    /*************************************** Activation ***************************************/

    private function is_user_active($user)
    {
        return (int) $user['active'] === 1;
    }

    public function activate()
    {
        $errors = [];
        $inputs = [];

        if (is_get_request()) {
            $fields = [
                'email' => 'string | required | email',
                'activation_code' => 'string | required'
            ];

            // custom messages
            $messages = [
                'email' => [
                    'required' => 'Please enter your email',
                    'email' => 'Please enter a valid email'
                ],
                'activation_code' => [
                    'required' => 'Please enter the activation code'
                ]
            ];

            // sanitize the email & activation code
            [$inputs, $errors] = $this->filter->filter($_GET, $fields, $messages);

            if (!$errors) {

                $user = $this->userModel->find_unverified_user($inputs['activation_code'], $inputs['email']);

                // if user exists and activate the user successfully
                if ($user && $this->userModel->activate($user['id'])) {
                    return $this->load_view_message(
                        'login.php',
                        'You account has been activated successfully. Please login here.'
                    );
                }
            }
        }

        return $this->load_view_message(
            'register.php',
            'The activation link is not valid, please register again.',
            FLASH_ERROR
        );
    }
}