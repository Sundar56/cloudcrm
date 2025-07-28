require('dotenv').config();

const express = require("express");
var app = express();
var request = require('request');
var cors = require('cors');

const http = require('http').Server(app);
const port = process.env.SOCKET_PORT || 3000;
const socketUrl = process.env.SOCKET_URL;
const broadCastUrl = process.env.SOCKET_BRODCASTURL;
const endUrl = socketUrl + ":" + port
app.use(cors())
const io = require('socket.io')(http, {
    allowEIO3: true,
    cors: {
        origin: "*",
        methods: ["GET", "POST"],
        credentials: true
    }
});

//   io.socket("conne")
io.on('connection', (socket) => {

})
global.io = io;

// Define state messages
function getStateMessage(state, customMessage = null) {
    if (customMessage) {
        return customMessage;
    }
    const messages = {
        0: "Processnig For Company database",
        1: "Company database has been created",
        2: "CRM Tables migration has been created",
        3: "SSO Tables migration has been created",
        4: "Shop Tables migration has been created",
        5: "Config File Created",
        6: "Folders created & Data dump has been completed",
        7: "All steps have been successfully completed.",
    };
    return messages[state] || "Unknown state.";
    
}

io.on('connection', (socket) => {
    console.log('A user connected: ' + socket.id);

    socket.on('disconnect', () => {
        console.log('A user disconnected: ' + socket.id);
    });
});

app.get('/broadcast', async (req, res) => {
    const {
        channel,
        state,
        customMessage
    } = req.query;

    if (channel && (state || customMessage))  {
        const message = getStateMessage(parseInt(state, 10), customMessage);
        io.emit(channel, { state, message });
       
        res.status(200).json({
            status: true,
            message: 'Broadcast success',
            data: { state, message }
        });
    } else {
        res.status(400).json({
            status: false,
            message: 'Invalid request: channel and state are required.'
        });
    }
});

http.listen(port, () => {
    console.log('Server is running. Port: ' + port);
});
