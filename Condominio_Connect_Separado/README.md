# Condomínio Connect — versão HTML, CSS, JavaScript, PHP e MySQL

Esta edição foi organizada para facilitar a leitura e a edição no VS Code. Cada tecnologia tem uma responsabilidade clara, sem misturar todo o sistema em um único arquivo.

## Estrutura

```text
Condominio_Connect_Separado/
├── config/
│   └── database.php               # conexão do PHP com o MySQL
├── database/
│   └── condominio_connect.sql     # tabelas, relacionamentos e dados iniciais
├── public/
│   ├── index.html                 # estrutura das telas
│   ├── assets/
│   │   ├── css/styles.css         # cores, fontes, layout e responsividade
│   │   └── js/
│   │       ├── api.js             # comunicação do JavaScript com o PHP
│   │       └── app.js             # telas, navegação, filtros e interações
│   └── api/
│       ├── bootstrap.php          # sessão, conexão e funções comuns
│       ├── login.php              # autenticação
│       ├── logout.php             # encerramento da sessão
│       ├── session.php            # consulta da sessão atual
│       ├── register.php           # cadastro de cliente ou profissional
│       ├── categories.php         # categorias de serviço
│       ├── professionals.php      # profissionais validados
│       ├── requests.php           # chamados por perfil
│       ├── messages.php           # chat vinculado ao chamado
│       └── admin.php              # usuários, profissionais, chamados e validação
└── storage/uploads/profissionais/ # documentos enviados pelos profissionais
```

## Como executar no XAMPP

1. Instale e abra o XAMPP.
2. Inicie os serviços **Apache** e **MySQL**.
3. Extraia esta pasta dentro de `C:\xampp\htdocs\`.
4. Abra `http://localhost/phpmyadmin`.
5. Acesse a aba **Importar** e selecione `database/condominio_connect.sql`.
6. Confira os dados de conexão em `config/database.php`.
7. Abra no navegador:

   `http://localhost/Condominio_Connect_Separado/public/`

## Acessos iniciais locais

Os registros incluídos no SQL usam a senha inicial `password`. Troque as senhas antes de disponibilizar o sistema fora do computador de desenvolvimento.

- Morador: `maria@condominio.com`
- Profissional: `roberto@servicos.com`
- Administrador: `admin@sgbtech.com`

## Onde editar

- Texto e blocos fixos das telas: `public/index.html`.
- Cores, tamanhos, espaçamentos e responsividade: `public/assets/css/styles.css`.
- Conteúdo dinâmico, navegação e eventos de clique: `public/assets/js/app.js`.
- Chamadas feitas ao servidor: `public/assets/js/api.js`.
- Regras do servidor e consultas ao banco: `public/api/*.php`.
- Estrutura e dados do banco: `database/condominio_connect.sql`.

## Observações para publicação

- Configure HTTPS e variáveis de ambiente para as credenciais do banco.
- Troque as senhas iniciais e remova dados que não serão usados.
- Conecte o fluxo de pagamento ao gateway escolhido e valide a resposta por webhook.
- Integre a consulta de CNPJ a uma fonte autorizada; a aprovação administrativa não deve depender apenas do texto informado pelo profissional.
- Armazene documentos fora da pasta pública e limite formato, tamanho e permissão de acesso.

O código usa PDO e consultas preparadas. O controle de acesso é feito no PHP de acordo com o perfil salvo na sessão.
