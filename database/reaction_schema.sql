-- =============================================
-- Chemiverse: Reaction Lab — Schema & Seeds
-- =============================================

CREATE TABLE IF NOT EXISTS reactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    reactants JSON NOT NULL COMMENT 'Array of reactant formulas',
    products JSON NOT NULL COMMENT 'Array of product formulas',
    equation VARCHAR(255) NOT NULL,
    type ENUM('synthesis','decomposition','single_replacement','double_replacement','combustion','acid_base') NOT NULL,
    energy ENUM('exothermic','endothermic') NOT NULL DEFAULT 'exothermic',
    reversible TINYINT(1) NOT NULL DEFAULT 0,
    conditions VARCHAR(255) DEFAULT 'Suhu kamar',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reaction_experiments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reaction_id INT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reaction_id) REFERENCES reactions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- SEED DATA — 15 iconic reactions
-- =============================================

INSERT INTO reactions (name, reactants, products, equation, type, energy, reversible, conditions, description) VALUES
('Pembentukan Air', '["H₂","O₂"]', '["H₂O"]',
 '2H₂ + O₂ → 2H₂O', 'synthesis', 'exothermic', 0,
 'Percikan api / nyala api',
 'Reaksi paling fundamental di alam semesta. Dua gas — hidrogen dan oksigen — bergabung secara eksplosif membentuk air. Reaksi ini menghasilkan energi luar biasa besar dan menjadi bahan bakar roket utama NASA.');

INSERT INTO reactions (name, reactants, products, equation, type, energy, reversible, conditions, description) VALUES
('Netralisasi HCl-NaOH', '["HCl","NaOH"]', '["NaCl","H₂O"]',
 'HCl + NaOH → NaCl + H₂O', 'acid_base', 'exothermic', 0,
 'Suhu kamar',
 'Reaksi netralisasi klasik. Asam klorida bertemu natrium hidroksida, menghasilkan garam dapur (NaCl) dan air. Panas yang dilepaskan bisa dirasakan jika dicampur dalam tabung reaksi.');

INSERT INTO reactions (name, reactants, products, equation, type, energy, reversible, conditions, description) VALUES
('Sintesis Natrium Klorida', '["Na","Cl₂"]', '["NaCl"]',
 '2Na + Cl₂ → 2NaCl', 'synthesis', 'exothermic', 0,
 'Natrium dibakar dalam gas klorin',
 'Logam natrium yang sangat reaktif bereaksi hebat dengan gas klorin beracun, namun menghasilkan garam dapur yang kita konsumsi setiap hari. Kontras yang menakjubkan!');

INSERT INTO reactions (name, reactants, products, equation, type, energy, reversible, conditions, description) VALUES
('Dekomposisi Kalsium Karbonat', '["CaCO₃"]', '["CaO","CO₂"]',
 'CaCO₃ → CaO + CO₂', 'decomposition', 'endothermic', 0,
 'Pemanasan >840°C',
 'Batu kapur (CaCO₃) dipanaskan hingga terurai menjadi kapur tohor (CaO) dan gas CO₂. Proses ini adalah dasar industri semen dan telah dilakukan manusia sejak ribuan tahun lalu.');

INSERT INTO reactions (name, reactants, products, equation, type, energy, reversible, conditions, description) VALUES
('Penggantian Seng-Tembaga', '["Zn","CuSO₄"]', '["ZnSO₄","Cu"]',
 'Zn + CuSO₄ → ZnSO₄ + Cu', 'single_replacement', 'exothermic', 0,
 'Suhu kamar, larutan CuSO₄',
 'Seng menggantikan tembaga dari larutan tembaga sulfat. Kamu bisa melihat tembaga merah muda mengendap di permukaan seng sementara larutan biru berubah menjadi bening.');

INSERT INTO reactions (name, reactants, products, equation, type, energy, reversible, conditions, description) VALUES
('Pembakaran Metana', '["CH₄","O₂"]', '["CO₂","H₂O"]',
 'CH₄ + 2O₂ → CO₂ + 2H₂O', 'combustion', 'exothermic', 0,
 'Nyala api',
 'Pembakaran gas alam (metana). Inilah reaksi yang terjadi setiap kali kamu menyalakan kompor gas di dapur. Menghasilkan karbon dioksida, air, dan banyak panas.');

INSERT INTO reactions (name, reactants, products, equation, type, energy, reversible, conditions, description) VALUES
('Presipitasi Perak Klorida', '["AgNO₃","NaCl"]', '["AgCl","NaNO₃"]',
 'AgNO₃ + NaCl → AgCl↓ + NaNO₃', 'double_replacement', 'exothermic', 0,
 'Suhu kamar, dalam larutan',
 'Endapan putih AgCl langsung terbentuk saat dua larutan bening dicampurkan. Reaksi ini digunakan dalam fotografi analog dan sebagai uji keberadaan ion klorida.');

