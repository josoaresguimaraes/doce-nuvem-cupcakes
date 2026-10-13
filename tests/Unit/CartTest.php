<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Cart;
use PHPUnit\Framework\TestCase;

/** US03: carrinho com personalização. */
final class CartTest extends TestCase
{
    private array $storage = [];
    private Cart $cart;

    protected function setUp(): void
    {
        $this->storage = [];
        $this->cart = new Cart($this->storage);
    }

    public function testMesmaPersonalizacaoSomaQuantidade(): void
    {
        $this->cart->add(3, 2, 'Feliz aniversário', 2);
        $this->cart->add(3, 2, 'Feliz aniversário', 3);
        self::assertCount(1, $this->cart->lines());
        self::assertSame(5, $this->cart->count());
    }

    public function testPersonalizacaoDiferenteViraOutraLinha(): void
    {
        $this->cart->add(3, 2, 'Ana', 1);
        $this->cart->add(3, 2, 'Bia', 1);
        $this->cart->add(3, null, 'Ana', 1);
        self::assertCount(3, $this->cart->lines());
    }

    public function testAtualizarERemover(): void
    {
        $key = $this->cart->add(1, null, '', 2);
        $this->cart->update($key, 7);
        self::assertSame(7, $this->cart->count());
        $this->cart->update($key, 0);
        self::assertTrue($this->cart->isEmpty(), 'quantidade 0 remove o item');

        $key = $this->cart->add(1, null, '', 1);
        $this->cart->remove($key);
        self::assertTrue($this->cart->isEmpty());
    }

    public function testLimiteDeQuantidadePorLinha(): void
    {
        $this->cart->add(1, null, '', 40);
        $this->cart->add(1, null, '', 40);
        self::assertSame(Cart::MAX_QTY_PER_LINE, $this->cart->count());
    }

    public function testCarrinhoFicaGuardadoNoArmazenamento(): void
    {
        $this->cart->add(1, null, '', 2);
        $outro = new Cart($this->storage);
        self::assertSame(2, $outro->count(), 'a sessão mantém o carrinho entre requisições');
        $outro->clear();
        self::assertTrue($this->cart->isEmpty());
    }
}
