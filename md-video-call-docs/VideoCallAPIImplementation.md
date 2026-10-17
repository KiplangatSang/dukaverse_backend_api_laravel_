# Video Call API Implementation Documentation

## Overview

This documentation provides a comprehensive guide for implementing video call APIs in the Dukaverse Backend project. The implementation includes real-time video calling with integrated chat functionality, permissions management, and Firebase integration for messaging and notifications.

## Technology Stack

- **Backend Framework**: Laravel 10+
- **Database**: MySQL/PostgreSQL
- **Real-time Communication**: WebRTC (for video/audio)
- **Chat System**: Firebase Firestore (for real-time messaging)
- **Notifications**: Firebase Cloud Messaging (FCM)
- **Authentication**: Laravel Sanctum
- **Documentation**: Swagger/OpenAPI

## Database Schema

### Video Calls Table
```sql
CREATE TABLE video_calls (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_id VARCHAR(255) UNIQUE NOT NULL,
    initiator_id BIGINT UNSIGNED NOT NULL,
    participants JSON NULL,
    status ENUM('waiting', 'active', 'ended') DEFAULT 'waiting',
    started_at TIMESTAMP NULL,
    ended_at TIMESTAMP NULL,
    settings JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (initiator_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Video Call Participants Table
```sql
CREATE TABLE video_call_participants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    video_call_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    role ENUM('host', 'participant', 'moderator') DEFAULT 'participant',
    joined_at TIMESTAMP NULL,
    left_at TIMESTAMP NULL,
    is_muted BOOLEAN DEFAULT FALSE,
    is_video_on BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (video_call_id) REFERENCES video_calls(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_participant (video_call_id, user_id)
);
```

### Video Call Permissions Table
```sql
CREATE TABLE video_call_permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    can_initiate BOOLEAN DEFAULT FALSE,
    can_moderate BOOLEAN DEFAULT FALSE,
    can_record BOOLEAN DEFAULT FALSE,
    can_share_screen BOOLEAN DEFAULT FALSE,
    can_mute_others BOOLEAN DEFAULT FALSE,
    can_kick_participants BOOLEAN DEFAULT FALSE,
    can_send_messages BOOLEAN DEFAULT TRUE,
    can_upload_files BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_permission (user_id, role_id)
);
```

## API Endpoints

### 1. Create Video Call Room
**Endpoint**: `POST /api/v1/video-calls`

**Request Body**:
```json
{
  "settings": {
    "recording": false,
    "screen_share": true,
    "max_participants": 10
  }
}
```

**Response**:
```json
{
  "success": true,
  "message": "Video call room created successfully",
  "data": {
    "room_id": "ABC123DEF4",
    "video_call": {
      "id": 1,
      "room_id": "ABC123DEF4",
      "status": "waiting",
      "settings": {...}
    }
  }
}
```

### 2. Join Video Call Room
**Endpoint**: `POST /api/v1/video-calls/{roomId}/join`

**Response**:
```json
{
  "success": true,
  "message": "Joined video call successfully",
  "data": {
    "participant": {
      "id": 1,
      "role": "participant",
      "joined_at": "2025-01-15T10:00:00Z"
    },
    "video_call": {...}
  }
}
```

### 3. Leave Video Call Room
**Endpoint**: `POST /api/v1/video-calls/{roomId}/leave`

**Response**:
```json
{
  "success": true,
  "message": "Left video call successfully"
}
```

### 4. Get Room Details
**Endpoint**: `GET /api/v1/video-calls/{roomId}`

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "room_id": "ABC123DEF4",
    "status": "active",
    "participants": [...],
    "initiator": {...}
  },
  "message": "Video call details retrieved successfully"
}
```

