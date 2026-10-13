<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\Covering;
use App\Models\Product;
use App\Services\Cart;
use App\Services\CheckoutService;
use App\Services\Validator;

/** Carrinho com personalização (US03) e resumo de custos (US04). */
final class CartController extends Controller
{
    public function index(): void
    {
        $cart = Cart::fromSession();
        $service = new CheckoutService();
        $lines = $service->detailedLines($cart);
        $quote = $service->quote($cart, null, Auth::id() ?? 0, '', new \DateTimeImmutable());

        $this->view('carrinho/index', [
            'title' => 'Carrinho',
            'lines' => $lines,
            'values' => $quote['valores'],
            'settings' => $service->settings(),
        ]);
    }

    public function add(): void
    {
        $productId = (int) $this->request->input('produto_id');
        $coveringId = (int) $this->request->input('cobertura_id') ?: null;
        $message = (string) $this->request->input('mensagem', '');
        $quantity = (int) $this->request->input('quantidade', 1);
        $back = '/produto/' . $productId;

        $product = (new Product())->find($productId, true);
        if ($product === null) {
            Session::flash('error', 'Produto não encontrado.');
            redirect('/');
        }

        $errors = [];
        if ((int) $product['estoque'] === 0) {
            $errors['quantidade'] = 'Este cupcake está indisponível no momento.';
        } elseif ($quantity < 1 || $quantity > min(Cart::MAX_QTY_PER_LINE, (int) $product['estoque'])) {
            $errors['quantidade'] = sprintf('Escolha entre 1 e %d unidades.', min(Cart::MAX_QTY_PER_LINE, (int) $product['estoque']));
        }
        if ($coveringId !== null) {
            $covering = (new Covering())->find($coveringId);
            if ($covering === null || (int) $covering['ativa'] !== 1) {
                $errors['cobertura_id'] = 'Cobertura indisponível. Escolha outra opção.';
            }
        }
        if (!Validator::cupcakeMessage($message)) {
            $errors['mensagem'] = 'A mensagem aceita até 50 letras, números e espaços (sem símbolos).';
        }
        if ($errors !== []) {
            $this->backWithErrors($back, $errors);
        }

        Cart::fromSession()->add($productId, $coveringId, $message, $quantity);
        Session::flash('success', "{$quantity}× {$product['nome']} adicionado ao carrinho.");
        redirect($this->request->input('continuar') ? '/' : '/carrinho');
    }

    public function update(): void
    {
        $cart = Cart::fromSession();
        $quantities = (array) $this->request->input('quantidades', []);
        $lines = (new CheckoutService())->detailedLines($cart);
        $stock = array_column($lines, 'estoque', 'key');

        foreach ($quantities as $key => $qty) {
            $qty = (int) $qty;
            if (isset($stock[$key]) && $qty > $stock[$key]) {
                Session::flash('error', "Quantidade ajustada ao estoque disponível ({$stock[$key]}).");
                $qty = $stock[$key];
            }
            $cart->update((string) $key, $qty);
        }
        Session::flash('success', 'Carrinho atualizado.');
        redirect('/carrinho');
    }

    public function remove(): void
    {
        Cart::fromSession()->remove((string) $this->request->input('key'));
        Session::flash('success', 'Item removido do carrinho.');
        redirect('/carrinho');
    }
}