INSERT INTO reactions (name, reactants, products, equation, type, energy, reversible, conditions, description) VALUES
('Dekomposisi Hidrogen Peroksida', '["H₂O₂"]', '["H₂O","O₂"]',
 '2H₂O₂ → 2H₂O + O₂', 'decomposition', 'exothermic', 0,
 'Katalis MnO₂ (atau kentang/hati mentah!)',
 'Air oksigenasi (H₂O₂) terurai menjadi air dan oksigen. Dengan katalis MnO₂, reaksi ini bisa sangat cepat dan dramatis — basis dari eksperimen "elephant toothpaste"!');

INSERT INTO reactions (name, reactants, products, equation, type, energy, reversible, conditions, description) VALUES
('Penggantian Besi-Tembaga', '["Fe","CuSO₄"]', '["FeSO₄","Cu"]',
 'Fe + CuSO₄ → FeSO₄ + Cu', 'single_replacement', 'exothermic', 0,
 'Suhu kamar, larutan CuSO₄',
 'Paku besi dicelupkan ke larutan tembaga sulfat biru. Setelah beberapa menit, paku terlapisi oleh tembaga berwarna merah kecokelatan. Bukti nyata deret aktivitas logam.');

INSERT INTO reactions (name, reactants, products, equation, type, energy, reversible, conditions, description) VALUES
('Proses Haber', '["N₂","H₂"]', '["NH₃"]',
 'N₂ + 3H₂ ⇌ 2NH₃', 'synthesis', 'exothermic', 1,
 'Suhu 400-500°C, tekanan 150-300 atm, katalis Fe',
 'Reaksi industri paling penting di dunia modern. Mengubah nitrogen dari udara menjadi amonia untuk pupuk. Tanpa proses Haber, separuh populasi dunia tidak akan bisa makan.');

INSERT INTO reactions (name, reactants, products, equation, type, energy, reversible, conditions, description) VALUES
('Pembakaran Etanol', '["C₂H₅OH","O₂"]', '["CO₂","H₂O"]',
 'C₂H₅OH + 3O₂ → 2CO₂ + 3H₂O', 'combustion', 'exothermic', 0,
 'Nyala api',
 'Etanol terbakar dengan nyala biru bersih menghasilkan CO₂ dan air. Digunakan sebagai bahan bakar alternatif dan bahan bakar di lampu spiritus laboratorium.');

INSERT INTO reactions (name, reactants, products, equation, type, energy, reversible, conditions, description) VALUES
('Reaksi Cuka-Soda Kue', '["CH₃COOH","NaHCO₃"]', '["CH₃COONa","H₂O","CO₂"]',
 'CH₃COOH + NaHCO₃ → CH₃COONa + H₂O + CO₂', 'acid_base', 'endothermic', 0,
 'Suhu kamar',
 'Eksperimen sains paling populer di sekolah! Campurkan cuka dan soda kue, saksikan buih dan gelembung CO₂ menyembur keluar. Sederhana namun selalu memukau.');

INSERT INTO reactions (name, reactants, products, equation, type, energy, reversible, conditions, description) VALUES
('Oksidasi Besi (Karat)', '["Fe","O₂","H₂O"]', '["Fe₂O₃·nH₂O"]',
 '4Fe + 3O₂ + 6H₂O → 4Fe(OH)₃', 'synthesis', 'exothermic', 0,
 'Udara lembap, lambat (berhari-hari)',
 'Proses perkaratan besi yang kita lihat setiap hari. Reaksi ini lambat namun pasti — besi bereaksi dengan oksigen dan air membentuk karat. Musuh utama infrastruktur logam.');

INSERT INTO reactions (name, reactants, products, equation, type, energy, reversible, conditions, description) VALUES
('Fotosintesis', '["CO₂","H₂O"]', '["C₆H₁₂O₆","O₂"]',
 '6CO₂ + 6H₂O → C₆H₁₂O₆ + 6O₂', 'synthesis', 'endothermic', 0,
 'Cahaya matahari + klorofil',
 'Reaksi yang menopang hampir seluruh kehidupan di Bumi. Tumbuhan mengubah CO₂ dan air menjadi glukosa dan oksigen menggunakan energi matahari. Keajaiban biokimia sejati.');

INSERT INTO reactions (name, reactants, products, equation, type, energy, reversible, conditions, description) VALUES
('Elektrolisis Air', '["H₂O"]', '["H₂","O₂"]',
 '2H₂O → 2H₂ + O₂', 'decomposition', 'endothermic', 1,
 'Arus listrik DC, elektroda inert',
 'Kebalikan dari pembentukan air. Dengan mengalirkan arus listrik, air terurai kembali menjadi hidrogen dan oksigen. Teknologi kunci dalam ekonomi hidrogen masa depan.');