### 5. Get Room Participants
**Endpoint**: `GET /api/v1/video-calls/{roomId}/participants`

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user": {
        "id": 1,
        "name": "John Doe"
      },
      "role": "host",
      "joined_at": "2025-01-15T10:00:00Z"
    }
  ],
  "message": "Participants retrieved successfully"
}
```

### 6. Send Chat Message
**Endpoint**: `POST /api/v1/video-calls/{roomId}/messages`

**Request Body**:
```json
{
  "message": "Hello everyone!",
  "type": "text"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Message sent successfully",
  "data": {
    "user_id": 1,
    "user_name": "John Doe",
    "message": "Hello everyone!",
    "type": "text",
    "timestamp": "2025-01-15T10:05:00Z"
  }
}
```

### 7. Get Chat Messages
**Endpoint**: `GET /api/v1/video-calls/{roomId}/messages`

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "user_id": 1,
      "user_name": "John Doe",
      "message": "Hello everyone!",
      "type": "text",
      "timestamp": "2025-01-15T10:05:00Z"
    }
  ],
  "message": "Messages retrieved successfully"
}
```

## Implementation Steps

### Step 1: Database Setup
1. Run the migrations:
```bash
php artisan migrate
```

2. Seed initial permissions if needed:
```php
// Create seeder for default permissions
VideoCallPermission::create([
    'user_id' => $user->id,
    'role_id' => $role->id,
    'can_initiate' => true,
    'can_send_messages' => true,
    // ... other permissions
]);
```

### Step 2: Firebase Configuration
1. Create a Firebase project at https://console.firebase.google.com/

2. Enable Firestore Database and Cloud Messaging

3. Download service account credentials and place in `storage/app/firebase-credentials.json`

4. Update `.env` file:
```env
FIREBASE_CREDENTIALS=storage/app/firebase-credentials.json
FIREBASE_DATABASE_URL=https://your-project.firebaseio.com
FIREBASE_PROJECT_ID=your-project-id
```

### Step 3: Install Dependencies
```bash
composer require kreait/firebase-php
```

### Step 4: Configure Routes
Routes are already defined in `routes/api.php`:
```php
Route::prefix('v1')->group(function () {
    Route::prefix('video-calls')->group(function () {
        Route::post('/', [VideoCallController::class, 'createRoom']);
        Route::post('/{roomId}/join', [VideoCallController::class, 'joinRoom']);
        Route::post('/{roomId}/leave', [VideoCallController::class, 'leaveRoom']);
        Route::get('/{roomId}', [VideoCallController::class, 'getRoom']);
        Route::get('/{roomId}/participants', [VideoCallController::class, 'getParticipants']);
        Route::post('/{roomId}/messages', [VideoCallController::class, 'sendMessage']);
        Route::get('/{roomId}/messages', [VideoCallController::class, 'getMessages']);
    });
});
```

### Step 5: Generate API Documentation
```bash
php artisan l5-swagger:generate
```

## Frontend Implementation

### Technology Stack
- **Framework**: React/Vue.js/Angular
- **WebRTC Library**: Simple-Peer, PeerJS, or WebRTC API directly
- **Real-time Communication**: Socket.io for signaling, Firebase for chat
- **State Management**: Redux, Vuex, or Context API
- **UI Components**: Material-UI, Ant Design, or custom components

### Project Setup

1. **Install Dependencies**:
```bash
npm install simple-peer socket.io-client firebase
# For React
npm install @material-ui/core react-redux redux-thunk
# For Vue
npm install vuex socket.io-client
```

2. **Environment Configuration**:
```javascript
// .env
REACT_APP_API_BASE_URL=http://localhost:8000/api/v1
REACT_APP_FIREBASE_CONFIG={"apiKey": "...", "projectId": "..."}
REACT_APP_SOCKET_URL=http://localhost:3001
```

### Core Components Structure

```
src/
├── components/
│   ├── VideoCall/
│   │   ├── VideoCallRoom.jsx
│   │   ├── ParticipantVideo.jsx
│   │   ├── ChatPanel.jsx
│   │   ├── ControlPanel.jsx
│   │   ├── ParticipantList.jsx
│   │   └── SettingsModal.jsx
│   └── common/
├── services/
│   ├── videoCallService.js
│   ├── chatService.js
│   ├── socketService.js
│   └── firebaseService.js
├── hooks/
│   ├── useVideoCall.js
│   ├── useChat.js
│   └── useWebRTC.js
├── store/
│   ├── actions/
│   ├── reducers/
│   └── store.js
└── utils/
    ├── constants.js
    └── helpers.js
```

