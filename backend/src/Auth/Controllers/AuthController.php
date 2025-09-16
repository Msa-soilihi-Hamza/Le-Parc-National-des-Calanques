<?php

declare(strict_types=1);

namespace ParcCalanques\Auth\Controllers;

use ParcCalanques\Auth\Services\AuthService;
use ParcCalanques\Auth\DTOs\LoginRequest;
use ParcCalanques\Auth\DTOs\AuthResponse;
use ParcCalanques\Shared\Exceptions\AuthException;

class AuthController
{
    public function __construct(private AuthService $authService) {}

    private function url($path = '/') {
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/') {
            return $path;
        }
        return $basePath . $path;
    }

    public function showLogin(): void
    {
        // Redirect if already authenticated
        if ($this->authService->isAuthenticated()) {
            header('Location: ' . $this->url('/profile'));
            exit;
        }

        $this->render('auth/login');
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->url('/login'));
            exit;
        }

        try {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember = !empty($_POST['remember']);

            if (empty($email) || empty($password)) {
                throw new AuthException('Email et mot de passe requis');
            }

            $user = $this->authService->login($email, $password, $remember);

            // Redirect to profile page to show user data after login
            $redirectTo = $_GET['redirect'] ?? $this->url('/profile');
            $redirectTo = filter_var($redirectTo, FILTER_SANITIZE_URL);
            
            header('Location: ' . $redirectTo);
            exit;

        } catch (AuthException $e) {
            $this->render('auth/login', [
                'error' => $e->getMessage(),
                'email' => $email ?? ''
            ]);
        }
    }

    public function showRegister(): void
    {
        // Redirect if already authenticated
        if ($this->authService->isAuthenticated()) {
            header('Location: ' . $this->url('/profile'));
            exit;
        }

        $this->render('auth/register');
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->url('/register'));
            exit;
        }

        try {
            $data = $this->validateRegistrationData($_POST);
            
            $user = $this->authService->register([
                'email' => $data['email'],
                'password' => $data['password'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'role' => 'user' // Force user role for registration
            ]);

            // Redirect to profile page to show user data after registration
            header('Location: ' . $this->url('/profile?welcome=1'));
            exit;

        } catch (AuthException $e) {
            $this->render('auth/register', [
                'error' => $e->getMessage(),
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'email' => $_POST['email'] ?? ''
            ]);
        } catch (\InvalidArgumentException $e) {
            $this->render('auth/register', [
                'errors' => json_decode($e->getMessage(), true),
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'email' => $_POST['email'] ?? ''
            ]);
        }
    }

    public function logout(): void
    {
        $this->authService->logout();
        header('Location: ' . $this->url('/login'));
        exit;
    }


    public function profile(): void
    {
        $user = AuthGuard::require();
        
        $welcomeMessage = null;
        if (isset($_GET['welcome'])) {
            $welcomeMessage = 'Bienvenue ' . htmlspecialchars($user->getFirstName()) . ' ! Voici vos informations de profil.';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle profile update
            try {
                $this->updateProfile($user, $_POST);
                $this->render('profile', [
                    'user' => $user,
                    'success' => 'Profil mis à jour avec succès'
                ]);
            } catch (\Exception $e) {
                $this->render('profile', [
                    'user' => $user,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            $this->render('profile', [
                'user' => $user,
                'welcome_message' => $welcomeMessage
            ]);
        }
    }

    private function validateRegistrationData(array $data): array
    {
        $errors = [];
        
        // First name validation
        $firstName = trim($data['first_name'] ?? '');
        if (empty($firstName)) {
            $errors['first_name'] = 'Le prénom est requis';
        } elseif (strlen($firstName) < 2) {
            $errors['first_name'] = 'Le prénom doit faire au moins 2 caractères';
        }

        // Last name validation
        $lastName = trim($data['last_name'] ?? '');
        if (empty($lastName)) {
            $errors['last_name'] = 'Le nom est requis';
        } elseif (strlen($lastName) < 2) {
            $errors['last_name'] = 'Le nom doit faire au moins 2 caractères';
        }

        // Email validation
        $email = trim($data['email'] ?? '');
        if (empty($email)) {
            $errors['email'] = 'L\'email est requis';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format d\'email invalide';
        }

        // Password validation
        $password = $data['password'] ?? '';
        $passwordConfirmation = $data['password_confirmation'] ?? '';
        
        if (empty($password)) {
            $errors['password'] = 'Le mot de passe est requis';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Le mot de passe doit faire au moins 8 caractères';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors['password'] = 'Le mot de passe doit contenir au moins une majuscule';
        } elseif (!preg_match('/[a-z]/', $password)) {
            $errors['password'] = 'Le mot de passe doit contenir au moins une minuscule';
        } elseif (!preg_match('/\d/', $password)) {
            $errors['password'] = 'Le mot de passe doit contenir au moins un chiffre';
        }

        if ($password !== $passwordConfirmation) {
            $errors['password_confirmation'] = 'Les mots de passe ne correspondent pas';
        }

        // Terms validation
        if (empty($data['terms'])) {
            $errors['terms'] = 'Vous devez accepter les conditions d\'utilisation';
        }

        if (!empty($errors)) {
            throw new \InvalidArgumentException(json_encode($errors));
        }

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => $password
        ];
    }

    private function updateProfile($user, array $data): void
    {
        // This is a simplified example
        // In a real application, you would update the user data in the database
        throw new \Exception('Fonctionnalité non encore implémentée');
    }

    private function render(string $template, array $data = []): void
    {
        // Set base path for templates
        $GLOBALS['basePath'] = dirname($_SERVER['SCRIPT_NAME']);
        if ($GLOBALS['basePath'] === '/') {
            $GLOBALS['basePath'] = '';
        }
        
        // Extract variables for template
        extract($data);
        
        $templatePath = __DIR__ . "/../../templates/{$template}.php";
        
        if (!file_exists($templatePath)) {
            throw new \Exception("Template not found: {$template}");
        }

        include $templatePath;
    }
}