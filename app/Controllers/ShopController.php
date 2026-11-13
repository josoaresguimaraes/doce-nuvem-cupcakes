<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\HttpException;
use App\Models\Category;
use App\Models\Covering;
use App\Models\DeliveryZone;
use App\Models\Product;
use App\Models\Setting;

/** Vitrine virtual (US01) com filtros por categoria (US02). */
final class ShopController extends Controller
{
    private const PER_PAGE = 8;

    public function index(): void
    {
        $selected = $this->selectedCategories();
        $page = (new Product())->paginate($selected, 1, self::PER_PAGE);

        $this->view('vitrine/index', [
            'title' => 'Vitrine',
            'categories' => (new Category())->all(),
            'selected' => $selected,
            'page' => $page,
        ]);
    }

    /** JSON usado pelo "carregar mais" e pelos filtros dinâmicos (sem recarregar a página). */
    public function products(): void
    {
        $result = (new Product())->paginate(
            $this->selectedCategories(),
            max(1, (int) $this->request->query('pagina', 1)),
            self::PER_PAGE
        );
        $result['items'] = array_map(static fn (array $p) => [
            'id' => (int) $p['id'],
            'nome' => $p['nome'],
            'descricao' => $p['descricao'],
            'preco' => money($p['preco']),
            'imagem' => product_image($p['imagem']),
            'categorias' => $p['categorias'] ?? '',
            'disponivel' => (int) $p['estoque'] > 0,
            'url' => url('produto/' . $p['id']),
        ], $result['items']);
        $this->json($result);
    }

    public function show(int $id): void
    {
        $product = (new Product())->find($id, true);
        if ($product === null) {
            throw new HttpException(404, 'Este cupcake não está mais disponível.');
        }
        $this->view('vitrine/show', [
            'title' => $product['nome'],
            'product' => $product,
            'coverings' => (new Covering())->active(),
        ]);
    }

    /**
     * Página "Sobre nós" (sugestão S01 do laudo de qualidade): apresenta a confeitaria e as regras de
     * entrega e pagamento. Horários, taxas e áreas vêm do banco, então mudam junto com as configurações do gerente.
     */
    public function about(): void
    {
        $this->view('institucional/sobre', [
            'title' => 'Sobre nós',
            'settings' => (new Setting())->values(),
            'zones' => (new DeliveryZone())->all(),
            'categories' => (new Category())->all(),
        ]);
    }

    /** @return int[] */
    private function selectedCategories(): array
    {
        $raw = $this->request->query('categorias', []);
        if (is_string($raw)) {
            $raw = explode(',', $raw);
        }
        return array_values(array_filter(array_map('intval', (array) $raw)));
    }
}
