<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

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
    latest_file: PdfFile | null
}>();

const isFullscreen = ref(false)
const showPdfViewer = ref(false)
const currentTime = ref(new Date())
const currentUrl = ref('')

// تحديث الوقت كل ثانية
onMounted(() => {
    // Set the current URL after component is mounted (client-side only)
    currentUrl.value = window.location.origin

    setInterval(() => {
        currentTime.value = new Date()
    }, 1000)
})

// Computed property for QR code URL - safe for SSR
const qrCodeUrl = computed(() => {
    if (typeof window !== 'undefined') {
        return `${window.location.origin}/pdf/last`
    }
    return '/pdf/last' // Fallback for SSR
})

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
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const formatTime = (date: Date): string => {
    return date.toLocaleTimeString('ar-EG', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    })
}

const toggleFullscreen = () => {
    if (typeof document !== 'undefined') {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen()
            isFullscreen.value = true
        } else {
            document.exitFullscreen()
            isFullscreen.value = false
        }
    }
}

const downloadPdf = () => {
    if (props.latest_file && typeof window !== 'undefined') {
        window.open(`/dashboard/files/pdf/${props.latest_file.id}/download`, '_blank')
    }
}

const openPdfViewer = () => {
    showPdfViewer.value = !showPdfViewer.value
}

const refreshPage = () => {
    if (typeof window !== 'undefined') {
        window.location.reload()
    }
}
</script>

<template>
    <Head title="آخر ملف PDF" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 dark:from-gray-900 dark:via-gray-800 dark:to-blue-900">
        <!-- Header -->
        <header class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm shadow-lg border-b border-gray-200 dark:border-gray-700" dir="rtl">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-lg font-bold text-gray-900 dark:text-white">
                                عارض PDF
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                آخر ملف تم رفعه
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <!-- Live Clock -->
                        <div class="text-center">
                            <div class="text-sm font-mono text-gray-600 dark:text-gray-400">
                                {{ formatTime(currentTime) }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-500">
                                {{ currentTime.toLocaleDateString('ar-EG') }}
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            <button
                                @click="refreshPage"
                                class="p-2 text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                title="تحديث"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </button>

                            <button
                                @click="toggleFullscreen"
                                class="p-2 text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                title="شاشة كاملة"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8" dir="rtl">
            <!-- No File State -->
            <div v-if="!latest_file" class="text-center py-16">
                <div class="w-24 h-24 mx-auto mb-6 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    لا توجد ملفات PDF
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mb-8">
                    لم يتم رفع أي ملفات PDF بعد
                </p>
                <a
                    href="/dashboard/files/pdf"
                    class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    رفع ملف جديد
                </a>
            </div>

            <!-- File Display -->
            <div v-else class="space-y-6">
                <!-- File Info Card -->
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-8">
                        <div class="flex items-start justify-between mb-6">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                                        <svg class="w-7 h-7 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                            {{ latest_file.title }}
                                        </h1>
                                        <p class="text-gray-600 dark:text-gray-400">
                                            {{ latest_file.original_name }}
                                        </p>
                                    </div>
                                </div>

                                <div v-if="latest_file.description" class="mb-4">
                                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                                        {{ latest_file.description }}
                                    </p>
                                </div>

                                <!-- File Details -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                                        <div class="text-blue-600 dark:text-blue-400 text-sm font-medium mb-1">
                                            حجم الملف
                                        </div>
                                        <div class="text-blue-900 dark:text-blue-100 font-bold">
                                            {{ formatFileSize(latest_file.file_size) }}
                                        </div>
                                    </div>

                                    <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                                        <div class="text-green-600 dark:text-green-400 text-sm font-medium mb-1">
                                            رفع بواسطة
                                        </div>
                                        <div class="text-green-900 dark:text-green-100 font-bold">
                                            {{ latest_file.uploader?.name }}
                                        </div>
                                    </div>

                                    <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg">
                                        <div class="text-purple-600 dark:text-purple-400 text-sm font-medium mb-1">
                                            تاريخ الرفع
                                        </div>
                                        <div class="text-purple-900 dark:text-purple-100 font-bold">
                                            {{ formatDate(latest_file.created_at) }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex flex-wrap gap-3">
                                    <button
                                        @click="openPdfViewer"
                                        class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-200 transform hover:scale-105 shadow-lg"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        {{ showPdfViewer ? 'إخفاء المعاينة' : 'معاينة الملف' }}
                                    </button>

                                    <button
                                        @click="downloadPdf"
                                        class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all duration-200 transform hover:scale-105 shadow-lg"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        تحميل الملف
                                    </butto
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PDF Viewer -->
                <div v-if="showPdfViewer" class="bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden animate-fade-in">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            معاينة الملف
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700">
                            <iframe
                                :src="`/storage/pdfs/${latest_file.file_name}`"
                                class="w-full h-96 md:h-[600px]"
                                frameborder="0"
                                title="PDF Viewer"
                            >
                                <p class="p-4 text-center text-gray-600 dark:text-gray-400">
                                    متصفحك لا يدعم عرض ملفات PDF.
                                    <button @click="downloadPdf" class="text-blue-600 hover:underline">
                                        اضغط هنا لتحميل الملف
                                    </button>
                                </p>
                            </iframe>
                        </div>
                    </div>
                </div>

                <!-- QR Code Info -->
            </div>
        </main>

        <!-- Auto-refresh indicator -->
        <div class="fixed bottom-4 right-4 bg-blue-600 text-white px-3 py-2 rounded-full text-xs shadow-lg">
            تحديث تلقائي كل 30 ثانية
        </div>
    </div>
</template>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fadeIn 0.5s ease-out;
}
</style>