### WebRTC Implementation

#### Video Call Service (`services/videoCallService.js`)
```javascript
import axios from 'axios';

const API_BASE = process.env.REACT_APP_API_BASE_URL;

export const videoCallService = {
    // Create new video call room
    createRoom: async (settings = {}) => {
        const response = await axios.post(`${API_BASE}/video-calls`, { settings });
        return response.data;
    },

    // Join existing room
    joinRoom: async (roomId) => {
        const response = await axios.post(`${API_BASE}/video-calls/${roomId}/join`);
        return response.data;
    },

    // Leave room
    leaveRoom: async (roomId) => {
        const response = await axios.post(`${API_BASE}/video-calls/${roomId}/leave`);
        return response.data;
    },

    // Get room details
    getRoom: async (roomId) => {
        const response = await axios.get(`${API_BASE}/video-calls/${roomId}`);
        return response.data;
    },

    // Get participants
    getParticipants: async (roomId) => {
        const response = await axios.get(`${API_BASE}/video-calls/${roomId}/participants`);
        return response.data;
    }
};
```

#### WebRTC Hook (`hooks/useWebRTC.js`)
```javascript
import { useEffect, useRef, useState } from 'react';
import Peer from 'simple-peer';
import { socketService } from '../services/socketService';

export const useWebRTC = (roomId, userId, isInitiator = false) => {
    const [peers, setPeers] = useState([]);
    const [stream, setStream] = useState(null);
    const [isConnected, setIsConnected] = useState(false);
    const userVideo = useRef();
    const peersRef = useRef([]);

    useEffect(() => {
        // Get user media
        navigator.mediaDevices.getUserMedia({ video: true, audio: true })
            .then(currentStream => {
                setStream(currentStream);
                if (userVideo.current) {
                    userVideo.current.srcObject = currentStream;
                }

                // Join room and create peer connections
                socketService.emit('join-room', { roomId, userId });

                socketService.on('user-joined', userId => {
                    createPeer(userId, currentStream, true);
                });

                socketService.on('receiving-signal', payload => {
                    addPeer(payload.signal, payload.callerId, currentStream);
                });

                socketService.on('user-left', userId => {
                    removePeer(userId);
                });
            })
            .catch(error => {
                console.error('Error accessing media devices:', error);
            });

        return () => {
            // Cleanup
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
            peersRef.current.forEach(peer => peer.peer.destroy());
            socketService.off('user-joined');
            socketService.off('receiving-signal');
            socketService.off('user-left');
        };
    }, [roomId, userId, isInitiator]);

    const createPeer = (userId, stream, initiator) => {
        const peer = new Peer({
            initiator,
            trickle: false,
            stream,
            config: {
                iceServers: [
                    { urls: 'stun:stun.l.google.com:19302' },
                    { urls: 'turn:turn.example.com', username: 'user', credential: 'pass' }
                ]
            }
        });

        peer.on('signal', signal => {
            socketService.emit('sending-signal', { signal, userId, callerId: userId });
        });

        peer.on('stream', userStream => {
            setPeers(prevPeers => [...prevPeers, { userId, stream: userStream, peer }]);
        });

        peer.on('error', error => {
            console.error('Peer error:', error);
        });

        peersRef.current.push({ peerId: userId, peer });
        return peer;
    };

    const addPeer = (incomingSignal, callerId, stream) => {
        const peer = new Peer({
            initiator: false,
            trickle: false,
            stream
        });

        peer.on('signal', signal => {
            socketService.emit('returning-signal', { signal, callerId });
        });

        peer.on('stream', userStream => {
            setPeers(prevPeers => [...prevPeers, { userId: callerId, stream: userStream, peer }]);
        });

        peer.signal(incomingSignal);
        peersRef.current.push({ peerId: callerId, peer });
    };

    const removePeer = (userId) => {
        const peerObj = peersRef.current.find(p => p.peerId === userId);
        if (peerObj) {
            peerObj.peer.destroy();
        }
        peersRef.current = peersRef.current.filter(p => p.peerId !== userId);
        setPeers(prevPeers => prevPeers.filter(p => p.userId !== userId));
    };

    const toggleAudio = () => {
        if (stream) {
            const audioTrack = stream.getAudioTracks()[0];
            audioTrack.enabled = !audioTrack.enabled;
        }
    };

    const toggleVideo = () => {
        if (stream) {
            const videoTrack = stream.getVideoTracks()[0];
            videoTrack.enabled = !videoTrack.enabled;
        }
    };

    const shareScreen = async () => {
        try {
            const screenStream = await navigator.mediaDevices.getDisplayMedia({ video: true });
            // Replace video track with screen share
            const videoTrack = screenStream.getVideoTracks()[0];
            peersRef.current.forEach(({ peer }) => {
                const sender = peer.streams[0].getVideoTracks()[0];
                peer.replaceTrack(sender, videoTrack, stream);
            });
        } catch (error) {
            console.error('Error sharing screen:', error);
        }
    };

    return {
        peers,
        stream,
        userVideo,
        isConnected,
        toggleAudio,
        toggleVideo,
        shareScreen
    };
};
```

