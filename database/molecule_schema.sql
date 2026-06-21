-- =============================================
-- Chemiverse: Molecular Viewer — Schema & Seeds
-- =============================================

CREATE TABLE IF NOT EXISTS molecules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    formula VARCHAR(100) NOT NULL,
    category ENUM('essential','organic','drug','macro') NOT NULL DEFAULT 'essential',
    description TEXT,
    molecular_weight DECIMAL(10,3),
    structure_data LONGTEXT NOT NULL COMMENT 'JSON {atoms:[{el,x,y,z}], bonds:[{a,b,order}]}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- SEED DATA — 10 molecules with 3D coordinates
-- =============================================

-- 1. Water (H₂O)
INSERT INTO molecules (name, formula, category, description, molecular_weight, structure_data) VALUES
('Water', 'H₂O', 'essential',
 'Molekul paling penting di Bumi. Terdiri dari dua atom hidrogen yang terikat pada satu atom oksigen dengan sudut ikatan 104.5°. Sifat polarnya membuat air menjadi pelarut universal.',
 18.015,
 '{"atoms":[{"el":"O","x":0,"y":0,"z":0},{"el":"H","x":0.757,"y":0.586,"z":0},{"el":"H","x":-0.757,"y":0.586,"z":0}],"bonds":[{"a":0,"b":1,"order":1},{"a":0,"b":2,"order":1}]}');

-- 2. Carbon Dioxide (CO₂)
INSERT INTO molecules (name, formula, category, description, molecular_weight, structure_data) VALUES
('Carbon Dioxide', 'CO₂', 'essential',
 'Gas rumah kaca utama. Molekul linear dengan dua ikatan rangkap C=O. Dihasilkan oleh respirasi dan pembakaran, serta digunakan tumbuhan dalam fotosintesis.',
 44.010,
 '{"atoms":[{"el":"C","x":0,"y":0,"z":0},{"el":"O","x":1.16,"y":0,"z":0},{"el":"O","x":-1.16,"y":0,"z":0}],"bonds":[{"a":0,"b":1,"order":2},{"a":0,"b":2,"order":2}]}');

-- 3. Methane (CH₄)
INSERT INTO molecules (name, formula, category, description, molecular_weight, structure_data) VALUES
('Methane', 'CH₄', 'essential',
 'Hidrokarbon paling sederhana dengan geometri tetrahedral sempurna. Komponen utama gas alam dan gas rumah kaca yang 80× lebih kuat dari CO₂ dalam 20 tahun.',
 16.043,
 '{"atoms":[{"el":"C","x":0,"y":0,"z":0},{"el":"H","x":0.629,"y":0.629,"z":0.629},{"el":"H","x":-0.629,"y":-0.629,"z":0.629},{"el":"H","x":-0.629,"y":0.629,"z":-0.629},{"el":"H","x":0.629,"y":-0.629,"z":-0.629}],"bonds":[{"a":0,"b":1,"order":1},{"a":0,"b":2,"order":1},{"a":0,"b":3,"order":1},{"a":0,"b":4,"order":1}]}');

-- 4. Ammonia (NH₃)
INSERT INTO molecules (name, formula, category, description, molecular_weight, structure_data) VALUES
('Ammonia', 'NH₃', 'essential',
 'Senyawa nitrogen dan hidrogen dengan bau menyengat yang khas. Geometri piramida trigonal. Digunakan secara luas dalam industri pupuk dan bahan pembersih.',
 17.031,
 '{"atoms":[{"el":"N","x":0,"y":0.374,"z":0},{"el":"H","x":0.938,"y":-0.125,"z":0},{"el":"H","x":-0.469,"y":-0.125,"z":0.812},{"el":"H","x":-0.469,"y":-0.125,"z":-0.812}],"bonds":[{"a":0,"b":1,"order":1},{"a":0,"b":2,"order":1},{"a":0,"b":3,"order":1}]}');

-- 5. Sodium Chloride (NaCl)
INSERT INTO molecules (name, formula, category, description, molecular_weight, structure_data) VALUES
('Sodium Chloride', 'NaCl', 'essential',
 'Garam dapur! Ikatan ionik klasik antara logam alkali (Na) dan halogen (Cl). Dalam bentuk kristal, membentuk struktur kubus raksasa.',
 58.440,
 '{"atoms":[{"el":"Na","x":-1.18,"y":0,"z":0},{"el":"Cl","x":1.18,"y":0,"z":0}],"bonds":[{"a":0,"b":1,"order":1}]}');

