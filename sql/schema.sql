-- Franca Dining & Coffee - Database schema + seed data
-- Import via: mysql -u root franca < schema.sql   (or phpMyAdmin Import)

CREATE DATABASE IF NOT EXISTS franca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE franca;

-- ---------------------------------------------------------------
-- Sessions (database-backed session storage). Required on serverless
-- hosts like Vercel where the local filesystem isn't guaranteed to
-- persist between function invocations -- see includes/session_handler.php.
-- ---------------------------------------------------------------
CREATE TABLE sessions (
  id VARCHAR(128) NOT NULL PRIMARY KEY,
  data MEDIUMTEXT NOT NULL,
  last_activity INT UNSIGNED NOT NULL,
  KEY idx_last_activity (last_activity)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Users (customers + admins share one table, distinguished by role)
-- ---------------------------------------------------------------
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(40) NULL,
  role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Menu categories (Coffee, Brunch, Lunch, Bakery ...)
-- ---------------------------------------------------------------
CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  name_en VARCHAR(80) NULL,
  slug VARCHAR(80) NOT NULL UNIQUE,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Menu items
-- ---------------------------------------------------------------
CREATE TABLE menu_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NOT NULL,
  name VARCHAR(150) NOT NULL,
  name_en VARCHAR(150) NULL,
  slug VARCHAR(150) NOT NULL UNIQUE,
  description TEXT NULL,
  description_en TEXT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  image VARCHAR(255) NULL,
  badge VARCHAR(60) NULL,
  badge_en VARCHAR(60) NULL,
  ingredients VARCHAR(500) NULL,
  ingredients_en VARCHAR(500) NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Reservations (table bookings)
-- ---------------------------------------------------------------
CREATE TABLE reservations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40) NULL,
  party_size TINYINT UNSIGNED NOT NULL DEFAULT 2,
  reservation_date DATE NOT NULL,
  reservation_time TIME NOT NULL,
  special_requests VARCHAR(500) NULL,
  status ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Orders / Bag (dine-in, takeaway, delivery)
-- ---------------------------------------------------------------
-- payment_status is intentionally separate from status (kitchen workflow).
-- payment_status is only ever set to 'paid' by webhook/mercadopago.php after
-- independently re-verifying the payment with MercadoPago's API — see that
-- file's header comment for the full trust model.
CREATE TABLE orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  guest_name VARCHAR(120) NULL,
  guest_phone VARCHAR(40) NULL,
  service_mode ENUM('dine-in','takeaway','delivery') NOT NULL DEFAULT 'dine-in',
  status ENUM('pending','preparing','ready','completed','cancelled') NOT NULL DEFAULT 'pending',
  payment_status ENUM('pending','paid','failed','cancelled') NOT NULL DEFAULT 'pending',
  payment_provider VARCHAR(30) NULL,
  payment_reference VARCHAR(120) NULL,
  payment_preference_id VARCHAR(120) NULL,
  payment_amount DECIMAL(10,2) NULL,
  payment_confirmed_at TIMESTAMP NULL,
  total DECIMAL(10,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  menu_item_id INT UNSIGNED NOT NULL,
  quantity SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  unit_price DECIMAL(10,2) NOT NULL,
  notes VARCHAR(255) NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
) ENGINE=InnoDB;

