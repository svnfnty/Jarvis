
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline AI</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style> 
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
            background-color: #f7f7f7;
        }

        /* Left Sidebar */
        .sidebar {
            width: 260px;
            background-color: #202123;
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
            transition: width 0.3s ease;
        }

        .sidebar.collapsed {
            width: 60px;
        }

        .sidebar.collapsed h2,
        .sidebar.collapsed .history h3,
        .sidebar.collapsed .history ul,
        .sidebar.collapsed button span {
            display: none;
        }

        .sidebar h2 {
            margin: 0 0 20px;
            font-size: 1.5rem;
            text-align: center;
        }

        .sidebar button {
            background-color: #343541;
            color: white;
            border: 1px solid #444;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            cursor: pointer;
            text-align: left;
            font-size: 1rem;
            display: flex;
            align-items: center;
        }

        .sidebar button i {
            margin-right: 10px;
        }

        .sidebar button:hover {
            background-color: #444;
        }

        .sidebar .history {
            flex: 1;
            overflow-y: auto;
            margin-top: 20px;
        }

        .sidebar .history h3 {
            margin: 0 0 10px;
            font-size: 1rem;
            color: #888;
        }

        .sidebar .history ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar .history ul li {
            margin: 10px 0;
        }

        .sidebar .history ul li a {
            color: white;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .sidebar .history ul li a:hover {
            color: #007bff;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            background-color: white;
        }

        /* Header */
        .header {
            background-color: #ffffff;
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            margin: 0;
            font-size: 1.5rem;
            color: #333;
        }

        .header .profile {
            display: flex;
            align-items: center;
        }

        .header .profile img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .header .profile span {
            font-size: 1rem;
            color: #333;
        }

        .header button {
            background: none;
            border: none;
            color: #333;
            font-size: 1.2rem;
            cursor: pointer;
        }

        /* Chat Box */
        .chat-box {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background-color: #f7f7f7;
        }

        .chat-message {
            padding: 10px 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            max-width: 70%;
            word-wrap: break-word;
        }

        .user-message {
            background-color: #007bff;
            color: white;
            margin-left: auto;
            text-align: right;
        }

        .ai-message {
            background-color: #e1e1e1;
            margin-right: auto;
        }

        /* Input Container */
        .input-container {
            display: flex;
            padding: 15px;
            background-color: white;
            border-top: 1px solid #ddd;
        }

        .input-container input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 10px;
        }

        .input-container button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .input-container button:hover {
            background-color: #0056b3;
        }

        /* Typing Indicator */
        .typing-indicator {
            background-color: #e1e1e1;
            padding: 10px 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            max-width: 70%;
            font-style: italic;
            color: #555;
        }

        /* Code Block */
        .code-block {
            background-color: #2d2d2d;
            color: #9cdcfe;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-family: "Courier New", monospace;
            font-size: 14px;
            position: relative;
        }

        .code-block pre {
            margin: 0;
            white-space: pre-wrap; /* Preserve formatting */
        }

        .code-block button {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .code-block button:hover {
            background-color: #45a049;
        }

        /* Console Output */
        .console-output {
            background-color: #1e1e1e;
            color: #4ec9b0;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-family: "Courier New", monospace;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <!-- Left Sidebar -->
    <div class="sidebar" id="sidebar">
        <h2>Mr. Coder</h2>
        <button id="newChat"><i class="fas fa-plus"></i><span> New Chat</span></button>
        <div class="history" id="history">
            <h3>Today</h3>
            <ul id="todayHistory"></ul>
            <h3>Yesterday</h3>
            <ul id="yesterdayHistory"></ul>
            <h3>Previous</h3>
            <ul id="previousHistory"></ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <button id="toggleSidebar"><i class="fas fa-bars"></i></button>
            <h1>Chat Interface</h1>
            <div class="profile">
                <img src="user.jpg" alt="Profile">
                <span>My Profile</span>
            </div>
        </div>

        <!-- Chat Box -->
        <div class="chat-box" id="chatBox">
            <div class="chat-message ai-message">Hello! How can I assist you today?</div>
        </div>

        <!-- Input Container -->
        <div class="input-container">
            <input type="text" id="userInput" placeholder="Type a message...">
            <button id="sendMessage">Send</button>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const chatBox = document.getElementById("chatBox");
            const userInput = document.getElementById("userInput");
            const sendMessageBtn = document.getElementById("sendMessage");
            const sidebar = document.getElementById("sidebar");
            const toggleSidebarBtn = document.getElementById("toggleSidebar");
            const newChatBtn = document.getElementById("newChat");
            const todayHistory = document.getElementById("todayHistory");
            const yesterdayHistory = document.getElementById("yesterdayHistory");
            const previousHistory = document.getElementById("previousHistory");

            let chatHistory = []; // Store all chat sessions
            let currentChat = []; // Store messages for the current chat
            let notificationCount = 1; // Notification count
            let isMaximized = false; // Track if the chat window is maximized

           function appendMessage(text, sender) {
    const messageDiv = document.createElement("div");
    messageDiv.classList.add("chat-message", sender === "user" ? "user-message" : "ai-message");
    chatBox.appendChild(messageDiv);
    chatBox.scrollTop = chatBox.scrollHeight;

    if (sender === "ai") {
        if (text.includes("```")) {
            const parts = text.split("```");
            let index = 0;

            function typeNextPart() {
                if (index < parts.length) {
                    if (index % 2 === 1) {
                        // Code block part
                        const codeContainer = document.createElement("div");
                        codeContainer.classList.add("code-block");
                        
                        const pre = document.createElement("pre");
                        const code = document.createElement("code");
                        code.textContent = parts[index].trim();
                        
                        pre.appendChild(code);
                        codeContainer.appendChild(pre);

                        // Copy button
                        const copyButton = document.createElement("button");
                        copyButton.textContent = "Copy Code";
                        copyButton.onclick = function () {
                            navigator.clipboard.writeText(code.textContent);
                        };
                        codeContainer.appendChild(copyButton);

                        messageDiv.appendChild(codeContainer);
                        index++;
                        setTimeout(typeNextPart, 500);
                    } else {
                        // Normal text part with typing effect
                        const textSpan = document.createElement("span");
                        messageDiv.appendChild(textSpan);
                        typeTextEffect(textSpan, parts[index], () => {
                            index++;
                            typeNextPart();
                        });
                    }
                }
            }
            typeNextPart();
        } else {
            // If no code block, type text normally
            const textSpan = document.createElement("span");
            messageDiv.appendChild(textSpan);
            typeTextEffect(textSpan, text);
        }
    } else {
        messageDiv.textContent = text;
    }
}

// Function to create a typing effect
function typeTextEffect(element, text, callback) {
    let i = 0;
    function type() {
        if (i < text.length) {
            element.textContent += text[i]; // Append one character at a time
            i++;
            setTimeout(type, 15); // Typing speed
        } else if (callback) {
            callback(); // Continue with callback function once typing is done
        }
    }
    type();
}

            // Function to show typing indicator
            function showTypingIndicator() {
                const typingDiv = document.createElement("div");
                typingDiv.classList.add("typing-indicator");
                typingDiv.id = "typingIndicator";
                typingDiv.textContent = "AI is thinking...";
                chatBox.appendChild(typingDiv);
                chatBox.scrollTop = chatBox.scrollHeight;
            }

            // Function to hide typing indicator
            function hideTypingIndicator() {
                const typingDiv = document.getElementById("typingIndicator");
                if (typingDiv) {
                    typingDiv.remove();
                }
            }

            // Function to send a message
            function sendMessage() {
                const message = userInput.value.trim();
                if (!message) return;

                appendMessage(message, "user");
                currentChat.push({ sender: "user", message: message, timestamp: new Date() });
                userInput.value = "";
                showTypingIndicator();

                fetch("chat.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `message=${encodeURIComponent(message)}`
                })
                .then(response => response.json())
                .then(data => {
                    hideTypingIndicator();
                    let aiMessage = data.ai_response?.[0]?.message || "No valid response received.";
                    appendMessage(aiMessage, "ai");
                    currentChat.push({ sender: "ai", message: aiMessage, timestamp: new Date() });

                    if (document.getElementById("chatWindow") && document.getElementById("chatWindow").style.display === "none") {
                        incrementNotificationCount();
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    hideTypingIndicator();
                });
            }

            // Function to save current chat to history
            function saveChatToHistory() {
                if (currentChat.length > 0) {
                    const chatSession = {
                        date: new Date(),
                        messages: [...currentChat]
                    };
                    chatHistory.push(chatSession);
                    updateHistoryTab();
                    currentChat = []; // Clear current chat
                }
            }

            // Function to update the history tab
            function updateHistoryTab() {
                todayHistory.innerHTML = "";
                yesterdayHistory.innerHTML = "";
                previousHistory.innerHTML = "";

                const now = new Date();
                chatHistory.forEach((session, index) => {
                    const sessionDate = new Date(session.date);
                    const diffTime = Math.abs(now - sessionDate);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                    const li = document.createElement("li");
                    li.innerHTML = `<a href="#" onclick="loadChatSession(${index})">Chat ${index + 1}</a>`;

                    if (diffDays === 0) {
                        todayHistory.appendChild(li);
                    } else if (diffDays === 1) {
                        yesterdayHistory.appendChild(li);
                    } else {
                        previousHistory.appendChild(li);
                    }
                });
            }

            // Function to load a chat session from history
            window.loadChatSession = function (index) {
                const session = chatHistory[index];
                chatBox.innerHTML = ""; // Clear current chat
                session.messages.forEach(msg => {
                    appendMessage(msg.message, msg.sender);
                });
            };

            // Send message on button click
            sendMessageBtn.addEventListener("click", sendMessage);

            // Send message on Enter key press
            userInput.addEventListener("keypress", function (e) {
                if (e.key === "Enter") {
                    sendMessage();
                }
            });

            // New Chat button
            newChatBtn.addEventListener("click", function () {
                saveChatToHistory(); // Save current chat to history
                chatBox.innerHTML = ""; // Clear chat box
                appendMessage("Hello! How can I assist you today?", "ai"); // Add initial AI message
            });

            // Toggle Sidebar
            toggleSidebarBtn.addEventListener("click", function () {
                sidebar.classList.toggle("collapsed");
            });

            // Notification Count
            function incrementNotificationCount() {
                notificationCount++;
                const notificationCountElement = document.getElementById("notificationCount");
                if (notificationCountElement) {
                    notificationCountElement.textContent = notificationCount;
                }
            }

            function resetNotificationCount() {
                notificationCount = 0;
                const notificationCountElement = document.getElementById("notificationCount");
                if (notificationCountElement) {
                    notificationCountElement.textContent = notificationCount;
                }
            }
        });
    </script>
</body>
</html>