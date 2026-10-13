<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Session;
use App\Models\Category;
use App\Models\Product;
use App\Services\ImageUploader;
use App\Services\Validator;

/** CRUD do catálogo (US09). Acesso restrito ao gerente pelo middleware (RN#1). */
final class ProductController extends Controller
{
    public function index(): void
    {
        $page = (new Product())->paginate([], 1, 500, false);
        $this->view('admin/produtos/index', ['title' => 'Produtos', 'products' => $page['items']], 'layouts/admin');
    }

    public function create(): void
    {
        $this->view('admin/produtos/form', [
            'title' => 'Novo produto',
            'product' => null,
            'categories' => (new Category())->all(),
        ], 'layouts/admin');
    }

    public function store(): void
    {
        [$data, $categoryIds, $errors] = $this->validated(true);
        if ($errors !== []) {
            $this->backWithErrors('/admin/produtos/novo', $errors);
        }
        $id = (new Product())->create($data, $categoryIds);
        Session::flash('success', "Produto \"{$data['nome']}\" cadastrado (#{$id}).");
        redirect('/admin/produtos');
    }

    public function edit(int $id): void
    {
        $product = (new Product())->find($id) ?? throw new HttpException(404, 'Produto não encontrado.');
        $this->view('admin/produtos/form', [
            'title' => 'Editar produto',
            'product' => $product,
            'categories' => (new Category())->all(),
        ], 'layouts/admin');
    }

    public function update(int $id): void
    {
        $products = new Product();
        $products->find($id) ?? throw new HttpException(404, 'Produto não encontrado.');
        [$data, $categoryIds, $errors] = $this->validated(false);
        if ($errors !== []) {
            $this->backWithErrors("/admin/produtos/{$id}/editar", $errors);
        }
        $products->update($id, $data, $categoryIds);
        Session::flash('success', "Produto \"{$data['nome']}\" atualizado.");
        redirect('/admin/produtos');
    }

    public function destroy(int $id): void
    {
        $result = (new Product())->remove($id);
        Session::flash('success', $result === 'excluido'
            ? 'Produto excluído.'
            : 'O produto já tem pedidos, então foi desativado (some da vitrine, mas o histórico é mantido).');
        redirect('/admin/produtos');
    }

    /** @return array{0: array, 1: int[], 2: array<string,string>} */
    private function validated(bool $creating): array
    {
        $errors = [];
        $data = [
            'nome' => (string) $this->request->input('nome', ''),
            'descricao' => (string) $this->request->input('descricao', ''),
            'ativo' => $this->request->input('ativo') ? 1 : 0,
        ];

        if (!Validator::length($data['nome'], 3, 80)) {
            $errors['nome'] = 'O nome deve ter de 3 a 80 caracteres.';
        }
        if (!Validator::length($data['descricao'], 5, Validator::DESCRIPTION_MAX)) {
            $errors['descricao'] = 'A descrição deve ter de 5 a 100 caracteres.';
        }
        $price = Validator::money((string) $this->request->input('preco', ''));
        if ($price === null || $price <= 0 || $price > 9999) {
            $errors['preco'] = 'Informe um preço válido, por exemplo 12,90.';
        }
        $data['preco'] = $price;

        $stock = (string) $this->request->input('estoque', '');
        if (!ctype_digit($stock) || (int) $stock > 100000) {
            $errors['estoque'] = 'Informe o estoque como número inteiro (0 = indisponível).';
        }
        $data['estoque'] = (int) $stock;

        $categoryIds = array_map('intval', (array) $this->request->input('categorias', []));
        $categoryIds = (new Category())->existingIds($categoryIds);
        if ($categoryIds === []) {
            $errors['categorias'] = 'Selecione pelo menos uma categoria.';
        }

        $file = $this->request->file('imagem');
        if ($file !== null) {
            $uploader = new ImageUploader();
            $imageError = $uploader->validate($file);
            if ($imageError !== null) {
                $errors['imagem'] = $imageError;
            } elseif ($errors === []) {
                $data['imagem'] = $uploader->store($file);
            }
        } elseif ($creating) {
            $errors['imagem'] = 'Envie uma imagem JPG ou PNG de até 2 MB.';
        }

        return [$data, $categoryIds, $errors];
    }
}
