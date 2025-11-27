# Email Management System Documentation

## Overview
The Dukaverse Backend provides a comprehensive email management system that allows administrators to configure multiple email accounts, manage incoming emails, send emails with attachments, create email signatures, and set up automated email responses.

## Features
- Multiple email account configurations (IMAP/SMTP)
- Automatic email fetching via scheduled jobs
- Manual email checking
- Email sending with attachments
- Email signatures management
- Automated email responses
- No-reply email settings
- Email statistics and analytics
- Bulk operations on notifications
- Swagger API documentation

## Email Configurations

### Creating Email Config
```http
POST /api/v1/admin/emails/configs
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "client_name": "Gmail Account",
  "imap_host": "imap.gmail.com",
  "imap_port": 993,
  "imap_encryption": "ssl",
  "imap_username": "your-email@gmail.com",
  "imap_password": "app-password",
  "smtp_host": "smtp.gmail.com",
  "smtp_port": 587,
  "smtp_encryption": "tls",
  "smtp_username": "your-email@gmail.com",
  "smtp_password": "app-password",
  "from_email": "your-email@gmail.com",
  "from_name": "Your Name",
  "no_reply_email": "noreply@yourdomain.com",
  "no_reply_name": "No Reply",
  "active": true
}
```

### Getting Email Configs
```http
GET /api/v1/admin/emails/configs
Authorization: Bearer {admin_token}
```

### Getting Single Config
```http
GET /api/v1/admin/emails/configs/{config_id}
Authorization: Bearer {admin_token}
```

### Updating Config
```http
PUT /api/v1/admin/emails/configs/{config_id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "client_name": "Updated Gmail Account",
  "active": false
}
```

### Deleting Config
```http
DELETE /api/v1/admin/emails/configs/{config_id}
Authorization: Bearer {admin_token}
```

## Email Notifications

### Getting Notifications
```http
GET /api/v1/admin/emails/notifications?processed=false&page=1
Authorization: Bearer {admin_token}
```

### Marking as Processed
```http
PUT /api/v1/admin/emails/notifications/{notification_id}/processed
Authorization: Bearer {admin_token}
```

### Bulk Operations
```http
POST /api/v1/admin/emails/notifications/bulk-mark-processed
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "notification_ids": [1, 2, 3]
}
```

```http
POST /api/v1/admin/emails/notifications/bulk-delete
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "notification_ids": [1, 2, 3]
}
```

### Archive Notifications
```http
POST /api/v1/admin/emails/notifications/archive
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "notification_ids": [1, 2, 3]
}
```

## Sending Emails

### Send Simple Email
```http
POST /api/v1/admin/emails/send
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "config_id": 1,
  "to": ["recipient@example.com"],
  "subject": "Test Email",
  "body": "This is a test email body"
}
```

### Send Email with Attachments
```http
POST /api/v1/admin/emails/send-with-attachments
Authorization: Bearer {admin_token}
Content-Type: multipart/form-data

{
  "config_id": 1,
  "to": ["recipient@example.com"],
  "subject": "Email with Attachments",
  "body": "Please find attached files",
  "attachments": [file1, file2] // Files up to 10MB each
}
```

### Resend Email
```http
POST /api/v1/admin/emails/resend
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "notification_id": 1
}
```

## Manual Email Check
```http
POST /api/v1/admin/emails/configs/{config_id}/check
Authorization: Bearer {admin_token}
```

## Email Statistics
```http
GET /api/v1/admin/emails/statistics?config_id=1
Authorization: Bearer {admin_token}
```

Response:
```json
{
  "total_notifications": 150,
  "processed_notifications": 120,
  "unprocessed_notifications": 30,
  "today_notifications": 5,
  "week_notifications": 25,
  "month_notifications": 75
}
```

## Email Signatures

### Creating Signature
```http
POST /api/v1/admin/emails/signatures
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "name": "Professional Signature",
  "content": "<p>Best regards,<br>Your Name<br>Position<br>Company</p>",
  "email_config_id": 1,
  "is_default": true,
  "active": true
}
```

