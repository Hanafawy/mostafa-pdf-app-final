<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PdfFilesTable from './pdfFiles.vue';

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
    auth?: {
        user: {
            role: string
            name: string
        }
    }
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'إدارة ملفات PDF',
        href: '/dashboard/files/pdf',
    },
];

const uploadForm = useForm({
    title: '',
    description: '',
    file: null as File | null
})

const fileInput = ref<HTMLInputElement>()
const uploading = ref(false)
const uploadError = ref('')
const uploadSuccess = ref('')
const showUploadForm = ref(false)

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement
    if (target.files && target.files[0]) {
        uploadForm.file = target.files[0]
    }
}

const uploadFile = () => {
    if (!uploadForm.file) {
        uploadError.value = 'يرجى اختيار ملف PDF'
        return
    }

    uploading.value = true
    uploadError.value = ''
    uploadSuccess.value = ''

    uploadForm.post('/dashboard/files/pdf', {
        forceFormData: true,
        onSuccess: () => {
            uploadSuccess.value = 'تم رفع الملف بنجاح'
            uploadForm.reset()
            if (fileInput.value) {
                fileInput.value.value = ''
            }
            showUploadForm.value = false
        },
        onError: (errors) => {
            uploadError.value = errors.file || errors.title || 'حدث خطأ في رفع الملف'
        },
        onFinish: () => {
            uploading.value = false
        }
    })
}

const toggleUploadForm = () => {
    showUploadForm.value = !showUploadForm.value
    if (!showUploadForm.value) {
        uploadError.value = ''
        uploadSuccess.value = ''
        uploadForm.reset()
    }
}
</script>

<template>
    <Head title="إدارة ملفات PDF" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6" dir="rtl">
            <!-- Page Header with Upload Button -->
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        إدارة ملفات PDF
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        رفع وإدارة ملفات PDF الخاصة بك
                    </p>
                </div>

                <!-- Upload Button - Available for All Users -->
                <div class="flex flex-col gap-4">
                    <button
                        @click="toggleUploadForm"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        {{ showUploadForm ? 'إلغاء' : 'إضافة ملف جديد' }}
                    </button>

                    <!-- Quick Stats -->
                    <div class="flex gap-4">
                        <div class="bg-blue-50 dark:bg-blue-900/20 px-4 py-2 rounded-lg text-center">
                            <div class="text-blue-600 dark:text-blue-400 text-sm font-medium">
                                إجمالي الملفات
                            </div>
                            <div class="text-blue-900 dark:text-blue-100 text-lg font-bold">
                                {{ files.length }}
                            </div>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 px-4 py-2 rounded-lg text-center">
                            <div class="text-green-600 dark:text-green-400 text-sm font-medium">
                                الملفات النشطة
                            </div>
                            <div class="text-green-900 dark:text-green-100 text-lg font-bold">
                                {{ files.filter(f => f.is_active).length }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Form - Available for All Users -->
            <div
                v-if="showUploadForm"
                class="bg-white dark:bg-gray-800 shadow-sm border border-sidebar-border/70 dark:border-sidebar-border rounded-xl animate-fade-in"
            >
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/50 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                        </div>
                        رفع ملف PDF جديد
                    </h3>
                </div>

                <div class="px-6 py-5">
                    <form @submit.prevent="uploadFile" class="space-y-4">
                        <!-- Error Message -->
                        <div
                            v-if="uploadError"
                            class="bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 rounded-lg"
                        >
                            {{ uploadError }}
                        </div>

                        <!-- Success Message -->
                        <div
                            v-if="uploadSuccess"
                            class="bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-800 text-green-600 dark:text-green-400 px-4 py-3 rounded-lg"
                        >
                            {{ uploadSuccess }}
                        </div>

                        <!-- Form Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    عنوان الملف *
                                </label>
                                <input
                                    id="title"
                                    v-model="uploadForm.title"
                                    type="text"
                                    required
                                    placeholder="أدخل عنوان الملف"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                />
                            </div>

                            <div>
                                <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    ملف PDF *
                                </label>
                                <input
                                    id="file"
                                    ref="fileInput"
                                    type="file"
                                    accept=".pdf"
                                    required
                                    @change="handleFileSelect"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                />
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                الوصف (اختياري)
                            </label>
                            <textarea
                                id="description"
                                v-model="uploadForm.description"
                                rows="3"
                                placeholder="أدخل وصف للملف (اختياري)"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            ></textarea>
                        </div>

                        <div class="flex justify-end gap-3">
                            <button
                                type="button"
                                @click="toggleUploadForm"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg shadow-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                            >
                                إلغاء
                            </button>
                            <button
                                type="submit"
                                :disabled="uploading"
                                class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                <svg v-if="uploading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                {{ uploading ? 'جاري الرفع...' : 'رفع الملف' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- PDF Files Table -->
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-sidebar-border/70 dark:border-sidebar-border rounded-xl overflow-hidden">
                <div class="px-6 py-5">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        قائمة ملفات PDF
                    </h3>

                    <div v-if="files.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                            لا توجد ملفات PDF
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            ابدأ برفع أول ملف PDF
                        </p>
                        <div class="mt-6">
                            <button
                                @click="showUploadForm = true"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                رفع ملف جديد
                            </button>
                        </div>
                    </div>

                    <PdfFilesTable
                        v-else
                        :files="files"
                        :can-manage="true"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
