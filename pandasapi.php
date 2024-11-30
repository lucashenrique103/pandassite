<?php
// Caminho para o arquivo de contas SMTP no servidor
$caminhoArquivoSmtp = __DIR__ . '/contas_smtp.txt'; // Arquivo deve estar no mesmo diretório da API

// Dados de exemplo para login (usuários e senhas)
$usuarios = [
    "maserati" => ["senha" => "maserati", "expiracao" => "2025-01-01 17:16:18"],
    "baby" => ["senha" => "baby2264", "expiracao" => "2025-01-01 17:16:18"],
];

// Recebe os dados enviados pelo cliente
$dados = json_decode(file_get_contents("php://input"), true);

// Log da entrada para depuração
file_put_contents('log.txt', "Dados recebidos: " . json_encode($dados) . PHP_EOL, FILE_APPEND);

$usuario = $dados['usuario'] ?? '';
$senha = $dados['senha'] ?? '';

if (isset($usuarios[$usuario]) && $usuarios[$usuario]['senha'] === $senha) {
    $dataAtual = date("Y-m-d H:i:s");
    $dataExpiracao = $usuarios[$usuario]['expiracao'];

    file_put_contents('log.txt', "Data atual: $dataAtual, Data de Expiração: $dataExpiracao" . PHP_EOL, FILE_APPEND);

    if ($dataAtual <= $dataExpiracao) {
        $diasRestantes = (new DateTime($dataExpiracao))->diff(new DateTime($dataAtual))->days;

        // Obtendo as contas SMTP do arquivo TXT
        $contasSmtp = [];
        if (file_exists($caminhoArquivoSmtp)) {
            $linhas = file($caminhoArquivoSmtp, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($linhas as $linha) {
                $partes = explode(';', $linha);
                if (count($partes) === 4) {
                    $contasSmtp[] = [
                        "email" => $partes[0],
                        "senha" => $partes[1],
                        "smtp" => $partes[2],
                        "porta" => (int)$partes[3]
                    ];
                }
            }
        } else {
            file_put_contents('log.txt', "Erro: Arquivo de contas SMTP não encontrado." . PHP_EOL, FILE_APPEND);
        }

        echo json_encode([
            "status" => "sucesso",
            "mensagem" => "Login válido",
            "diasrestantes" => $diasRestantes,
            "contasSmtp" => $contasSmtp // Retorna as contas SMTP diretamente
        ]);
    } else {
        echo json_encode([
            "status" => "expirado",
            "mensagem" => "Acesso expirado. Entre em contato para renovação."
        ]);
    }
} else {
    echo json_encode([
        "status" => "erro",
        "mensagem" => "Usuário ou senha inválidos."
    ]);
}
?>
