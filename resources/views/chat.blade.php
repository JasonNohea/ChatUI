<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat UI</title>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
    <style>

    </style>
</head>

<body>
    <!-- Register Popup -->
    <div class="popup" id="registerPopup" style="display:none;">
        <div class="popup-content">
            <h3>Register</h3>
            <input type="text" id="regUsername" placeholder="Username" /><br />
            <input type="email" id="regEmail" placeholder="Email" /><br />
            <input type="password" id="regPassword" placeholder="Password" /><br />
            <button id="registerBtn">Register</button>
            <br />
            <button id="gotoLogin" style="margin-top:10px; background:none; border:none; color:#0078ff; cursor:pointer;">
                Already have an account? Login
            </button>
        </div>
    </div>

    <!-- Login Popup -->
    <div class="popup" id="loginPopup">
        <div class="popup-content">
            <h3>Login to Chat</h3>
            <input type="text" id="loginUsername" placeholder="Username" />
            <br />
            <input type="password" id="loginPassword" placeholder="Password" />
            <br />
            <button id="loginBtn">Login</button>
            <br />

            <!-- Forgot password -->
            <button id="forgotPasswordBtn"
                style="margin-top:10px; background:none; border:none; color:#0078ff; cursor:pointer;">
                Forgot Password?
            </button>
            <br />

            <!-- Register button -->
            <button id="gotoRegister"
                style="margin-top:5px; background:none; border:none; color:#28a745; cursor:pointer;">
                Don’t have an account? Register
            </button>
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
        // Open Register popup
        $("#gotoRegister").on("click", function() {
            $("#loginPopup").hide();
            $("#registerPopup").show();
        });

        // Back to Login
        $("#gotoLogin").on("click", function() {
            $("#registerPopup").hide();
            $("#loginPopup").show();
        });

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

        // Login Button Click
        $("#loginBtn").on("click", function() {
            const username = $("#loginUsername").val().trim();
            const password = $("#loginPassword").val().trim();

            if (username === "" || password === "") {
                alert("Please enter both username and password!");
                return;
            }

            // 🚀 Normally, you’d send this to backend to validate login
            // Example:
            /*
            $.ajax({
              url: "http://127.0.0.1:3000/api/login",
              method: "POST",
              contentType: "application/json",
              data: JSON.stringify({ username, password }),
              success: function(res) {
                if (res.status === "success") {
                  currentUser = username;
                  $("#loginPopup").hide();
                  $("#chatContainer").show();
                } else {
                  alert("Invalid login");
                }
              },
              error: function() {
                alert("Login failed");
              }
            });
            */

            // 🔹 For now (no backend login), just accept any input
            currentUser = username;
            $("#loginPopup").hide();
            $("#chatContainer").show();
        });

        // Forgot Password Click
        $("#forgotPasswordBtn").on("click", function() {
            const username = $("#loginUsername").val().trim();
            if (username === "") {
                alert("Enter your username first to reset password.");
                return;
            }

            $.ajax({
                url: "http://127.0.0.1:3000/api/forgot-password",
                method: "POST",
                contentType: "application/json",
                data: JSON.stringify({
                    username
                }),
                success: function(res) {
                    if (res.status === "success") {
                        alert("Password reset email sent! Check your inbox.");
                    } else {
                        alert("No account found with that username.");
                    }
                },
                error: function() {
                    alert("Failed to send reset email. Try again.");
                }
            });
        });
    </script>
</body>

</html>