# Ecommerce Vendor Frontend Documentation (Vue)

## Overview

This document describes how to interact with the Ecommerce Vendor APIs from the Vue.js frontend application.

## List Vendors

Fetch all ecommerce vendors accessible to the authenticated user:

```vue
<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const vendors = ref([])

const fetchVendors = async () => {
  try {
    const response = await axios.get('/api/v1/ecommerce/vendors', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('authToken')}`,
      },
    })
    vendors.value = response.data.data || response.data
  } catch (error) {
    console.error('Failed to fetch vendors:', error)
  }
}

onMounted(() => {
  fetchVendors()
})
</script>

<template>
  <div>
    <h2>Ecommerce Vendors</h2>
    <ul>
      <li v-for="vendor in vendors" :key="vendor.id">
        {{ vendor.name }} - {{ vendor.email }}
      </li>
    </ul>
  </div>
</template>
```

## Create Vendor

Example to create a new vendor:

```vue
<script setup>
import { ref } from 'vue'
import axios from 'axios'

const vendorData = ref({
  name: '',
  email: '',
  phone: '',
  // other required fields...
})

const createVendor = async () => {
  try {
    const response = await axios.post('/api/v1/ecommerce/vendors', vendorData.value, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('authToken')}`,
      }
    })
    alert('Vendor created successfully')
  } catch (error) {
    console.error('Error creating vendor:', error)
  }
}
</script>

<template>
  <div>
    <h2>Create Vendor</h2>
    <form @submit.prevent="createVendor">
      <input v-model="vendorData.name" placeholder="Name" required />
      <input v-model="vendorData.email" placeholder="Email" type="email" required />
      <input v-model="vendorData.phone" placeholder="Phone" />
      <button type="submit">Create</button>
    </form>
  </div>
</template>
```

## Update Vendor

Use the vendor ID to update vendor details:

```vue
<script setup>
import { ref } from 'vue'
import axios from 'axios'

const vendorId = 'put_vendor_id_here'
const vendorData = ref({
  name: 'Updated Name',
  email: 'updated@example.com',
  phone: '1234567890',
})

const updateVendor = async () => {
  try {
    const response = await axios.put(`/api/v1/ecommerce/vendors/${vendorId}`, vendorData.value, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('authToken')}`
      }
    })
    alert('Vendor updated successfully')
  } catch (error) {
    console.error('Error updating vendor:', error)
  }
}
</script>

<template>
  <div>
    <h2>Update Vendor</h2>
    <form @submit.prevent="updateVendor">
      <input v-model="vendorData.name" placeholder="Name" required />
      <input v-model="vendorData.email" placeholder="Email" type="email" required />
      <input v-model="vendorData.phone" placeholder="Phone" />
      <button type="submit">Update</button>
    </form>
  </div>
</template>
```

## Delete Vendor

Delete a vendor by ID:

```vue
<script setup>
import axios from 'axios'

const vendorId = 'put_vendor_id_here'

const deleteVendor = async () => {
  try {
    await axios.delete(`/api/v1/ecommerce/vendors/${vendorId}`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('authToken')}`
      }
    })
    alert('Vendor deleted successfully')
  } catch (error) {
    console.error('Error deleting vendor:', error)
  }
}
</script>

<template>
  <div>
    <h2>Delete Vendor</h2>
    <button @click="deleteVendor">Delete Vendor</button>
  </div>
</template>
```

---
