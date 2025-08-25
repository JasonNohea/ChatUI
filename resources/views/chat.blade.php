<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat UI</title>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        /* Scrolling mechanism */
        /* width */
        ::-webkit-scrollbar {
            width: 4px;
        }

        /* Track */
        ::-webkit-scrollbar-track {
            background: #e0eaf6;
        }

        /* Handle */
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        /* Handle on hover */
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .chat-container {
            width: 400px;
            height: 600px;
            max-height: 600px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            display: none;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-header {
            padding: 15px;
            background: #0078ff;
            color: #fff;
            font-weight: bold;
            text-align: center;
        }

        .chat-message-container {
            height: 510px;
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 0px;
            overflow: hidden;
        }

        .chat-messages {
            flex: 1;
            /* takes remaining space */
            padding: 15px;
            overflow-y: auto;
            /* enables vertical scroll */
            display: flex;
            flex-direction: column;
            gap: 10px;
            height: 600px;
            /* max-height: 90%; */
            /* keeps inside the container */
        }

        .message {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 18px;
            line-height: 1.4;
        }

        .message.sent {
            background: #0078ff;
            color: #fff;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }

        .message.received {
            background: #e5e5ea;
            color: #000;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
        }

        .chat-input {
            display: flex;
            border-top: 1px solid #ddd;
        }

        .chat-input input {
            flex: 1;
            padding: 12px;
            border: none;
            outline: none;
            font-size: 14px;
        }

        .chat-input button {
            background: #0078ff;
            color: white;
            border: none;
            padding: 0 20px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .chat-input button:hover {
            background: #005fcc;
        }

        /* Popup style */
        .popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .popup-content {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .popup-content input {
            padding: 10px;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            width: 80%;
        }

        .popup-content button {
            margin-top: 15px;
            padding: 10px 20px;
            background: #0078ff;
            border: none;
            color: white;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <!-- Popup -->
    <div class="popup" id="usernamePopup">
        <div class="popup-content">
            <h3>Enter your username</h3>
            <input type="text" id="usernameInput" placeholder="Your name..." />
            <br />
            <button id="joinBtn">Join Chat</button>
        </div>
    </div>

    <!-- Chat UI -->
    <div class="chat-container" id="chatContainer">
        <div class="chat-header">Chat Room</div>
        <div class="chat-message-container">
            <div class="chat-messages">

            </div>
        </div>
        <div class="chat-input">
            <input type="text" id="chatInput" placeholder="Type a message..." />
            <button id="sendBtn">Send</button>
        </div>
    </div>

    <script src="https://www.gstatic.com/firebasejs/10.12.2/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.12.2/firebase-firestore-compat.js"></script>

    <script>
        var cur_timestamp = 0;
        let currentUser = null; // dynamic user

        const firebaseConfig = {
            apiKey: "AIzaSyC8cushn7uKs5UTZCfufg2MvK4xQVIcCiM",
            authDomain: "testchatproject123.firebaseapp.com",
            projectId: "testchatproject123",
            storageBucket: "testchatproject123.firebasestorage.app",
            messagingSenderId: "797056779882",
            appId: "1:797056779882:web:7cdb914e6e87ca3c64ec13",
            measurementId: "G-E0D790MWX2"
        };

        // Initialize Firebase
        const app = firebase.initializeApp(firebaseConfig);
        const db = firebase.firestore();

        // Join Chat after entering username
        $("#joinBtn").on("click", function() {
            const name = $("#usernameInput").val().trim();
            if (name === "") {
                alert("Please enter a username!");
                return;
            }
            currentUser = name;

            $("#usernamePopup").hide();
            $("#chatContainer").show();
        });

        // Watch Firestore doc for updates
        db.collection('chat').doc('1').onSnapshot((doc) => {
            if (doc.exists) {
                let data = doc.data();
                let timestamp = data.last_timestamp;

                if (cur_timestamp < timestamp) {
                    cur_timestamp = timestamp;

                    // Fetch messages from API
                    $.ajax({
                        url: 'http://127.0.0.1:3000/api/chat?id=1',
                        method: 'GET',
                        dataType: "json",
                        success: function(response) {
                            const responseObject = response[0];
                            const chatHistoryString = responseObject.chat_history;
                            const messages = JSON.parse(chatHistoryString);

                            const chatContainer = $(".chat-messages");
                            chatContainer.empty();

                            messages.forEach(function(chat) {
                                let username = chat.username || "Unknown";
                                let message = chat.message || "";

                                let messageClass = (username === currentUser) ? "sent" : "received";

                                let messageHtml = `
                  <div class="message ${messageClass}">
                    <strong>${username}:</strong> ${message}
                  </div>`;

                                chatContainer.append(messageHtml);
                            });

                            chatContainer.scrollTop(chatContainer[0].scrollHeight);
                        },
                        error: function(err) {
                            console.error("Failed to fetch messages:", err);
                        }
                    });
                }
            }
        });

        // Handle Send Button
        $("#sendBtn").on("click", function() {
            let msg = $("#chatInput").val().trim();
            if (msg === "" || !currentUser) return;

            $.ajax({
                url: 'http://127.0.0.1:3000/api/chat?id=1',
                method: 'POST',
                contentType: "application/json",
                data: JSON.stringify({
                    username: currentUser,
                    message: msg
                }),
                success: function() {
                    $("#chatInput").val("");
                }
            });
        });

        // Allow Enter key to send
        $("#chatInput").on("keypress", function(e) {
            if (e.which === 13) {
                $("#sendBtn").click();
            }
        });
    </script>
</body>

</html>