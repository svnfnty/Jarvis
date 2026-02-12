<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
 <style> 
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .chat-bubble {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 70px;
            height: 70px;
            background-color: #007bff;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            z-index: 1000;
        }
        .chat-bubble:hover {
            animation: shake 0.5s ease;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px) rotate(-5deg); }
            50% { transform: translateX(5px) rotate(5deg); }
            75% { transform: translateX(-5px) rotate(-5deg); }
        }
        .chat-bubble img {
            width: 60%;
            height: 60%;
            border-radius: 50%;
        }
        .notification {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #ff4757;
            color: white;
            font-size: 12px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
        }
        .chat-window {
            position: fixed;
            bottom: 100px;
            right: 20px;
            width: 350px;
            height: 400px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
            display: none;
            animation: slideIn 0.3s ease;
            transition: width 0.3s ease, height 0.3s ease;
            z-index: 1000;
        }
        .chat-window.maximized {
            width: 50%;
            height: 90%;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .chat-header {
            padding: 10px;
            background: #007bff;
            color: white;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .chat-header span {
            font-weight: bold;
        }
        .chat-header button {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 18px;
            margin-left: 10px;
        }
        .chat-box {
            height: calc(100% - 120px);
            overflow-y: auto;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            z-index: 1000;

        }
        .chat-message {
            padding: 8px 12px;
            border-radius: 10px;
            margin-bottom: 8px;
            max-width: 80%;
            word-wrap: break-word;
        }
        .user-message {
            background: #007bff;
            color: white;
            text-align: right;
            margin-left: auto;
        }
        .ai-message {
            background: #e1e1e1;
            margin-right: auto;
        }
        .typing-indicator {
            background: #e1e1e1;
            padding: 8px 12px;
            border-radius: 10px;
            margin-bottom: 8px;
            max-width: 80%;
            font-style: italic;
            color: #555;
        }
        .input-container {
            display: flex;
            padding: 10px;
        }
        .input-container input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .input-container button {
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 5px;
        }
        /* Floating Notification - Modern AI Design */
        .floating-notification {
            position: fixed;
            bottom: 100px;
            right: -350px; /* Start off-screen */
            background: rgba(30, 30, 30, 0.8); /* Dark glass effect */
            color: white;
            padding: 15px 25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            font-size: 16px;
            font-weight: bold;
            display: flex;
            align-items: center;
            backdrop-filter: blur(10px); /* Glass effect */
            border: 1px solid rgba(255, 255, 255, 0.2);
            opacity: 1;
            transform: translateX(0);
            transition: right 0.5s ease-in-out, opacity 0.5s ease-in-out;
            z-index: 1001;
        }

        /* AI Icon inside notification */
        .floating-notification img {
            width: 30px;
            height: 30px;
            margin-right: 10px;
        }

        /* Show notification smoothly */
        .floating-notification.show {
            right: 20px; /* Slide into view */
        }

        /* Hide notification smoothly */
        .floating-notification.hide {
            opacity: 0;
            right: -350px;
        }

        /* AI Glow Effect */
        @keyframes ai-glow {
            0% { box-shadow: 0 0 10px rgba(0, 174, 255, 0.5); }
            50% { box-shadow: 0 0 20px rgba(0, 174, 255, 0.8); }
            100% { box-shadow: 0 0 10px rgba(0, 174, 255, 0.5); }
        }

    </style>
</head>
<body>
    <!-- Chat Bubble -->
    <div class="chat-bubble">
        <img src="https://cdn-icons-png.flaticon.com/512/4712/4712035.png" alt="AI Icon">
        <div class="notification" id="notificationCount">1</div>
    </div>

    <!-- Chat Window -->
    <div class="chat-window" id="chatWindow">
        <div class="chat-header">
            <span>Vimsys AI Chatbot</span>
            <div>
                <button id="maximizeBtn">⬜</button>
                <button id="closeChat">×</button>
            </div>
        </div>
        <div class="chat-box" id="chatBox"></div>
        <div class="input-container">
            <input type="text" id="userInput" placeholder="Type a message...">
            <button id="sendMessage">Send</button>
        </div>
    </div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    let notificationCount = 1; // Initial notification count
    let isMaximized = false; // Track if the chat window is maximized

    function toggleChatWindow() {
        const chatWindow = document.getElementById("chatWindow");
        chatWindow.style.display = chatWindow.style.display === "none" ? "block" : "none";
        if (chatWindow.style.display === "block") {
            resetNotificationCount(); // Reset notification count when chat window is opened
        }
    }

    function toggleMaximize() {
        const chatWindow = document.getElementById("chatWindow");
        const maximizeBtn = document.getElementById("maximizeBtn");
        isMaximized = !isMaximized;
        chatWindow.classList.toggle("maximized", isMaximized);
        maximizeBtn.textContent = isMaximized ? "🗗" : "⬜"; // Change button icon
    }

    function appendMessage(text, sender) {
        const chatBox = document.getElementById("chatBox");
        const messageDiv = document.createElement("div");
        messageDiv.classList.add("chat-message", sender === "user" ? "user-message" : "ai-message");
        messageDiv.innerHTML = text;
        chatBox.appendChild(messageDiv);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function showTypingIndicator() {
        const chatBox = document.getElementById("chatBox");
        const typingDiv = document.createElement("div");
        typingDiv.classList.add("typing-indicator");
        typingDiv.id = "typingIndicator";
        typingDiv.textContent = "AI is thinking...";
        chatBox.appendChild(typingDiv);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function hideTypingIndicator() {
        const typingDiv = document.getElementById("typingIndicator");
        if (typingDiv) {
            typingDiv.remove();
        }
    }

    function sendMessage() {
        const userInput = document.getElementById("userInput").value.trim();
        if (!userInput) return;

        appendMessage(userInput, "user");
        document.getElementById("userInput").value = "";

        showTypingIndicator();

        fetch("Apichat.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `message=${encodeURIComponent(userInput)}`
        })
        .then(response => response.json())
        .then(data => {
            hideTypingIndicator();
            let aiMessage = data.ai_response?.[0]?.message || "No valid response received.";
            appendMessage(aiMessage, "ai");
            if (document.getElementById("chatWindow").style.display === "none") {
                incrementNotificationCount();
            }
        })
        .catch(error => {
            console.error("Error:", error);
            hideTypingIndicator();
        });
    }

    function handleKeyPress(event) {
        if (event.key === "Enter") {
            sendMessage();
        }
    }

    function incrementNotificationCount() {
        notificationCount++;
        document.getElementById("notificationCount").textContent = notificationCount;
    }

    function resetNotificationCount() {
        notificationCount = 0;
        document.getElementById("notificationCount").textContent = notificationCount;
    }

    // Attach event listeners
    document.querySelector(".chat-bubble").addEventListener("click", toggleChatWindow);
    document.getElementById("maximizeBtn").addEventListener("click", toggleMaximize);
    document.getElementById("closeChat").addEventListener("click", toggleChatWindow);
    document.getElementById("sendMessage").addEventListener("click", sendMessage);
    document.getElementById("userInput").addEventListener("keypress", handleKeyPress);
});
</script>

</body>
</html>