### Getting Signatures
```http
GET /api/v1/admin/emails/signatures?config_id=1
Authorization: Bearer {admin_token}
```

### Updating Signature
```http
PUT /api/v1/admin/emails/signatures/{signature_id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "name": "Updated Signature",
  "is_default": false
}
```

### Deleting Signature
```http
DELETE /api/v1/admin/emails/signatures/{signature_id}
Authorization: Bearer {admin_token}
```

## Auto Emails

### Creating Auto Email
```http
POST /api/v1/admin/emails/auto-emails
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "name": "Welcome Email",
  "trigger_event": "user_registered",
  "subject": "Welcome to Dukaverse!",
  "body": "Welcome {user_name}! Thank you for registering.",
  "email_config_id": 1,
  "conditions": {
    "user_type": "customer"
  },
  "delay_minutes": 5,
  "active": true
}
```

### Getting Auto Emails
```http
GET /api/v1/admin/emails/auto-emails?config_id=1
Authorization: Bearer {admin_token}
```

### Updating Auto Email
```http
PUT /api/v1/admin/emails/auto-emails/{auto_email_id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "active": false
}
```

### Deleting Auto Email
```http
DELETE /api/v1/admin/emails/auto-emails/{auto_email_id}
Authorization: Bearer {admin_token}
```

## Frontend Implementation Guide

### Admin Dashboard Structure
```
Email Management
├── Configurations
│   ├── List all email configs
│   ├── Add new config
│   ├── Edit config
│   ├── Delete config
│   └── Test connection
├── Inbox (Notifications)
│   ├── List incoming emails
│   ├── Mark as read/processed
│   ├── Bulk actions
│   ├── Search and filter
│   └── View email details
├── Compose
│   ├── Send new email
│   ├── Attach files
│   ├── Use signatures
│   └── Select email config
├── Signatures
│   ├── Create/edit signatures
│   ├── HTML editor
│   ├── Set default signature
│   └── Preview
├── Auto Emails
│   ├── Create automated responses
│   ├── Set triggers and conditions
│   ├── Delay settings
│   └── Enable/disable
└── Statistics
    ├── Email counts
    ├── Processing status
    ├── Time-based analytics
    └── Config performance
```

### Key Frontend Components

#### Email Config Form
```javascript
const EmailConfigForm = () => {
  const [config, setConfig] = useState({
    client_name: '',
    imap_host: '',
    imap_port: 993,
    imap_encryption: 'ssl',
    imap_username: '',
    imap_password: '',
    smtp_host: '',
    smtp_port: 587,
    smtp_encryption: 'tls',
    smtp_username: '',
    smtp_password: '',
    from_email: '',
    from_name: '',
    no_reply_email: '',
    no_reply_name: '',
    active: true
  });

  const handleSubmit = async () => {
    try {
      const response = await fetch('/api/v1/admin/emails/configs', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(config)
      });
      // Handle success
    } catch (error) {
      // Handle error
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      {/* Form fields for all config properties */}
    </form>
  );
};
```

