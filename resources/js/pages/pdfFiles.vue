<template>
    <div class="w-full">
        <!-- Search and Filters -->
        <div class="flex items-center justify-between py-4 gap-4">
            <div class="flex-1 max-w-sm">
                <div class="relative">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="البحث في الملفات..."
                        class="w-full px-4 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        @input="applyFilter"
                    />
                    <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>

            <div class="flex gap-2">
                <select
                    v-model="statusFilter"
                    @change="applyFilter"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                >
                    <option value="">جميع الملفات</option>
                    <option value="active">النشطة</option>
                    <option value="inactive">المعطلة</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th
                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-200"
                        @click="sortBy('title')"
                    >
                        العنوان
                        <span v-if="sortField === 'title'">
                {{ sortDirection === 'asc' ? '↑' : '↓' }}
              </span>
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        الحجم
                    </th>
                    <th
                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-200"
                        @click="sortBy('is_active')"
                    >
                        الحالة
                        <span v-if="sortField === 'is_active'">
                {{ sortDirection === 'asc' ? '↑' : '↓' }}
              </span>
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        رفع بواسطة
                    </th>
                    <th
                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-200"
                        @click="sortBy('created_at')"
                    >
                        التاريخ
                        <span v-if="sortField === 'created_at'">
                {{ sortDirection === 'asc' ? '↑' : '↓' }}
              </span>
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        العمليات
                    </th>
                </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                <tr
                    v-for="file in paginatedFiles"
                    :key="file.id"
                    class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                >
                    <td class="px-6 py-4">
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ file.title }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ file.original_name }}
                            </div>
                            <div v-if="file.description" class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                {{ file.description }}
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ formatFileSize(file.file_size) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
              <span
                  :class="file.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400'"
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
              >
                {{ file.is_active ? 'نشط' : 'معطل' }}
              </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ file.uploader?.name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ formatDate(file.created_at) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex gap-2">
                            <a
                                :href="`/dashboard/files/pdf/${file.id}/download`"
                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 inline-flex items-center px-2 py-1 rounded-md hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                title="تحميل"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </a>
                            <button
                                @click="toggleStatus(file)"
                                class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300 inline-flex items-center px-2 py-1 rounded-md hover:bg-yellow-50 dark:hover:bg-yellow-900/20"
                                :title="file.is_active ? 'إلغاء تفعيل' : 'تفعيل'"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                            </button>
                            <button
                                @click="deleteFile(file)"
                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 inline-flex items-center px-2 py-1 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20"
                                title="حذف"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="text-sm text-gray-700 dark:text-gray-300">
                عرض {{ startIndex + 1 }} إلى {{ Math.min(endIndex, filteredFiles.length) }} من {{ filteredFiles.length }} ملف
            </div>

            <div class="flex gap-2">
                <button
                    @click="previousPage"
                    :disabled="currentPage === 1"
                    class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    السابق
                </button>

                <span class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
          صفحة {{ currentPage }} من {{ totalPages }}
        </span>

                <button
                    @click="nextPage"
                    :disabled="currentPage === totalPages"
                    class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    التالي
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'

interface PdfFile {
    id: number
    title: string
    original_name: string
    file_name: string
    file_size: number
    description: string | null
    is_active: boolean
    created_at: string
    uploader: {
        name: string
        email: string
    }
}

const props = defineProps<{
    files: PdfFile[]
    canManage: boolean
}>()

// State
const searchQuery = ref('')
const statusFilter = ref('')
const sortField = ref('created_at')
const sortDirection = ref<'asc' | 'desc'>('desc')
const currentPage = ref(1)
const itemsPerPage = 10

// Computed
const filteredFiles = computed(() => {
    let filtered = [...props.files]

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(file =>
            file.title.toLowerCase().includes(query) ||
            file.original_name.toLowerCase().includes(query) ||
            file.description?.toLowerCase().includes(query) ||
            file.uploader.name.toLowerCase().includes(query)
        )
    }

    if (statusFilter.value === 'active') {
        filtered = filtered.filter(file => file.is_active)
    } else if (statusFilter.value === 'inactive') {
        filtered = filtered.filter(file => !file.is_active)
    }

    filtered.sort((a, b) => {
        let aValue: any = a[sortField.value as keyof PdfFile]
        let bValue: any = b[sortField.value as keyof PdfFile]

        if (sortField.value === 'created_at') {
            aValue = new Date(aValue).getTime()
            bValue = new Date(bValue).getTime()
        }

        if (typeof aValue === 'string') {
            aValue = aValue.toLowerCase()
            bValue = bValue.toLowerCase()
        }

        if (sortDirection.value === 'asc') {
            return aValue > bValue ? 1 : -1
        } else {
            return aValue < bValue ? 1 : -1
        }
    })

    return filtered
})

const totalPages = computed(() => Math.ceil(filteredFiles.value.length / itemsPerPage))
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage)
const endIndex = computed(() => startIndex.value + itemsPerPage)
const paginatedFiles = computed(() =>
    filteredFiles.value.slice(startIndex.value, endIndex.value)
)

// Methods
const applyFilter = () => {
    currentPage.value = 1
}

const sortBy = (field: string) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortField.value = field
        sortDirection.value = 'asc'
    }
}

const previousPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--
    }
}

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++
    }
}

const formatFileSize = (bytes: number): string => {
    const units = ['B', 'KB', 'MB', 'GB']
    let size = bytes
    let unitIndex = 0

    while (size > 1024 && unitIndex < units.length - 1) {
        size /= 1024
        unitIndex++
    }

    return `${Math.round(size * 100) / 100} ${units[unitIndex]}`
}

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('ar-EG', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const toggleStatus = (file: PdfFile) => {
    router.put(`/dashboard/files/pdf/${file.id}`, {
        is_active: !file.is_active
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // File will be updated by Inertia
        }
    })
}

const deleteFile = (file: PdfFile) => {
    if (confirm(`هل أنت متأكد من حذف الملف "${file.title}"؟`)) {
        router.delete(`/dashboard/files/pdf/${file.id}`, {
            preserveScroll: true
        })
    }
}

// Reset page when filters change
watch([searchQuery, statusFilter], () => {
    currentPage.value = 1
})
</script>