#### Socket Service (`services/socketService.js`)
```javascript
import io from 'socket.io-client';

class SocketService {
    constructor() {
        this.socket = null;
    }

    connect() {
        this.socket = io(process.env.REACT_APP_SOCKET_URL);
        return new Promise((resolve) => {
            this.socket.on('connect', () => {
                console.log('Connected to signaling server');
                resolve();
            });
        });
    }

    disconnect() {
        if (this.socket) {
            this.socket.disconnect();
        }
    }

    emit(event, data) {
        if (this.socket) {
            this.socket.emit(event, data);
        }
    }

    on(event, callback) {
        if (this.socket) {
            this.socket.on(event, callback);
        }
    }

    off(event) {
        if (this.socket) {
            this.socket.off(event);
        }
    }
}

export const socketService = new SocketService();
```

### Firebase Chat Integration

#### Chat Service (`services/chatService.js`)
```javascript
import { initializeApp } from 'firebase/app';
import { getFirestore, collection, addDoc, onSnapshot, query, orderBy, limit } from 'firebase/firestore';
import axios from 'axios';

const firebaseConfig = JSON.parse(process.env.REACT_APP_FIREBASE_CONFIG);
const app = initializeApp(firebaseConfig);
const db = getFirestore(app);

const API_BASE = process.env.REACT_APP_API_BASE_URL;

export const chatService = {
    // Send message via API (for persistence and validation)
    sendMessage: async (roomId, message, type = 'text') => {
        const response = await axios.post(`${API_BASE}/video-calls/${roomId}/messages`, {
            message,
            type
        });
        return response.data;
    },

    // Get messages via API
    getMessages: async (roomId) => {
        const response = await axios.get(`${API_BASE}/video-calls/${roomId}/messages`);
        return response.data;
    },

    // Real-time listener for new messages
    subscribeToMessages: (roomId, callback) => {
        const messagesRef = collection(db, 'video_calls', roomId, 'messages');
        const q = query(messagesRef, orderBy('timestamp', 'desc'), limit(50));

        return onSnapshot(q, (snapshot) => {
            const messages = [];
            snapshot.forEach((doc) => {
                messages.unshift(doc.data());
            });
            callback(messages);
        });
    },

    // Send file message
    sendFile: async (roomId, file) => {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('type', 'file');

        const response = await axios.post(`${API_BASE}/video-calls/${roomId}/messages`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        return response.data;
    }
};
```

