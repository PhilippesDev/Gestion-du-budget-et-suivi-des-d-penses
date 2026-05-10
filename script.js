document.addEventListener('DOMContentLoaded', function () {
    var budgetForm = document.getElementById('budget-form');
    var expenseForm = document.getElementById('expense-form');

    if (budgetForm) {
        budgetForm.addEventListener('submit', function (e) {
            var amount = document.getElementById('budget_amount').value;
            if (Number(amount) <= 0) {
                e.preventDefault();
            }
        });
    }

    if (expenseForm) {
        expenseForm.addEventListener('submit', function (e) {
            var amount = document.getElementById('expense_amount').value;
            var description = document.getElementById('expense_description').value.trim();
            if (description == '' || Number(amount) <= 0) {
                e.preventDefault();
            }
        });
    }
});