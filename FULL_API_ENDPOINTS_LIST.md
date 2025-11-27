# Full API Endpoints List

This document contains a comprehensive list of all API endpoints found in the project’s route files including ecommerce, permissions, platforms, retails, projects, campaigns, tasks, calendars, subscriptions, coupons, auth, and more.

You can use this document to manually update your API documentation or routes.

---

## Authentication & User

- POST /login
- POST /register
- POST /forgot-password
- POST /reset-password
- POST /login/validate-token
- GET /auth/{provider}
- GET /auth/{provider}/callback
- POST /auth/{provider}/link
- DELETE /auth/{provider}/unlink
- GET /auth/linked-accounts
- POST /logout

## Platforms

- GET /platforms
- POST /platforms
- GET /platforms/{platform}
- POST /platforms/{platform}
- POST /platforms/{platform}/users
- POST /platforms/{platform}/users/{user}/role
- POST /platforms/{platform}/users/{user}/unassign-role
- GET /platforms/{platform}/users
- GET /platforms/{platform}/users/{user}
- POST /platforms/{platform}/users/{user}/remove
- GET /platforms/{platform}/users/{user}/role
- GET /platforms/{platform}/users/{user}/permissions

## Email Verification

- GET /email/verify/{id}/{hash}
- POST /email/resend

## Retails & Sessions

- POST /retails/simple
- GET /offices/office/create-office
- POST /offices/office/create-office
- Resource: /retails
- Resource: /retailsessions

## Retail Items

- Resource: /retailitems
- PUT /retailitems/{id}
- POST /retailitems/{id}

## Dashboard

- GET /dashboard/analytics
- GET /dashboard/projects

## Projects

- Resource: /projects
- POST /projects/{project}/change-priority
- POST /projects/{project}/comments
- PUT /projects/{project}/comments/{comment}
- DELETE /projects/{project}/comments/{comment}

## Campaigns

- Resource: /campaigns
- POST /campaigns/{campaign_id}/teams/members

## Tasks

- Resource: /tasks
- POST /tasks/{task_id}/assign
- POST /tasks/{taskId}/convert-to-todo
- POST /tasks/{taskId}/break-into-todos
- POST /tasks/{taskId}/convert-subtasks-to-todos

## Task Dependencies

- Resource: /task-dependencies

## Todos

- Resource: /todos
- DELETE /todos/delete/{all}
- GET /todos/{type}
- GET /todos/create/{type}
- GET /todos/{todo}/{type}
- PUT /todos/{todo}/edit/{type}
- PUT /todos/update/{todo}/{type}
- DELETE /todos/{todo}/{type}

## Kanban

- GET /kanban/projects/{project_id}/tasks
- PUT /kanban/projects/{project_id}/tasks/update-positions
- PUT /kanban/projects/tasks/update-positions

## Calendars

- Resource: /calendars
- POST /calendars/create-from-task/{task_id}
- PUT /calendars/{calendar}/reschedule
- PUT /calendars/{calendar}/resize
- POST /calendars/bulk-update
- POST /calendars/bulk-delete
- PUT /calendars/{calendar}/attendees/{user_id}/status
- POST /calendars/check-conflicts
- POST /calendars/{calendar}/attendees
- DELETE /calendars/{calendar}/attendees/{attendee}

## Leads

- Resource: /leads
- POST /campaign/{campaign_id}/leads

## Subscriptions

- Resource: /subscriptions

## Coupons

- Resource: /coupons

---

This list reflects the entire set of route API endpoints derived from your project’s route files for a complete overview.
