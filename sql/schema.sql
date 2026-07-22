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
  slug VARCHAR(150) NOT NULL UNIQUE,
  description TEXT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  image VARCHAR(255) NULL,
  badge VARCHAR(60) NULL,
  ingredients VARCHAR(500) NULL,
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
('email', 'hola@franca.uy'),
('hours_mon_fri', '08:00 - 20:00'),
('hours_sat', '09:00 - 21:00'),
('hours_sun', '09:00 - 16:00'),
('instagram', '@franca.mvd'),
('map_lat', '-34.905800'),
('map_lng', '-56.191400');

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
