<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\User;
use App\Services\Validator;

/** Cadastro e login (US11). */
final class AuthController extends Controller
{
    public function registerForm(): void
    {
        $this->view('auth/register', ['title' => 'Criar conta']);
    }

    public function register(): void
    {
        $name = (string) $this->request->input('nome', '');
        $email = mb_strtolower((string) $this->request->input('email', ''));
        $password = (string) $this->request->input('senha', '');
        $confirmation = (string) $this->request->input('senha_confirmacao', '');
        $users = new User();

        $errors = [];
        if (!Validator::length($name, 3, 100)) {
            $errors['nome'] = 'Informe seu nome (mínimo de 3 letras).';
        }
        if (!Validator::email($email)) {
            $errors['email'] = 'Informe um e-mail válido.';
        } elseif ($users->emailExists($email)) {
            $errors['email'] = 'Este e-mail já está cadastrado. Faça login ou use outro e-mail.';
        }
        if (!Validator::password($password)) {
            $errors['senha'] = 'A senha precisa ter pelo menos 8 caracteres, com letras e números.';
        } elseif ($password !== $confirmation) {
            $errors['senha_confirmacao'] = 'As senhas não conferem.';
        }
        if ($errors !== []) {
            $this->backWithErrors('/cadastro', $errors);
        }

        $id = $users->create($name, $email, $password);
        Auth::login($users->find($id));
        Session::flash('success', 'Conta criada! Bem-vindo(a), ' . explode(' ', $name)[0] . '.');
        redirect($this->intended());
    }

    public function loginForm(): void
    {
        if (Auth::check()) {
            redirect('/');
        }
        $this->view('auth/login', ['title' => 'Entrar']);
    }

    public function login(): void
    {
        $email = mb_strtolower((string) $this->request->input('email', ''));
        $password = (string) $this->request->input('senha', '');
        $user = (new User())->findByEmail($email);

        // Mensagem genérica para não revelar quais e-mails estão cadastrados.
        if ($user === null || !password_verify($password, $user['senha_hash'])) {
            $this->backWithErrors('/login', ['email' => 'E-mail ou senha incorretos.']);
        }

        unset($user['senha_hash']);
        Auth::login($user);
        Session::flash('success', 'Olá, ' . explode(' ', $user['nome'])[0] . '!');
        redirect($user['perfil'] === 'gerente' ? '/admin' : $this->intended());
    }

    public function logout(): void
    {
        $cart = $_SESSION['cart'] ?? [];
        Auth::logout();
        Session::start();
        $_SESSION['cart'] = $cart;
        redirect('/');
    }

    private function intended(): string
    {
        $path = (string) Session::get('_intended', '/');
        Session::forget('_intended');
        return str_starts_with($path, '/api/') ? '/' : $path;
    }
}
