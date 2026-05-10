<?php
require __DIR__ . '/db.php';

$pdo->exec('CREATE TABLE IF NOT EXISTS budgets(id INT PRIMARY KEY, amount DECIMAL(12,2) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

$pdo->exec('CREATE TABLE IF NOT EXISTS expenses(id INT AUTO_INCREMENT PRIMARY KEY, description VARCHAR(255) NOT NULL, category VARCHAR(100) NOT NULL, amount DECIMAL(12,2) NOT NULL, date DATE NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

if($_SERVER['REQUEST_METHOD'] === 'POST'){

if(isset($_POST['budget_amount'])){

    $amount = floatval($_POST['budget_amount']);
    
        if($amount > 0){
            $stmt = $pdo->prepare('INSERT INTO budgets (id, amount) VALUES (1, ?) ON DUPLICATE KEY UPDATE amount = VALUES(amount)');
            $stmt->execute([$amount]);
        }
    }
    if(isset($_POST['expense_description'], $_POST['expense_category'], $_POST['expense_amount'], $_POST['expense_date'])){
       
        $desc = trim($_POST['expense_description']);
        $category = trim($_POST['expense_category']);
        $amount = floatval($_POST['expense_amount']);
        $date = $_POST['expense_date'];
        
        if($desc !== '' && $category !== '' && $amount > 0 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)){
            $stmt = $pdo->prepare('INSERT INTO expenses(description, category, amount, date) VALUES (?, ?, ?, ?)');
            $stmt->execute([$desc, $category, $amount, $date]);
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
if(isset($_GET['delete']) && ctype_digit($_GET['delete'])){
    $stmt = $pdo->prepare('DELETE FROM expenses WHERE id = ?');
    $stmt->execute([$_GET['delete']]);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
$budget = 0;

$stmt = $pdo->query('SELECT amount FROM budgets WHERE id = 1');

$row = $stmt->fetch();

if($row){$budget = (float)$row['amount'];}

$stmt = $pdo->query('SELECT SUM(amount) AS total FROM expenses');

$totalExpenses = (float)($stmt->fetchColumn() ?: 0);

$remaining = $budget - $totalExpenses;

$stmt = $pdo->query('SELECT * FROM expenses ORDER BY date DESC, id DESC');

$expenses = $stmt->fetchAll();

function formatAmount($value){return number_format($value,2,',',' ');}?>


<!DOCTYPE html>
<html lang="fr">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestion de budget</title>
        <link rel="stylesheet" href="styles.css">
</head>
<body>
        <div class="container">
        <header>
        <h1>Gestion de budget et dépenses</h1>
        <p>Suivez votre budget, enregistrez vos dépenses et visualisez l&apos;état financier en temps réel.</p>
        </header>
        <div class="summary">
            <div class="card">
                <h2>Budget</h2>
                <p><strong><?= formatAmount($budget) ?> €</strong></p>
            </div>
            <div class="card">
                <h2>Dépenses totales</h2>
                <p><strong><?= formatAmount($totalExpenses) ?> €</strong></p>
            </div>
            <div class="card">
                <h2>Reste</h2>
                <p><strong><?= formatAmount($remaining) ?> €</strong></p>
            </div>
        </div>
        <div class="grid grid-3">
            <div class="card">
            <h2>Définir le budget</h2>
            <form id="budget-form" method="post">
                <div class="form-group">
                    <label for="budget_amount">Montant du budget</label>
                    <input id="budget_amount" name="budget_amount" type="number" step="0.01" min="0" value="<?= $budget ?>" required>
                </div>
                <button type="submit" class="button">Enregistrer</button>
            </form>
        </div>
        <div class="card">
        <h2>Ajouter une dépense</h2>
            <form id="expense-form" method="post">
            <div class="form-group">
                <label for="expense_description">Description</label>
                <input id="expense_description" name="expense_description" type="text" required>
            </div>
            <div class="form-group">
                <label for="expense_category">Catégorie</label>
            <select id="expense_category" name="expense_category" required>
                <option value="">Sélectionner</option>
                <option value="Alimentation">Alimentation</option>
                <option value="Transport">Transport</option>
                <option value="Logement">Logement</option>
                <option value="Loisirs">Loisirs</option>
                <option value="Autre">Autre</option>
            </select>
            </div>
            <div class="form-group">
                <label for="expense_amount">Montant</label>
                <input id="expense_amount" name="expense_amount" type="number" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label for="expense_date">Date</label>
                <input id="expense_date" name="expense_date" type="date" value="<?= date('Y-m-d') ?>" required>
            </div>
                <button type="submit" class="button">Ajouter</button>
            </form>
        </div>
        <div class="card">
        <h2>Conseils</h2>
        <p class="small-text">Maintenez votre budget à jour et enregistrez chaque dépense pour avoir une vision claire de vos finances.</p>
        </div>
        </div>
        <div class="card" style="margin-top:18px">
        <h2>Liste des dépenses</h2>
        <div class="table-wrapper">
                <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Catégorie</th>
                        <th>Date</th>
                        <th class="text-right">Montant</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($expenses)): ?>
                    <tr><td colspan="5" class="small-text">Aucune dépense enregistrée.</td></tr>
                    <?php else: foreach($expenses as $expense): ?>
                    <tr>
                        <td><?= htmlspecialchars($expense['description'],ENT_QUOTES,'UTF-8') ?></td>
                        <td><?= htmlspecialchars($expense['category'],ENT_QUOTES,'UTF-8') ?></td>
                        <td><?= htmlspecialchars($expense['date'],ENT_QUOTES,'UTF-8') ?></td>
                        <td class="text-right"><?= formatAmount($expense['amount']) ?> €</td>
                        <td class="actions"><a href="?delete=<?= $expense['id'] ?>">Supprimer</a></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
                </table>
        </div>
        </div>
        </div>
        <script src="script.js"></script>
</body>
</html>
