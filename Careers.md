# Dukaverse Careers API Documentation

## Overview

The Dukaverse Careers API provides a comprehensive job posting and application management system. This API allows companies to post job openings and candidates to apply for positions, with full tracking and management capabilities.

## Features

- **Job Postings**: Create, update, and manage job listings
- **Job Applications**: Submit applications with cover letters and salary expectations
- **Application Tracking**: Monitor application status and manage hiring pipeline
- **Filtering & Search**: Find jobs by type, experience level, and location
- **User Management**: Separate interfaces for job seekers and employers

## API Endpoints

### Job Management

#### List Jobs
```
GET /api/v1/jobs
```
**Query Parameters:**
- `job_type` (optional): Filter by job type (full-time, part-time, contract, freelance, internship)
- `experience_level` (optional): Filter by experience level (entry, mid, senior, executive)
- `location` (optional): Filter by location (partial match)

**Response:**
```json
{
  "jobs": [...],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 100
  }
}
```

#### Create Job
```
POST /api/v1/jobs
```
**Request Body:**
```json
{
  "title": "Senior Laravel Developer",
  "description": "We are looking for an experienced Laravel developer...",
  "requirements": "3+ years of Laravel experience, PHP 8.0+, MySQL...",
  "location": "Nairobi, Kenya",
  "job_type": "full-time",
  "experience_level": "senior",
  "salary_min": 50000,
  "salary_max": 80000,
  "currency": "USD",
  "department": "Engineering",
  "application_deadline": "2024-12-31T23:59:59Z",
  "benefits": ["Health Insurance", "Remote Work", "Professional Development"],
  "skills_required": ["Laravel", "PHP", "MySQL", "REST APIs"]
}
```

#### Get Job Details
```
GET /api/v1/jobs/{id}
```

#### Update Job
```
PUT /api/v1/jobs/{id}
```

#### Delete Job
```
DELETE /api/v1/jobs/{id}
```

### Job Applications

#### Apply for Job
```
POST /api/v1/jobs/{job}/apply
```
**Request Body:**
```json
{
  "cover_letter": "I am excited to apply for this position because...",
  "expected_salary": 60000,
  "currency": "USD"
}
```

#### Get My Applications
```
GET /api/v1/my-job-applications
```

#### Get Applications for Job (Employer Only)
```
GET /api/v1/jobs/{job}/applications
```

#### Update Application Status (Employer Only)
```
PUT /api/v1/jobs/applications/{application}/status
```
**Request Body:**
```json
{
  "status": "shortlisted",
  "notes": "Strong technical background, good fit for the team"
}
```

#### Select Interview Date (Applicant Only)
```
POST /api/v1/jobs/applications/{application}/select-interview-date
```
**Request Body:**
```json
{
  "start_time": "2025-09-22T10:00:00Z",
  "end_time": "2025-09-22T11:00:00Z",
  "location": "Zoom Meeting Room",
  "meeting_link": "https://zoom.us/j/123456789"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "application": {
      "id": 1,
      "status": "interviewed",
      "calendar": {
        "id": 10,
        "title": "Interview: Senior Laravel Developer",
        "start_time": "2025-09-22T10:00:00Z",
        "end_time": "2025-09-22T11:00:00Z",
        "location": "Zoom Meeting Room",
        "meeting_link": "https://zoom.us/j/123456789"
      },
      "task": {
        "id": 5,
        "title": "Interview: Senior Laravel Developer - John Doe",
        "status": "pending"
      }
    }
  },
  "message": "Interview date selected successfully"
}
```

**Error Response (Time Conflict):**
```json
{
  "success": false,
  "message": "Time conflict",
  "errors": {
    "error": "The selected time conflicts with existing scheduled events.",
    "conflicts": [
      {
        "id": 15,
        "title": "Team Standup",
        "start_time": "2025-09-22T09:30:00Z",
        "end_time": "2025-09-22T10:30:00Z"
      }
    ]
  }
}
```

## Application Status Flow

