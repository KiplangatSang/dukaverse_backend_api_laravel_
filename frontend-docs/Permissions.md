# Permissions Frontend Documentation (Vue)

## Overview

This document explains how permissions are managed and used in the frontend application.
Permissions define what actions a user or account can perform in the system.
They are retrieved from the backend via API calls and drive UI rendering and authorization.

## Permissions API Endpoints

The Permissions API provides various endpoints to manage and interact with permissions.

| Method | Endpoint                                  | Description                          |
|--------|-------------------------------------------|----------------------------------|
| GET    | /user/permissions-list                    | Fetch all permissions for user     |
| POST   | /user/permissions                         | Create a new permission             |
| GET    | /user/permissions/{id}                    | Retrieve a specific permission      |
| PUT    | /user/permissions/{id}                    | Update a permission                 |
| DELETE | /user/permissions/{id}                    | Remove a permission                 |

### Tier-Permission Linking Endpoints

| Method | Endpoint                                   | Description                             |
|--------|--------------------------------------------|-------------------------------------|
| GET    | /tiers/{tier}/permissions                  | List permissions assigned to a tier  |
| POST   | /tiers/{tier}/permissions                  | Assign permissions to a tier          |
| DELETE | /tiers/{tier}/permissions/{permission}    | Remove a permission from a tier       |

## Fetching Permissions Example

```vue
<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const permissions = ref([])

const fetchPermissions = async () => {
  try {
    const response = await axios.get('/api/v1/user/permissions-list', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('authToken')}`,
      },
    })
    permissions.value = response.data
  } catch (error) {
    console.error('Failed to fetch permissions:', error)
  }
}

onMounted(() => {
  fetchPermissions()
})
</script>

<template>
  <div>
    <h2>User Permissions</h2>
    <ul>
      <li v-for="perm in permissions" :key="perm.id">
        {{ perm.name }} - {{ perm.description || 'No description' }}
      </li>
    </ul>
  </div>
</template>
```

## Creating a New Permission Example

```vue
<script setup>
import { ref } from 'vue'
import axios from 'axios'

const newPermission = ref({
  name: '',
  description: '',
})

const createPermission = async () => {
  try {
    await axios.post('/api/v1/user/permissions', newPermission.value, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('authToken')}`,
      },
    })
    alert('Permission created successfully')
  } catch (error) {
    console.error('Error creating permission:', error)
  }
}
</script>

<template>
  <div>
    <h2>Create Permission</h2>
    <form @submit.prevent="createPermission">
      <input v-model="newPermission.name" placeholder="Name" required />
      <input v-model="newPermission.description" placeholder="Description" />
      <button type="submit">Create</button>
    </form>
  </div>
</template>
```

## Updating a Permission Example

```vue
<script setup>
import { ref } from 'vue'
import axios from 'axios'

const permissionId = 'put_permission_id_here'
const updatedPermission = ref({
  name: '',
  description: '',
})

const updatePermission = async () => {
  try {
    await axios.put(`/api/v1/user/permissions/${permissionId}`, updatedPermission.value, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('authToken')}`,
      },
    })
    alert('Permission updated successfully')
  } catch (error) {
    console.error('Error updating permission:', error)
  }
}
</script>

<template>
  <div>
    <h2>Update Permission</h2>
    <form @submit.prevent="updatePermission">
      <input v-model="updatedPermission.name" placeholder="Name" required />
      <input v-model="updatedPermission.description" placeholder="Description" />
      <button type="submit">Update</button>
    </form>
  </div>
</template>
```

## Deleting a Permission Example

```vue
<script setup>
import axios from 'axios'

const permissionId = 'put_permission_id_here'

const deletePermission = async () => {
  try {
    await axios.delete(`/api/v1/user/permissions/${permissionId}`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('authToken')}`,
      },
    })
    alert('Permission deleted successfully')
  } catch (error) {
    console.error('Error deleting permission:', error)
  }
}
</script>

<template>
  <div>
    <h2>Delete Permission</h2>
    <button @click="deletePermission">Delete Permission</button>
  </div>
</template>