#### Chat Hook (`hooks/useChat.js`)
```javascript
import { useEffect, useState } from 'react';
import { chatService } from '../services/chatService';

export const useChat = (roomId) => {
    const [messages, setMessages] = useState([]);
    const [isLoading, setIsLoading] = useState(false);

    useEffect(() => {
        if (!roomId) return;

        // Load initial messages
        const loadMessages = async () => {
            try {
                const response = await chatService.getMessages(roomId);
                if (response.success) {
                    setMessages(response.data);
                }
            } catch (error) {
                console.error('Error loading messages:', error);
            }
        };

        loadMessages();

        // Subscribe to real-time updates
        const unsubscribe = chatService.subscribeToMessages(roomId, (newMessages) => {
            setMessages(newMessages);
        });

        return () => {
            unsubscribe();
        };
    }, [roomId]);

    const sendMessage = async (message, type = 'text') => {
        setIsLoading(true);
        try {
            await chatService.sendMessage(roomId, message, type);
        } catch (error) {
            console.error('Error sending message:', error);
            throw error;
        } finally {
            setIsLoading(false);
        }
    };

    const sendFile = async (file) => {
        setIsLoading(true);
        try {
            await chatService.sendFile(roomId, file);
        } catch (error) {
            console.error('Error sending file:', error);
            throw error;
        } finally {
            setIsLoading(false);
        }
    };

    return {
        messages,
        sendMessage,
        sendFile,
        isLoading
    };
};
```

### React Components

#### Video Call Room Component (`components/VideoCall/VideoCallRoom.jsx`)
```jsx
import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { useWebRTC } from '../../hooks/useWebRTC';
import { useChat } from '../../hooks/useChat';
import { videoCallService } from '../../services/videoCallService';
import ParticipantVideo from './ParticipantVideo';
import ChatPanel from './ChatPanel';
import ControlPanel from './ControlPanel';
import ParticipantList from './ParticipantList';

const VideoCallRoom = () => {
    const { roomId } = useParams();
    const [roomData, setRoomData] = useState(null);
    const [participants, setParticipants] = useState([]);
    const [isHost, setIsHost] = useState(false);

    const { peers, stream, userVideo, toggleAudio, toggleVideo, shareScreen } = useWebRTC(roomId, userId);
    const { messages, sendMessage, sendFile } = useChat(roomId);

    useEffect(() => {
        const loadRoomData = async () => {
            try {
                const roomResponse = await videoCallService.getRoom(roomId);
                const participantsResponse = await videoCallService.getParticipants(roomId);

                if (roomResponse.success) {
                    setRoomData(roomResponse.data);
                    setIsHost(roomResponse.data.initiator_id === currentUser.id);
                }

                if (participantsResponse.success) {
                    setParticipants(participantsResponse.data);
                }
            } catch (error) {
                console.error('Error loading room data:', error);
            }
        };

        loadRoomData();
    }, [roomId]);

    if (!roomData) {
        return <div>Loading room...</div>;
    }

    return (
        <div className="video-call-room">
            <div className="video-grid">
                {/* Local video */}
                <div className="local-video">
                    <video
                        ref={userVideo}
                        autoPlay
                        muted
                        className="video-element"
                    />
                    <div className="video-label">You</div>
                </div>

                {/* Remote videos */}
                {peers.map((peer, index) => (
                    <ParticipantVideo
                        key={peer.userId}
                        stream={peer.stream}
                        userId={peer.userId}
                        participant={participants.find(p => p.user.id === peer.userId)}
                    />
                ))}
            </div>

            <ControlPanel
                onToggleAudio={toggleAudio}
                onToggleVideo={toggleVideo}
                onShareScreen={shareScreen}
                onLeaveRoom={() => {/* handle leave */}}
                isHost={isHost}
            />

            <div className="sidebar">
                <ParticipantList participants={participants} />
                <ChatPanel
                    messages={messages}
                    onSendMessage={sendMessage}
                    onSendFile={sendFile}
                />
            </div>
        </div>
    );
};

export default VideoCallRoom;
```

