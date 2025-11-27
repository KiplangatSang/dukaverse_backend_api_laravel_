# Projects and Campaigns Management Frontend Implementation

This document outlines the frontend implementation for managing Projects, Campaigns, Tasks, Todos, and their relationships in the Dukaverse application.

## Table of Contents
1. [Projects Management](#projects-management)
2. [Campaigns Management](#campaigns-management)
3. [Tasks Management](#tasks-management)
4. [Todos Management](#todos-management)
5. [Kanban Board Implementation](#kanban-board-implementation)
6. [Task Dependencies](#task-dependencies)
7. [Task Priorities](#task-priorities)
8. [Converting Tasks to Todos](#converting-tasks-to-todos)
9. [Calendar Integration](#calendar-integration)
10. [API Routes Reference](#api-routes-reference)
11. [API Integration Examples](#api-integration-examples)

## Projects Management

### Project CRUD Operations

#### Create Project
```javascript
// API Endpoint: POST /api/v1/projects
const createProject = async (projectData) => {
  const formData = new FormData();
  formData.append('name', projectData.name);
  formData.append('overview', projectData.overview);
  formData.append('start_date', projectData.startDate);
  formData.append('due_date', projectData.dueDate);
  formData.append('budget', projectData.budget);
  formData.append('avatar', projectData.avatar);
  formData.append('teamMembers', JSON.stringify(projectData.teamMembers));

  const response = await fetch('/api/v1/projects', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`
    },
    body: formData
  });

  return response.json();
};
```

#### Fetch Projects
```javascript
// API Endpoint: GET /api/v1/projects
const fetchProjects = async () => {
  const response = await fetch('/api/v1/projects', {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });

  const data = await response.json();
  return data.data.projects;
};
```

#### Update Project
```javascript
// API Endpoint: PUT /api/v1/projects/{id}
const updateProject = async (projectId, projectData) => {
  const response = await fetch(`/api/v1/projects/${projectId}`, {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      name: projectData.name,
      overview: projectData.overview,
      start_date: projectData.startDate,
      due_date: projectData.dueDate,
      budget: projectData.budget,
      status: projectData.status,
      priority: projectData.priority
    })
  });

  return response.json();
};
```

#### Delete Project
```javascript
// API Endpoint: DELETE /api/v1/projects/{id}
const deleteProject = async (projectId) => {
  const response = await fetch(`/api/v1/projects/${projectId}`, {
    method: 'DELETE',
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });

  return response.json();
};
```

### Project Priority Management
```javascript
// API Endpoint: POST /api/v1/projects/{project}/change-priority
const changeProjectPriority = async (projectId, priority) => {
  const response = await fetch(`/api/v1/projects/${projectId}/change-priority`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ priority })
  });

  return response.json();
};
```

### Project Comments
```javascript
// Add comment: POST /api/v1/projects/{project}/comments
const addProjectComment = async (projectId, content) => {
  const response = await fetch(`/api/v1/projects/${projectId}/comments`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ content })
  });

  return response.json();
};

