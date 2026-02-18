# Fakestore Products (WordPress плагін)

> **Реалізація тестового завдання (PHP 8)**  
> Інтеграція з публічним API: `https://fakestoreapi.com/products/{id}`

---

## Постановка задачі

### Варіант 1.1 (базовий)

1. Створити **сторінку налаштувань**, де вказується:
    - **ID продукту** (текстове/числове поле)

2. **Шорткод №1**
    - Береться ID із налаштувань плагіна
    - Виконується запит до API: `https://fakestoreapi.com/products/{id}`
    - Виводяться дані продукту (картка)

3. **Шорткод №2**
    - Виводиться кнопка
    - Після натискання через API отримується **випадковий товар**
    - Дані відображаються **без перезавантаження сторінки** (AJAX)  
      (у тестовому сервісі API всього 20 продуктів)

### Варіант 2 (ускладнений)

Взяти Варіант **1.1** і доповнити:

1. **Custom Post Type (CPT)**
    - Структуру полів визначити самостійно під обраний варіант

2. **Збереження даних**
    - Під час виконання **Шорткоду №2**:
        - створити новий запис CPT
        - зберегти отримані з API дані у відповідні поля
        - у картці додати посилання на створений запис

3. **Покращення сторінки налаштувань**
    - Відображати дату та час **останнього успішного** створення/оновлення запису
    - Додати перемикач режимів:
        - **Простий**: запит + збереження одразу
        - **Продуманий/Async**: користувач на фронті отримує відповідь одразу, а збереження - у фоні  
          (за потреби через WP-CLI + повідомлення на email)

---

## Опис плагіна

### Основні можливості

- **Сторінка налаштувань** (WordPress Settings API)
    - Поле ID продукту (для Шорткоду №1)
    - Перемикач режиму: `simple` / `async`
    - (Опційно) Email для повідомлень в async режимі
    - Вивід **Last successful saved at** (час останнього успішного збереження)
    - Інфоблок із **URL фронтенд-сторінки** (демо-сторінка з шорткодами, створюється автоматично)

- **Шорткоди**
    - `[fakestore_product]` - показує продукт за ID із налаштувань
    - `[fakestore_random]` - кнопка, яка завантажує випадковий продукт через AJAX без перезавантаження

- **Інтеграція з API**
    - Використовується **WP HTTP API** (`wp_remote_get`)
    - Перевірка статус-коду та JSON-відповіді

- **Custom Post Type**
    - `fakestore_product` - збережені продукти з API
    - **Унікальність по API id** (`_api_id`):
        - якщо продукт із таким API id вже існує - запис **оновлюється** (без дублікатів)
    - Мета-поля зареєстровані з `show_in_rest => true`
    - В адмінці є **Meta Box** з полями (захист nonce + capability)

- **Категорії для CPT**
    - Окрема таксономія `fakestore_category`
    - При створенні/оновленні запису:
        - якщо категорія (term) існує за назвою > призначити
        - якщо не існує > створити і призначити
    - Терміни, створені плагіном, маркуються для коректного видалення при uninstall

- **Безпека**
    - Санітизація опцій через `sanitize_callback`
    - AJAX захищено **nonce** (`check_ajax_referer`)
    - Збереження Meta Box - nonce + `current_user_can`
    - Жодні дані користувача не передаються у shell-команди

---

## Вимоги

- WordPress 6.x (рекомендовано)
- PHP **8.0+**
- Увімкнені пермалінки (рекомендовано для зручних URL CPT)

---

## Встановлення

1. Скопіюйте папку плагіна в:
    - `wp-content/plugins/fakestore-products/`

2. Активуйте плагін у:
    - **WP Admin > Plugins**

3. Відкрийте налаштування:
    - **WP Admin > Settings > Fakestore Products**

> Під час активації плагін також створює демо-сторінку (типово: **Fakestore Demo**) з обома шорткодами.

---

## Сторінка налаштувань

Шлях: **WP Admin > Settings > Fakestore Products**

Параметри:

- **Product ID**
    - ціле число (1..20)

- **Mode**
    - `Simple`: збереження CPT одразу під час кліку
    - `Async`: додавання у чергу, обробка у фоні

- **Notify Email (опційно)**
    - використовується лише в async режимі, коли фонова задача завершиться

Інформаційні блоки:

- **Last successful product saved at**
- **Frontend demo page URL**
- URL архіву CPT

---

## Шорткоди

### 1) Продукт за ID (із налаштувань)

```text
[fakestore_product]
```

- Читає ID із налаштувань
- Викликає API: `https://fakestoreapi.com/products/{id}`
- Рендерить картку продукту

### 2) Випадковий продукт (AJAX)

```text
[fakestore_random]
```

- Показує кнопку
- Клік викликає AJAX:
    - отримує випадковий продукт (1..20)
    - повертає HTML без перезавантаження
- У **simple** режимі: також створює/оновлює CPT і повертає посилання на запис

---

## Custom Post Type та поля

### CPT

- **post type**: `fakestore_product`
- Public: так
- Has archive: так

### Таксономія

- **taxonomy**: `fakestore_category` (ієрархічна)
- Автопризначення з API поля `category`

### Meta поля (post meta)

Зберігаються такі значення:

- `_api_id` (int) - унікальний ID продукту в API
- `_price` (float)
- `_category` (string)
- `_image` (string URL)
- `_rating_rate` (float)
- `_rating_count` (int)

В адмінці - Meta Box для перегляду/редагування (з nonce).  
Примітка: `_api_id` типово **read-only**.

---

## Async режим: черга + фонова обробка

В async режимі плагін:

1. Додає продукт у чергу:
    - option key: `fakestore_products_queue`

2. Планує одиничну подію WP-Cron:
    - hook: `fakestore_products_process_queue`

3. Процесор черги:
    - бере 1 елемент
    - створює/оновлює CPT
    - оновлює `Last successful ...`
    - за потреби надсилає email

### Запуск воркера через WP-CLI (вручну)

Якщо у вашому середовищі доступний WP-CLI:

```bash
wp fakestore process_queue --allow-root --path=/var/www/html --quiet
```

Можна запускати регулярно через системний cron або окремий worker-контейнер.

---

## Очищення при видаленні (Uninstall)

Плагін має `uninstall.php`, який:

- видаляє опції:
    - `fakestore_products_options`
    - `fakestore_products_last_created_at`
    - `fakestore_products_page_id`
    - `fakestore_products_queue`
- видаляє авто-створену демо-сторінку (якщо є)
- видаляє всі записи CPT `fakestore_product`
- видаляє терміни таксономії, створені плагіном (за маркером term meta)

---

## Структура проєкту

```text
.
├── assets
│   ├── css
│   │   └── front.css
│   └── js
│       └── front.js
├── fakestore-products.php
├── readme.md
├── src
│   ├── Admin
│   │   └── SettingsPage.php
│   ├── Cli
│   │   └── Commands.php
│   ├── Core
│   │   ├── ApiClient.php
│   │   ├── Autoloader.php
│   │   ├── Options.php
│   │   └── View.php
│   ├── Front
│   │   ├── Ajax.php
│   │   └── Shortcodes.php
│   ├── Plugin.php
│   └── PostType
│       ├── ProductCPT.php
│       └── ProductMeta.php
├── templates
│   ├── admin
│   │   ├── metabox-product.php
│   │   └── settings.php
│   └── front
│       └── product-card.php
└── uninstall.php
```

---