#### Participant Video Component (`components/VideoCall/ParticipantVideo.jsx`)
```jsx
import React, { useRef, useEffect } from 'react';

const ParticipantVideo = ({ stream, userId, participant }) => {
    const videoRef = useRef();

    useEffect(() => {
        if (videoRef.current && stream) {
            videoRef.current.srcObject = stream;
        }
    }, [stream]);

    return (
        <div className="participant-video">
            <video
                ref={videoRef}
                autoPlay
                className="video-element"
            />
            <div className="participant-info">
                <span className="participant-name">
                    {participant?.user?.name || 'Unknown'}
                </span>
                {participant?.is_muted && <span className="muted-indicator">🔇</span>}
                {!participant?.is_video_on && <span className="video-off-indicator">📷</span>}
            </div>
        </div>
    );
};

export default ParticipantVideo;
```

#### Chat Panel Component (`components/VideoCall/ChatPanel.jsx`)
```jsx
import React, { useState, useRef } from 'react';

const ChatPanel = ({ messages, onSendMessage, onSendFile }) => {
    const [message, setMessage] = useState('');
    const [isTyping, setIsTyping] = useState(false);
    const fileInputRef = useRef();
    const messagesEndRef = useRef();

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    React.useEffect(() => {
        scrollToBottom();
    }, [messages]);

    const handleSendMessage = async (e) => {
        e.preventDefault();
        if (!message.trim()) return;

        try {
            await onSendMessage(message);
            setMessage('');
        } catch (error) {
            console.error('Error sending message:', error);
        }
    };

    const handleFileSelect = async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        try {
            await onSendFile(file);
        } catch (error) {
            console.error('Error sending file:', error);
        }
    };

    return (
        <div className="chat-panel">
            <div className="chat-header">
                <h3>Chat</h3>
            </div>

            <div className="messages-container">
                {messages.map((msg, index) => (
                    <div key={index} className="message">
                        <div className="message-header">
                            <span className="sender-name">{msg.user_name}</span>
                            <span className="timestamp">
                                {new Date(msg.timestamp).toLocaleTimeString()}
                            </span>
                        </div>
                        <div className="message-content">
                            {msg.type === 'text' ? (
                                <p>{msg.message}</p>
                            ) : (
                                <div className="file-message">
                                    <a href={msg.file_url} target="_blank" rel="noopener noreferrer">
                                        📎 {msg.file_name}
                                    </a>
                                </div>
                            )}
                        </div>
                    </div>
                ))}
                <div ref={messagesEndRef} />
            </div>

            <form onSubmit={handleSendMessage} className="message-form">
                <input
                    type="text"
                    value={message}
                    onChange={(e) => setMessage(e.target.value)}
                    placeholder="Type a message..."
                    className="message-input"
                />
                <button
                    type="button"
                    onClick={() => fileInputRef.current?.click()}
                    className="file-button"
                >
                    📎
                </button>
                <input
                    ref={fileInputRef}
                    type="file"
                    onChange={handleFileSelect}
                    style={{ display: 'none' }}
                />
                <button type="submit" className="send-button">
                    Send
                </button>
            </form>
        </div>
    );
};

export default ChatPanel;
```

#### Control Panel Component (`components/VideoCall/ControlPanel.jsx`)
```jsx
import React, { useState } from 'react';

const ControlPanel = ({
    onToggleAudio,
    onToggleVideo,
    onShareScreen,
    onLeaveRoom,
    isHost
}) => {
    const [audioEnabled, setAudioEnabled] = useState(true);
    const [videoEnabled, setVideoEnabled] = useState(true);

    const handleToggleAudio = () => {
        setAudioEnabled(!audioEnabled);
        onToggleAudio();
    };

    const handleToggleVideo = () => {
        setVideoEnabled(!videoEnabled);
        onToggleVideo();
    };

    return (
        <div className="control-panel">
            <button
                onClick={handleToggleAudio}
                className={`control-button ${audioEnabled ? 'active' : 'inactive'}`}
            >
                {audioEnabled ? '🔊' : '🔇'}
            </button>

            <button
                onClick={handleToggleVideo}
                className={`control-button ${videoEnabled ? 'active' : 'inactive'}`}
            >
                {videoEnabled ? '📹' : '📷'}
            </button>

            <button onClick={onShareScreen} className="control-button">
                🖥️
            </button>

            {isHost && (
                <button className="control-button host-only">
                    ⚙️
                </button>
            )}

            <button onClick={onLeaveRoom} className="control-button leave">
                📞 Leave
            </button>
        </div>
    );
};

export default ControlPanel;
```