-- 6. Ethanol (C₂H₅OH)
INSERT INTO molecules (name, formula, category, description, molecular_weight, structure_data) VALUES
('Ethanol', 'C₂H₅OH', 'organic',
 'Alkohol yang terdapat dalam minuman beralkohol. Juga digunakan sebagai bahan bakar, pelarut, dan antiseptik. Dibuat melalui fermentasi gula oleh ragi.',
 46.069,
 '{"atoms":[{"el":"C","x":-0.752,"y":-0.035,"z":0.0},{"el":"C","x":0.752,"y":0.035,"z":0.0},{"el":"O","x":1.13,"y":1.380,"z":0.0},{"el":"H","x":1.16,"y":-0.48,"z":0.89},{"el":"H","x":1.16,"y":-0.48,"z":-0.89},{"el":"H","x":-1.16,"y":0.48,"z":0.89},{"el":"H","x":-1.16,"y":0.48,"z":-0.89},{"el":"H","x":-1.16,"y":-1.06,"z":0.0},{"el":"H","x":2.08,"y":1.40,"z":0.0}],"bonds":[{"a":0,"b":1,"order":1},{"a":1,"b":2,"order":1},{"a":1,"b":3,"order":1},{"a":1,"b":4,"order":1},{"a":0,"b":5,"order":1},{"a":0,"b":6,"order":1},{"a":0,"b":7,"order":1},{"a":2,"b":8,"order":1}]}');

-- 7. Benzene (C₆H₆)
INSERT INTO molecules (name, formula, category, description, molecular_weight, structure_data) VALUES
('Benzene', 'C₆H₆', 'organic',
 'Cincin aromatik sempurna — fondasi kimia organik. Enam atom karbon membentuk heksagon planar dengan elektron terdelokalisasi yang menciptakan stabilitas luar biasa.',
 78.114,
 '{"atoms":[{"el":"C","x":1.40,"y":0,"z":0},{"el":"C","x":0.70,"y":1.21,"z":0},{"el":"C","x":-0.70,"y":1.21,"z":0},{"el":"C","x":-1.40,"y":0,"z":0},{"el":"C","x":-0.70,"y":-1.21,"z":0},{"el":"C","x":0.70,"y":-1.21,"z":0},{"el":"H","x":2.48,"y":0,"z":0},{"el":"H","x":1.24,"y":2.15,"z":0},{"el":"H","x":-1.24,"y":2.15,"z":0},{"el":"H","x":-2.48,"y":0,"z":0},{"el":"H","x":-1.24,"y":-2.15,"z":0},{"el":"H","x":1.24,"y":-2.15,"z":0}],"bonds":[{"a":0,"b":1,"order":2},{"a":1,"b":2,"order":1},{"a":2,"b":3,"order":2},{"a":3,"b":4,"order":1},{"a":4,"b":5,"order":2},{"a":5,"b":0,"order":1},{"a":0,"b":6,"order":1},{"a":1,"b":7,"order":1},{"a":2,"b":8,"order":1},{"a":3,"b":9,"order":1},{"a":4,"b":10,"order":1},{"a":5,"b":11,"order":1}]}');

-- 8. Acetic Acid (CH₃COOH)
INSERT INTO molecules (name, formula, category, description, molecular_weight, structure_data) VALUES
('Acetic Acid', 'CH₃COOH', 'organic',
 'Komponen utama cuka! Asam karboksilat paling sederhana kedua. Digunakan dalam industri makanan, kimia, dan farmasi.',
 60.052,
 '{"atoms":[{"el":"C","x":-0.764,"y":0,"z":0},{"el":"C","x":0.764,"y":0,"z":0},{"el":"O","x":1.36,"y":1.03,"z":0},{"el":"O","x":1.36,"y":-1.03,"z":0},{"el":"H","x":-1.16,"y":0.52,"z":0.89},{"el":"H","x":-1.16,"y":0.52,"z":-0.89},{"el":"H","x":-1.16,"y":-1.04,"z":0},{"el":"H","x":2.30,"y":-1.03,"z":0}],"bonds":[{"a":0,"b":1,"order":1},{"a":1,"b":2,"order":2},{"a":1,"b":3,"order":1},{"a":0,"b":4,"order":1},{"a":0,"b":5,"order":1},{"a":0,"b":6,"order":1},{"a":3,"b":7,"order":1}]}');

