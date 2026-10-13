# Como rodar, versionar e publicar

## 1. Rodar no seu computador

**Opção A: XAMPP (Windows), o mais simples**
1. Instale o [XAMPP](https://www.apachefriends.org/) e inicie **Apache** e **MySQL**.
2. Copie a pasta do projeto para `C:\xampp\htdocs\cupcakes`.
3. Abra `http://localhost/phpmyadmin`, crie o banco `cupcakes` (agrupamento `utf8mb4_unicode_ci`) e, na aba **Importar**, envie `database/schema.sql` e depois `database/seed.sql`.
4. Copie `.env.example` para `.env` e ajuste:
   ```
   BASE_PATH=/cupcakes
   DB_NAME=cupcakes
   DB_USER=root
   DB_PASS=
   ```
5. Acesse `http://localhost/cupcakes`.

**Opção B: servidor embutido do PHP**
```bash
cp .env.example .env            # ajuste DB_USER/DB_PASS
mysql -u root -p -e "CREATE DATABASE cupcakes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql -u root -p cupcakes < database/schema.sql
mysql -u root -p cupcakes < database/seed.sql
php -S 127.0.0.1:8000 -t public server.php
```
Acesse `http://127.0.0.1:8000`.

## 2. Publicar o código no GitHub

1. Crie uma conta em [github.com](https://github.com/) (use um nome de usuário profissional: o repositório vira seu portfólio).
2. Clique em **New repository**, com nome `doce-nuvem-cupcakes`, visibilidade **Public** e **sem** README (o projeto já tem um).
3. No terminal, dentro da pasta do projeto:
   ```bash
   git remote add origin https://github.com/SEU-USUARIO/doce-nuvem-cupcakes.git
   git push -u origin main
   ```
   O GitHub vai pedir login. Use um *Personal Access Token* como senha ou o GitHub Desktop.
4. Confira no navegador que pastas, `README.md` e diagramas aparecem. O GitHub desenha os diagramas Mermaid automaticamente.

O arquivo `.env` (com senhas) **não vai** para o GitHub, porque está no `.gitignore`. Só o `.env.example` vai.

Para as próximas entregas, depois de corrigir algo:
```bash
git add .
git commit -m "Corrige validação do CEP apontada pelo testador 2"
git push
```

## 3. Publicar o site (hospedagem gratuita com PHP e MySQL)

Os passos abaixo usam o **InfinityFree** como exemplo (gratuito, com PHP, MySQL, `.htaccess` e SSL grátis). Os nomes dos menus podem mudar um pouco.

1. Crie a conta e uma **hospedagem** com um subdomínio gratuito (ex.: `docenuvem.infinityfreeapp.com`).
2. No painel, em **MySQL Databases**, crie um banco. Anote **host** (ex.: `sqlXXX.infinityfree.com`), **nome do banco**, **usuário** e **senha**.
3. Abra o **phpMyAdmin** desse banco, aba **Importar**, e envie `database/schema.sql` e depois `database/seed.sql`.
4. No seu computador, crie o `.env` de produção:
   ```
   APP_ENV=production
   APP_DEBUG=false
   BASE_PATH=
   DB_HOST=sqlXXX.infinityfree.com
   DB_PORT=3306
   DB_NAME=if0_XXXXXXX_cupcakes
   DB_USER=if0_XXXXXXX
   DB_PASS=sua-senha
   ```
5. Pelo **Gerenciador de Arquivos** (ou FTP com FileZilla), envie **todo o conteúdo** do projeto para dentro de `htdocs/`, **incluindo** os arquivos ocultos `.htaccess` e `.env`. Não é preciso enviar `tests/`, `docs/` nem `vendor/`.
   - O `.htaccess` da raiz encaminha tudo para `public/` e bloqueia o acesso a `app/`, `database/`, `.env` etc.
6. Ative o **SSL gratuito** no painel, para o site abrir com `https://` (requisito de segurança da US05).
7. Teste: abra o site, entre como cliente, faça um pedido, depois entre como gerente e avance o status.
8. **Troque as senhas das contas de demonstração** ou crie as suas antes de divulgar o link (ou mantenha as de demonstração só durante a avaliação).

**Se algo der errado:** ative `APP_DEBUG=true` temporariamente para ver a mensagem de erro, ou veja `storage/logs/app.log`. Erros comuns: dados do banco errados no `.env`, ou `.htaccess` não enviado (as páginas internas dão 404).

## 4. Links para o documento da atividade

Depois de publicar, preencha no documento da atividade:
- **Link do repositório:** `https://github.com/SEU-USUARIO/doce-nuvem-cupcakes`
- **Link da solução em funcionamento:** `https://seu-subdominio.infinityfreeapp.com`
- **Link do vídeo:** YouTube (não listado) ou Google Drive com acesso "qualquer pessoa com o link"
