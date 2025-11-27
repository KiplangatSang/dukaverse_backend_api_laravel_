# App Notifications Frontend Implementation Guide

This document provides a comprehensive guide for implementing app notifications in the frontend, including notification types, user preferences management, and all available API endpoints.

## Overview

The app notification system allows users to receive real-time notifications through the application with Firebase integration. Notifications can be associated with various entities via polymorphic relationships.

## Notification Types

The system supports the following notification types:

- **info**: General informational messages (default)
- **success**: Success confirmations and positive updates
- **warning**: Warnings and alerts requiring attention
- **error**: Error messages and critical issues

Each notification type can be styled differently in the frontend to provide visual distinction.

## User Notification Preferences

Users can manage their notification preferences to control which types of notifications they receive and how they are delivered.

### Saving Notification Preferences

To save user preferences for notification types:

1. **API Endpoint**: `POST /api/v1/user/notification-preferences`
2. **Request Body**:
   ```json
   {
     "enabled_types": ["info", "success", "warning", "error"],
     "email_notifications": true,
     "push_notifications": true,
     "sms_notifications": false
   }
   ```

### Removing Notification Types

To disable specific notification types:

1. **API Endpoint**: `PUT /api/v1/user/notification-preferences`
2. **Request Body**:
   ```json
   {
     "enabled_types": ["success", "warning"],
     "email_notifications": false,
     "push_notifications": true,
     "sms_notifications": false
   }
   ```

### Retrieving User Preferences

To get current user preferences:

1. **API Endpoint**: `GET /api/v1/user/notification-preferences`
2. **Response**:
   ```json
   {
     "enabled_types": ["info", "success", "warning", "error"],
     "email_notifications": true,
     "push_notifications": true,
     "sms_notifications": false
   }
   ```

## API Endpoints

All endpoints require authentication via Bearer token.

### 1. Get All Notifications

**Endpoint**: `GET /api/v1/app-notifications`

