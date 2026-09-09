<template>
  <div class="relative" ref="dropdownRef">
    <input type="hidden" :name="name" :value="modelValue" />
    <button
      type="button"
      @click="isOpen = !isOpen"
      class="w-full flex items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-red-500 focus:ring-2 focus:ring-red-200 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
      :class="{ 'border-red-400': error }"
    >
      <div class="flex items-center gap-2" v-if="selectedCategory">
        <i :class="[selectedCategory.icon, selectedCategory.color]"></i>
        <span>{{ selectedCategory.name }}</span>
      </div>
      <div v-else class="text-gray-400 dark:text-gray-500">
        Select Category
      </div>
      <i class="fa-solid fa-chevron-down text-gray-400"></i>
    </button>

    <div
      v-if="isOpen"
      class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700"
    >
      <ul class="py-1">
        <li
          v-for="category in categories"
          :key="category.id"
          @click="selectOption(category.id)"
          class="flex cursor-pointer items-center gap-2 px-3 py-2 text-sm text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-600"
        >
          <i :class="[category.icon, category.color]" class="w-5 text-center"></i>
          <span>{{ category.name }}</span>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  modelValue: {
    type: [Number, String],
    default: ''
  },
  categories: {
    type: Array,
    default: () => []
  },
  name: {
    type: String,
    required: true
  },
  error: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)
const dropdownRef = ref<HTMLElement | null>(null)

const selectedCategory = computed(() => {
  return props.categories.find((c: any) => c.id == props.modelValue)
})

const selectOption = (id: number) => {
  emit('update:modelValue', id)
  isOpen.value = false
}

const handleClickOutside = (event: MouseEvent) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
