<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Category;
use App\Models\Covering;
use App\Services\Validator;

/** Categorias (US02 RN#1) e coberturas (US03 RN#2) configuráveis pelo gerente. */
final class CatalogController extends Controller
{
    public function categories(): void
    {
        $this->view('admin/categorias/index', [
            'title' => 'Categorias e coberturas',
            'categories' => (new Category())->all(),
            'coverings' => (new Covering())->all(),
        ], 'layouts/admin');
    }

    public function storeCategory(): void
    {
        $name = (string) $this->request->input('nome', '');
        $model = new Category();
        if (!Validator::length($name, 3, 50)) {
            $this->backWithErrors('/admin/categorias', ['nome' => 'O nome deve ter de 3 a 50 caracteres.']);
        }
        if ($model->nameExists($name)) {
            $this->backWithErrors('/admin/categorias', ['nome' => 'Já existe uma categoria com esse nome.']);
        }
        $model->create($name);
        Session::flash('success', "Categoria \"{$name}\" criada.");
        redirect('/admin/categorias');
    }

    public function destroyCategory(int $id): void
    {
        (new Category())->delete($id);
        Session::flash('success', 'Categoria excluída. Os produtos continuam no catálogo.');
        redirect('/admin/categorias');
    }

    public function storeCovering(): void
    {
        $name = (string) $this->request->input('nome_cobertura', '');
        $extra = Validator::money((string) $this->request->input('preco_adicional', '0'));
        $model = new Covering();
        $errors = [];
        if (!Validator::length($name, 3, 50)) {
            $errors['nome_cobertura'] = 'O nome deve ter de 3 a 50 caracteres.';
        } elseif ($model->nameExists($name)) {
            $errors['nome_cobertura'] = 'Já existe uma cobertura com esse nome.';
        }
        if ($extra === null || $extra > 100) {
            $errors['preco_adicional'] = 'Informe o valor adicional, por exemplo 2,00 (ou 0).';
        }
        if ($errors !== []) {
            $this->backWithErrors('/admin/categorias', $errors);
        }
        $model->create($name, $extra);
        Session::flash('success', "Cobertura \"{$name}\" criada.");
        redirect('/admin/categorias');
    }

    public function toggleCovering(int $id): void
    {
        (new Covering())->toggle($id);
        Session::flash('success', 'Disponibilidade da cobertura atualizada.');
        redirect('/admin/categorias');
    }
}