### State Management (Redux Example)

#### Actions (`store/actions/videoCallActions.js`)
```javascript
export const CREATE_ROOM_REQUEST = 'CREATE_ROOM_REQUEST';
export const CREATE_ROOM_SUCCESS = 'CREATE_ROOM_SUCCESS';
export const CREATE_ROOM_FAILURE = 'CREATE_ROOM_FAILURE';

export const createRoom = (settings) => async (dispatch) => {
    dispatch({ type: CREATE_ROOM_REQUEST });

    try {
        const response = await videoCallService.createRoom(settings);
        dispatch({
            type: CREATE_ROOM_SUCCESS,
            payload: response.data
        });
    } catch (error) {
        dispatch({
            type: CREATE_ROOM_FAILURE,
            payload: error.message
        });
    }
};

// Similar actions for joinRoom, leaveRoom, etc.
```

#### Reducer (`store/reducers/videoCallReducer.js`)
```javascript
const initialState = {
    currentRoom: null,
    participants: [],
    messages: [],
    isLoading: false,
    error: null
};

export const videoCallReducer = (state = initialState, action) => {
    switch (action.type) {
        case CREATE_ROOM_REQUEST:
            return { ...state, isLoading: true, error: null };

        case CREATE_ROOM_SUCCESS:
            return {
                ...state,
                isLoading: false,
                currentRoom: action.payload
            };

        case CREATE_ROOM_FAILURE:
            return {
                ...state,
                isLoading: false,
                error: action.payload
            };

        // Handle other actions...

        default:
            return state;
    }
};
```

### Error Handling and Edge Cases

#### Error Boundary Component
```jsx
import React from 'react';

class VideoCallErrorBoundary extends React.Component {
    constructor(props) {
        super(props);
        this.state = { hasError: false, error: null };
    }

    static getDerivedStateFromError(error) {
        return { hasError: true, error };
    }

    componentDidCatch(error, errorInfo) {
        console.error('Video call error:', error, errorInfo);
        // Log to monitoring service
    }

    render() {
        if (this.state.hasError) {
            return (
                <div className="error-fallback">
                    <h2>Something went wrong with the video call</h2>
                    <button onClick={() => window.location.reload()}>
                        Reload Page
                    </button>
                </div>
            );
        }

        return this.props.children;
    }
}

export default VideoCallErrorBoundary;
```

#### Connection Quality Monitoring
```javascript
// Monitor WebRTC connection quality
const monitorConnectionQuality = (peer) => {
    let lastBytesReceived = 0;
    let lastTimestamp = Date.now();

    const checkQuality = () => {
        const stats = peer.getStats();
        stats.then(reports => {
            reports.forEach(report => {
                if (report.type === 'inbound-rtp' && report.mediaType === 'video') {
                    const bytesReceived = report.bytesReceived;
                    const timestamp = Date.now();
                    const bitrate = (bytesReceived - lastBytesReceived) * 8 / (timestamp - lastTimestamp);

                    // Update UI based on bitrate
                    if (bitrate < 100000) { // Less than 100kbps
                        showLowQualityIndicator();
                    }

                    lastBytesReceived = bytesReceived;
                    lastTimestamp = timestamp;
                }
            });
        });
    };

    setInterval(checkQuality, 2000);
};
```

### Mobile Considerations

#### Responsive Design
```css
.video-call-room {
    display: flex;
    flex-direction: column;
    height: 100vh;
}

@media (max-width: 768px) {
    .video-call-room {
        flex-direction: column;
    }

    .video-grid {
        grid-template-columns: 1fr;
        max-height: 60vh;
    }

    .sidebar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: 40vh;
        background: white;
    }
}
```

