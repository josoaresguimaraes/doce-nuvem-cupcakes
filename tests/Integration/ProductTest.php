<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Models\Order;
use App\Models\Product;

/** US01 (vitrine), US02 (filtros) e US09 (gestão do catálogo) contra o MySQL. */
final class ProductTest extends DatabaseTestCase
{
    public function testVitrinePaginadaComIndisponiveisNoFim(): void
    {
        $products = new Product();
        $first = $products->paginate([], 1, 8);
        $second = $products->paginate([], 2, 8);

        self::assertSame(14, $first['total']);
        self::assertCount(8, $first['items']);
        self::assertTrue($first['has_more']);
        self::assertCount(6, $second['items']);
        self::assertFalse($second['has_more']);
        self::assertSame('Sem Glúten de Coco', end($second['items'])['nome'], 'esgotado aparece por último');
    }

    /** CA#3 da US02: combinação de filtros exige todas as categorias marcadas. */
    public function testFiltroCombinado(): void
    {
        $products = new Product();
        self::assertSame(2, $products->paginate([2])['total'], 'Veganos');
        self::assertSame(3, $products->paginate([3])['total'], 'Sem glúten');
        $both = $products->paginate([2, 3]);
        self::assertSame(1, $both['total']);
        self::assertSame('Vegano de Cacau', $both['items'][0]['nome']);
    }

    public function testCriarEditarProdutoComCategorias(): void
    {
        $products = new Product();
        $id = $products->create(['nome' => 'Teste', 'descricao' => 'Descrição curta', 'preco' => 10.5, 'estoque' => 3, 'imagem' => null], [1, 5]);
        self::assertSame([1, 5], $products->find($id)['categoria_ids']);

        $products->update($id, ['nome' => 'Teste 2', 'descricao' => 'Outra', 'preco' => 11, 'estoque' => 0, 'ativo' => 1], [4]);
        $found = $products->find($id);
        self::assertSame('Teste 2', $found['nome']);
        self::assertSame([4], $found['categoria_ids']);
    }

    /** Produto com pedidos é desativado (não excluído) para preservar o histórico. */
    public function testRemoverProdutoUsadoEmPedidoApenasDesativa(): void
    {
        $products = new Product();
        $orderId = (new Order())->create($this->orderData(), [[
            'produto_id' => 1, 'nome_produto' => 'Chocolate Clássico', 'cobertura_id' => null, 'nome_cobertura' => null,
            'mensagem' => '', 'quantidade' => 1, 'preco_unitario' => 9.9, 'subtotal' => 9.9,
        ]]);
        self::assertGreaterThan(0, $orderId);

        self::assertSame('desativado', $products->remove(1));
        self::assertSame(0, (int) $products->find(1)['ativo']);
        self::assertNull($products->find(1, true), 'some da vitrine');

        self::assertSame('excluido', $products->remove(2));
        self::assertNull($products->find(2));
    }

    private function orderData(): array
    {
        return [
            'usuario_id' => 2, 'endereco_entrega' => 'Rua Exemplo, 100', 'cep_entrega' => '18035000',
            'subtotal' => 9.9, 'taxa_entrega' => 8, 'taxa_pedido_minimo' => 5, 'taxa_urgencia' => 0, 'total' => 22.9,
            'data_entrega' => date('Y-m-d', strtotime('+1 day')), 'hora_entrega' => '10:00:00',
            'metodo_pagamento' => 'pix', 'status_pagamento' => 'aguardando',
        ];
    }
}
