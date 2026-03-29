<div align="center">
  <img src="docs/screenshots/desktop-login.png" width="500" alt="CrohnCare Logo" />

  <h1>CrohnCare</h1>
  <p><strong>Seu companheiro no cuidado com a saúde intestinal</strong></p>
  <p>Aplicativo web e mobile para pacientes com Doença de Crohn e Retocolite Ulcerativa gerenciarem seu tratamento, registrarem sintomas e acompanharem sua saúde ao longo do tempo.</p>

  <img src="https://img.shields.io/badge/Vue.js-3.x-4FC08D?logo=vue.js&logoColor=white" />
  <img src="https://img.shields.io/badge/Laravel-10.x-FF2D20?logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/Deploy-crohncare.online-6aaa2a" />
</div>

---

## Funcionalidades

- **Início** — Painel com resumo diário: remédios, crises, próxima consulta, aderência e gráficos
- **Remédios** — Cadastro de medicamentos com horários, dosagem e controle de estoque
- **Diário de Saúde** — Anotações diárias com indicador de bem-estar e registro de crises
- **Consultas e Exames** — Agenda médica com histórico de atendimentos
- **Avisos** — Notificações de lembretes de remédios e alertas de estoque baixo
- **Perfil** — Configurações da conta, tema e informações de saúde
- **Tour interativo** — Guia pelas principais funcionalidades ao primeiro acesso
- **Notificações push** — Lembretes de medicamentos via FCM
- **Tema personalizável** — Verde, azul, âmbar ou rosa

---

## Telas — Desktop

| Início | Remédios |
|--------|----------|
| ![Home Desktop](docs/screenshots/desktop-home.png) | ![Medicamentos Desktop](docs/screenshots/desktop-medicamentos.png) |

| Diário de Saúde | Consultas e Exames |
|-----------------|-------------------|
| ![Diário Desktop](docs/screenshots/desktop-diario.png) | ![Consultas Desktop](docs/screenshots/desktop-consultas.png) |

| Avisos | Perfil |
|--------|--------|
| ![Avisos Desktop](docs/screenshots/desktop-avisos.png) | ![Perfil Desktop](docs/screenshots/desktop-perfil.png) |

---

## Telas — Mobile

<div align="center">

| Início | Remédios | Diário |
|--------|----------|--------|
| <img src="docs/screenshots/mobile-home.png" width="220" /> | <img src="docs/screenshots/mobile-medicamentos.png" width="220" /> | <img src="docs/screenshots/mobile-diario.png" width="220" /> |

| Consultas | Avisos | Perfil |
|-----------|--------|--------|
| <img src="docs/screenshots/mobile-consultas.png" width="220" /> | <img src="docs/screenshots/mobile-avisos.png" width="220" /> | <img src="docs/screenshots/mobile-perfil.png" width="220" /> |

</div>

---

## Stack

| Camada | Tecnologia |
|--------|-----------|
| Frontend | Vue 3 + Vite + Pinia |
| Backend | Laravel 10 + MySQL |
| Notificações | Firebase Cloud Messaging (FCM) |
| Deploy | VPS com GitHub Actions CI/CD |
| URL | [crohncare.online](https://crohncare.online) |

---

## Executar localmente

```bash
# Backend
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve

# Frontend
cd frontend
npm install
npm run dev
```

---

## Deploy

O deploy é automático via GitHub Actions a cada push na branch `main`. O workflow conecta ao VPS via SSH e executa o script de build e restart.

---

<div align="center">
  <sub>Desenvolvido por <strong>Vitor Sandrin</strong></sub>
</div>
