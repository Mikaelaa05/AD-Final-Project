<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frontend Test Page</title>
    <link rel="stylesheet" href="/assets/css/example.css">
    <link rel="stylesheet" href="/pages/TestPage/assets/css/test.css">
</head>

<body class="test-page">
    <div class="test-container">
        <h1>🧪 Frontend Test Environment</h1>
        <p><strong>Note:</strong> This page bypasses authentication and database requirements for frontend testing.</p>

        <div class="test-section">
            <h2>📊 Mock User Data</h2>
            <div class="mock-data">
                <strong>Current Mock User:</strong><br>
                <?php
                if (isset($mockUser)) {
                    echo htmlspecialchars(print_r($mockUser, true));
                } else {
                    echo "Mock user data not available";
                }
                ?>
            </div>
        </div>

        <div class="test-section">
            <h2>🎨 Component Testing Area</h2>
            <p>Test your components here without database dependencies:</p>

            <div class="component-test">
                <h3>Welcome Message Test</h3>
                <?php if (isset($mockUser)): ?>
                    <p>Welcome, <?= htmlEscape($mockUser['first_name']) ?>     <?= htmlEscape($mockUser['last_name']) ?>!</p>
                    <p>Your role: <strong><?= htmlEscape($mockUser['role']) ?></strong></p>
                    <p>Email: <?= htmlEscape($mockUser['email']) ?></p>
                <?php else: ?>
                    <p>Mock user data not available for testing</p>
                <?php endif; ?>
            </div>

            <div class="component-test">
                <h3>Component Testing Area</h3>
                <p>Add your components below this section for testing:</p>
                <?php
                // Example: Include a component if it exists
                $testComponent = BASE_PATH . '/components/componentGroup/example.component.php';
                if (file_exists($testComponent)) {
                    echo '<div class="component-wrapper">';
                    include $testComponent;
                    echo '</div>';
                }
                ?>
            </div>
        </div>

        <div class="test-section">
            <h2>📝 Form Testing</h2>
            <form id="test-form">
                <div class="form-group">
                    <label for="test-input">Test Input:</label>
                    <input type="text" id="test-input" name="test-input" placeholder="Enter test data">
                </div>
                <div class="form-group">
                    <label for="test-select">Test Select:</label>
                    <select id="test-select" name="test-select">
                        <option value="">Choose option...</option>
                        <option value="option1">Option 1</option>
                        <option value="option2">Option 2</option>
                        <option value="option3">Option 3</option>
                    </select>
                </div>
                <button type="submit" class="btn-test">Test Submit</button>
                <button type="button" class="btn-test btn-secondary" onclick="clearForm()">Clear</button>
            </form>
        </div>

        <div class="test-section">
            <h2>📱 JavaScript Testing</h2>
            <button class="btn-test" onclick="testAlert()">Test Alert</button>
            <button class="btn-test" onclick="testConsole()">Test Console Log</button>
            <button class="btn-test" onclick="toggleVisibility()">Toggle Content</button>

            <div id="toggle-content" style="margin-top: 15px; padding: 10px; background-color: #fff3cd; display: none;">
                <p>This content can be toggled for testing show/hide functionality!</p>
            </div>
        </div>

        <div class="test-section">
            <h2>🎯 Mock API Response Testing</h2>
            <button class="btn-test" onclick="mockApiCall()">Simulate API Call</button>
            <div id="api-response" style="margin-top: 15px;"></div>
        </div>

        <div class="test-section">
            <h2>⌨️ Keyboard Shortcuts</h2>
            <p><strong>Available shortcuts:</strong></p>
            <ul>
                <li><kbd>Ctrl + T</kbd> - Toggle test mode (visual debugging)</li>
                <li><kbd>Ctrl + Shift + R</kbd> - Reload test data</li>
                <li><kbd>F12</kbd> - Open browser dev tools</li>
            </ul>
        </div>

        <div class="test-section">
            <h2>📊 Session Data</h2>
            <div class="mock-data">
                <strong>Current Session:</strong><br>
                <?php
                if (isset($_SESSION)) {
                    echo htmlspecialchars(print_r($_SESSION, true));
                } else {
                    echo "No session data available";
                }
                ?>
            </div>
        </div>
    </div>

    <script src="/assets/js/example.js"></script>
    <script src="/pages/TestPage/assets/js/test.js"></script>
</body>

</html>