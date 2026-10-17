# Projects and Campaigns Management API Endpoints List

This document lists all API endpoints relevant to the Projects and Campaigns Management frontend integration. It covers endpoints for projects, campaigns, tasks, todos, kanban boards, task dependencies, calendar integration, leads, subscriptions, and coupons.

## Projects APIs

- **List Projects**
  - Method: GET
  - URL: /api/v1/projects

- **Create Project**
  - Method: POST
  - URL: /api/v1/projects

- **Get Single Project**
  - Method: GET
  - URL: /api/v1/projects/{project}

- **Update Project**
  - Method: PUT
  - URL: /api/v1/projects/{project}

- **Delete Project**
  - Method: DELETE
  - URL: /api/v1/projects/{project}

- **Change Project Priority**
  - Method: POST
  - URL: /api/v1/projects/{project}/change-priority

- **Project Comments**
  - Add comment: POST /api/v1/projects/{project}/comments
  - Update comment: PUT /api/v1/projects/{project}/comments/{comment}
  - Delete comment: DELETE /api/v1/projects/{project}/comments/{comment}

## Campaigns APIs

- **List Campaigns**
  - Method: GET
  - URL: /api/v1/campaigns

- **Create Campaign**
  - Method: POST
  - URL: /api/v1/campaigns

- **Get Single Campaign**
  - Method: GET
  - URL: /api/v1/campaigns/{campaign}

- **Update Campaign**
  - Method: PUT
  - URL: /api/v1/campaigns/{campaign}

- **Delete Campaign**
  - Method: DELETE
  - URL: /api/v1/campaigns/{campaign}

- **Campaign Team Members**
  - Add member: POST /api/v1/campaigns/{campaign_id}/teams/members

## Tasks APIs

- **List Tasks**
  - Method: GET
  - URL: /api/v1/tasks

- **Create Task**
  - Method: POST
  - URL: /api/v1/tasks

- **Get Single Task**
  - Method: GET
  - URL: /api/v1/tasks/{task}

- **Update Task**
  - Method: PUT
  - URL: /api/v1/tasks/{task}

- **Delete Task**
  - Method: DELETE
  - URL: /api/v1/tasks/{task}

- **Assign Task to Users**
  - Method: POST
  - URL: /api/v1/tasks/{task_id}/assign

- **Task Conversion to Todos**
  - Convert task to todo: POST /api/v1/tasks/{taskId}/convert-to-todo
  - Break task into todos: POST /api/v1/tasks/{taskId}/break-into-todos
  - Convert subtasks to todos: POST /api/v1/tasks/{taskId}/convert-subtasks-to-todos

## Task Dependencies APIs

- **Task Dependencies Resource**
  - CRUD operations: /api/v1/task-dependencies (resource)

## Todos APIs

- **List Todos**
  - Method: GET
  - URL: /api/v1/todos/{type}

- **Create Todo**
  - Method: POST
  - URL: /api/v1/todos/{type}

- **Get Single Todo**
  - Method: GET
  - URL: /api/v1/todos/{todo}/{type}

- **Update Todo**
  - Method: PUT
  - URL: /api/v1/todos/{todo}/edit/{type}

- **Delete Todo**
  - Method: DELETE
  - URL: /api/v1/todos/{todo}/{type}

- **Delete All Todos**
  - Method: DELETE
  - URL: /api/v1/todos/delete/{all}

## Kanban APIs

- **Get Kanban Board by Project**
  - Method: GET
  - URL: /api/v1/kanban/{project_id}

- **Update Kanban Board Task Positions (Project-specific)**
  - Method: PUT
  - URL: /api/v1/kanban/projects/{project_id}/tasks/update-positions

- **Update Kanban Board Task Positions (Global)**
  - Method: PUT
  - URL: /api/v1/kanban/projects/tasks/update-positions

## Calendar APIs

- **Calendar Resource APIs**
  - Standard CRUD routes via /api/v1/calendars

- **Create Calendar Event from Task**
  - Method: POST
  - URL: /api/v1/calendars/create-from-task/{task_id}

- **Reschedule Calendar Event**
  - Method: PUT
  - URL: /api/v1/calendars/{calendar}/reschedule

- **Resize Calendar Event**
  - Method: PUT
  - URL: /api/v1/calendars/{calendar}/resize

- **Bulk Update Calendar Events**
  - Method: POST
  - URL: /api/v1/calendars/bulk-update

- **Bulk Delete Calendar Events**
  - Method: POST
  - URL: /api/v1/calendars/bulk-delete

- **Update Calendar Event Attendee Status**
  - Method: PUT
  - URL: /api/v1/calendars/{calendar}/attendees/{user_id}/status

- **Check Calendar Conflicts**
  - Method: POST
  - URL: /api/v1/calendars/check-conflicts

## Leads APIs

- **Leads Resource**
  - Standard CRUD routes: /api/v1/leads

- **Add Leads to Campaign**
  - Method: POST
  - URL: /api/v1/campaign/{campaign_id}/leads

## Subscriptions and Coupons APIs

- **Subscriptions Resource**
  - Standard resource routes: /api/v1/subscriptions

- **Coupons Resource**
  - Standard resource routes: /api/v1/coupons

---

This API list fully covers the projects and campaigns management backend routes as documented and used in the frontend implementation. It includes all CRUD, special operations, and integration points necessary for task-to-todo conversion, Kanban board management, calendar event synchronization, and campaign lead management.
