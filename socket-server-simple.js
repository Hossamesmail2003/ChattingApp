import express from 'express';
import { createServer } from 'http';
import { Server } from 'socket.io';

// Create Express app and HTTP server
const app = express();
const server = createServer(app);

// Configure Socket.IO with CORS
const io = new Server(server, {
    cors: {
        origin: ["http://localhost", "http://127.0.0.1", "http://localhost:8000", "http://127.0.0.1:8000"],
        methods: ["GET", "POST"],
        credentials: true
    }
});

// Handle Socket.IO connections
io.on('connection', (socket) => {
    console.log('New client connected:', socket.id);

    // Join the chat channel
    socket.join('chat');
    
    // Handle client disconnect
    socket.on('disconnect', () => {
        console.log('Client disconnected:', socket.id);
    });

    // Handle manual message sending (for testing)
    socket.on('send-message', (data) => {
        console.log('📨 Message received from client:', socket.id, data);
        // Broadcast to all clients in the chat channel
        io.to('chat').emit('message-received', {
            message: data.message,
            timestamp: new Date().toISOString(),
            sender: 'user'
        });
        console.log('📤 Message broadcasted to all clients in chat channel');
    });

    // Send welcome message
    socket.emit('message-received', {
        message: 'Welcome to the chat! Socket.IO server is running.',
        timestamp: new Date().toISOString(),
        sender: 'system'
    });
});

// Basic route for health check
app.get('/', (req, res) => {
    res.json({ 
        status: 'Socket.IO server is running',
        timestamp: new Date().toISOString(),
        connections: io.engine.clientsCount
    });
});

// Start the server
const PORT = process.env.PORT || 3001;

server.listen(PORT, () => {
    console.log(`Socket.IO server running on port ${PORT}`);
    console.log(`WebSocket endpoint: ws://localhost:${PORT}`);
    console.log(`Health check: http://localhost:${PORT}`);
});

// Handle graceful shutdown
process.on('SIGINT', () => {
    console.log('Shutting down gracefully...');
    server.close(() => {
        console.log('Server closed');
        process.exit(0);
    });
});
