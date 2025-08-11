<?php
/*
Coisas a melhorar
duplicação de codigo,
classe sobrecarregada
nomes em camelCase e nomes que fazem sentido,
try catch ao inves de string para erros,
separar os dados de entrada da logica q estão tudo junto e aproveitar a orientação a objeto
*/

session_start(); //Utilizado para se manter logado em um site 


class Tarefa
{
    //declaração das variaveis
    public int $id;
    public string $titulo;
    public string $descricao;
    public int $usuarioID;
    public string $status;
    public string $dataVencimento;

    //construtor
    public function __construct(int $id, string $titulo, string $descricao, int $usuarioID, string $status, string $dataVencimento)
    {
        //assosiação
        $this->id = $id;
        $this->titulo = $titulo;
        $this->descricao = $descricao;
        $this->usuarioID = $usuarioID;
        $this->status = $status;
        $this->dataVencimento = $dataVencimento;
    }

    // Conversão para Array
    public function converterArray(): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'usuarioID' => $this->usuarioID,
            'status' => $this->status,
            'dataVencimento' => $this->dataVencimento
        ];
    }
}
// simular o banco de dados e utilizado encapsulamento
class ArmazenarTarefas
{
    private array $tarefas = [];
    private int $contador = 1;

    public function __construct()
    {
        $this->tarefas = [
            1 => new Tarefa(1, 'Estudar PHP', 'Revisar conceitos básicos', 1, 'pendente', '2024-01-15'),
            2 => new Tarefa(2, 'Fazer compras', 'Ir ao supermercado', 1, 'concluida', '2024-01-14'),
            3 => new Tarefa(3, 'Exercitar-se', 'Academia às 18h', 2, 'pendente', '2024-01-16')
        ];
        $this->contador = 4;
    }

    public function encontrarTodasTarefas(): array
    {
        return $this->tarefas;
    }
    
    // Encontrar uma tarefa por ID.
    public function encontrarTarefaPorId(int $id): ?Tarefa
    {
        return $this->tarefas[$id] ?? null;
    }

    // Encontrar e filtrar tarefas.
    public function filtragemTarefas(int $usuarioID = null, string $status = null, string $procurar = null): array
    {
        $tarefasFiltradas = $this->tarefas;

        if ($usuarioID !== null) {
            $tarefasFiltradas = array_filter($tarefasFiltradas, fn($tarefa) => $tarefa->usuarioID === $usuarioID);
        }

        if ($status !== null) {
            $tarefasFiltradas = array_filter($tarefasFiltradas, fn($tarefa) => $tarefa->status === $status);
        }

        if ($procurar !== null) {
            $tarefasFiltradas = array_filter($tarefasFiltradas, fn($tarefa) =>
                stripos($tarefa->titulo, $procurar) !== false || stripos($tarefa->descricao, $procurar) !== false
            );
        }

        return array_values($tarefasFiltradas);
    }

    public function salvarTarefa(Tarefa $tarefa): void
    {
        if ($tarefa->id === 0) {
            $tarefa->id = $this->contador++;
        }
        $this->tarefas[$tarefa->id] = $tarefa;
    }

    public function deletarTarefa(int $id): bool
    {
        if (isset($this->tarefas[$id])) {
            unset($this->tarefas[$id]);
            return true;
        }
        return false;
    }
}

// Validação de dados separado em uma classe a tal do white box
class ValidarTarefas
{
    public function validar(array $data): void
    {
        $titulo = trim($data['titulo'] ?? '');
        $descricao = trim($data['descricao'] ?? '');
        $usuarioID = $data['usuarioID'] ?? 0;
        $dataVencimento = trim($data['dataVencimento'] ?? '');

        if (empty($titulo) || strlen($titulo) < 3 || strlen($titulo) > 100 || is_numeric($titulo)) {
            throw new InvalidArgumentException("Título Inválido. Deve ter entre 3 e 100 caracteres e não ser um número.");
        }
        if (empty($descricao) || strlen($descricao) < 5 || strlen($descricao) > 500) {
            throw new InvalidArgumentException("Descrição Inválida. Deve ter entre 5 e 500 caracteres.");
        }
        if (!is_numeric($usuarioID) || $usuarioID <= 0) {
            throw new InvalidArgumentException("ID de usuário é inválido. Deve ser um número positivo.");
        }
        if (empty($dataVencimento) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataVencimento) || strtotime($dataVencimento) < strtotime(date('Y-m-d'))) {
            throw new InvalidArgumentException("Data de vencimento está Inválida. Formato YYYY-MM-DD e não pode ser no passado.");
        }
    }
}

