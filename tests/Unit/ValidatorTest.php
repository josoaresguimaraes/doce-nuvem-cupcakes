<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Validator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    public function testEmailValidoEInvalido(): void
    {
        self::assertTrue(Validator::email('ana@exemplo.com.br'));
        self::assertFalse(Validator::email('ana@'));
        self::assertFalse(Validator::email('ana exemplo.com'));
    }

    /** US11 RN#1: mínimo 8 caracteres, com letras e números. */
    public function testRegraDeSenha(): void
    {
        self::assertTrue(Validator::password('cupcake123'));
        self::assertTrue(Validator::password('Açúcar2026'));
        self::assertFalse(Validator::password('abc123'), 'menos de 8 caracteres');
        self::assertFalse(Validator::password('somenteletras'), 'sem números');
        self::assertFalse(Validator::password('1234567890'), 'sem letras');
    }

    /** US15 CA#2: CEP com 8 dígitos. */
    public function testCep(): void
    {
        self::assertTrue(Validator::cep('18035-000'));
        self::assertTrue(Validator::cep('18035000'));
        self::assertFalse(Validator::cep('1803500'));
        self::assertFalse(Validator::cep('18035-00a'));
        self::assertSame('18035000', Validator::digits('18035-000'));
    }

    /** US03 RN#1 (revisada — defeito D01 do laudo): até 50 caracteres, com pontuação comum. */
    public function testMensagemDoCupcake(): void
    {
        self::assertTrue(Validator::cupcakeMessage(''), 'mensagem é opcional');
        self::assertTrue(Validator::cupcakeMessage('Feliz aniversário Ana 30'));
        self::assertTrue(Validator::cupcakeMessage(str_repeat('a', 50)));
        self::assertFalse(Validator::cupcakeMessage(str_repeat('a', 51)), 'mais de 50 caracteres');
        self::assertTrue(Validator::cupcakeMessage('Parabéns!'), 'D01: ponto de exclamação é aceito');
        self::assertTrue(Validator::cupcakeMessage('Feliz aniversário, Ana! (30 anos)'), 'vírgula e parênteses');
        self::assertTrue(Validator::cupcakeMessage("Ana & Léo: felicidades? Sim!"), 'demais pontuações comuns');
        self::assertTrue(Validator::cupcakeMessage("D'Ávila - 10 anos."), 'apóstrofo, hífen e ponto');
        self::assertFalse(Validator::cupcakeMessage('<script>'), 'tentativa de HTML');
        self::assertFalse(Validator::cupcakeMessage('Diga "oi"'), 'aspas duplas continuam bloqueadas');
        self::assertFalse(Validator::cupcakeMessage('a/b\\c'), 'barras continuam bloqueadas');
        self::assertFalse(Validator::cupcakeMessage('Parabéns 🎉'), 'emoji não é impresso no topper');
    }

    public function testConversaoDeValores(): void
    {
        self::assertSame(12.9, Validator::money('12,90'));
        self::assertSame(12.9, Validator::money('12.90'));
        self::assertSame(1234.56, Validator::money('1.234,56'));
        self::assertSame(0.0, Validator::money('0'));
        self::assertNull(Validator::money('doze'));
        self::assertNull(Validator::money('-5'));
        self::assertNull(Validator::money('1,999'));
    }

    public function testEnderecoValidoENormalizado(): void
    {
        [$data, $errors] = Validator::address([
            'apelido' => ' Casa ', 'cep' => '18035-000', 'logradouro' => 'Rua A', 'numero' => '10',
            'complemento' => '', 'bairro' => 'Centro', 'cidade' => 'Sorocaba', 'uf' => 'sp',
        ]);
        self::assertSame([], $errors);
        self::assertSame('18035000', $data['cep']);
        self::assertSame('SP', $data['uf']);
        self::assertSame('Casa', $data['apelido']);
    }

    public function testEnderecoComErros(): void
    {
        [, $errors] = Validator::address(['cep' => '123', 'uf' => 'XX']);
        self::assertArrayHasKey('cep', $errors);
        self::assertArrayHasKey('uf', $errors);
        self::assertArrayHasKey('logradouro', $errors);
        self::assertArrayHasKey('apelido', $errors);
    }
}