**Query Parameters**:
- `read` (boolean): Filter by read status
- `type` (string): Filter by notification type
- `per_page` (integer): Number of notifications per page (default: 15)

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "title": "New Message",
      "message": "You have received a new message",
      "type": "info",
      "read": false,
      "read_at": null,
      "created_at": "2025-01-15T10:30:00Z",
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
      }
    }
  ],
  "meta": {
    "total": 25,
    "per_page": 15,
    "current_page": 1,
    "last_page": 2,
    "unread_count": 5
  }
}
```

### 2. Create Notification

**Endpoint**: `POST /api/v1/app-notifications`

**Request Body**:
```json
{
  "title": "New Message",
  "message": "You have received a new message",
  "type": "info",
  "data": {
    "sender_id": 2,
    "message_id": 123
  }
}
```

**Response**: Created notification object

### 3. Get Single Notification

**Endpoint**: `GET /api/v1/app-notifications/{id}`

**Response**: Single notification object

### 4. Update Notification

**Endpoint**: `PUT /api/v1/app-notifications/{id}`

**Request Body**:
```json
{
  "read": true
}
```

**Response**: Updated notification object

### 5. Delete Notification

**Endpoint**: `DELETE /api/v1/app-notifications/{id}`

**Response**: Success message

### 6. Mark All Notifications as Read

**Endpoint**: `POST /api/v1/app-notifications/mark-all-read`

**Response**:
```json
{
  "marked_count": 5
}
```

### 7. Delete All Read Notifications

**Endpoint**: `DELETE /api/v1/app-notifications/delete-read`

**Response**:
```json
{
  "deleted_count": 10
}
```

### 8. Get Notification Statistics

**Endpoint**: `GET /api/v1/app-notifications/stats`

**Response**:
```json
{
  "total": 25,
  "unread": 5,
  "read": 20,
  "today": 3
}
```

## Frontend Implementation

### 1. Notification List Component

```javascript
// React/Vue example for notification list
const NotificationList = () => {
  const [notifications, setNotifications] = useState([]);
  const [loading, setLoading] = useState(true);
  const [stats, setStats] = useState({});

  useEffect(() => {
    fetchNotifications();
    fetchStats();
  }, []);

  const fetchNotifications = async (page = 1, filters = {}) => {
    try {
      const params = new URLSearchParams({
        page,
        per_page: 15,
        ...filters
      });

      const response = await fetch(`/api/v1/app-notifications?${params}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      });

      const data = await response.json();
      setNotifications(data.data);
    } catch (error) {
      console.error('Error fetching notifications:', error);
    } finally {
      setLoading(false);
    }
  };

  const fetchStats = async () => {
    try {
      const response = await fetch('/api/v1/app-notifications/stats', {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });

      const data = await response.json();
      setStats(data);
    } catch (error) {
      console.error('Error fetching stats:', error);
    }
  };

  const markAsRead = async (notificationId) => {
    try {
      await fetch(`/api/v1/app-notifications/${notificationId}`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ read: true })
      });

      // Update local state
      setNotifications(prev =>
        prev.map(n =>
          n.id === notificationId ? { ...n, read: true, read_at: new Date() } : n
        )
      );
    } catch (error) {
      console.error('Error marking notification as read:', error);
    }
  };

  const deleteNotification = async (notificationId) => {
    try {
      await fetch(`/api/v1/app-notifications/${notificationId}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });

      // Remove from local state
      setNotifications(prev => prev.filter(n => n.id !== notificationId));
    } catch (error) {
      console.error('Error deleting notification:', error);
    }
  };

  return (
    <div className="notification-list">
      <div className="notification-stats">
        <span>Total: {stats.total}</span>
        <span>Unread: {stats.unread}</span>
      </div>

      {loading ? (
        <div>Loading...</div>
      ) : (
        notifications.map(notification => (
          <div
            key={notification.id}
            className={`notification-item ${notification.type} ${notification.read ? 'read' : 'unread'}`}
          >
            <h4>{notification.title}</h4>
            <p>{notification.message}</p>
            <div className="notification-actions">
              {!notification.read && (
                <button onClick={() => markAsRead(notification.id)}>
                  Mark as Read
                </button>
              )}
              <button onClick={() => deleteNotification(notification.id)}>
                Delete
              </button>
            </div>
          </div>
        ))
      )}
    </div>
  );
};
```

### 2. Notification Preferences Component

```javascript
const NotificationPreferences = () => {
  const [preferences, setPreferences] = useState({
    enabled_types: ['info', 'success', 'warning', 'error'],
    email_notifications: true,
    push_notifications: true,
    sms_notifications: false
  });

  useEffect(() => {
    fetchPreferences();
  }, []);

  const fetchPreferences = async () => {
    try {
      const response = await fetch('/api/v1/user/notification-preferences', {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });

      const data = await response.json();
      setPreferences(data);
    } catch (error) {
      console.error('Error fetching preferences:', error);
    }
  };

  const updatePreferences = async () => {
    try {
      await fetch('/api/v1/user/notification-preferences', {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(preferences)
      });

      alert('Preferences updated successfully');
    } catch (error) {
      console.error('Error updating preferences:', error);
    }
  };

  const handleTypeToggle = (type) => {
    setPreferences(prev => ({
      ...prev,
      enabled_types: prev.enabled_types.includes(type)
        ? prev.enabled_types.filter(t => t !== type)
        : [...prev.enabled_types, type]
    }));
  };

  return (
    <div className="notification-preferences">
      <h3>Notification Preferences</h3>

      <div className="notification-types">
        <h4>Enabled Types</h4>
        {['info', 'success', 'warning', 'error'].map(type => (
          <label key={type}>
            <input
              type="checkbox"
              checked={preferences.enabled_types.includes(type)}
              onChange={() => handleTypeToggle(type)}
            />
            {type.charAt(0).toUpperCase() + type.slice(1)}
          </label>
        ))}
      </div>

      <div className="delivery-methods">
        <h4>Delivery Methods</h4>
        <label>
          <input
            type="checkbox"
            checked={preferences.email_notifications}
            onChange={(e) => setPreferences(prev => ({
              ...prev,
              email_notifications: e.target.checked
            }))}
          />
          Email Notifications
        </label>

        <label>
          <input
            type="checkbox"
            checked={preferences.push_notifications}
            onChange={(e) => setPreferences(prev => ({
              ...prev,
              push_notifications: e.target.checked
            }))}
          />
          Push Notifications
        </label>

        <label>
          <input
            type="checkbox"
            checked={preferences.sms_notifications}
            onChange={(e) => setPreferences(prev => ({
              ...prev,
              sms_notifications: e.target.checked
            }))}
          />
          SMS Notifications
        </label>
      </div>

      <button onClick={updatePreferences}>Save Preferences</button>
    </div>
  );
};
```

### 3. Real-time Updates with WebSockets/Firebase

For real-time notification updates, integrate with Firebase Cloud Messaging:

```javascript
import { getMessaging, onMessage } from 'firebase/messaging';

const messaging = getMessaging();

onMessage(messaging, (payload) => {
  // Handle incoming notification
  const notification = payload.data;

  // Add to notification list
  addNewNotification(notification);

  // Show browser notification
  if (Notification.permission === 'granted') {
    new Notification(notification.title, {
      body: notification.message,
      icon: '/notification-icon.png'
    });
  }
});
```

## Error Handling

Always handle API errors gracefully:

```javascript
const handleApiError = (error) => {
  if (error.response?.status === 401) {
    // Redirect to login
    redirectToLogin();
  } else if (error.response?.status === 403) {
    // Show unauthorized message
    showError('You do not have permission to perform this action');
  } else {
    // Show generic error
    showError('An error occurred. Please try again.');
  }
};
```

## Best Practices

1. **Pagination**: Always implement pagination for notification lists
2. **Real-time Updates**: Use WebSockets or Firebase for real-time notifications
3. **Offline Support**: Cache notifications locally for offline viewing
4. **Accessibility**: Ensure notifications are accessible to screen readers
5. **Performance**: Limit the number of notifications fetched at once
6. **User Experience**: Provide clear visual indicators for different notification types
7. **Privacy**: Respect user preferences and do not send unwanted notifications

## Testing

Test the following scenarios:

- Creating notifications of all types
- Marking notifications as read/unread
- Deleting notifications
- Bulk operations (mark all read, delete read)
- Filtering by type and read status
- Real-time notification delivery
- User preference management
- Error handling for network issues
- Offline functionality

This implementation guide covers all aspects of the app notification system for frontend development.
