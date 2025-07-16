/**
 * Frontend Test Page JavaScript
 * Utility functions for testing frontend functionality
 */

// Test page utilities
const TestPage = {
  // Initialize test page
  init: function () {
    console.log("🧪 Test Page initialized");
    this.setupEventListeners();
    this.loadTestData();
  },

  // Set up event listeners
  setupEventListeners: function () {
    // Auto-submit form handler for testing
    const testForm = document.getElementById("test-form");
    if (testForm) {
      testForm.addEventListener("submit", this.handleTestFormSubmit.bind(this));
    }

    // Add keyboard shortcuts for testing
    document.addEventListener(
      "keydown",
      this.handleKeyboardShortcuts.bind(this)
    );
  },

  // Handle test form submission
  handleTestFormSubmit: function (e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());

    console.log("📝 Test form submitted:", data);
    this.showAlert("Form data logged to console!", "success");

    // Simulate form processing
    this.simulateFormProcessing(data);
  },

  // Simulate form processing with loading state
  simulateFormProcessing: function (data) {
    const submitBtn = document.querySelector(
      '#test-form button[type="submit"]'
    );
    const originalText = submitBtn.textContent;

    submitBtn.textContent = "Processing...";
    submitBtn.disabled = true;

    setTimeout(() => {
      submitBtn.textContent = originalText;
      submitBtn.disabled = false;
      this.showAlert("Form processing complete!", "success");
    }, 2000);
  },

  // Show alert messages
  showAlert: function (message, type = "info") {
    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    alertDiv.style.position = "fixed";
    alertDiv.style.top = "20px";
    alertDiv.style.right = "20px";
    alertDiv.style.zIndex = "9999";
    alertDiv.style.minWidth = "250px";

    document.body.appendChild(alertDiv);

    // Auto remove after 3 seconds
    setTimeout(() => {
      if (alertDiv.parentNode) {
        alertDiv.parentNode.removeChild(alertDiv);
      }
    }, 3000);
  },

  // Load test data
  loadTestData: function () {
    const testData = {
      projects: [
        { id: 1, name: "Test Project 1", status: "active" },
        { id: 2, name: "Test Project 2", status: "completed" },
        { id: 3, name: "Test Project 3", status: "pending" },
      ],
      tasks: [
        { id: 1, title: "Test Task 1", project_id: 1, status: "todo" },
        { id: 2, title: "Test Task 2", project_id: 1, status: "in_progress" },
        { id: 3, title: "Test Task 3", project_id: 2, status: "done" },
      ],
    };

    console.log("📊 Test data loaded:", testData);
    window.testData = testData; // Make available globally for testing
  },

  // Keyboard shortcuts for testing
  handleKeyboardShortcuts: function (e) {
    // Ctrl/Cmd + T = Toggle test mode
    if ((e.ctrlKey || e.metaKey) && e.key === "t") {
      e.preventDefault();
      this.toggleTestMode();
    }

    // Ctrl/Cmd + R = Reload test data
    if ((e.ctrlKey || e.metaKey) && e.key === "r" && e.shiftKey) {
      e.preventDefault();
      this.loadTestData();
      this.showAlert("Test data reloaded!", "info");
    }
  },

  // Toggle test mode visual indicators
  toggleTestMode: function () {
    document.body.classList.toggle("test-mode");
    const isTestMode = document.body.classList.contains("test-mode");

    if (isTestMode) {
      this.showAlert("Test mode enabled! Press Ctrl+T to disable.", "warning");
      // Add test mode styles
      this.addTestModeStyles();
    } else {
      this.showAlert("Test mode disabled!", "info");
      this.removeTestModeStyles();
    }
  },

  // Add visual indicators for test mode
  addTestModeStyles: function () {
    if (!document.getElementById("test-mode-styles")) {
      const style = document.createElement("style");
      style.id = "test-mode-styles";
      style.textContent = `
                .test-mode * {
                    outline: 1px dashed rgba(255, 0, 0, 0.3) !important;
                }
                .test-mode::before {
                    content: "🧪 TEST MODE";
                    position: fixed;
                    top: 0;
                    left: 50%;
                    transform: translateX(-50%);
                    background: #ff6b6b;
                    color: white;
                    padding: 5px 15px;
                    z-index: 10000;
                    font-weight: bold;
                    border-radius: 0 0 5px 5px;
                }
            `;
      document.head.appendChild(style);
    }
  },

  // Remove test mode styles
  removeTestModeStyles: function () {
    const style = document.getElementById("test-mode-styles");
    if (style) {
      style.remove();
    }
  },

  // Utility function to mock API calls
  mockApiCall: function (endpoint, data = {}) {
    return new Promise((resolve) => {
      console.log(`🌐 Mock API call to: ${endpoint}`, data);

      setTimeout(() => {
        const mockResponse = {
          status: "success",
          endpoint: endpoint,
          data: data,
          timestamp: new Date().toISOString(),
          mock: true,
        };

        console.log("📡 Mock API response:", mockResponse);
        resolve(mockResponse);
      }, Math.random() * 1000 + 500); // Random delay 500-1500ms
    });
  },

  // Component testing utilities
  testComponent: function (componentName, props = {}) {
    console.log(`🔧 Testing component: ${componentName}`, props);
    this.showAlert(`Testing ${componentName} component`, "info");

    // You can add specific component testing logic here
    return {
      component: componentName,
      props: props,
      tested: true,
      timestamp: new Date().toISOString(),
    };
  },
};

// Global utility functions for easy testing
window.testAlert = function () {
  TestPage.showAlert("Test alert is working!", "success");
};

window.testConsole = function () {
  console.log("🔍 Console test successful!", {
    timestamp: new Date().toISOString(),
    userAgent: navigator.userAgent,
    testData: window.testData,
  });
  TestPage.showAlert("Check console for test output!", "info");
};

window.toggleVisibility = function () {
  const content = document.getElementById("toggle-content");
  if (content) {
    content.style.display = content.style.display === "none" ? "block" : "none";
    TestPage.showAlert("Visibility toggled!", "info");
  }
};

window.mockApiCall = function () {
  const responseDiv = document.getElementById("api-response");
  if (responseDiv) {
    responseDiv.innerHTML = "<p>⏳ Loading...</p>";

    TestPage.mockApiCall("/api/test", { test: true }).then((response) => {
      responseDiv.innerHTML = `
                    <div class="mock-data">
                        <strong>✅ Mock API Response:</strong><br>
                        <pre>${JSON.stringify(response, null, 2)}</pre>
                    </div>
                `;
    });
  }
};

window.clearForm = function () {
  const form = document.getElementById("test-form");
  if (form) {
    form.reset();
    TestPage.showAlert("Form cleared!", "info");
  }
};

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
  TestPage.init();
});

// Export for module use if needed
if (typeof module !== "undefined" && module.exports) {
  module.exports = TestPage;
}
