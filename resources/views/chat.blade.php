@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Chat Room') }}</div>

                <div class="card-body">
                    <div id="chat-messages" class="mb-3" style="height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                        <!-- Messages will appear here -->
                    </div>
                    
                    <div class="input-group">
                        <input type="text" id="message-input" class="form-control" placeholder="Type your message..." maxlength="255">
                        <button class="btn btn-primary" type="button" id="send-button">Send</button>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <span id="connection-status" class="badge bg-secondary">Connecting...</span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const messagesContainer = document.getElementById('chat-messages');
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');
    const connectionStatus = document.getElementById('connection-status');
    
    // Function to add message to chat
    function addMessage(message, isOwn = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `mb-2 ${isOwn ? 'text-end' : ''}`;
        
        const timestamp = new Date().toLocaleTimeString();
        messageDiv.innerHTML = `
            <div class="d-inline-block p-2 rounded ${isOwn ? 'bg-primary text-white' : 'bg-light'}">
                <div>${message}</div>
                <small class="opacity-75">${timestamp}</small>
            </div>
        `;
        
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    // Function to send message
    function sendMessage() {
        const message = messageInput.value.trim();
        if (message === '') return;

        // Send via Socket.IO directly (for real-time broadcasting)
        if (window.directSocket) {
            window.directSocket.emit('send-message', { message: message });
            console.log('Message sent via Socket.IO:', message);
        }

        // Laravel backend call removed - not needed for direct Socket.IO messaging

        messageInput.value = '';
    }
    
    // Event listeners
    sendButton.addEventListener('click', sendMessage);
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
    
    // Laravel Echo setup removed - not needed without Redis
    
    // Direct Socket.IO connection as fallback/additional
    if (window.io) {
        const socket = window.io('http://localhost:3001');

        // Make socket available globally for sending messages
        window.directSocket = socket;

        socket.on('connect', () => {
            console.log('Connected to Socket.IO server');
            connectionStatus.textContent = 'Connected to Socket.IO';
            connectionStatus.className = 'badge bg-success';
        });

        socket.on('disconnect', () => {
            console.log('Disconnected from Socket.IO server');
            connectionStatus.textContent = 'Disconnected';
            connectionStatus.className = 'badge bg-danger';
        });

        socket.on('message-received', (data) => {
            console.log('Message received via Socket.IO:', data);
            addMessage(data.message, false);
        });

        // Socket.IO is now the primary messaging method
    }
    
    // Add initial welcome message
    addMessage('Welcome to the chat room! Start typing to send messages.', false);
});
</script>
@endsection