#### Email Composer with Attachments
```javascript
const EmailComposer = () => {
  const [email, setEmail] = useState({
    config_id: '',
    to: [],
    subject: '',
    body: '',
    attachments: []
  });

  const handleFileUpload = (files) => {
    setEmail(prev => ({
      ...prev,
      attachments: [...prev.attachments, ...files]
    }));
  };

  const handleSend = async () => {
    const formData = new FormData();
    Object.keys(email).forEach(key => {
      if (key === 'attachments') {
        email.attachments.forEach(file => {
          formData.append('attachments[]', file);
        });
      } else if (key === 'to') {
        formData.append(key, JSON.stringify(email[key]));
      } else {
        formData.append(key, email[key]);
      }
    });

    try {
      const response = await fetch('/api/v1/admin/emails/send-with-attachments', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`
        },
        body: formData
      });
      // Handle success
    } catch (error) {
      // Handle error
    }
  };

  return (
    <div>
      {/* Email composition form */}
      <input type="file" multiple onChange={handleFileUpload} />
      <button onClick={handleSend}>Send Email</button>
    </div>
  );
};
```

#### Email Statistics Dashboard
```javascript
const EmailStatistics = () => {
  const [stats, setStats] = useState({});

  useEffect(() => {
    fetchStats();
  }, []);

  const fetchStats = async () => {
    try {
      const response = await fetch('/api/v1/admin/emails/statistics', {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });
      const data = await response.json();
      setStats(data);
    } catch (error) {
      // Handle error
    }
  };

  return (
    <div className="stats-grid">
      <div className="stat-card">
        <h3>Total Notifications</h3>
        <p>{stats.total_notifications}</p>
      </div>
      <div className="stat-card">
        <h3>Processed</h3>
        <p>{stats.processed_notifications}</p>
      </div>
      <div className="stat-card">
        <h3>Unprocessed</h3>
        <p>{stats.unprocessed_notifications}</p>
      </div>
      <div className="stat-card">
        <h3>Today</h3>
        <p>{stats.today_notifications}</p>
      </div>
    </div>
  );
};
```

### Bulk Operations
```javascript
const EmailInbox = () => {
  const [selectedEmails, setSelectedEmails] = useState([]);

  const handleBulkMarkProcessed = async () => {
    try {
      const response = await fetch('/api/v1/admin/emails/notifications/bulk-mark-processed', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          notification_ids: selectedEmails
        })
      });
      // Handle success - refresh list
    } catch (error) {
      // Handle error
    }
  };

  const handleBulkDelete = async () => {
    if (confirm('Are you sure you want to delete selected emails?')) {
      try {
        const response = await fetch('/api/v1/admin/emails/notifications/bulk-delete', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            notification_ids: selectedEmails
          })
        });
        // Handle success
      } catch (error) {
        // Handle error
      }
    }
  };

  return (
    <div>
      <div className="bulk-actions">
        <button onClick={handleBulkMarkProcessed}>Mark as Processed</button>
        <button onClick={handleBulkDelete}>Delete Selected</button>
      </div>
      {/* Email list with checkboxes */}
    </div>
  );
};
```

## Security Considerations
- All email management endpoints require admin authentication
- File uploads are limited to 10MB per file
- IMAP/SMTP credentials are encrypted in database
- Rate limiting on email sending operations
- Input validation on all email-related data

## Error Handling
- Invalid email configurations return 400 status
- IMAP/SMTP connection failures are logged
- File upload errors provide specific messages
- Bulk operations handle partial failures gracefully

## Testing
Use these commands to test the email management system:

```bash
# Create email config
curl -X POST http://localhost:8000/api/v1/admin/emails/configs \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"client_name":"Test","imap_host":"imap.test.com","smtp_host":"smtp.test.com","from_email":"test@test.com","active":true}'

# Send email
curl -X POST http://localhost:8000/api/v1/admin/emails/send \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"config_id":1,"to":["test@example.com"],"subject":"Test","body":"Hello"}'

# Get statistics
curl -X GET http://localhost:8000/api/v1/admin/emails/statistics \
  -H "Authorization: Bearer {token}"
```

## Configuration
Ensure these settings are configured in your environment:

```env
# IMAP Settings (for email fetching)
IMAP_HOST=imap.gmail.com
IMAP_PORT=993
IMAP_ENCRYPTION=ssl
IMAP_USERNAME=your-email@gmail.com
IMAP_PASSWORD=your-app-password

# SMTP Settings (for sending emails)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"

# File Storage
FILESYSTEM_DISK=public
```

## Scheduled Jobs
Set up the following cron job for automatic email checking:

```bash
# Check emails every 5 minutes
* /5 * * * * php artisan email:check
```

This will run the `CheckEmailsJob` for all active email configurations.
