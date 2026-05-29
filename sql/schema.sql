
CREATE DATABASE IF NOT EXISTS food_recipes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE food_recipes;

CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(180) NOT NULL UNIQUE,
    email         VARCHAR(180) NOT NULL UNIQUE,
    password      VARCHAR(255) NOT NULL,
    roles         JSON         NOT NULL DEFAULT ('["ROLE_USER"]'),
    is_verified   TINYINT(1)   NOT NULL DEFAULT 0,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS cuisines (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    flag_emoji  VARCHAR(10)  NOT NULL DEFAULT ''
);

CREATE TABLE IF NOT EXISTS recipes (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(255)  NOT NULL,
    description     TEXT,
    meal_type       VARCHAR(50)   NOT NULL,
    difficulty      VARCHAR(50)   NOT NULL,
    prep_time       INT           NOT NULL DEFAULT 0,
    cook_time       INT           NOT NULL DEFAULT 0,
    servings        INT           NOT NULL DEFAULT 1,
    instructions    LONGTEXT,
    image_filename  VARCHAR(255),
    created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    cuisine_id      INT           NOT NULL,
    author_id       INT           NOT NULL,
    FOREIGN KEY (cuisine_id) REFERENCES cuisines(id) ON DELETE RESTRICT,
    FOREIGN KEY (author_id)  REFERENCES users(id)    ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS ingredients (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS recipe_ingredients (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id       INT          NOT NULL,
    ingredient_id   INT          NOT NULL,
    ingredient_name VARCHAR(150) NOT NULL,
    quantity        VARCHAR(100),
    FOREIGN KEY (recipe_id)     REFERENCES recipes(id)     ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS favorites (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    recipe_id  INT NOT NULL,
    added_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_recipe (user_id, recipe_id),
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
);

INSERT INTO cuisines (name, flag_emoji) VALUES
('Italian',  '🇮🇹'), ('French',   '🇫🇷'), ('Japanese', '🇯🇵'),
('Mexican',  '🇲🇽'), ('Indian',   '🇮🇳'), ('American', '🇺🇸'),
('Chinese',  '🇨🇳'), ('Tunisian', '🇹🇳'), ('Lebanese', '🇱🇧'), ('Spanish',  '🇪🇸');

INSERT INTO users (username, email, password, roles, is_verified) VALUES (
    'admin',
    'admin@letmecook.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    '["ROLE_USER","ROLE_ADMIN"]',
    1
);