// Update comment: PUT /api/v1/projects/{project}/comments/{comment}
const updateProjectComment = async (projectId, commentId, content) => {
  const response = await fetch(`/api/v1/projects/${projectId}/comments/${commentId}`, {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ content })
  });

  return response.json();
};
```

## Campaigns Management

### Campaign CRUD Operations

#### Create Campaign
```javascript
// API Endpoint: POST /api/v1/campaigns
const createCampaign = async (campaignData) => {
  const formData = new FormData();
  formData.append('name', campaignData.name);
  formData.append('description', campaignData.description);
  formData.append('budget', campaignData.budget);
  formData.append('status', campaignData.status);
  formData.append('start_date', campaignData.startDate);
  formData.append('due_date', campaignData.dueDate);
  formData.append('target', campaignData.target);
  formData.append('avatar', campaignData.avatar);
  formData.append('teamMembers', JSON.stringify(campaignData.teamMembers));

  const response = await fetch('/api/v1/campaigns', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`
    },
    body: formData
  });

  return response.json();
};
```

#### Fetch Campaigns
```javascript
// API Endpoint: GET /api/v1/campaigns
const fetchCampaigns = async () => {
  const response = await fetch('/api/v1/campaigns', {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });

  const data = await response.json();
  return data.data.campaigns;
};
```

#### Update Campaign
```javascript
// API Endpoint: PUT /api/v1/campaigns/{id}
const updateCampaign = async (campaignId, campaignData) => {
  const response = await fetch(`/api/v1/campaigns/${campaignId}`, {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      name: campaignData.name,
      description: campaignData.description,
      budget: campaignData.budget,
      status: campaignData.status,
      target: campaignData.target
    })
  });

  return response.json();
};
```

## Tasks Management

### Task CRUD Operations

#### Create Task
```javascript
// API Endpoint: POST /api/v1/tasks
const createTask = async (taskData) => {
  const response = await fetch('/api/v1/tasks', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      name: taskData.name,
      start_date: taskData.startDate,
      end_date: taskData.endDate,
      progress: taskData.progress,
      priority: taskData.priority,
      project_id: taskData.projectId, // or campaign_id
      dependencies: taskData.dependencies // array of dependency objects
    })
  });

  return response.json();
};
```

#### Fetch Tasks
```javascript
// API Endpoint: GET /api/v1/tasks
const fetchTasks = async () => {
  const response = await fetch('/api/v1/tasks', {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });

  const data = await response.json();
  return data.data.tasks;
};
```

#### Update Task
```javascript
// API Endpoint: PUT /api/v1/tasks/{id}
const updateTask = async (taskId, taskData) => {
  const response = await fetch(`/api/v1/tasks/${taskId}`, {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      name: taskData.name,
      start_date: taskData.startDate,
      end_date: taskData.endDate,
      progress: taskData.progress,
      priority: taskData.priority,
      status: taskData.status
    })
  });

  return response.json();
};
```

#### Assign Task to Team Members
```javascript
// API Endpoint: POST /api/v1/tasks/{task_id}/assign
const assignTask = async (taskId, assignees) => {
  const response = await fetch(`/api/v1/tasks/${taskId}/assign`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      assignees: assignees // array of user IDs
    })
  });

  return response.json();
};
```

### Task Dependencies

#### Create Task Dependency
```javascript
// API Endpoint: POST /api/v1/task-dependencies
const createTaskDependency = async (dependencyData) => {
  const response = await fetch('/api/v1/task-dependencies', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      task_id: dependencyData.taskId,
      depends_on: dependencyData.dependsOnTaskId,
      dependency_type: dependencyData.type, // 'FS', 'SS', 'FF', 'SF'
      project_id: dependencyData.projectId
    })
  });

  return response.json();
};
```

#### Fetch Task Dependencies
```javascript
// API Endpoint: GET /api/v1/task-dependencies
const fetchTaskDependencies = async () => {
  const response = await fetch('/api/v1/task-dependencies', {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });

  const data = await response.json();
  return data.data.task_dependencies;
};
```

## Todos Management

### Todo CRUD Operations

#### Create Todo
```javascript
// API Endpoint: POST /api/v1/todos/{type}
const createTodo = async (todoData, type = 'account') => {
  const response = await fetch(`/api/v1/todos/${type}`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      todo: todoData.todo,
      note: todoData.note,
      project_id: todoData.projectId,
      assigned_to: todoData.assignedTo
    })
  });

  return response.json();
};
```

#### Fetch Todos
```javascript
// API Endpoint: GET /api/v1/todos/{type}
const fetchTodos = async (type = 'account') => {
  const response = await fetch(`/api/v1/todos/${type}`, {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });

  const data = await response.json();
  return data.data;
};
```

#### Update Todo
```javascript
// API Endpoint: PUT /api/v1/todos/{todo}/{type}
const updateTodo = async (todoId, todoData, type = 'account') => {
  const response = await fetch(`/api/v1/todos/${todoId}/${type}`, {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      todo: todoData.todo,
      note: todoData.note,
      done: todoData.done,
      archived: todoData.archived
    })
  });

  return response.json();
};
```

## Kanban Board Implementation

### Fetch Kanban Board
```javascript
// API Endpoint: GET /api/v1/kanban/{project_id}
const fetchKanbanBoard = async (projectId = null) => {
  const url = projectId ? `/api/v1/kanban/${projectId}` : '/api/v1/kanban/0';
  const response = await fetch(url, {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });

  const data = await response.json();
  return data.data;
};
```

### Update Kanban Board (Move Tasks)
```javascript
// API Endpoint: PUT /api/v1/kanban/projects/{project_id}/tasks/update-positions
const updateKanbanBoard = async (projectId, tasks) => {
  const response = await fetch(`/api/v1/kanban/projects/${projectId}/tasks/update-positions`, {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      tasks: tasks.map(task => ({
        id: task.id,
        status: task.status
      }))
    })
  });

  return response.json();
};
```

### Alternative Update for Account-wide Tasks
```javascript
// API Endpoint: PUT /api/v1/kanban/projects/tasks/update-positions
const updateKanbanBoardGlobal = async (tasks) => {
  const response = await fetch('/api/v1/kanban/projects/tasks/update-positions', {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      tasks: tasks.map(task => ({
        id: task.id,
        status: task.status
      }))
    })
  });

  return response.json();
};
```

## Breaking Tasks into Subtasks and Converting to Todos

### Task Subtasks (Hierarchical Tasks)

Tasks can be broken down into subtasks using the `parent_id` relationship. This creates a hierarchical structure where main tasks can have multiple subtasks.

#### Create Subtask
```javascript
// API Endpoint: POST /api/v1/tasks/subtasks (custom endpoint needed)
const createSubtask = async (parentTaskId, subtaskData) => {
  const response = await fetch('/api/v1/tasks/subtasks', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      task_id: parentTaskId,
      name: subtaskData.name,
      user_id: subtaskData.userId,
      start_date: subtaskData.startDate,
      end_date: subtaskData.endDate,
      priority: subtaskData.priority
    })
  });

  return response.json();
};
```

#### Fetch Task with Subtasks
```javascript
// API Endpoint: GET /api/v1/tasks/{id} (includes subtasks in response)
const fetchTaskWithSubtasks = async (taskId) => {
  const response = await fetch(`/api/v1/tasks/${taskId}`, {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });

  const data = await response.json();
  return data.data.task; // Includes subtasks array
};
```

### Task Breakdown into Multiple Subtasks

```javascript
// Function to break down a task into multiple subtasks
const breakTaskIntoSubtasks = async (parentTaskId, subtasksData) => {
  const createdSubtasks = [];

  for (const subtaskData of subtasksData) {
    const response = await fetch('/api/v1/tasks/subtasks', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        task_id: parentTaskId,
        name: subtaskData.name,
        user_id: subtaskData.userId,
        start_date: subtaskData.startDate,
        end_date: subtaskData.endDate,
        priority: subtaskData.priority
      })
    });

    const result = await response.json();
    if (result.success) {
      createdSubtasks.push(result.data.task);
    }
  }

  return createdSubtasks;
};

// Usage example:
const subtasks = [
  { name: 'Research requirements', userId: userId1, startDate: '2025-01-01', endDate: '2025-01-05', priority: 'high' },
  { name: 'Create wireframes', userId: userId2, startDate: '2025-01-06', endDate: '2025-01-10', priority: 'medium' },
  { name: 'Implement features', userId: userId3, startDate: '2025-01-11', endDate: '2025-01-20', priority: 'medium' },
  { name: 'Testing and QA', userId: userId4, startDate: '2025-01-21', endDate: '2025-01-25', priority: 'high' }
];

const createdSubtasks = await breakTaskIntoSubtasks(parentTaskId, subtasks);
```

### Converting Tasks to Todos

**Note:** Dedicated backend APIs have been implemented for converting tasks to todos. The frontend can now use these APIs directly instead of manually creating todos.

#### Convert Task to Todo (Single Todo)
```javascript
// API Endpoint: POST /api/v1/tasks/{taskId}/convert-to-todo
const convertTaskToTodo = async (taskId, todoType = 'account') => {
  const response = await fetch(`/api/v1/tasks/${taskId}/convert-to-todo`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      todo_type: todoType // 'account' or 'user'
    })
  });

  const data = await response.json();

  // If task has subtasks, the API will return a message asking user to choose
  if (data.has_subtasks) {
    // Show user options: create subtasks or convert to single todo
    const userChoice = await promptUserChoice(data.subtasks_count);

    if (userChoice === 'create_subtasks') {
      // Call break-into-todos API
      return await breakTaskIntoTodos(taskId, getSubtaskBreakdown(), todoType);
    } else if (userChoice === 'convert_single') {
      // Force convert to single todo (this would require a different endpoint)
      return await forceConvertToSingleTodo(taskId, todoType);
    } else if (userChoice === 'convert_subtasks') {
      // Convert existing subtasks to todos
      return await convertSubtasksToTodos(taskId, todoType);
    }
  }

  return data;
};

// Helper function to prompt user for choice
const promptUserChoice = async (subtasksCount) => {
  return new Promise((resolve) => {
    const choice = window.confirm(
      `This task has ${subtasksCount} subtasks. Would you like to:\n\n` +
      `• Convert subtasks to todos (recommended)\n` +
      `• Break task into multiple todos\n` +
      `• Convert to single todo only\n\n` +
      `Press OK to convert subtasks, Cancel to break into todos`
    );
    resolve(choice ? 'convert_subtasks' : 'break_into_todos');
  });
};
```

#### Convert Task Subtasks to Todos
```javascript
// API Endpoint: POST /api/v1/tasks/{taskId}/convert-subtasks-to-todos
const convertSubtasksToTodos = async (taskId, todoType = 'account') => {
  const response = await fetch(`/api/v1/tasks/${taskId}/convert-subtasks-to-todos`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      todo_type: todoType // 'account' or 'user'
    })
  });

  return response.json();
};
```

### Breaking Down Tasks into Multiple Todos

```javascript
// API Endpoint: POST /api/v1/tasks/{taskId}/break-into-todos
const breakTaskIntoTodos = async (taskId, todoBreakdown, todoType = 'account') => {
  const response = await fetch(`/api/v1/tasks/${taskId}/break-into-todos`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      breakdown: todoBreakdown,
      todo_type: todoType
    })
  });

  return response.json();
};

// Usage example:
const breakdown = [
  { title: 'Research phase', description: 'Gather requirements and research', assigned_to: userId1 },
  { title: 'Design phase', description: 'Create wireframes and mockups', assigned_to: userId2 },
  { title: 'Implementation', description: 'Code the solution', assigned_to: userId3 },
  { title: 'Testing', description: 'Test and validate the implementation', assigned_to: userId4 }
];

const result = await breakTaskIntoTodos(taskId, breakdown);
```

### Backend API Implementation for Task-to-Todo Conversion

**Note:** The backend APIs have been implemented in the TaskController and routes have been added to the API routes file. The implementation includes logic to handle tasks with subtasks by prompting the frontend to choose the conversion method.

## Task Priorities

### Priority Levels
- `low`
- `medium`
- `high`

### Update Task Priority
```javascript
const updateTaskPriority = async (taskId, priority) => {
  const response = await fetch(`/api/v1/tasks/${taskId}`, {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      priority: priority
    })
  });

  return response.json();
};
```

## React Component Examples

#### Task Hierarchy Component
```jsx
import React, { useState, useEffect } from 'react';

const TaskHierarchy = ({ taskId }) => {
  const [task, setTask] = useState(null);
  const [showSubtasks, setShowSubtasks] = useState(true);
  const [conversionMode, setConversionMode] = useState(null); // 'single', 'breakdown', 'subtasks'

  useEffect(() => {
    fetchTaskWithSubtasks(taskId).then(setTask);
  }, [taskId]);

  const toggleSubtasks = () => setShowSubtasks(!showSubtasks);

  const handleConvertToTodo = async () => {
    try {
      const result = await convertTaskToTodo(taskId);

      if (result.has_subtasks) {
        // Show conversion options modal
        setConversionMode('choose');
      } else {
        alert('Task converted to todo successfully!');
        // Refresh task data
        fetchTaskWithSubtasks(taskId).then(setTask);
      }
    } catch (error) {
      alert('Error converting task: ' + error.message);
    }
  };

  const handleConversionChoice = async (choice) => {
    setConversionMode(null);

    try {
      if (choice === 'convert_subtasks') {
        await convertSubtasksToTodos(taskId);
        alert('Subtasks converted to todos successfully!');
      } else if (choice === 'break_into_todos') {
        // Show breakdown form
        setConversionMode('breakdown_form');
      } else if (choice === 'single_todo') {
        // This would require a force-convert endpoint
        alert('Single todo conversion not implemented yet');
      }
    } catch (error) {
      alert('Error: ' + error.message);
    }

    // Refresh task data
    fetchTaskWithSubtasks(taskId).then(setTask);
  };

  const handleBreakIntoTodos = async (breakdown) => {
    try {
      await breakTaskIntoTodos(taskId, breakdown);
      alert('Task broken into todos successfully!');
      setConversionMode(null);
      // Refresh task data
      fetchTaskWithSubtasks(taskId).then(setTask);
    } catch (error) {
      alert('Error breaking task into todos: ' + error.message);
    }
  };

  if (!task) return <div>Loading...</div>;

  return (
    <div className="task-hierarchy">
      <div className="main-task">
        <h3>{task.name}</h3>
        <p>Priority: {task.priority}</p>
        <p>Status: {task.status}</p>
        <div className="task-actions">
          <button onClick={toggleSubtasks}>
            {showSubtasks ? 'Hide' : 'Show'} Subtasks ({task.subTasks?.length || 0})
          </button>
          <button onClick={handleConvertToTodo}>Convert to Todo</button>
          <button onClick={() => setConversionMode('breakdown_form')}>Break into Todos</button>
        </div>
      </div>

      {showSubtasks && task.subTasks && (
        <div className="subtasks">
          {task.subTasks.map(subtask => (
            <div key={subtask.id} className="subtask">
              <h4>{subtask.name}</h4>
              <p>Priority: {subtask.priority}</p>
              <p>Status: {subtask.status}</p>
              <button onClick={() => convertSubtasksToTodos(taskId)}>
                Convert All Subtasks to Todos
              </button>
            </div>
          ))}
        </div>
      )}

      {/* Conversion Choice Modal */}
      {conversionMode === 'choose' && (
        <div className="modal">
          <div className="modal-content">
            <h4>This task has {task.subTasks?.length || 0} subtasks</h4>
            <p>How would you like to proceed?</p>
            <div className="modal-actions">
              <button onClick={() => handleConversionChoice('convert_subtasks')}>
                Convert existing subtasks to todos
              </button>
              <button onClick={() => handleConversionChoice('break_into_todos')}>
                Break task into multiple todos
              </button>
              <button onClick={() => handleConversionChoice('single_todo')}>
                Convert to single todo only
              </button>
              <button onClick={() => setConversionMode(null)}>Cancel</button>
            </div>
          </div>
        </div>
      )}

      {/* Breakdown Form Modal */}
      {conversionMode === 'breakdown_form' && (
        <TaskBreakdownForm
          onSubmit={handleBreakIntoTodos}
          onCancel={() => setConversionMode(null)}
        />
      )}
    </div>
  );
};

// Task Breakdown Form Component
const TaskBreakdownForm = ({ onSubmit, onCancel }) => {
  const [breakdown, setBreakdown] = useState([
    { title: '', description: '', assigned_to: null }
  ]);

  const addBreakdownItem = () => {
    setBreakdown([...breakdown, { title: '', description: '', assigned_to: null }]);
  };

  const updateBreakdownItem = (index, field, value) => {
    const newBreakdown = [...breakdown];
    newBreakdown[index][field] = value;
    setBreakdown(newBreakdown);
  };

  const removeBreakdownItem = (index) => {
    setBreakdown(breakdown.filter((_, i) => i !== index));
  };

  const handleSubmit = () => {
    onSubmit(breakdown.filter(item => item.title.trim()));
  };

  return (
    <div className="modal">
      <div className="modal-content">
        <h4>Break Task into Todos</h4>
        {breakdown.map((item, index) => (
          <div key={index} className="breakdown-item">
            <input
              type="text"
              placeholder="Todo title"
              value={item.title}
              onChange={(e) => updateBreakdownItem(index, 'title', e.target.value)}
            />
            <input
              type="text"
              placeholder="Description"
              value={item.description}
              onChange={(e) => updateBreakdownItem(index, 'description', e.target.value)}
            />
            <input
              type="text"
              placeholder="Assigned to (user ID)"
              value={item.assigned_to || ''}
              onChange={(e) => updateBreakdownItem(index, 'assigned_to', e.target.value)}
            />
            {breakdown.length > 1 && (
              <button onClick={() => removeBreakdownItem(index)}>Remove</button>
            )}
          </div>
        ))}
        <div className="modal-actions">
          <button onClick={addBreakdownItem}>Add Todo</button>
          <button onClick={handleSubmit}>Create Todos</button>
          <button onClick={onCancel}>Cancel</button>
        </div>
      </div>
    </div>
  );
};

export default TaskHierarchy;
```

### Kanban Board Component
```jsx
import React, { useState, useEffect } from 'react';
import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';

const KanbanBoard = ({ projectId }) => {
  const [kanbanData, setKanbanData] = useState(null);

  useEffect(() => {
    fetchKanbanBoard(projectId).then(data => setKanbanData(data));
  }, [projectId]);

  const onDragEnd = async (result) => {
    if (!result.destination) return;

    const { source, destination, draggableId } = result;

    if (source.droppableId === destination.droppableId) return;

    // Update local state optimistically
    const newKanbanData = { ...kanbanData };
    const sourceColumn = newKanbanData.kanban_columns.find(col => col.id === parseInt(source.droppableId));
    const destColumn = newKanbanData.kanban_columns.find(col => col.id === parseInt(destination.droppableId));

    const [movedTask] = sourceColumn.tasks.splice(source.index, 1);
    movedTask.status = destColumn.title;
    destColumn.tasks.splice(destination.index, 0, movedTask);

    setKanbanData(newKanbanData);

    // Update backend
    try {
      await updateKanbanBoard(projectId, [
        ...sourceColumn.tasks,
        ...destColumn.tasks
      ]);
    } catch (error) {
      // Revert on error
      fetchKanbanBoard(projectId).then(data => setKanbanData(data));
    }
  };

  if (!kanbanData) return <div>Loading...</div>;

  return (
    <DragDropContext onDragEnd={onDragEnd}>
      <div className="kanban-board">
        {kanbanData.kanban_columns.map(column => (
          <Droppable key={column.id} droppableId={column.id.toString()}>
            {(provided) => (
              <div
                ref={provided.innerRef}
                {...provided.droppableProps}
                className="kanban-column"
              >
                <h3>{column.title}</h3>
                {column.tasks.map((task, index) => (
                  <Draggable key={task.id} draggableId={task.id.toString()} index={index}>
                    {(provided) => (
                      <div
                        ref={provided.innerRef}
                        {...provided.draggableProps}
                        {...provided.dragHandleProps}
                        className="kanban-task"
                      >
                        <h4>{task.name}</h4>
                        <p>Priority: {task.priority}</p>
                        <p>Assignees: {task.assignees.map(a => a.name).join(', ')}</p>
                      </div>
                    )}
                  </Draggable>
                ))}
                {provided.placeholder}
              </div>
            )}
          </Droppable>
        ))}
      </div>
    </DragDropContext>
  );
};

export default KanbanBoard;
```

### Task Dependencies Component
```jsx
import React, { useState, useEffect } from 'react';

const TaskDependencies = ({ taskId, projectId }) => {
  const [dependencies, setDependencies] = useState([]);
  const [availableTasks, setAvailableTasks] = useState([]);

  useEffect(() => {
    // Fetch existing dependencies and available tasks
    fetchTaskDependencies().then(data => {
      const taskDeps = data.filter(dep => dep.task_id === taskId);
      setDependencies(taskDeps);
    });

    fetchTasks().then(tasks => {
      setAvailableTasks(tasks.filter(task => task.id !== taskId));
    });
  }, [taskId]);

  const addDependency = async (dependsOnTaskId, dependencyType) => {
    const result = await createTaskDependency({
      taskId,
      dependsOnTaskId,
      type: dependencyType,
      projectId
    });

    if (result.success) {
      setDependencies([...dependencies, result.data.task_dependency]);
    }
  };

  const dependencyTypes = [
    { value: 'FS', label: 'Finish to Start' },
    { value: 'SS', label: 'Start to Start' },
    { value: 'FF', label: 'Finish to Finish' },
    { value: 'SF', label: 'Start to Finish' }
  ];

  return (
    <div className="task-dependencies">
      <h4>Task Dependencies</h4>
      <ul>
        {dependencies.map(dep => (
          <li key={dep.id}>
            Depends on: {dep.dependedTask.name} ({dep.dependency_type})
          </li>
        ))}
      </ul>

      <div className="add-dependency">
        <select id="dependsOnTask">
          <option value="">Select task...</option>
          {availableTasks.map(task => (
            <option key={task.id} value={task.id}>{task.name}</option>
          ))}
        </select>

        <select id="dependencyType">
          {dependencyTypes.map(type => (
            <option key={type.value} value={type.value}>{type.label}</option>
          ))}
        </select>

        <button onClick={() => {
          const taskSelect = document.getElementById('dependsOnTask');
          const typeSelect = document.getElementById('dependencyType');
          addDependency(taskSelect.value, typeSelect.value);
        }}>
          Add Dependency
        </button>
      </div>
    </div>
  );
};

export default TaskDependencies;
```

## Error Handling and Loading States

```javascript
const useApiCall = (apiFunction) => {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [data, setData] = useState(null);

  const execute = async (...args) => {
    setLoading(true);
    setError(null);

    try {
      const result = await apiFunction(...args);
      setData(result.data);
      return result;
    } catch (err) {
      setError(err.message);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  return { loading, error, data, execute };
};
```

## State Management with Redux/Context

```javascript
// Redux slice for projects
import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';

export const fetchProjectsAsync = createAsyncThunk(
  'projects/fetchProjects',
  async () => {
    const response = await fetch('/api/v1/projects', {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    return response.json();
  }
);

const projectsSlice = createSlice({
  name: 'projects',
  initialState: {
    items: [],
    loading: false,
    error: null
  },
  reducers: {
    addProject: (state, action) => {
      state.items.push(action.payload);
    },
    updateProject: (state, action) => {
      const index = state.items.findIndex(p => p.id === action.payload.id);
      if (index !== -1) {
        state.items[index] = action.payload;
      }
    },
    deleteProject: (state, action) => {
      state.items = state.items.filter(p => p.id !== action.payload);
    }
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchProjectsAsync.pending, (state) => {
        state.loading = true;
      })
      .addCase(fetchProjectsAsync.fulfilled, (state, action) => {
        state.loading = false;
        state.items = action.payload.data.projects;
      })
      .addCase(fetchProjectsAsync.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message;
      });
  }
});

export const { addProject, updateProject, deleteProject } = projectsSlice.actions;
export default projectsSlice.reducer;
```

## Real-time Updates with WebSockets

```javascript
import { io } from 'socket.io-client';

const socket = io('ws://your-websocket-server');

const useRealtimeUpdates = () => {
  const [updates, setUpdates] = useState([]);

  useEffect(() => {
    socket.on('task-updated', (data) => {
      setUpdates(prev => [...prev, data]);
    });

    socket.on('project-updated', (data) => {
      setUpdates(prev => [...prev, data]);
    });

    return () => {
      socket.off('task-updated');
      socket.off('project-updated');
    };
  }, []);

  return updates;
};
```

## Testing Examples

```javascript
// Unit test for task creation
import { render, fireEvent, waitFor } from '@testing-library/react';
import { createTask } from './api';

test('creates a task successfully', async () => {
  const mockTask = {
    name: 'Test Task',
    startDate: '2025-01-01',
    endDate: '2025-01-31',
    priority: 'high',
    projectId: 1
  };

  // Mock the API call
  global.fetch = jest.fn(() =>
    Promise.resolve({
      json: () => Promise.resolve({ success: true, data: { task: mockTask } })
    })
  );

  const result = await createTask(mockTask);
  expect(result.success).toBe(true);
  expect(result.data.task.name).toBe('Test Task');
});
```

## API Routes Reference

### Projects Routes
```php
// CRUD operations
Route::resource('projects', ProjectController::class);

// Priority management
Route::post('/projects/{project}/change-priority', [ProjectController::class, 'changePriority']);

// Comments
Route::post('/projects/{project}/comments', [ProjectController::class, 'addComment']);
Route::put('/projects/{project}/comments/{comment}', [ProjectController::class, 'updateComment']);
Route::delete('/projects/{project}/comments/{comment}', [ProjectController::class, 'deleteComment']);
```

### Campaigns Routes
```php
Route::resource('campaigns', CampaignController::class);
Route::post('/campaigns/{campaign_id}/teams/members', [CampaignController::class, "addMemberToCampaignTeam"]);
```

### Tasks Routes
```php
Route::resource('tasks', TaskController::class);
Route::post('tasks/{task_id}/assign', [TaskController::class, 'assignTask']);

// Task conversion routes
Route::post('tasks/{taskId}/convert-to-todo', [TaskController::class, 'convertToTodo']);
Route::post('tasks/{taskId}/break-into-todos', [TaskController::class, 'breakIntoTodos']);
Route::post('tasks/{taskId}/convert-subtasks-to-todos', [TaskController::class, 'convertSubtasksToTodos']);
```

### Todos Routes
```php
Route::resource('todos', TodoController::class);
Route::delete('todos/delete/{all}', [TodoController::class, 'deleteAll']);
Route::get('todos/{type}', [TodoController::class, 'index']);
Route::get('todos/create/{type}', [TodoController::class, 'create']);
Route::get('todos/{todo}/{type}', [TodoController::class, 'show']);
Route::put('todos/{todo}/edit/{type}', [TodoController::class, 'edit']);
Route::put('todos/update/{todo}/{type}', [TodoController::class, 'update']);
```

### Kanban Routes
```php
Route::get('/kanban/{project_id}', [KanbanController::class, 'index']);
Route::put('kanban/projects/{project_id}/tasks/update-positions', [KanbanController::class, 'updateKanbanboard']);
Route::put('kanban/projects/tasks/update-positions', [KanbanController::class, 'updateKanbanboard']);
```

#### Kanban API Endpoints

**GET /api/v1/kanban/{project_id}**
- Fetch Kanban board data for a specific project
- Returns kanban columns with tasks grouped by status
- If project_id is 0, returns account-wide tasks

**PUT /api/v1/kanban/projects/{project_id}/tasks/update-positions**
- Update task positions when dragged between columns
- Updates task status based on target column
- Requires array of tasks with id and status

**PUT /api/v1/kanban/projects/tasks/update-positions**
- Alternative endpoint for account-wide task updates
- Same functionality as above but for global tasks

### Task Dependencies Routes
```php
Route::resource('task-dependencies', TaskDependancyController::class);
```

### Leads Routes
```php
Route::resource('leads', LeadController::class);
Route::post('/campaign/{campaign_id}/leads', [LeadController::class, "addLeadsToCampaign"]);
```

### Calendar Routes
```php
Route::resource('calendars', CalendarController::class);
Route::post('calendars/create-from-task/{task_id}', [CalendarController::class, 'createFromTask']);
Route::put('calendars/{calendar}/reschedule', [CalendarController::class, 'reschedule']);
Route::put('calendars/{calendar}/resize', [CalendarController::class, 'resize']);
Route::post('calendars/bulk-update', [CalendarController::class, 'bulkUpdate']);
Route::post('calendars/bulk-delete', [CalendarController::class, 'bulkDelete']);
Route::put('calendars/{calendar}/attendees/{user_id}/status', [CalendarController::class, 'updateAttendeeStatus']);
Route::post('calendars/check-conflicts', [CalendarController::class, 'checkConflicts']);
```

## Calendar Integration

### Entity Relationships

The Dukaverse application integrates Projects, Campaigns, Tasks, and Calendar events through polymorphic relationships:

#### Task Relationships
- **Tasks belong to Projects or Campaigns** via `taskable` polymorphic relationship
  - `taskable_type`: `App\Models\Project` or `App\Models\Campaign`
  - `taskable_id`: ID of the parent Project/Campaign
- **Tasks can have subtasks** via `parent_id` relationship (hierarchical tasks)
- **Tasks can be converted to Calendar events** via dedicated API endpoint

#### Calendar Event Relationships
- **Calendar events can be linked to Tasks** via `task_id` foreign key
- **Calendar events have attendees** via many-to-many relationship with Users
- **Calendar events can be created from Tasks** using the `create-from-task` endpoint

### Integration Features

#### Creating Calendar Events from Tasks
```javascript
// API Endpoint: POST /api/v1/calendars/create-from-task/{task_id}
const createCalendarFromTask = async (taskId, eventData) => {
  const response = await fetch(`/api/v1/calendars/create-from-task/${taskId}`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      start_time: eventData.startTime,
      end_time: eventData.endTime,
      location: eventData.location,
      meeting_link: eventData.meetingLink,
      reminder_minutes_before: eventData.reminderMinutes,
      attendees: eventData.attendees // array of user IDs
    })
  });

  return response.json();
};
```

#### Fetching Calendar Events with Task Context
```javascript
// API Endpoint: GET /api/v1/calendars (with task relationships loaded)
const fetchCalendarEvents = async (filters = {}) => {
  const queryParams = new URLSearchParams({
    ...filters,
    with: 'task,taskable' // Load task and its parent (project/campaign)
  });

  const response = await fetch(`/api/v1/calendars?${queryParams}`, {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });

  const data = await response.json();
  return data.data;
};
```

### Calendar-Task Integration Workflow

Based on the current API implementation, the following features are supported:

1. ✅ **Task Creation**: Tasks are created under Projects or Campaigns via `taskable` polymorphic relationship
2. ✅ **Calendar Event Creation**: Users can create calendar events directly from tasks using `POST /api/v1/calendars/create-from-task/{task_id}`
3. ✅ **Attendee Management**: Task assignees are automatically added as calendar attendees
4. ✅ **Conflict Detection**: System checks for scheduling conflicts before creating events and returns detailed conflict information
5. ✅ **Reminder System**: Automatic reminders are set up for calendar events with configurable timing
6. ❌ **Status Synchronization**: Task progress is not automatically updated based on calendar event completion (requires additional implementation)

### Status Synchronization Implementation

To implement automatic task progress updates based on calendar event completion, you would need to add the following functionality:

#### Proposed API Endpoint: `PUT /api/v1/tasks/{taskId}/sync-calendar-status`

```php
/**
 * @OA\Put(
 *     path="/api/v1/tasks/{taskId}/sync-calendar-status",
 *     operationId="syncTaskCalendarStatus",
 *     tags={"Tasks"},
 *     security={{"bearerAuth":{}}},
 *     summary="Sync task progress with calendar event status",
 *     description="Updates task progress based on associated calendar event completion status",
 *     @OA\Parameter(
 *         name="taskId",
 *         in="path",
 *         required=true,
 *         description="ID of the task to sync",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Task status synchronized successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object"),
 *             @OA\Property(property="message", type="string", example="Task status synchronized with calendar event")
 *         )
 *     )
 * )
 */
public function syncCalendarStatus(Request $request, $taskId)
{
    $task = $this->account()->tasks()->where('id', $taskId)->first();

    if (!$task) {
        return $this->sendError("Task not found", ["errors" => "Task does not exist"]);
    }

    // Find associated calendar events
    $calendarEvents = Calendar::where('task_id', $taskId)->get();

    if ($calendarEvents->isEmpty()) {
        return $this->sendError("No calendar events found", ["errors" => "Task has no associated calendar events"]);
    }

    // Check if all calendar events are completed
    $allCompleted = $calendarEvents->every(function ($event) {
        return $event->status === 'completed';
    });

    if ($allCompleted) {
        $task->update(['progress' => 100, 'status' => 'completed']);
        return $this->sendResponse(["task" => $task], "Task marked as completed based on calendar events");
    }

    // Calculate progress based on completed events
    $completedCount = $calendarEvents->where('status', 'completed')->count();
    $progress = ($completedCount / $calendarEvents->count()) * 100;

    $task->update(['progress' => $progress]);

    return $this->sendResponse(["task" => $task], "Task progress updated based on calendar events");
}
```

#### Frontend Integration Example

```javascript
// Hook to sync task status when calendar event is completed
const syncTaskWithCalendar = async (taskId) => {
  try {
    const response = await fetch(`/api/v1/tasks/${taskId}/sync-calendar-status`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    });

    const result = await response.json();
    if (result.success) {
      // Update task in local state
      updateTaskInState(result.data.task);
    }
  } catch (error) {
    console.error('Error syncing task status:', error);
  }
};

// Call this when calendar event status changes to 'completed'
const handleCalendarEventCompletion = (calendarEventId, taskId) => {
  // Update calendar event status first
  updateCalendarEventStatus(calendarEventId, 'completed')
    .then(() => {
      // Then sync task progress
      return syncTaskWithCalendar(taskId);
    });
};
```

### React Component Example: Task-to-Calendar Integration

```jsx
import React, { useState } from 'react';

const TaskCalendarIntegration = ({ task }) => {
  const [showCalendarModal, setShowCalendarModal] = useState(false);
  const [calendarEvent, setCalendarEvent] = useState({
    start_time: '',
    end_time: '',
    location: '',
    meeting_link: '',
    reminder_minutes_before: 15
  });

  const createCalendarEvent = async () => {
    try {
      const result = await createCalendarFromTask(task.id, calendarEvent);
      if (result.success) {
        alert('Calendar event created successfully!');
        setShowCalendarModal(false);
        // Refresh task data or calendar view
      }
    } catch (error) {
      alert('Error creating calendar event: ' + error.message);
    }
  };

  return (
    <div className="task-calendar-integration">
      <button onClick={() => setShowCalendarModal(true)}>
        Create Calendar Event
      </button>

      {showCalendarModal && (
        <div className="modal">
          <div className="modal-content">
            <h4>Create Calendar Event for Task: {task.name}</h4>
            <div className="form-group">
              <label>Start Time:</label>
              <input
                type="datetime-local"
                value={calendarEvent.start_time}
                onChange={(e) => setCalendarEvent({
                  ...calendarEvent,
                  start_time: e.target.value
                })}
              />
            </div>
            <div className="form-group">
              <label>End Time:</label>
              <input
                type="datetime-local"
                value={calendarEvent.end_time}
                onChange={(e) => setCalendarEvent({
                  ...calendarEvent,
                  end_time: e.target.value
                })}
              />
            </div>
            <div className="form-group">
              <label>Location:</label>
              <input
                type="text"
                value={calendarEvent.location}
                onChange={(e) => setCalendarEvent({
                  ...calendarEvent,
                  location: e.target.value
                })}
                placeholder="Office, Zoom, etc."
              />
            </div>
            <div className="form-group">
              <label>Meeting Link:</label>
              <input
                type="url"
                value={calendarEvent.meeting_link}
                onChange={(e) => setCalendarEvent({
                  ...calendarEvent,
                  meeting_link: e.target.value
                })}
              />
            </div>
            <div className="form-group">
              <label>Reminder (minutes before):</label>
              <input
                type="number"
                value={calendarEvent.reminder_minutes_before}
                onChange={(e) => setCalendarEvent({
                  ...calendarEvent,
                  reminder_minutes_before: parseInt(e.target.value)
                })}
              />
            </div>
            <div className="modal-actions">
              <button onClick={createCalendarEvent}>Create Event</button>
              <button onClick={() => setShowCalendarModal(false)}>Cancel</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default TaskCalendarIntegration;
```

This implementation guide covers all the major aspects of projects and campaigns management, including CRUD operations, task dependencies, priorities, Kanban board functionality, converting tasks to todos, and calendar integration. The code examples are ready to be integrated into your frontend application.
