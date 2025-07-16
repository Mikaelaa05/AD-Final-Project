<?php
/**
 * Frontend Test Page
 * This page bypasses authentication and database requirements
 * Use this for testing frontend components and functionality
 */

// Include only the essential bootstrap (no database handlers)
require_once __DIR__ . '/../../bootstrap.php';

// Mock user data for testing (no database required)
$mockUser = [
    'id' => 1,
    'first_name' => 'Test',
    'last_name' => 'User',
    'email' => 'test@example.com',
    'role' => 'developer'
];

// Set up mock session for testing
if (!session_id()) {
    session_start();
}
$_SESSION['user'] = $mockUser;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frontend Test Page</title>
    <!-- Include your CSS files -->
    <link rel="stylesheet" href="/assets/css/example.css">
    <style>
        /* Quick test styles */
        .test-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 2px dashed #ccc;
            border-radius: 8px;
        }

        .test-section h2 {
            color: #007bff;
            margin-top: 0;
        }

        .mock-data {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
        }

        .component-test {
            background-color: #e8f4fd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <!-- Include navbar component -->
    <?php include BASE_PATH . '/components/navbar.component.php'; ?>

    <div class="test-container">
        <h1>🧪 Frontend Test Environment</h1>
        <p><strong>Note:</strong> This page bypasses authentication and database requirements for frontend testing.</p>

        <div class="test-section">
            <h2>📊 Mock User Data</h2>
            <div class="mock-data">
                <pre><?php print_r($mockUser); ?></pre>
            </div>
        </div>

        <div class="test-section">
            <h2>🎨 Component Testing Area</h2>
            <p>Test your components here without database dependencies:</p>

            <div class="component-test">
                <h3>Sample Component Test</h3>
                <p>Welcome, <?= htmlspecialchars($mockUser['first_name']) ?>
                    <?= htmlspecialchars($mockUser['last_name']) ?>!</p>
                <p>Your role: <strong><?= htmlspecialchars($mockUser['role']) ?></strong></p>
                <p>Email: <?= htmlspecialchars($mockUser['email']) ?></p>
            </div>

            <!-- Add your components here for testing -->
            <?php
            // Example: Include a component if it exists
            $testComponent = BASE_PATH . '/components/componentGroup/example.component.php';
            if (file_exists($testComponent)) {
                echo '<div class="component-test">';
                echo '<h3>Example Component:</h3>';
                include $testComponent;
                echo '</div>';
            }
            ?>
        </div>

        <div class="test-section">
            <h2>📝 Form Testing</h2>
            <form id="test-form" onsubmit="return false;">
                <div style="margin-bottom: 15px;">
                    <label for="test-input">Test Input:</label><br>
                    <input type="text" id="test-input" name="test-input" placeholder="Enter test data">
                </div>
                <div style="margin-bottom: 15px;">
                    <label for="test-select">Test Select:</label><br>
                    <select id="test-select" name="test-select">
                        <option value="">Choose option...</option>
                        <option value="option1">Option 1</option>
                        <option value="option2">Option 2</option>
                        <option value="option3">Option 3</option>
                    </select>
                </div>
                <button type="submit">Test Submit</button>
                <button type="button" onclick="clearForm()">Clear</button>
            </form>
        </div>

        <div class="test-section">
            <h2>📱 JavaScript Testing</h2>
            <button onclick="testAlert()">Test Alert</button>
            <button onclick="testConsole()">Test Console Log</button>
            <button onclick="toggleVisibility()">Toggle Content</button>

            <div id="toggle-content" style="margin-top: 15px; padding: 10px; background-color: #fff3cd; display: none;">
                <p>This content can be toggled for testing show/hide functionality!</p>
            </div>
        </div>

        <div class="test-section">
            <h2>🎯 Mock API Response Testing</h2>
            <button onclick="mockApiCall()">Simulate API Call</button>
            <div id="api-response" style="margin-top: 15px;"></div>
        </div>
    </div>

    <!-- Include footer component -->
    <?php include BASE_PATH . '/components/footer.component.php'; ?>

    <!-- Include your JS files -->
    <script src="/assets/js/example.js"></script>

    <!-- Test JavaScript functions -->
    <script>
        function testAlert() {
            alert('Test alert is working!');
        }

        function testConsole() {
            console.log('Test console log is working!', {
                timestamp: new Date().toISOString(),
                user: <?= json_encode($mockUser) ?>
            });
            alert('Check the browser console (F12) for the log message!');
        }

        function toggleVisibility() {
            const content = document.getElementById('toggle-content');
            content.style.display = content.style.display === 'none' ? 'block' : 'none';
        }

        function clearForm() {
            document.getElementById('test-form').reset();
        }

        function mockApiCall() {
            const responseDiv = document.getElementById('api-response');
            responseDiv.innerHTML = '<p>Loading...</p>';

            // Simulate API delay
            setTimeout(() => {
                const mockResponse = {
                    status: 'success',
                    data: {
                        message: 'Mock API response successful!',
                        timestamp: new Date().toISOString(),
                        user_id: <?= $mockUser['id'] ?>
                    }
                };

                responseDiv.innerHTML = `
                    <div class="mock-data">
                        <strong>Mock API Response:</strong><br>
                        <pre>${JSON.stringify(mockResponse, null, 2)}</pre>
                    </div>
                `;
            }, 1000);
        }

        // Auto-load test on page ready
        document.addEventListener('DOMContentLoaded', function () {
            console.log('Frontend test page loaded successfully!');
            console.log('Mock user data:', <?= json_encode($mockUser) ?>);
        });
    </script>
</body>

</html>