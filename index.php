<?php
// Conexão com o banco de dados PostgreSQL
$host = '192.168.88.222';
$dbname = 'rifa';
$user = 'db_manager';
$password = 'qrup535954';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Erro ao conectar: " . $e->getMessage());
}

// Verifica se a tabela "rifas" já existe, e cria se necessário
$pdo->exec("CREATE TABLE IF NOT EXISTS rifas (
    id SERIAL PRIMARY KEY,
    numero INT NOT NULL UNIQUE,
    reservado BOOLEAN DEFAULT FALSE,
    pago BOOLEAN DEFAULT FALSE,
    tipo SMALLINT CHECK (tipo IN (1, 2, 3))
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS reservas (
    id SERIAL PRIMARY KEY,
    numero INT NOT NULL REFERENCES rifas(numero),
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    data_reserva TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rifa - Chá de Bebê</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* Fundo personalizado */
        body {
            background-image: url('bg.png'); /* Substitua com o caminho correto */
            background-size: cover;
            background-repeat: repeat;
            background-color: #f0f8ff;
        }
        /* Header estilizado */
        .header {
            background-image: url('header.jpg'); /* Imagem do header */
            background-size: cover;
            background-position: center;
            padding: 60px 20px;
            color: #ffffff;
            text-align: center;
            position: relative;
        }
        .header-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5); /* Fundo escuro com transparência */
        }
        .header-content {
            position: relative;
            z-index: 2;
            color: #fff;
        }
        .header h1 {
            font-family: 'Pacifico', cursive;
            font-size: 3em;
            margin: 0;
        }
        .header h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 2em;
            font-weight: 700;
            margin-top: 10px;
        }
        .header p {
            font-family: 'Poppins', sans-serif;
            font-size: 1.2em;
            margin-top: 20px;
        }
        .prizes-list {
            margin-top: 20px;
            font-size: 1em;
            line-height: 1.5;
        }
        /* Estilos dos números */
        .container-numbers {
            border: 5px solid #ADD8E6; /* Azul bebê */
            border-radius: 15px;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.8);
            margin-top: 30px;
        }
        .number-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            margin: 5px;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
            font-size: 14px;
        }
        .number-card:hover {
            transform: scale(1.1);
        }
        .numbers-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
    </style>
</head>
<body>

<div class="header">
    <div class="header-overlay"></div>
    <div class="header-content">
        <h1>Chá Rifa <span style="color: #ffd700;">{Nome}</span></h1>
        <h2>Sorteio: 18/04</h2>
        <p class="prizes-list">
            PRÊMIO: R$500,00
        </p>
    </div>
</div>

<div class="container text-center">
    <h1 class="my-5">Escolha seu Número da Rifa</h1>
    <div class="container-numbers">
        <div class="numbers-container">
            <?php
            // Obtem todos os números de rifa
            $stmt = $pdo->query("SELECT numero, reservado, pago FROM rifas ORDER BY numero");
            while ($row = $stmt->fetch()) {
                $class = 'number-available';
                if ($row['pago']) {
                    $class = 'number-paid';
                } elseif ($row['reservado']) {
                    $class = 'number-reserved';
                }
                echo "<div class='number-card $class' data-bs-toggle='modal' data-bs-target='#modalRifa' data-numero='{$row['numero']}'>{$row['numero']}</div>";
            }
            ?>
        </div>
    </div>
</div>

<!-- Modal para Reserva -->
<div class="modal fade" id="modalRifa" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reservar Número</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <img src="qrcode.png" class="img-fluid mb-3" alt="QR Code Pix">
                </div>
                <p>Preencha os campos abaixo para reservar um número da rifa. Entraremos em contato para o pagamento.</p>
                <p>Se preferir, escaneie o código acima e envie o comprovante para (61) 98888-7777</p>
                <form id="formReserva" method="POST">
                    <input type="hidden" name="numero" id="numeroRifa">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="tel" class="form-control" name="telefone" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Reservar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.number-card').forEach(card => {
        card.addEventListener('click', function () {
            const numero = this.getAttribute('data-numero');
            document.getElementById('numeroRifa').value = numero;
        });
    });
</script>
</body>
</html>
