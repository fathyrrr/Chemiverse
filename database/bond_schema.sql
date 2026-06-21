-- Bond Simulator Schema for Chemiverse
-- Run this after the main schema.sql

USE chemiverse_db;

-- Reference table for bond types
DROP TABLE IF EXISTS bond_simulations;
DROP TABLE IF EXISTS bond_types;

CREATE TABLE bond_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    electron_behavior VARCHAR(100) NOT NULL
);

-- Seed bond types
INSERT INTO bond_types (name, description, electron_behavior) VALUES
('Ionic', 'Terjadi ketika satu atom mentransfer elektron ke atom lain, biasanya antara logam dan nonlogam. Perbedaan elektronegativitas > 1.7.', 'transfer'),
('Covalent', 'Terjadi ketika dua atom berbagi elektron secara merata. Biasanya antara sesama nonlogam. Perbedaan elektronegativitas < 0.4.', 'sharing'),
('Polar Covalent', 'Ikatan kovalen dengan pembagian elektron tidak merata. Perbedaan elektronegativitas antara 0.4 dan 1.7.', 'unequal_sharing'),
('Metallic', 'Ikatan antara atom-atom logam di mana elektron valensi bergerak bebas membentuk "lautan elektron".', 'sea_of_electrons');

-- User experiments table (CUD target)
CREATE TABLE bond_simulations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    element1_symbol VARCHAR(5) NOT NULL,
    element1_name VARCHAR(50) NOT NULL,
    element2_symbol VARCHAR(5) NOT NULL,
    element2_name VARCHAR(50) NOT NULL,
    bond_type_id INT,
    bond_type_name VARCHAR(50),
    bond_count INT DEFAULT 1,
    electronegativity_diff DECIMAL(4,2) DEFAULT 0.00,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (bond_type_id) REFERENCES bond_types(id) ON DELETE SET NULL
);