-- Tamper-evident log of every inbound MercadoPago webhook call, whether or
-- not it resulted in a state change. See webhook/mercadopago.php.
CREATE TABLE payment_events (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NULL,
  provider VARCHAR(30) NOT NULL DEFAULT 'mercadopago',
  payment_id VARCHAR(120) NULL,
  raw_payload TEXT NULL,
  verified_status VARCHAR(30) NULL,
  verified_amount DECIMAL(10,2) NULL,
  outcome VARCHAR(60) NULL,
  processed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Newsletter subscribers
-- ---------------------------------------------------------------
CREATE TABLE newsletter_subscribers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  subscribed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Site settings (key/value, editable from admin)
-- ---------------------------------------------------------------
CREATE TABLE settings (
  `key` VARCHAR(80) PRIMARY KEY,
  `value` TEXT NULL
) ENGINE=InnoDB;

INSERT INTO settings (`key`, `value`) VALUES
('site_name', 'Franca Dining & Coffee'),
('address', 'Plaza Cagancha 1124, Montevideo, Uruguay'),
('phone', '+598 2902 1124'),
('email', 'Cafe@franca.uy'),
('hours_mon_fri', '07:30-19:30'),
('hours_sat', '09:00-16:00'),
('hours_sun', 'closed'),
('instagram', '@franca'),
('map_lat', '-34.906540803107035'),
('map_lng', '-56.19178169498318'),
('menu_notes_en', 'Our kitchen relies on organic and local products as much as possible. Our gluten-free products are not suitable for people with celiac disease due to cross-contamination. Glass bottles are Franca''s property — please leave them at the venue. Thank you so much!');

-- ---------------------------------------------------------------
-- Seed: admin account (email: admin@franca.uy / password: FrancaAdmin123!)
-- ---------------------------------------------------------------
INSERT INTO users (name, email, password_hash, role) VALUES
('Franca Admin', 'admin@franca.uy', '$2y$10$HjGuUxIMUQocInzUELqePOYmryb3afQTzW06pVdah1UppE9IOG2rC', 'admin');

-- ---------------------------------------------------------------
-- Seed: categories (matching the physical Franca menu sections)
-- ---------------------------------------------------------------
INSERT INTO categories (id, name, slug, sort_order) VALUES
(1, 'Café', 'cafe', 1),
(2, 'Mañanas y Tardes', 'mananas-y-tardes', 2),
(3, 'Mediodías', 'mediodias', 3),
(4, 'Bebidas', 'bebidas', 4),
(5, 'Almacén', 'almacen', 5),
(6, 'Pedidos Especiales', 'pedidos-especiales', 6);

-- ---------------------------------------------------------------
-- Seed: menu items (transcribed from the real Franca menu, prices in UYU)
-- ---------------------------------------------------------------
-- Café
INSERT INTO menu_items (category_id, name, slug, description, price, image, badge, ingredients, is_featured, sort_order) VALUES
(1, 'Espresso', 'espresso', 'Café espresso clásico.', 130, NULL, NULL, 'Espresso', 0, 1),
(1, 'Espresso Doble', 'espresso-doble', 'Doble shot de espresso.', 150, NULL, NULL, 'Espresso', 0, 2),
(1, 'Extra Shot', 'extra-shot', 'Shot adicional de espresso para sumar a cualquier café.', 30, NULL, NULL, 'Espresso', 0, 3),
(1, 'Long Black', 'long-black', 'Doble espresso + agua caliente, 180ml.', 170, NULL, NULL, 'Espresso, Agua caliente', 0, 4),
(1, 'Americano', 'americano', 'Doble espresso + agua caliente, 350ml.', 190, NULL, NULL, 'Espresso, Agua caliente', 0, 5),
(1, 'Americano XL', 'americano-xl', 'Triple espresso + agua caliente, 500ml.', 230, NULL, NULL, 'Espresso, Agua caliente', 0, 6),
(1, 'Filtrado', 'filtrado', 'Origen del día, consultar. Métodos disponibles: V60, prensa francesa o Clever dripper. Varios tamaños, de $100 a $160.', 100, 'v60-coffee.jpg', NULL, 'Café de origen', 0, 7),
(1, 'Espresso Especial', 'espresso-especial', 'Origen especial, consultar al barista. Suplemento de $40 sobre el café base.', 40, NULL, NULL, 'Espresso de origen', 0, 8),
(1, 'Macchiato', 'macchiato', 'Doble espresso + espuma de leche, 100ml.', 150, 'gallery-latte-art.jpg', NULL, 'Espresso, Espuma de leche', 0, 9),
(1, 'Cortado', 'cortado', 'Espresso + leche, 100ml o 160ml. De $150 a $160.', 150, 'gallery-latte-art.jpg', NULL, 'Espresso, Leche', 0, 10),
(1, 'Flat White', 'flat-white', 'Doble espresso + leche, 180ml.', 180, 'gallery-latte-art.jpg', NULL, 'Espresso, Leche', 1, 11),
(1, 'Cappuccino', 'cappuccino', 'Doble espresso + leche, 240ml.', 190, 'gallery-latte-art.jpg', NULL, 'Espresso, Leche', 0, 12),
(1, 'Latte', 'latte', 'Espresso + leche, 350ml.', 190, 'gallery-latte-art.jpg', NULL, 'Espresso, Leche', 1, 13),
(1, 'Latte Doble Shot', 'latte-doble-shot', 'Doble espresso + leche, 350ml.', 220, 'gallery-latte-art.jpg', NULL, 'Espresso, Leche', 0, 14),
(1, 'Mocca', 'mocca', 'Doble espresso + chocolate belga + leche, 350ml.', 240, 'gallery-latte-art.jpg', NULL, 'Espresso, Chocolate belga, Leche', 0, 15),
(1, 'Latte XL', 'latte-xl', 'Doble espresso + leche, 500ml.', 250, 'gallery-latte-art.jpg', NULL, 'Espresso, Leche', 0, 16),
(1, 'Mocca XL', 'mocca-xl', 'Triple espresso + chocolate belga + leche, 500ml.', 310, 'gallery-latte-art.jpg', NULL, 'Espresso, Chocolate belga, Leche', 0, 17),
(1, 'Iced Americano', 'iced-americano', 'Café americano servido con hielo.', 190, 'cold-brew.jpg', NULL, 'Espresso, Agua, Hielo', 0, 18),
(1, 'Iced Flat White', 'iced-flat-white', 'Flat white servido con hielo.', 190, 'cold-brew.jpg', NULL, 'Espresso, Leche, Hielo', 0, 19),
(1, 'Iced Latte', 'iced-latte', 'Latte servido con hielo.', 200, 'cold-brew.jpg', NULL, 'Espresso, Leche, Hielo', 0, 20),
(1, 'Iced Cappuccino', 'iced-cappuccino', 'Cappuccino servido con hielo.', 210, 'cold-brew.jpg', NULL, 'Espresso, Leche, Hielo', 0, 21),
(1, 'Iced Mocca', 'iced-mocca', 'Mocca servido con hielo.', 240, 'cold-brew.jpg', NULL, 'Espresso, Chocolate belga, Leche, Hielo', 0, 22),
(1, 'Espresso Tonic', 'espresso-tonic', 'Espresso doble + tónica artesanal.', 210, 'cold-brew.jpg', NULL, 'Espresso, Tónica artesanal', 0, 23),
(1, 'Cold Brew', 'cold-brew', 'Café infusionado en frío.', 240, 'cold-brew.jpg', NULL, 'Café infusionado en frío', 1, 24),
(1, 'Refresco Franca', 'refresco-franca', 'Soda, cold brew y almíbar de membrillo.', 240, 'cold-brew.jpg', NULL, 'Soda, Cold brew, Almíbar de membrillo', 0, 25),
(1, 'Chocolate Belga Caliente', 'chocolate-belga-caliente', 'Chocolate belga caliente. De $190 a $240 según tamaño.', 190, NULL, NULL, 'Chocolate belga, Leche', 0, 26),
(1, 'Matcha', 'matcha', 'Té matcha con leche.', 240, 'gallery-coworking.jpg', NULL, 'Matcha, Leche', 0, 27),
(1, 'Té de Cáscara de Café', 'te-cascara-de-cafe', 'Infusión de cáscara de café (cascara tea).', 150, NULL, NULL, 'Cáscara de café', 0, 28),
(1, 'Té de Cedrón', 'te-de-cedron', 'Infusión de cedrón.', 140, NULL, NULL, 'Cedrón', 0, 29),
(1, 'Té Negro', 'te-negro', 'Té negro.', 140, NULL, NULL, 'Té negro', 0, 30),
(1, 'Té Verde', 'te-verde', 'Té verde.', 140, NULL, NULL, 'Té verde', 0, 31),
(1, 'Affogato', 'affogato', 'Helado de vainilla con 1 shot de espresso.', 240, NULL, NULL, 'Helado de vainilla, Espresso', 0, 32);

-- Mañanas y Tardes (07:30-11:30hs y 15:30-19:15hs)
INSERT INTO menu_items (category_id, name, slug, description, price, image, badge, ingredients, is_featured, sort_order) VALUES
(2, 'Pan de Campo | Dulce | Queso', 'pan-de-campo-dulce-queso', 'Tostada de pan de campo de masa madre, mermelada casera, manteca o queso crema.', 225, NULL, NULL, 'Pan de campo, Mermelada casera, Manteca o queso crema', 0, 1),
(2, 'Yogurt | Fruta | Granola', 'yogurt-fruta-granola', 'Yogurt orgánico sin azúcar, fruta fresca de estación, granola casera sin gluten y sin azúcar, agave. Adicional manteca de maní +$120.', 320, NULL, 'Veganizable', 'Yogurt orgánico, Fruta de estación, Granola casera, Agave', 0, 2),
(2, 'Panqueques', 'panqueques', 'Panqueques de avena sin harina de trigo, yogurt o miel, manteca de maní, dulce casero de frutos rojos, crocante de granola. Opción vegana con cremoso de cajú.', 340, 'menu-pancakes.jpg', 'Sin harina de trigo', 'Avena, Yogurt o miel, Manteca de maní, Dulce de frutos rojos, Granola', 1, 3),
(2, 'Medialuna Rellena', 'medialuna-rellena', 'De jamón y queso.', 240, NULL, NULL, 'Medialuna, Jamón, Queso', 0, 4),
(2, 'Prensado de Jamón y Queso', 'prensado-jamon-y-queso', 'Pan de leche, jamón artesanal y quesos varios.', 310, 'tostado-jamon-queso.jpg', NULL, 'Pan de leche, Jamón artesanal, Quesos varios', 0, 5),
(2, 'Pan de Campo | Hummus | Tempeh', 'pan-de-campo-hummus-tempeh', 'Pan de campo, hummus de remolacha, tempeh orgánico sellado, vegetales asados.', 320, NULL, 'Vegano', 'Pan de campo, Hummus de remolacha, Tempeh, Vegetales asados', 0, 6),
(2, 'English Muffin', 'english-muffin', 'Relleno de queso provolone, jamón artesanal, huevo a la plancha y panceta.', 360, 'menu-eggsbenedict.jpg', NULL, 'Queso provolone, Jamón artesanal, Huevo, Panceta', 0, 7),
(2, 'Pan de Campo | Huevo Revuelto | Verdes', 'pan-de-campo-huevo-revuelto-verdes', 'Pan de campo de masa madre, huevo revuelto cremoso, hojitas frescas. Adicional opcional jamón crudo +$130.', 360, NULL, NULL, 'Pan de campo, Huevo revuelto, Hojas verdes', 0, 8),
(2, 'Panqueques | Bacon | Miel | Manteca', 'panqueques-bacon-miel-manteca', 'Panqueques de avena sin harina de trigo, miel picantona, manteca cultivada con miso y panceta crocante.', 360, 'menu-pancakes.jpg', 'Sin harina de trigo', 'Avena, Miel picantona, Manteca cultivada, Panceta crocante', 0, 9),
(2, 'Pan de Campo | Palta | Huevo Mollet', 'pan-de-campo-palta-huevo-mollet', 'Pan de campo de masa madre, palta con limón, huevo molet, brotes. Opcional vegano con tempeh orgánico sellado.', 380, 'avocado-toast-detail.jpg', 'Sin harina de trigo', 'Pan de campo, Palta, Limón, Huevo molet, Brotes', 1, 10),
(2, 'Sándwich de Chipá', 'sandwich-de-chipa', 'Chipá, queso crema, tomates asados, pesto casero.', 390, 'chipa.jpg', 'Sin harina de trigo', 'Chipá, Queso crema, Tomates asados, Pesto casero', 1, 11),
(2, 'Olímpico', 'olimpico', 'Pan lactal, jamón, queso, huevo duro, mostaza, tomate y lechuga. Opción vegetariana sin jamón, $350.', 430, NULL, NULL, 'Pan lactal, Jamón, Queso, Huevo duro, Mostaza, Tomate, Lechuga', 0, 12);

-- Mediodías (mostrador de lunes a viernes desde 12hs; platos de carta de lunes a sábados desde 11:30hs)
INSERT INTO menu_items (category_id, name, slug, description, price, image, badge, ingredients, is_featured, sort_order) VALUES
(3, 'Plato Común', 'plato-comun', '1 proteína + 2 ensaladas. Elegí tu proteína: milanesa de cerdo (opción veggie: seitán), pollo tikka (opción vegana: tempeh), roast beef, soufflé de kale, hongos, cebolla caramelizada y queso parmesano, o churrasquito de seitán.', 430, NULL, NULL, 'Proteína a elección, 2 ensaladas', 0, 1),
(3, 'Plato Grande', 'plato-grande', '1 proteína + 3 ensaladas. Mismas opciones de proteína que el Plato Común.', 590, NULL, NULL, 'Proteína a elección, 3 ensaladas', 0, 2),
(3, 'Menú Completo', 'menu-completo', 'Plato común + bebida + postre o café.', 640, NULL, NULL, 'Plato común, Bebida, Postre o café', 0, 3),
(3, 'Menú + Sopa', 'menu-mas-sopa', 'Menú completo + sopa de la semana.', 780, NULL, NULL, 'Plato común, Bebida, Postre o café, Sopa de la semana', 0, 4),
(3, 'Tarta de la Semana', 'tarta-de-la-semana', '1 porción triangular. Consultá el relleno del día. Con guarnición: $340.', 210, NULL, NULL, 'Masa casera, Relleno de estación', 0, 5),
(3, 'Clásico Franca', 'clasico-franca', 'Focaccia, alioli, queso gruyere, huevo a la plancha, remolacha encurtida, chips de boniato casero, tahini de verdes. Con guarnición: $550.', 420, 'focaccia-rellena.jpg', NULL, 'Focaccia, Alioli, Queso gruyere, Huevo, Remolacha encurtida, Chips de boniato', 1, 6),
(3, 'Milanesa al Pan', 'milanesa-al-pan', 'Pan focaccia, milanesa de cerdo, alioli, huevo duro, lechuga y tomate. Opción vegetariana mila de seitán. Con guarnición: $570.', 440, NULL, NULL, 'Focaccia, Milanesa de cerdo, Alioli, Huevo duro, Lechuga, Tomate', 0, 7),
(3, 'Chivito Franca', 'chivito-franca', 'Pan de leche, roast beef, queso emmental, huevo a la plancha, jamón artesanal, mostaza, tomate, mix de verdes y alioli. Opción vegetariana con seitán casero y panceta vegana +$430. Con guarnición: $590.', 460, NULL, 'Más Pedido', 'Pan de leche, Roast beef, Queso emmental, Huevo, Jamón artesanal, Mostaza, Verdes', 1, 8),
(3, 'Guarnición', 'guarnicion', 'Papas y boniatos fritos o ensaladita trozeada, para sumar a cualquier plato de carta.', 190, NULL, NULL, 'Papas y boniatos fritos, o ensalada', 0, 9);

-- Bebidas (las botellas de vidrio son propiedad de Franca, por favor dejarlas en el local)
INSERT INTO menu_items (category_id, name, slug, description, price, image, badge, ingredients, is_featured, sort_order) VALUES
(4, 'Jugo de Naranja', 'jugo-de-naranja', 'Jugo de naranja natural. 330ml / 500ml, de $180 a $240.', 180, NULL, NULL, 'Naranja', 0, 1),
(4, 'Limonada con Jengibre', 'limonada-con-jengibre', '330ml / 500ml, de $150 a $210.', 150, NULL, NULL, 'Limón, Jengibre', 0, 2),
(4, 'Agua de Jamaica', 'agua-de-jamaica', '330ml / 500ml, de $150 a $210.', 150, NULL, NULL, 'Flor de Jamaica', 0, 3),
(4, 'Té Frío', 'te-frio', 'Con notas a durazno, miel y limón.', 140, NULL, NULL, 'Té, Durazno, Miel, Limón', 0, 4),
(4, 'Jugo de Zanahoria y Naranja', 'jugo-de-zanahoria-y-naranja', '330ml / 500ml, de $180 a $240.', 180, NULL, NULL, 'Zanahoria, Naranja', 0, 5),
(4, 'Jugo Detox Verde', 'jugo-detox-verde', '330ml / 500ml, de $210 a $280.', 210, NULL, NULL, 'Vegetales verdes de estación', 0, 6),
(4, 'Kombucha Bendita', 'kombucha-bendita', '250ml.', 180, NULL, NULL, 'Kombucha artesanal', 0, 7),
(4, 'Tónica Artesanal Max Graff', 'tonica-artesanal-max-graff', '275ml.', 200, NULL, NULL, 'Tónica artesanal', 0, 8),
(4, 'Ginger Ale Mansa', 'ginger-ale-mansa', '350ml.', 170, NULL, NULL, 'Ginger ale', 0, 9),
(4, 'Agua Salus', 'agua-salus', 'Con gas o sin gas.', 130, NULL, NULL, 'Agua mineral', 0, 10),
(4, 'Hue Light Lager', 'hue-light-lager', 'Cerveza Malafama.', 190, NULL, NULL, 'Cerveza lager', 0, 11),
(4, 'Hue Pilsen Lager', 'hue-pilsen-lager', 'Cerveza Malafama.', 230, NULL, NULL, 'Cerveza pilsen', 0, 12),
(4, 'IPA Alboroto', 'ipa-alboroto', 'Cerveza Malafama.', 250, NULL, NULL, 'Cerveza IPA', 0, 13),
(4, 'IPA Tas Loco', 'ipa-tas-loco', 'Cerveza Malafama.', 250, NULL, NULL, 'Cerveza IPA', 0, 14),
(4, 'Vermouth Rooster (Vaso)', 'vermouth-rooster-vaso', 'Vaso de rosso o rosado.', 170, NULL, NULL, 'Vermouth', 0, 15),
(4, 'Vermouth Rooster (Botella)', 'vermouth-rooster-botella', 'Botella de rosso o rosado.', 580, NULL, NULL, 'Vermouth', 0, 16);

-- Almacén
INSERT INTO menu_items (category_id, name, slug, description, price, image, badge, ingredients, is_featured, sort_order) VALUES
(5, 'Focaccia con Gustos', 'focaccia-con-gustos', 'Pan de la casa para llevar.', 260, 'focaccia-rellena.jpg', NULL, 'Focaccia casera', 0, 1),
(5, 'Pan de Campo Masa Madre', 'pan-de-campo-masa-madre', 'Pan de la casa para llevar.', 240, NULL, NULL, 'Pan de campo, Masa madre', 0, 2),
(5, 'Pan de Campo Individual', 'pan-de-campo-individual', 'Pan de la casa para llevar.', 120, NULL, NULL, 'Pan de campo', 0, 3),
(5, 'Café Seis Montes', 'cafe-seis-montes', 'Origen del mes, 250gr. Consultar precio en caja.', 0, NULL, NULL, 'Café de origen, 250gr', 0, 4);

-- Pedidos Especiales (¡Hacemos de todo! Escribinos con lo que quieres y lo vemos)
INSERT INTO menu_items (category_id, name, slug, description, price, image, badge, ingredients, is_featured, sort_order) VALUES
(6, 'Torta de Chocolate, Boniato y Trigo Sarraceno', 'torta-chocolate-boniato-trigo-sarraceno', 'Por encargo.', 1290, NULL, NULL, 'Chocolate, Boniato, Trigo sarraceno', 0, 1),
(6, 'Dacquoise de Almendras y DDL', 'dacquoise-de-almendras-y-ddl', 'Torta de cumpleaños. Por encargo.', 1890, NULL, NULL, 'Almendras, Dulce de leche', 0, 2),
(6, 'Budín de Carrot o de Banana', 'budin-de-carrot-o-banana', 'Por encargo.', 590, NULL, NULL, 'Zanahoria o banana', 0, 3);

INSERT INTO settings (`key`, `value`) VALUES
('menu_notes', 'Nuestra cocina se basa en productos orgánicos y locales en la mayor medida posible. Nuestros productos sin gluten no son aptos para celíacos por la contaminación cruzada. Las botellas de vidrio son propiedad de Franca, por favor dejarlas en el local. ¡Muchas gracias!');

-- ---------------------------------------------------------------
-- Seed: sample reservation + order so admin views aren't empty
-- ---------------------------------------------------------------
INSERT INTO reservations (name, email, phone, party_size, reservation_date, reservation_time, special_requests, status) VALUES
('Lucía Fernández', 'lucia@example.com', '+598 99 123 456', 2, CURDATE() + INTERVAL 1 DAY, '11:00:00', 'Window seat if possible', 'pending');

INSERT INTO orders (guest_name, guest_phone, service_mode, status, payment_status, payment_provider, payment_confirmed_at, total) VALUES
('Walk-in Guest', '+598 99 555 111', 'takeaway', 'completed', 'paid', 'manual', NOW(), 590.00);

INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price) VALUES
(1, (SELECT id FROM menu_items WHERE slug='chivito-franca'), 1, 460.00),
(1, (SELECT id FROM menu_items WHERE slug='espresso'), 1, 130.00);


