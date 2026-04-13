# 📚 EduNews
**Portal de Notícias Estudantil — Informação para a Comunidade Acadêmica**

Portal de notícias desenvolvido em **PHP** e **MySQL**, voltado à comunidade acadêmica brasileira.  
Permite publicação, edição e gerenciamento de notícias por categorias como **Vestibular, Acadêmico, Bolsas & Editais, Pesquisa e Cultura & Eventos**, com painel administrativo completo e suporte a **tema claro/escuro**.

---

## ⚙️ Informações Gerais

- **Banco de dados:** noticiasge  
- **Servidor local:** XAMPP (Apache + MySQL)  
- **URL:** http://localhost  
- **Tema:** Claro / Escuro (alternável)  

---

## 📁 Estrutura de Pastas

```
Site_Estudantil/
├── admin/              # Painel administrativo
├── assets/
│   ├── css/            # style.css (tema EduNews)
│   └── img/            # imagens enviadas
├── include/
│   ├── conexao.php     # conexão com banco
│   ├── funcoes.php     # funções auxiliares
│   ├── header.php      # cabeçalho
│   └── footer.php      # rodapé
├── public/
│   ├── index.php       # página inicial
│   ├── noticia.php     # notícia individual
│   ├── login.php       # login
│   └── cadastro.php    # cadastro
└── noticiasge.sql      # estrutura do banco
```

---

## 🚀 Como Instalar

### 1. Instalar o XAMPP
Ative **Apache** e **MySQL**.

### 2. Copiar o projeto
Coloque a pasta em:

```
C:\xampp\htdocs\Site_Estudantil\
```

### 3. Criar banco de dados
Acesse:
```
http://localhost/phpmyadmin
```

Crie o banco:
```
noticiasge
```

Importe o arquivo:
```
noticiasge.sql
```

### 4. Configurar conexão

Arquivo:
```
include/conexao.php
```

```php
$host = 'localhost';
$db   = 'noticiasge';
$user = 'root';
$pass = '';
```

### 5. Executar o projeto

Acesse no navegador:
```
http://localhost/Site_Estudantil/public/index.php
```

---

## 🗂️ Categorias

- 📝 **Vestibular** (`vestibular`) — ENEM, concursos  
- 🎓 **Acadêmico** (`academico`) — vida universitária  
- 💰 **Bolsas & Editais** (`bolsas`) — ProUni, FIES  
- 🔬 **Pesquisa** (`pesquisa`) — ciência e inovação  
- 🎨 **Cultura & Eventos** (`cultura`) — eventos e exposições  

---

## ✨ Funcionalidades

### Área Pública
- Destaque para notícia mais recente  
- Filtro por categoria  
- Busca por conteúdo  
- Página individual de notícias  
- Clima em tempo real  
- Ticker de manchetes  
- Tema claro/escuro  

### Área Administrativa
- Login com sessão  
- Cadastro de jornalistas  
- Dashboard com estatísticas  
- CRUD de notícias  
- Upload de imagem  
- Gerenciamento de usuários  

---

## 🛠️ Tecnologias

- PHP 8.2  
- MySQL  
- Apache (XAMPP)  
- HTML5  
- CSS3  
- JavaScript  

### Fontes
- Playfair Display  
- Source Sans 3  
- JetBrains Mono  

### APIs
- Open-Meteo API  
- Nominatim / OpenStreetMap  

---

## 📄 Licença

EduNews © 2026