//classe de como deve funcionar o sistema e executar as tarefas
class GerenciadorDeTarefas
{
    private ArmazenarTarefas $repositorio;
    private ValidarTarefas $validador;

    public function __construct(ArmazenarTarefas $repositorio, ValidarTarefas $validador)
    {
        $this->repositorio = $repositorio;
        $this->validador = $validador;
    }

    public function criar(array $dados): Tarefa
    {
        $this->validador->validar($dados);

        // 2. Cria um novo objeto Tarefa.
        $novaTarefa = new Tarefa(
            0, // 
            $dados['titulo'],
            $dados['descricao'],
            $dados['usuarioID'],
            'pendente',
            $dados['dataVencimento']
        );

        // 3. Salva a tarefa no repositório.
        $this->repositorio->salvarTarefa($novaTarefa);
        
        return $novaTarefa;
    }

    public function atualizar(int $id, array $dados): Tarefa
    {
        // 1. Encontra a tarefa existente.
        $tarefa = $this->repositorio->encontrarTarefaPorId($id);
        if (!$tarefa) {
            throw new InvalidArgumentException("Tarefa com ID $id não encontrada.");
        }

        // 2. Valida os novos dados.
        $this->validador->validar($dados);

        // 3. Atualiza as propriedades da tarefa.
        $tarefa->titulo = $dados['titulo'];
        $tarefa->descricao = $dados['descricao'];
        $tarefa->dataVencimento = $dados['dataVencimento'];

        // 4. Salva a tarefa atualizada.
        $this->repositorio->salvarTarefa($tarefa);

        return $tarefa;
    }

    public function concluir(int $id): Tarefa
    {
        $tarefa = $this->repositorio->encontrarTarefaPorId($id);
        if (!$tarefa) {
            throw new InvalidArgumentException("Tarefa com ID $id não encontrada.");
        }
        if ($tarefa->status === 'concluida') {
            throw new InvalidArgumentException("Tarefa já está concluída.");
        }
        
        $tarefa->status = 'concluida';
        $this->repositorio->salvarTarefa($tarefa);
        
        return $tarefa;
    }

    public function deletar(int $id): void
    {
        if (!$this->repositorio->deletarTarefa($id)) {
            throw new InvalidArgumentException("Não foi possível deletar a tarefa com ID $id.");
        }
    }

    public function encontrarTodas(): array
    {
        return $this->repositorio->encontrarTodasTarefas();
    }
}

// Semente

echo "<h1>Sistema de Gerenciamento de Tarefas</h1>";

$repositorioTarefas = new ArmazenarTarefas();
$validadorTarefas = new ValidarTarefas();
$gerenciador = new GerenciadorDeTarefas($repositorioTarefas, $validadorTarefas);

// Adicionando tarefa
echo "<h2>Criando uma nova tarefa</h2>";
$novosDados = [
    'titulo' => 'Aprender Design Patterns',
    'descricao' => 'Estudar os padrões de projeto de GoF',
    'usuarioID' => 1,
    'dataVencimento' => '2025-09-01'
];

try {
    $novaTarefa = $gerenciador->criar($novosDados);
    echo "<p>Tarefa criada com sucesso! ID: {$novaTarefa->id}</p>";
} catch (InvalidArgumentException $e) {
    echo "<p style='color: red;'>Erro ao criar tarefa: " . $e->getMessage() . "</p>";
}

// Listando todas as tarefas
echo "<h2>Todas as Tarefas</h2>";
$todasAsTarefas = $gerenciador->encontrarTodas();
foreach ($todasAsTarefas as $tarefa) {
    echo "<p>ID: {$tarefa->id} | Título: {$tarefa->titulo} | Status: {$tarefa->status}</p>";
}

// Concluindo uma tarefa
echo "<h2>Concluindo uma tarefa</h2>";
try {
    $gerenciador->concluir(1);
    echo "<p>Tarefa com ID 1 foi concluída com sucesso!</p>";
} catch (InvalidArgumentException $e) {
    echo "<p style='color: red;'>Erro ao concluir tarefa: " . $e->getMessage() . "</p>";
}


?>