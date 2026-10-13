<?php

declare(strict_types=1);

namespace Tests\Integration;

/**
 * Testes de integração pela camada HTTP: sobe o servidor embutido do PHP
 * apontando para o banco de testes e faz requisições reais (rotas, segurança, JSON).
 */
final class HttpTest extends DatabaseTestCase
{
    private static $server;
    private static string $base;
    private string $cookies;

    public static function setUpBeforeClass(): void
    {
        if (!function_exists('curl_init')) {
            self::markTestSkipped('Extensão curl não disponível.');
        }
        $port = random_int(18000, 18999);
        self::$base = "http://127.0.0.1:{$port}";
        $env = [
            'DB_HOST' => getenv('DB_HOST'), 'DB_PORT' => getenv('DB_PORT'), 'DB_NAME' => getenv('DB_NAME'),
            'DB_USER' => getenv('DB_USER'), 'DB_PASS' => getenv('DB_PASS'), 'APP_DEBUG' => 'false', 'PATH' => getenv('PATH'),
        ];
        self::$server = proc_open(
            [PHP_BINARY, '-S', "127.0.0.1:{$port}", '-t', ROOT_PATH . '/public', ROOT_PATH . '/server.php'],
            [1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']],
            $pipes,
            ROOT_PATH,
            $env
        );
        for ($i = 0; $i < 50; $i++) {
            if (@fsockopen('127.0.0.1', $port)) {
                return;
            }
            usleep(100000);
        }
        self::markTestSkipped('Servidor embutido não iniciou.');
    }

    public static function tearDownAfterClass(): void
    {
        if (is_resource(self::$server)) {
            proc_terminate(self::$server);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->cookies = tempnam(sys_get_temp_dir(), 'ck');
    }

    protected function tearDown(): void
    {
        @unlink($this->cookies);
    }

    /** @return array{status:int, body:string, location:?string} */
    private function request(string $method, string $path, array $data = [], array $headers = []): array
    {
        $ch = curl_init(self::$base . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_COOKIEJAR => $this->cookies,
            CURLOPT_COOKIEFILE => $this->cookies,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
        ]);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        $raw = (string) curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        preg_match('/^Location:\s*(\S+)/mi', substr($raw, 0, $headerSize), $m);
        return ['status' => $status, 'body' => substr($raw, $headerSize), 'location' => $m[1] ?? null];
    }

    private function token(string $html): string
    {
        preg_match('/name="_token" value="([a-f0-9]+)"/', $html, $m);
        return $m[1] ?? '';
    }

    private function login(string $email, string $password): array
    {
        $token = $this->token($this->request('GET', '/login')['body']);
        return $this->request('POST', '/login', ['_token' => $token, 'email' => $email, 'senha' => $password]);
    }

    public function testPaginasPublicasERotaInexistente(): void
    {
        self::assertSame(200, $this->request('GET', '/')['status']);
        self::assertSame(200, $this->request('GET', '/produto/3')['status']);
        self::assertSame(404, $this->request('GET', '/produto/9999')['status']);
        self::assertSame(404, $this->request('GET', '/nao-existe')['status']);
    }

    public function testApiDaVitrineRespondeJson(): void
    {
        $r = $this->request('GET', '/api/produtos?pagina=2');
        $json = json_decode($r['body'], true);
        self::assertSame(200, $r['status']);
        self::assertSame(2, $json['page']);
        self::assertCount(6, $json['items']);
    }

    public function testAreasRestritasExigemLogin(): void
    {
        foreach (['/checkout', '/pedidos', '/enderecos', '/admin'] as $path) {
            $r = $this->request('GET', $path);
            self::assertSame(302, $r['status'], $path);
            self::assertStringEndsWith('/login', (string) $r['location']);
        }
        self::assertSame(401, $this->request('GET', '/api/pedidos/1/status')['status']);
    }

    public function testPostSemTokenCsrfERecusado(): void
    {
        $this->request('GET', '/');
        self::assertSame(419, $this->request('POST', '/carrinho/adicionar', ['produto_id' => 1, 'quantidade' => 1])['status']);
    }

    /** US09 RN#1 / US10 RN#2: apenas gerentes acessam a área administrativa. */
    public function testClienteNaoAcessaAreaDoGerente(): void
    {
        $this->login('cliente@exemplo.com.br', 'Cliente123');
        self::assertSame(403, $this->request('GET', '/admin/produtos')['status']);
        self::assertSame(403, $this->request('GET', '/admin/relatorios')['status']);
    }

    public function testLoginInvalidoMostraMensagemGenerica(): void
    {
        $r = $this->login('cliente@exemplo.com.br', 'errada');
        self::assertSame(302, $r['status']);
        self::assertStringContainsString('E-mail ou senha incorretos', $this->request('GET', '/login')['body']);
    }

    public function testGerenteExportaRelatorioCsv(): void
    {
        $r = $this->login('gerente@docenuvem.com.br', 'Gerente@123');
        self::assertStringEndsWith('/admin', (string) $r['location']);
        $csv = $this->request('GET', '/admin/relatorios/csv');
        self::assertSame(200, $csv['status']);
        self::assertStringContainsString('Período;Pedidos;"Faturamento (R$)"', $csv['body']);
    }

    /** Conteúdo digitado pelo usuário é escapado (proteção contra XSS). */
    public function testCadastroEscapaHtml(): void
    {
        $token = $this->token($this->request('GET', '/cadastro')['body']);
        $this->request('POST', '/cadastro', [
            '_token' => $token, 'nome' => '<script>alert(1)</script>', 'email' => 'xss@exemplo.com',
            'senha' => 'senha1234', 'senha_confirmacao' => 'senha1234',
        ]);
        $page = $this->request('GET', '/')['body'];
        self::assertStringNotContainsString('<script>alert(1)</script>', $page);
        self::assertStringContainsString('&lt;script&gt;', $page);
    }
}
