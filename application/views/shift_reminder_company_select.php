<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Company - Shift Reminders</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 100%;
            padding: 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 8px;
        }

        .header p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            background-color: white;
            cursor: pointer;
            color: #333;
            transition: border-color 0.3s;
        }

        select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        select option {
            padding: 8px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        button:active {
            transform: translateY(0);
        }

        .empty-state {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 12px;
        }

        .error {
            background-color: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Shift Reminder Dashboard</h1>
            <p>Select a company to continue</p>
        </div>

        <?php if (empty($companies)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <p>No companies available</p>
            </div>
        <?php else: ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="company_id">Company</label>
                    <select name="company_id" id="company_id" required autofocus>
                        <option value="">-- Select a Company --</option>
                        <?php foreach ($companies as $company): ?>
                            <option value="<?php echo (int)$company['id']; ?>">
                                <?php echo htmlspecialchars($company['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit">
                    Continue to Dashboard
                </button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        // Auto-focus the select field
        document.addEventListener('DOMContentLoaded', function() {
            var select = document.getElementById('company_id');
            if (select) {
                select.focus();
            }
        });

        // Handle form submission
        document.querySelector('form')?.addEventListener('submit', function(e) {
            var companySelect = document.getElementById('company_id');
            if (!companySelect.value) {
                e.preventDefault();
                alert('Please select a company');
                return false;
            }

            // Redirect to dashboard with selected company
            var code = '<?php echo isset($code) ? htmlspecialchars($code) : ''; ?>';
            window.location.href = '?code=' + encodeURIComponent(code) +
                                   '&company_id=' + encodeURIComponent(companySelect.value);
        });
    </script>
</body>
</html>