#### Mobile WebRTC Optimizations
```javascript
// Adjust video constraints for mobile
const getMobileConstraints = () => {
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

    return {
        video: {
            width: isMobile ? 640 : 1280,
            height: isMobile ? 480 : 720,
            frameRate: isMobile ? 15 : 30
        },
        audio: {
            echoCancellation: true,
            noiseSuppression: true,
            sampleRate: isMobile ? 16000 : 48000
        }
    };
};
```

### Testing

#### Unit Tests
```javascript
import { render, screen, fireEvent } from '@testing-library/react';
import VideoCallRoom from './VideoCallRoom';

test('renders video call room', () => {
    render(<VideoCallRoom />);
    expect(screen.getByText('Loading room...')).toBeInTheDocument();
});

test('toggles audio control', () => {
    const mockToggleAudio = jest.fn();
    render(<ControlPanel onToggleAudio={mockToggleAudio} />);

    const audioButton = screen.getByRole('button', { name: /audio/i });
    fireEvent.click(audioButton);

    expect(mockToggleAudio).toHaveBeenCalled();
});
```

#### Integration Tests
```javascript
import { render, waitFor } from '@testing-library/react';
import { videoCallService } from '../services/videoCallService';

jest.mock('../services/videoCallService');

test('loads room data on mount', async () => {
    videoCallService.getRoom.mockResolvedValue({
        success: true,
        data: { id: 1, room_id: 'test-room' }
    });

    render(<VideoCallRoom roomId="test-room" />);

    await waitFor(() => {
        expect(screen.getByText('test-room')).toBeInTheDocument();
    });
});
```

This comprehensive frontend implementation provides a complete video calling solution with real-time chat, participant management, and mobile responsiveness.

## Security Considerations

### Authentication
- All endpoints require bearer token  middleware
- Room access validated per user permissions

### Permissions
- Role-based access control for call features
- Host can moderate participants
- Participants can only access authorized rooms

### Data Validation
- Input sanitization for all API parameters
- Rate limiting on message sending
- File upload restrictions for chat

### Firebase Security
- Firestore security rules to restrict access:
```javascript
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {
    match /video_calls/{roomId}/messages/{messageId} {
      allow read, write: if request.auth != null &&
        exists(/databases/$(database)/documents/video_calls/$(roomId)/participants/$(request.auth.uid));
    }
  }
}
```

## Testing

### Unit Tests
```php
// Test video call creation
public function test_can_create_video_call_room()
{
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->postJson('/api/v1/video-calls', [
        'settings' => ['recording' => false]
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'success',
                 'data' => ['room_id', 'video_call']
             ]);
}
```

### API Testing with cURL
```bash
# Create room
curl -X POST http://localhost:8000/api/v1/video-calls \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"settings": {"recording": false}}'

# Join room
curl -X POST http://localhost:8000/api/v1/video-calls/ABC123/join \
  -H "Authorization: Bearer {token}"

# Send message
curl -X POST http://localhost:8000/api/v1/video-calls/ABC123/messages \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"message": "Hello!", "type": "text"}'
```

## Monitoring and Maintenance

### Logging
- Log all call events for debugging
- Monitor Firebase usage and costs
- Track API performance metrics

### Cleanup
- Implement job to clean up ended calls after retention period
- Remove old chat messages from Firestore
- Archive recordings if enabled

### Scaling Considerations
- Use Redis for signaling if WebSocket server is separate
- Implement load balancing for multiple call servers
- Monitor concurrent call limits

## Troubleshooting

### Common Issues
1. **Firebase Connection Errors**: Check credentials file path and permissions
2. **Room Not Found**: Verify room_id exists and user has access
3. **Permission Denied**: Check user roles and permissions table
4. **WebRTC Connection Issues**: Ensure STUN/TURN servers are configured

### Debug Commands
```bash
# Check migrations
php artisan migrate:status

# Test Firebase connection
php artisan tinker
>>> $firebase = app('firebase.firestore');
>>> $firebase->database()->collection('test')->documents();

# Generate fresh API docs
php artisan l5-swagger:generate
```

This implementation provides a solid foundation for video calling with real-time chat. For production use, consider adding TURN servers, implementing proper WebRTC signaling, and enhancing security measures.
