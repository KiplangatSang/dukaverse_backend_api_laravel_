# Video Call Backend Implementation TODO

## Overview
Implement backend APIs for video calls with Firebase Firestore integration for instant messaging, storage, and notifications. Include proper API documentation with Swagger.

## Database Migrations
- [ ] Create video_calls migration
- [ ] Create video_call_participants migration
- [ ] Create video_call_messages migration
- [ ] Create video_call_permissions migration
- [ ] Run migrations

## Models
- [ ] VideoCall model with relationships
- [ ] VideoCallParticipant model
- [ ] VideoCallMessage model
- [ ] VideoCallPermission model

## Controllers
- [ ] VideoCallController with CRUD operations
- [ ] VideoCallChatController for messaging
- [ ] VideoCallPermissionController

## Services
- [ ] VideoCallService for business logic
- [ ] VideoCallChatService with Firebase integration
- [ ] FirebaseService for Firestore operations

## Middleware
- [ ] VideoCallPermissionMiddleware
- [ ] RoomAccessMiddleware

## Firebase Integration
- [ ] Install Firebase SDK
- [ ] Configure Firebase credentials
- [ ] Implement Firestore collections for messages
- [ ] Real-time listeners for chat messages
- [ ] Push notifications via Firebase Cloud Messaging

## API Routes
- [ ] Define routes in api.php
- [ ] Group routes with middleware

## Swagger Documentation
- [ ] Add OA annotations to controllers
- [ ] Generate documentation
- [ ] Test API endpoints

## Testing
- [ ] Unit tests for models and services
- [ ] API tests for endpoints
- [ ] Firebase integration tests
