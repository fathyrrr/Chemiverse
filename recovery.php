<?php
// Script Pemulihan Otomatis Chemiverse (Auto-Recovery)
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // Konek ke MySQL tanpa menargetkan database (agar tidak error jika DB belum ada)
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h3>Memulai Proses Pemulihan Database Chemiverse...</h3>";

    // 1. Buat Database jika belum ada
    $pdo->exec("CREATE DATABASE IF NOT EXISTS chemiverse_db");
    $pdo->exec("USE chemiverse_db");
    echo "<p>✅ Database 'chemiverse_db' berhasil dibuat/dipilih.</p>";

    // 2. Buat ulang tabel 'elements' secara paksa (DROP lalu CREATE)
    $pdo->exec("DROP TABLE IF EXISTS elements");
    
    $createTableQuery = "
    CREATE TABLE elements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        atomic_number INT NOT NULL,
        symbol VARCHAR(5) NOT NULL,
        name VARCHAR(50) NOT NULL,
        category VARCHAR(100),
        atomic_mass DECIMAL(10, 4),
        xpos INT,
        ypos INT,
        color_hex VARCHAR(20)
    )";
    $pdo->exec($createTableQuery);
    echo "<p>✅ Tabel 'elements' berhasil dibuat ulang (bersih).</p>";

    // 3. Ambil data asli dari repositori publik (JSON)
    echo "<p>⏳ Mengunduh data 118 elemen dari repositori global...</p>";
    $json_data = file_get_contents("https://raw.githubusercontent.com/Bowserinator/Periodic-Table-JSON/master/PeriodicTableJSON.json");
    $data = json_decode($json_data, true);

    if (!$data || !isset($data['elements'])) {
        throw new Exception("Gagal mengunduh JSON data elemen.");
    }

    // 4. Suntikkan (Insert) ke database kita
    $stmt = $pdo->prepare("INSERT INTO elements (atomic_number, symbol, name, category, atomic_mass, xpos, ypos, color_hex) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $count = 0;
    foreach ($data['elements'] as $el) {
        // Pemetaan warna fiksi ilmiah (Sci-fi) secara dinamis jika warna asli tidak ada
        $color = isset($el['cpk-hex']) && !empty($el['cpk-hex']) ? '#' . $el['cpk-hex'] : '#ffffff';
        if ($el['category'] == 'diatomic nonmetal') $color = '#00ffff';
        if ($el['category'] == 'noble gas') $color = '#ff00ff';
        if ($el['category'] == 'alkali metal') $color = '#ffaa00';
        
        $stmt->execute([
            $el['number'],
            $el['symbol'],
            $el['name'],
            $el['category'],
            $el['atomic_mass'],
            $el['xpos'],
            $el['ypos'],
            $color
        ]);
        $count++;
    }

    echo "<p>✅ <b>SUKSES!</b> $count elemen berhasil disuntikkan ke dalam tabel.</p>";
    echo "<h3>🎉 Pemulihan Selesai. Silakan kembali ke <a href='index.html'>Halaman Utama</a>!</h3>";

} catch (PDOException $e) {
    echo "<p style='color:red;'><b>Error Database:</b> " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'><b>Error Sistem:</b> " . $e->getMessage() . "</p>";
}
?>
