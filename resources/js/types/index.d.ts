export interface BreadcrumbItem {
    title: string
    href?: string
    current?: boolean
}

export interface User {
    id: number
    name: string
    email: string
    role: 'admin' | 'user'
    is_active: boolean
    email_verified_at: string | null
    created_at: string
    updated_at: string
}

export interface PdfFile {
    id: number
    title: string
    original_name: string
    file_name: string
    file_path: string
    file_size: number
    mime_type: string
    description: string | null
    is_active: boolean
    uploaded_by: number
    created_at: string
    updated_at: string
    uploader?: User
}

export interface PageProps {
    auth: {
        user: User | null
    }
    flash?: {
        success?: string
        error?: string
        warning?: string
        info?: string
    }
    errors: Record<string, string>
}