-- ---------------------------------------------------------------
-- ---------------------------------------------------------------
-- English translations (name_en / description_en / badge_en /
-- ingredients_en) for the ES/EN language switcher.
-- ---------------------------------------------------------------
UPDATE categories SET name_en='Coffee' WHERE id=1;
UPDATE categories SET name_en='Mornings & Afternoons' WHERE id=2;
UPDATE categories SET name_en='Midday' WHERE id=3;
UPDATE categories SET name_en='Drinks' WHERE id=4;
UPDATE categories SET name_en='Pantry' WHERE id=5;
UPDATE categories SET name_en='Special Orders' WHERE id=6;

UPDATE menu_items SET name_en='Espresso', description_en='Classic espresso.', badge_en=NULL, ingredients_en='Espresso' WHERE id=1;
UPDATE menu_items SET name_en='Double Espresso', description_en='Double shot of espresso.', badge_en=NULL, ingredients_en='Espresso' WHERE id=2;
UPDATE menu_items SET name_en='Extra Shot', description_en='Extra shot of espresso to add to any coffee.', badge_en=NULL, ingredients_en='Espresso' WHERE id=3;
UPDATE menu_items SET name_en='Long Black', description_en='Double espresso + hot water, 180ml.', badge_en=NULL, ingredients_en='Espresso, Hot water' WHERE id=4;
UPDATE menu_items SET name_en='Americano', description_en='Double espresso + hot water, 350ml.', badge_en=NULL, ingredients_en='Espresso, Hot water' WHERE id=5;
UPDATE menu_items SET name_en='Americano XL', description_en='Triple espresso + hot water, 500ml.', badge_en=NULL, ingredients_en='Espresso, Hot water' WHERE id=6;
UPDATE menu_items SET name_en='Filter Coffee', description_en='Origin of the day, ask your barista. Available methods: V60, French press, or Clever dripper. Various sizes, from $100 to $160.', badge_en=NULL, ingredients_en='Single-origin coffee' WHERE id=7;
UPDATE menu_items SET name_en='Special Espresso', description_en='Special origin, ask the barista. $40 supplement over the base coffee.', badge_en=NULL, ingredients_en='Single-origin espresso' WHERE id=8;
UPDATE menu_items SET name_en='Macchiato', description_en='Double espresso + milk foam, 100ml.', badge_en=NULL, ingredients_en='Espresso, Milk foam' WHERE id=9;
UPDATE menu_items SET name_en='Cortado', description_en='Espresso + milk, 100ml or 160ml. From $150 to $160.', badge_en=NULL, ingredients_en='Espresso, Milk' WHERE id=10;
UPDATE menu_items SET name_en='Flat White', description_en='Double espresso + milk, 180ml.', badge_en=NULL, ingredients_en='Espresso, Milk' WHERE id=11;
UPDATE menu_items SET name_en='Cappuccino', description_en='Double espresso + milk, 240ml.', badge_en=NULL, ingredients_en='Espresso, Milk' WHERE id=12;
UPDATE menu_items SET name_en='Latte', description_en='Espresso + milk, 350ml.', badge_en=NULL, ingredients_en='Espresso, Milk' WHERE id=13;
UPDATE menu_items SET name_en='Double Shot Latte', description_en='Double espresso + milk, 350ml.', badge_en=NULL, ingredients_en='Espresso, Milk' WHERE id=14;
UPDATE menu_items SET name_en='Mocha', description_en='Double espresso + Belgian chocolate + milk, 350ml.', badge_en=NULL, ingredients_en='Espresso, Belgian chocolate, Milk' WHERE id=15;
UPDATE menu_items SET name_en='Latte XL', description_en='Double espresso + milk, 500ml.', badge_en=NULL, ingredients_en='Espresso, Milk' WHERE id=16;
UPDATE menu_items SET name_en='Mocha XL', description_en='Triple espresso + Belgian chocolate + milk, 500ml.', badge_en=NULL, ingredients_en='Espresso, Belgian chocolate, Milk' WHERE id=17;
UPDATE menu_items SET name_en='Iced Americano', description_en='Americano served over ice.', badge_en=NULL, ingredients_en='Espresso, Water, Ice' WHERE id=18;
UPDATE menu_items SET name_en='Iced Flat White', description_en='Flat white served over ice.', badge_en=NULL, ingredients_en='Espresso, Milk, Ice' WHERE id=19;
UPDATE menu_items SET name_en='Iced Latte', description_en='Latte served over ice.', badge_en=NULL, ingredients_en='Espresso, Milk, Ice' WHERE id=20;
UPDATE menu_items SET name_en='Iced Cappuccino', description_en='Cappuccino served over ice.', badge_en=NULL, ingredients_en='Espresso, Milk, Ice' WHERE id=21;
UPDATE menu_items SET name_en='Iced Mocha', description_en='Mocha served over ice.', badge_en=NULL, ingredients_en='Espresso, Belgian chocolate, Milk, Ice' WHERE id=22;
UPDATE menu_items SET name_en='Espresso Tonic', description_en='Double espresso + artisanal tonic water.', badge_en=NULL, ingredients_en='Espresso, Artisanal tonic water' WHERE id=23;
UPDATE menu_items SET name_en='Cold Brew', description_en='Cold-brewed coffee.', badge_en=NULL, ingredients_en='Cold-brewed coffee' WHERE id=24;
UPDATE menu_items SET name_en='Franca Refresher', description_en='Soda, cold brew, and quince syrup.', badge_en=NULL, ingredients_en='Soda, Cold brew, Quince syrup' WHERE id=25;
UPDATE menu_items SET name_en='Hot Belgian Chocolate', description_en='Hot Belgian chocolate. From $190 to $240 depending on size.', badge_en=NULL, ingredients_en='Belgian chocolate, Milk' WHERE id=26;
UPDATE menu_items SET name_en='Matcha', description_en='Matcha tea with milk.', badge_en=NULL, ingredients_en='Matcha, Milk' WHERE id=27;
UPDATE menu_items SET name_en='Coffee Cherry Tea', description_en='Coffee cherry husk infusion (cascara tea).', badge_en=NULL, ingredients_en='Coffee cherry husk' WHERE id=28;
UPDATE menu_items SET name_en='Lemon Verbena Tea', description_en='Lemon verbena infusion.', badge_en=NULL, ingredients_en='Lemon verbena' WHERE id=29;
UPDATE menu_items SET name_en='Black Tea', description_en='Black tea.', badge_en=NULL, ingredients_en='Black tea' WHERE id=30;
UPDATE menu_items SET name_en='Green Tea', description_en='Green tea.', badge_en=NULL, ingredients_en='Green tea' WHERE id=31;
UPDATE menu_items SET name_en='Affogato', description_en='Vanilla ice cream with a shot of espresso.', badge_en=NULL, ingredients_en='Vanilla ice cream, Espresso' WHERE id=32;
UPDATE menu_items SET name_en='Country Bread | Jam | Cheese', description_en='Toasted sourdough country bread, homemade jam, butter or cream cheese.', badge_en=NULL, ingredients_en='Country bread, Homemade jam, Butter or cream cheese' WHERE id=33;
UPDATE menu_items SET name_en='Yogurt | Fruit | Granola', description_en='Organic sugar-free yogurt, fresh seasonal fruit, homemade gluten-free and sugar-free granola, agave. Add peanut butter +$120.', badge_en='Vegan option', ingredients_en='Organic yogurt, Seasonal fruit, Homemade granola, Agave' WHERE id=34;
UPDATE menu_items SET name_en='Pancakes', description_en='Wheat-flour-free oat pancakes, yogurt or honey, peanut butter, homemade berry jam, granola crunch. Vegan option with creamy cashew cream.', badge_en='Wheat-flour-free', ingredients_en='Oats, Yogurt or honey, Peanut butter, Berry jam, Granola' WHERE id=35;
UPDATE menu_items SET name_en='Filled Croissant', description_en='Ham and cheese.', badge_en=NULL, ingredients_en='Croissant, Ham, Cheese' WHERE id=36;
UPDATE menu_items SET name_en='Pressed Ham & Cheese', description_en='Milk bread, artisanal ham, and assorted cheeses.', badge_en=NULL, ingredients_en='Milk bread, Artisanal ham, Assorted cheeses' WHERE id=37;
UPDATE menu_items SET name_en='Country Bread | Hummus | Tempeh', description_en='Country bread, beet hummus, seared organic tempeh, roasted vegetables.', badge_en='Vegan', ingredients_en='Country bread, Beet hummus, Tempeh, Roasted vegetables' WHERE id=38;
UPDATE menu_items SET name_en='English Muffin', description_en='Filled with provolone cheese, artisanal ham, fried egg, and bacon.', badge_en=NULL, ingredients_en='Provolone cheese, Artisanal ham, Egg, Bacon' WHERE id=39;
UPDATE menu_items SET name_en='Country Bread | Scrambled Eggs | Greens', description_en='Sourdough country bread, creamy scrambled eggs, fresh greens. Optional prosciutto add-on +$130.', badge_en=NULL, ingredients_en='Country bread, Scrambled eggs, Fresh greens' WHERE id=40;
UPDATE menu_items SET name_en='Pancakes | Bacon | Honey | Butter', description_en='Wheat-flour-free oat pancakes, spicy honey, miso-cultured butter, and crispy bacon.', badge_en='Wheat-flour-free', ingredients_en='Oats, Spicy honey, Cultured butter, Crispy bacon' WHERE id=41;
UPDATE menu_items SET name_en='Country Bread | Avocado | Soft-Boiled Egg', description_en='Sourdough country bread, lemon avocado, soft-boiled egg, sprouts. Optional vegan version with seared organic tempeh.', badge_en='Wheat-flour-free', ingredients_en='Country bread, Avocado, Lemon, Soft-boiled egg, Sprouts' WHERE id=42;
UPDATE menu_items SET name_en='Chipá Sandwich', description_en='Chipá (cheese bread), cream cheese, roasted tomatoes, homemade pesto.', badge_en='Wheat-flour-free', ingredients_en='Chipá, Cream cheese, Roasted tomatoes, Homemade pesto' WHERE id=43;
UPDATE menu_items SET name_en='Club Sandwich', description_en='Sandwich bread, ham, cheese, hard-boiled egg, mustard, tomato, and lettuce. Vegetarian option without ham, $350.', badge_en=NULL, ingredients_en='Sandwich bread, Ham, Cheese, Hard-boiled egg, Mustard, Tomato, Lettuce' WHERE id=44;
UPDATE menu_items SET name_en='Regular Plate', description_en='One protein + 2 salads. Choose your protein: pork milanesa (veggie option: seitan), chicken tikka (vegan option: tempeh), roast beef, kale souffle, mushrooms, caramelized onion and parmesan cheese, or grilled seitan steak.', badge_en=NULL, ingredients_en='Choice of protein, 2 salads' WHERE id=45;
UPDATE menu_items SET name_en='Large Plate', description_en='One protein + 3 salads. Same protein options as the Regular Plate.', badge_en=NULL, ingredients_en='Choice of protein, 3 salads' WHERE id=46;
UPDATE menu_items SET name_en='Full Menu', description_en='Regular Plate + drink + dessert or coffee.', badge_en=NULL, ingredients_en='Regular Plate, Drink, Dessert or coffee' WHERE id=47;
UPDATE menu_items SET name_en='Menu + Soup', description_en='Full Menu + soup of the week.', badge_en=NULL, ingredients_en='Regular Plate, Drink, Dessert or coffee, Soup of the week' WHERE id=48;
UPDATE menu_items SET name_en='Savory Pie of the Week', description_en='One triangular slice. Ask about today''s filling. With a side: $340.', badge_en=NULL, ingredients_en='Homemade dough, Seasonal filling' WHERE id=49;
UPDATE menu_items SET name_en='Franca Classic', description_en='Focaccia, aioli, gruyere cheese, fried egg, pickled beets, homemade sweet potato chips, green tahini. With a side: $550.', badge_en=NULL, ingredients_en='Focaccia, Aioli, Gruyere cheese, Egg, Pickled beets, Sweet potato chips' WHERE id=50;
UPDATE menu_items SET name_en='Milanesa Sandwich', description_en='Focaccia bread, pork milanesa, aioli, hard-boiled egg, lettuce, and tomato. Vegetarian option with seitan milanesa. With a side: $570.', badge_en=NULL, ingredients_en='Focaccia, Pork milanesa, Aioli, Hard-boiled egg, Lettuce, Tomato' WHERE id=51;
UPDATE menu_items SET name_en='Franca Chivito', description_en='Milk bread, roast beef, emmental cheese, fried egg, artisanal ham, mustard, tomato, mixed greens, and aioli. Vegetarian option with homemade seitan and vegan bacon +$430. With a side: $590.', badge_en='Most Popular', ingredients_en='Milk bread, Roast beef, Emmental cheese, Egg, Artisanal ham, Mustard, Greens' WHERE id=52;
UPDATE menu_items SET name_en='Side Dish', description_en='Fried potatoes and sweet potatoes, or a small chopped salad, to add to any menu dish.', badge_en=NULL, ingredients_en='Fried potatoes and sweet potatoes, or salad' WHERE id=53;
UPDATE menu_items SET name_en='Orange Juice', description_en='Fresh orange juice. 330ml / 500ml, from $180 to $240.', badge_en=NULL, ingredients_en='Orange' WHERE id=54;
UPDATE menu_items SET name_en='Ginger Lemonade', description_en='330ml / 500ml, from $150 to $210.', badge_en=NULL, ingredients_en='Lemon, Ginger' WHERE id=55;
UPDATE menu_items SET name_en='Hibiscus Water', description_en='330ml / 500ml, from $150 to $210.', badge_en=NULL, ingredients_en='Hibiscus flower' WHERE id=56;
UPDATE menu_items SET name_en='Iced Tea', description_en='With notes of peach, honey, and lemon.', badge_en=NULL, ingredients_en='Tea, Peach, Honey, Lemon' WHERE id=57;
UPDATE menu_items SET name_en='Carrot & Orange Juice', description_en='330ml / 500ml, from $180 to $240.', badge_en=NULL, ingredients_en='Carrot, Orange' WHERE id=58;
UPDATE menu_items SET name_en='Green Detox Juice', description_en='330ml / 500ml, from $210 to $280.', badge_en=NULL, ingredients_en='Seasonal green vegetables' WHERE id=59;
UPDATE menu_items SET name_en='Kombucha Bendita', description_en='250ml.', badge_en=NULL, ingredients_en='Artisanal kombucha' WHERE id=60;
UPDATE menu_items SET name_en='Max Graff Artisanal Tonic Water', description_en='275ml.', badge_en=NULL, ingredients_en='Artisanal tonic water' WHERE id=61;
UPDATE menu_items SET name_en='Mansa Ginger Ale', description_en='350ml.', badge_en=NULL, ingredients_en='Ginger ale' WHERE id=62;
UPDATE menu_items SET name_en='Salus Water', description_en='Sparkling or still.', badge_en=NULL, ingredients_en='Mineral water' WHERE id=63;
UPDATE menu_items SET name_en='Hue Light Lager', description_en='Malafama beer.', badge_en=NULL, ingredients_en='Lager beer' WHERE id=64;
UPDATE menu_items SET name_en='Hue Pilsen Lager', description_en='Malafama beer.', badge_en=NULL, ingredients_en='Pilsen beer' WHERE id=65;
UPDATE menu_items SET name_en='IPA Alboroto', description_en='Malafama beer.', badge_en=NULL, ingredients_en='IPA beer' WHERE id=66;
UPDATE menu_items SET name_en='IPA Tas Loco', description_en='Malafama beer.', badge_en=NULL, ingredients_en='IPA beer' WHERE id=67;
UPDATE menu_items SET name_en='Rooster Vermouth (Glass)', description_en='Glass of rosso or rose.', badge_en=NULL, ingredients_en='Vermouth' WHERE id=68;
UPDATE menu_items SET name_en='Rooster Vermouth (Bottle)', description_en='Bottle of rosso or rose.', badge_en=NULL, ingredients_en='Vermouth' WHERE id=69;
UPDATE menu_items SET name_en='Flavored Focaccia', description_en='House-made bread to take home.', badge_en=NULL, ingredients_en='Homemade focaccia' WHERE id=70;
UPDATE menu_items SET name_en='Sourdough Country Bread', description_en='House-made bread to take home.', badge_en=NULL, ingredients_en='Country bread, Sourdough' WHERE id=71;
UPDATE menu_items SET name_en='Individual Country Bread', description_en='House-made bread to take home.', badge_en=NULL, ingredients_en='Country bread' WHERE id=72;
UPDATE menu_items SET name_en='Seis Montes Coffee', description_en='Origin of the month, 250g. Ask at the register for pricing.', badge_en=NULL, ingredients_en='Single-origin coffee, 250g' WHERE id=73;
UPDATE menu_items SET name_en='Chocolate, Sweet Potato & Buckwheat Cake', description_en='Made to order.', badge_en=NULL, ingredients_en='Chocolate, Sweet potato, Buckwheat' WHERE id=74;
UPDATE menu_items SET name_en='Almond & Dulce de Leche Dacquoise', description_en='Birthday cake. Made to order.', badge_en=NULL, ingredients_en='Almonds, Dulce de leche' WHERE id=75;
UPDATE menu_items SET name_en='Carrot or Banana Loaf Cake', description_en='Made to order.', badge_en=NULL, ingredients_en='Carrot or banana' WHERE id=76;
