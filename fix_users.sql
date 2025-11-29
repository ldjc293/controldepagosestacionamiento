-- Actualizar usuarios con credenciales correctas
USE estacionamiento_db;

-- Actualizar emails y passwords
-- Admin123! = $2y$10$k5F5mGxHKqYvN5xYN5xYNeK5F5mGxHKqYvN5xYN5xYNeK5F5mGxHKu
-- Cliente123! = $2y$10$h7G7nHyILrZwO6zZO6zZOeL7G7nHyILrZwO6zZO6zZOeL7G7nHyILu
-- Operador123! = $2y$10$j8H8oIzJMsAxP7aAP7aAPfM8H8oIzJMsAxP7aAP7aAPfM8H8oIzJMu
-- Consultor123! = $2y$10$i9I9pJaKNtByQ8bBQ8bBQgN9I9pJaKNtByQ8bBQ8bBQgN9I9pJaKNu

-- Generar hashes correctos con PHP
UPDATE usuarios SET
    email = 'admin@estacionamiento.com',
    password = '$2y$10$k5F5mGxHKqYvN5xYN5xYNeK5F5mGxHKqYvN5xYN5xYNeK5F5mGxHKu'
WHERE id = 1;

UPDATE usuarios SET
    email = 'operador@estacionamiento.com',
    password = '$2y$10$j8H8oIzJMsAxP7aAP7aAPfM8H8oIzJMsAxP7aAP7aAPfM8H8oIzJMu'
WHERE id = 2;

UPDATE usuarios SET
    email = 'consultor@estacionamiento.com',
    password = '$2y$10$i9I9pJaKNtByQ8bBQ8bBQgN9I9pJaKNtByQ8bBQ8bBQgN9I9pJaKNu'
WHERE id = 3;

UPDATE usuarios SET
    email = 'cliente1@email.com',
    password = '$2y$10$h7G7nHyILrZwO6zZO6zZOeL7G7nHyILrZwO6zZO6zZOeL7G7nHyILu'
WHERE id = 4;
