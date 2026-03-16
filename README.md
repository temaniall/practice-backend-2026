# Преддипломная практика. 

Проект: API сервиса опросов и голосований (Survey API)

---

## Список эндпоинтов

Блок: Авторизация (Auth)

| метод | URL           | Описание                                             |
| ----- | ------------- | ---------------------------------------------------- |
| POST  | /api/register | Регистрация нового пользователя (имя, email, пароль) |
| POST  | /api/login    | Вход. Сервер проверяет пароль и выдает JWT токен.    |

Блок: Управление опросами (Для Авторов)

| метод | URL                         | Описание                                            |
| ----- | --------------------------- | --------------------------------------------------- |
| GET   | /api/my-surveys             | Показать все опросы, которые создал текущий юзер.   |
| POST  | /api/surveys                | Создать пустой опрос (название, описание).          |
| POST  | /api/surveys/{id}/questions | Добавить вопрос к опросу №{id}.                     |
| PATCH | /api/surveys/{id}/status    | Смена статуса (draft, published, closed).           |

Блок: Прохождение и Статистика (Для Респондентов и Авторов)

| метод | URL                         | Описание                                                             |
| ----- | --------------------------- | -------------------------------------------------------------------- |
| GET   | /api/surveys                | Показать список всех активных (опубликованных) опросов.              |
| GET   | /api/surveys/{id}/take      | Получить опрос для прохождения (только опубликованные).              |
| POST  | /api/surveys/{id}/answers   | Отправить ответы на опрос.                                           |
| GET   | /api/surveys/{id}/stats     | Показать результаты (доступно только автору).                        |
| PUT   | /api/questions/{id}.        | Редактирование вопроса (только в статусе draft).                     |


---

## Стек

- **PHP** — Laravel

База данных: MySQL.

---

## ER диаграмма базы данных

Как работает твоя БД:

Кто создал? (users → surveys)
Юзер создает запись в surveys. В поле user_id записывается его ID. Это связь «Один ко многим»: один юзер может создать много опросов.

Что внутри опроса? (surveys → questions → question_options)
В таблице questions лежат сами вопросы («Как вам наш сервис?», «Ваш пол?»). Если вопрос подразумевает выбор (одиночный или множественный), то варианты («Хорошо/Плохо», «М/Ж») лежат в question_options.

Кто и когда зашел? (responses)
Это ключевая таблица. Когда респондент нажимает кнопку «Начать опрос», создается запись в responses. Она фиксирует: «Юзер Вася начал Опрос №5 в 12:00».

Зачем это нужно? Чтобы выполнить требование ТЗ: "Один респондент может пройти один опрос только один раз". Перед началом мы просто проверяем: есть ли уже в этой таблице пара user_id + survey_id.

Что именно ответил? (responses → answers)
Когда Вася отправляет опрос, в таблицу answers сыплются его ответы. Каждая строчка ссылается на responses_id (конкретную сессию Васи).

Если вопрос с выбором — заполняется option_id.

Если вопрос текстовый — заполняется text_answer.

Table users {
  id integer [primary key]
  name varchar
  email varchar
  password varchar
  role varchar [default: 'user']
  created_at timestamp
}

Table surveys {
  id integer [primary key]
  user_id integer [ref: > users.id]
  title varchar
  description text
  status varchar
  created_at timestamp
}

Table questions {
  id integer [primary key]
  survey_id integer [ref: > surveys.id]
  content text
  type varchar
  order integer
}

Table question_options {
  id integer [primary key]
  question_id integer [ref: > questions.id]
  option_text varchar
}

Table responses {
  id integer [primary key]
  survey_id integer [ref: > surveys.id]
  user_id integer [ref: > users.id]
  completed_at timestamp
}

Table answers {
  id integer [primary key]
  response_id integer [ref: > responses.id]
  question_id integer [ref: > questions.id]
  option_id integer [ref: > question_options.id, null]
  text_answer text [null]
}

- **dbdiagram.io** https://dbdiagram.io

![Database Schema](docs/er-diagram.png)

## Структура проекта
* `src/` — исходный код Laravel (Backend).
* `docker-compose.yml` — конфигурация сервисов Docker.
* `Dockerfile` — инструкция по сборке PHP 8.4 окружения.

## Быстрый запуск

### 1. Клонирование репозитория
Откройте терминал и выполните:
```bash
git clone [https://github.com/temaniall/practice-backend-2026.git](https://github.com/temaniall/practice-backend-2026.git)
cd practice-backend-2026
```

### 2. Настройка окружения (.env)
Необходимо подготовить файл конфигурации внутри папки с кодом:
```bash
cp src/.env.example src/.env
```

Важно: Откройте src/.env и убедитесь, что параметры БД настроены на работу внутри сети Docker (вместо 127.0.0.1 используем db):

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=survey_db
DB_USERNAME=root
DB_PASSWORD=root

APP_URL=http://localhost:8000

### 3. Запуск контейнеров
Запустите сборку и старт сервисов (PHP 8.4, MySQL 8.0, phpMyAdmin):
```bash
open -a Docker
docker-compose up -d --build
docker-compose up -d
```

### 4. Настройка приложения внутри контейнера
Выполните команды для установки зависимостей и генерации ключей безопасности:
```bash
docker exec -it survey_app composer install
docker exec -it survey_app php artisan key:generate
docker exec -it survey_app php artisan jwt:secret
```

### 5. Миграции и тестовые данные
Создайте структуру таблиц и наполните базу тестовыми данными (админ-аккаунт и примеры опросов):
```bash
docker exec -it survey_app php artisan migrate:fresh --seed
```

---

## Доступ к сервисам

| Сервис      | Адрес                                   | Описание                                          |
| ----------- | --------------------------------------- | ------------------------------------------------- |
| Swagger UI  | http://localhost:8000/api/documentation | Документация и тестирование API                   |
| phpMyAdmin  | http://localhost:8080                   | Визуальное управление БД (Server: db, Pass: root) |
| API Base URL| http://localhost:8000/api               | Эндпоинт для подключения фронтенда                |

---
