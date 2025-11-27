# Subscriptions Frontend Documentation (Vue)

## Overview

This document describes how to interact with the Subscriptions APIs from the Vue.js frontend. Subscriptions manage access levels, features, and permissions for users or accounts.

## Key API Endpoints

| Method | Endpoint                 | Description                            |
|--------|--------------------------|------------------------------------|
| GET    | /subscriptions/active    | Fetch active subscriptions of user  |
| GET    | /subscriptions          | List all subscriptions                |
| POST   | /subscriptions          | Create a new subscription             |
| PUT    | /subscriptions/{id}     | Update an existing subscription       |
| DELETE | /subscriptions/{id}     | Delete a subscription                  |

## Fetching Active Subscriptions

Example using Axios to fetch authenticated user's active subscriptions:

```vue
<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const subscriptions = ref([])

const fetchActiveSubscriptions = async () => {
  try {
    const response = await axios.get('/api/v1/subscriptions/active', {
      headers: { Authorization: \`Bearer \${localStorage.getItem('authToken')}\` }
    })
    subscriptions.value = response.data
  } catch (error) {
    console.error('Failed to fetch subscriptions:', error)
  }
}

onMounted(() => {
  fetchActiveSubscriptions()
})
</script>

<template>
  <div>
    <h2>Active Subscriptions</h2>
    <ul>
      <li v-for="sub in subscriptions" :key="sub.id">
        Tier: {{ sub.tier_name }} - Expires: {{ sub.expires_at }}
      </li>
    </ul>
  </div>
</template>
```

## Creating a Subscription

Example to create a new subscription:

```vue
<script setup>
import { ref } from 'vue'
import axios from 'axios'

const newSubscription = ref({
  tier_id: '',
  start_date: '',
  end_date: '',
  auto_renew: false,
})

const createSubscription = async () => {
  try {
    await axios.post('/api/v1/subscriptions', newSubscription.value, {
      headers: { Authorization: \`Bearer \${localStorage.getItem('authToken')}\` }
    })
    alert('Subscription created successfully')
  } catch (error) {
    console.error('Error creating subscription:', error)
  }
}
</script>

<template>
  <div>
    <h2>Create Subscription</h2>
    <form @submit.prevent="createSubscription">
      <input v-model="newSubscription.tier_id" placeholder="Tier ID" required />
      <input v-model="newSubscription.start_date" type="date" required />
      <input v-model="newSubscription.end_date" type="date" required />
      <label>
        Auto Renew
        <input type="checkbox" v-model="newSubscription.auto_renew" />
      </label>
      <button type="submit">Create</button>
    </form>
  </div>
</template>
```

## Updating a Subscription

Update an existing subscription by ID:

```vue
<script setup>
import { ref } from 'vue'
import axios from 'axios'

const subscriptionId = 'put_subscription_id_here'
const updatedSubscription = ref({
  tier_id: '',
  start_date: '',
  end_date: '',
  auto_renew: false,
})

const updateSubscription = async () => {
  try {
    await axios.put(\`/api/v1/subscriptions/\${subscriptionId}\`, updatedSubscription.value, {
      headers: { Authorization: \`Bearer \${localStorage.getItem('authToken')}\` }
    })
    alert('Subscription updated successfully')
  } catch (error) {
    console.error('Error updating subscription:', error)
  }
}
</script>

<template>
  <div>
    <h2>Update Subscription</h2>
    <form @submit.prevent="updateSubscription">
      <input v-model="updatedSubscription.tier_id" placeholder="Tier ID" required />
      <input v-model="updatedSubscription.start_date" type="date" required />
      <input v-model="updatedSubscription.end_date" type="date" required />
      <label>
        Auto Renew
        <input type="checkbox" v-model="updatedSubscription.auto_renew" />
      </label>
      <button type="submit">Update</button>
    </form>
  </div>
</template>
```

## Deleting a Subscription

Delete a subscription by ID:

```vue
<script setup>
import axios from 'axios'

const subscriptionId = 'put_subscription_id_here'

const deleteSubscription = async () => {
  try {
    await axios.delete(\`/api/v1/subscriptions/\${subscriptionId}\`, {
      headers: { Authorization: \`Bearer \${localStorage.getItem('authToken')}\` }
    })
    alert('Subscription deleted successfully')
  } catch (error) {
    console.error('Error deleting subscription:', error)
  }
}
</script>

<template>
  <div>
    <h2>Delete Subscription</h2>
    <button @click="deleteSubscription">Delete</button>
  </div>
</template>
```

---

This enhanced documentation provides detailed Vue.js code examples to consume Subscription APIs effectively.