-- 9. Caffeine (C₈H₁₀N₄O₂)
INSERT INTO molecules (name, formula, category, description, molecular_weight, structure_data) VALUES
('Caffeine', 'C₈H₁₀N₄O₂', 'drug',
 'Stimulan psikoaktif paling banyak dikonsumsi di dunia — ditemukan dalam kopi, teh, dan cokelat. Bekerja dengan memblokir reseptor adenosin di otak, mencegah rasa kantuk.',
 194.190,
 '{"atoms":[{"el":"N","x":1.33,"y":0.77,"z":0},{"el":"C","x":1.33,"y":-0.77,"z":0},{"el":"N","x":0.0,"y":-1.54,"z":0},{"el":"C","x":-1.33,"y":-0.77,"z":0},{"el":"C","x":-1.33,"y":0.77,"z":0},{"el":"C","x":0.0,"y":1.54,"z":0},{"el":"N","x":-2.50,"y":1.30,"z":0},{"el":"C","x":-3.20,"y":0.10,"z":0},{"el":"N","x":-2.50,"y":-1.10,"z":0},{"el":"O","x":0.0,"y":2.76,"z":0},{"el":"O","x":1.33,"y":-1.97,"z":0},{"el":"C","x":2.66,"y":1.30,"z":0},{"el":"C","x":0.0,"y":-2.96,"z":0},{"el":"C","x":-2.80,"y":2.72,"z":0},{"el":"H","x":-4.28,"y":0.10,"z":0},{"el":"H","x":2.66,"y":2.38,"z":0},{"el":"H","x":3.38,"y":0.86,"z":0.74},{"el":"H","x":3.38,"y":0.86,"z":-0.74},{"el":"H","x":0.0,"y":-3.50,"z":0.89},{"el":"H","x":0.0,"y":-3.50,"z":-0.89},{"el":"H","x":0.92,"y":-3.30,"z":0},{"el":"H","x":-2.10,"y":3.26,"z":0.74},{"el":"H","x":-2.10,"y":3.26,"z":-0.74},{"el":"H","x":-3.85,"y":2.95,"z":0}],"bonds":[{"a":0,"b":1,"order":1},{"a":1,"b":2,"order":1},{"a":2,"b":3,"order":1},{"a":3,"b":4,"order":2},{"a":4,"b":5,"order":1},{"a":5,"b":0,"order":1},{"a":4,"b":6,"order":1},{"a":6,"b":7,"order":1},{"a":7,"b":8,"order":2},{"a":8,"b":3,"order":1},{"a":5,"b":9,"order":2},{"a":1,"b":10,"order":2},{"a":0,"b":11,"order":1},{"a":2,"b":12,"order":1},{"a":6,"b":13,"order":1},{"a":7,"b":14,"order":1},{"a":11,"b":15,"order":1},{"a":11,"b":16,"order":1},{"a":11,"b":17,"order":1},{"a":12,"b":18,"order":1},{"a":12,"b":19,"order":1},{"a":12,"b":20,"order":1},{"a":13,"b":21,"order":1},{"a":13,"b":22,"order":1},{"a":13,"b":23,"order":1}]}');

-- 10. Aspirin (C₉H₈O₄)
INSERT INTO molecules (name, formula, category, description, molecular_weight, structure_data) VALUES
('Aspirin', 'C₉H₈O₄', 'drug',
 'Obat anti-inflamasi paling terkenal di dunia, ditemukan tahun 1897 oleh Felix Hoffmann dari Bayer. Bekerja dengan menghambat enzim COX, mengurangi nyeri dan peradangan.',
 180.158,
 '{"atoms":[{"el":"C","x":1.40,"y":0,"z":0},{"el":"C","x":0.70,"y":1.21,"z":0},{"el":"C","x":-0.70,"y":1.21,"z":0},{"el":"C","x":-1.40,"y":0,"z":0},{"el":"C","x":-0.70,"y":-1.21,"z":0},{"el":"C","x":0.70,"y":-1.21,"z":0},{"el":"C","x":2.85,"y":0,"z":0},{"el":"O","x":3.45,"y":1.03,"z":0},{"el":"O","x":3.45,"y":-1.03,"z":0},{"el":"H","x":4.40,"y":-1.03,"z":0},{"el":"O","x":-1.15,"y":-2.38,"z":0},{"el":"C","x":-0.50,"y":-3.55,"z":0},{"el":"O","x":0.70,"y":-3.65,"z":0},{"el":"C","x":-1.30,"y":-4.70,"z":0},{"el":"H","x":0.70,"y":2.15,"z":0},{"el":"H","x":-1.24,"y":2.15,"z":0},{"el":"H","x":-2.48,"y":0,"z":0},{"el":"H","x":-1.30,"y":-5.30,"z":0.89},{"el":"H","x":-1.30,"y":-5.30,"z":-0.89},{"el":"H","x":-2.35,"y":-4.40,"z":0}],"bonds":[{"a":0,"b":1,"order":2},{"a":1,"b":2,"order":1},{"a":2,"b":3,"order":2},{"a":3,"b":4,"order":1},{"a":4,"b":5,"order":2},{"a":5,"b":0,"order":1},{"a":0,"b":6,"order":1},{"a":6,"b":7,"order":2},{"a":6,"b":8,"order":1},{"a":8,"b":9,"order":1},{"a":4,"b":10,"order":1},{"a":10,"b":11,"order":1},{"a":11,"b":12,"order":2},{"a":11,"b":13,"order":1},{"a":1,"b":14,"order":1},{"a":2,"b":15,"order":1},{"a":3,"b":16,"order":1},{"a":13,"b":17,"order":1},{"a":13,"b":18,"order":1},{"a":13,"b":19,"order":1}]}');
