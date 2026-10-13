<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Models\Address;
use App\Models\User;
use PDOException;

/** US11 (conta) e US15 (endereços). */
final class UserAndAddressTest extends DatabaseTestCase
{
    public function testSenhaGuardadaComoHash(): void
    {
        $users = new User();
        $id = $users->create('Bia Souza', 'Bia@Exemplo.com', 'segredo123');
        $row = $users->findByEmail('bia@exemplo.com');

        self::assertSame($id, (int) $row['id']);
        self::assertSame('cliente', $row['perfil']);
        self::assertNotSame('segredo123', $row['senha_hash']);
        self::assertTrue(password_verify('segredo123', $row['senha_hash']));
        self::assertArrayNotHasKey('senha_hash', $users->find($id), 'find() não expõe o hash');
    }

    /** RN#2: e-mail único (garantido também pelo banco). */
    public function testEmailUnico(): void
    {
        $users = new User();
        self::assertTrue($users->emailExists('CLIENTE@exemplo.com.br'));
        $this->expectException(PDOException::class);
        $users->create('Duplicado', 'cliente@exemplo.com.br', 'senha1234');
    }

    /** RN#1 da US15: endereço só é acessível pelo dono. */
    public function testEnderecoPertenceAoDono(): void
    {
        $addresses = new Address();
        self::assertNotNull($addresses->findForUser(1, 2));
        self::assertNull($addresses->findForUser(1, 1));
        self::assertFalse($addresses->delete(1, 1), 'outro usuário não exclui');
        self::assertSame(1, $addresses->countForUser(2));
        self::assertTrue($addresses->delete(1, 2));
        self::assertSame(0, $addresses->countForUser(2));
    }

    public function testFormatoDoEndereco(): void
    {
        $text = Address::format((new Address())->findForUser(1, 2));
        self::assertSame('Rua Exemplo, 100 – Apto 12 – Centro, Sorocaba/SP – CEP 18035-000', $text);
    }
}