1. **pending** - Initial application status
2. **reviewed** - Application has been reviewed
3. **shortlisted** - Candidate moved to shortlist
4. **interviewed** - Interview process initiated
5. **rejected** - Application rejected
6. **hired** - Candidate hired
7. **withdrawn** - Application withdrawn by candidate

## Job Types

- `full-time` - Full-time employment
- `part-time` - Part-time employment
- `contract` - Contract-based work
- `freelance` - Freelance projects
- `internship` - Internship positions

## Experience Levels

- `entry` - Entry level (0-2 years)
- `mid` - Mid level (2-5 years)
- `senior` - Senior level (5-10 years)
- `executive` - Executive level (10+ years)

## Authentication

All endpoints require Bearer token authentication. Include the token in the Authorization header:

```
Authorization: Bearer {your_token}
```

## Error Handling

The API returns standardized error responses:

```json
{
  "error": "Validation failed",
  "errors": {
    "title": ["The title field is required."]
  }
}
```

## Rate Limiting

- Job listing: 60 requests per minute
- Job applications: 10 requests per minute
- General endpoints: 30 requests per minute

## Data Models

### Job
```json
{
  "id": 1,
  "title": "Senior Laravel Developer",
  "description": "...",
  "requirements": "...",
  "location": "Nairobi, Kenya",
  "job_type": "full-time",
  "experience_level": "senior",
  "salary_min": 50000,
  "salary_max": 80000,
  "currency": "USD",
  "department": "Engineering",
  "is_active": true,
  "application_deadline": "2024-12-31T23:59:59Z",
  "benefits": ["Health Insurance", "Remote Work"],
  "skills_required": ["Laravel", "PHP", "MySQL"],
  "posted_by": 1,
  "created_at": "2024-01-15T10:00:00Z",
  "updated_at": "2024-01-15T10:00:00Z",
  "formatted_salary": "USD 50,000 - 80,000",
  "poster": {
    "id": 1,
    "name": "John Doe"
  }
}
```

### Job Application
```json
{
  "id": 1,
  "job_id": 1,
  "applicant_id": 2,
  "cover_letter": "...",
  "status": "pending",
  "expected_salary": 60000,
  "currency": "USD",
  "notes": null,
  "reviewed_at": null,
  "reviewed_by": null,
  "created_at": "2024-01-16T14:30:00Z",
  "updated_at": "2024-01-16T14:30:00Z",
  "formatted_expected_salary": "USD 60,000",
  "job": {
    "id": 1,
    "title": "Senior Laravel Developer",
    "location": "Nairobi, Kenya",
    "job_type": "full-time"
  },
  "applicant": {
    "id": 2,
    "name": "Jane Smith",
    "email": "jane@example.com"
  }
}
```

## Integration Examples

### JavaScript (Fetch API)
```javascript
// Get jobs
const response = await fetch('/api/v1/jobs', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
});
const data = await response.json();

// Apply for job
const applicationData = {
  cover_letter: "I am very interested in this position...",
  expected_salary: 65000,
  currency: "USD"
};

const applyResponse = await fetch(`/api/v1/jobs/${jobId}/apply`, {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify(applicationData)
});
```

### PHP (Guzzle)
```php
use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'https://api.dukaverse.com/api/v1/',
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json',
    ]
]);

// Get jobs
$response = $client->get('jobs');
$jobs = json_decode($response->getBody(), true);

// Apply for job
$applicationData = [
    'cover_letter' => 'I am very interested in this position...',
    'expected_salary' => 65000,
    'currency' => 'USD'
];

$response = $client->post("jobs/{$jobId}/apply", [
    'json' => $applicationData
]);
```

## Webhooks (Future Feature)

Webhooks will be available for:
- New job applications
- Application status changes
- Job posting updates

## Support

For API support or questions, please contact:
- Email: api-support@dukaverse.com
- Documentation: https://docs.dukaverse.com/careers-api
- Status Page: https://status.dukaverse.com
