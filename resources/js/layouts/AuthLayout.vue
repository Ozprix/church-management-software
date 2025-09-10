<template>
  <div class="min-h-screen flex flex-col justify-center items-center pt-6 sm:pt-0 bg-gray-100">
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
      <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-indigo-600">Church Management System</h1>
      </div>
      
      <router-view></router-view>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AuthLayout'
};
</script>
