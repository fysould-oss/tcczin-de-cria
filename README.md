# ConectaPrédio

Projeto web acadêmico para gestão de manutenção e comunicação em condomínios.

## Estrutura
- `index.php`: página inicial pública
- `pages/`: páginas públicas como Sobre, Serviços, Contato, FAQ, Login, Cadastro e recuperação de senha
- `includes/`: cabeçalho, navegação, rodapé e helpers PHP reutilizáveis
- `assets/`: CSS e JavaScript da interface
- `config/`: configuração do projeto
- `storage/`: banco SQLite local gerado automaticamente

## Como executar localmente
1. Instale o PHP 8+ e rode o servidor na raiz do projeto:
   `php -S 127.0.0.1:8000 -t .`
2. Acesse `http://127.0.0.1:8000/` no navegador.

## Próximos passos
- Implementar autenticação real e cadastro de usuários
- Criar dashboards por perfil
- Desenvolver fluxo de chamados, anexos e acompanhamento

https://prod.liveshare.vsengsaas.visualstudio.com/join?F9CC91292EFDF8714349F9FB8B15F431E146
