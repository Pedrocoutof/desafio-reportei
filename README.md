<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://www.edialog.com.br/wp-content/uploads/2020/10/reportei-logo.png" width="400" alt="Reportei Logo"></a></p>

## Sobre o Desafio

O desafio proposto trata-se de um sistema que seja capaz de realizar o login do usuário via GitHub, e montar um gráfico com o número de commits de um determinado repositório.

## Funcionalidades

- **Login via GitHub**: O sistema permite que os usuários façam login usando suas credenciais do GitHub.
- **Gráfico de Commits**: Após o login, o usuário pode selecionar um repositório e visualizar um gráfico com o número de commits ao longo do tempo.
- **Responsividade**: A aplicação é responsiva e pode ser usada em dispositivos móveis e desktops.

## Requisitos

- **PHP**: 8.2.7 ou superior
- **Composer**: 2.7.6 ou superior
- **Node.js**: 20.12.2 ou superior (para compilar assets front-end)
- **GitHub Developer Account**: Para obter as credenciais necessárias para a autenticação via GitHub.

## Instalação

### Passo 1: Clone o repositório

```
git clone https://github.com/seu-usuario/seu-repositorio.git
cd seu-repositorio
```

### Instale as dependências

```
composer install
npm install
npm run dev
```

### Passo 3: Configure o arquivo .env

```
cp .env.example .env
```
Edite o arquivo .env com suas credenciais de banco de dados e GitHub OAuth:
```
GITHUB_CLIENT_ID=seu_client_id
GITHUB_CLIENT_SECRET=seu_client_secret
GITHUB_REDIRECT_URL=http://{seu_dominio}/callback
```

### Passo 4: Gere a chave da aplicação

```
php artisan key:generate
```

### Passo 5: Execute as migrações

```
php artisan migrate
```

### Passo 6: Inicie o servidor de desenvolvimento

```
php artisan serve
```
