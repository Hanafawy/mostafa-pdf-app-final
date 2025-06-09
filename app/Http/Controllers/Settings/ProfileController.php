<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\PdfFile;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{

    /**
     * عرض صفحة إدارة ملفات PDF
     */
    public function index(): Response
    {
        $files = PdfFile::with('uploader')->latest()->get();

        return Inertia::render('PdfManager', [
            'files' => $files,
        ]);
    }

    /**
     * رفع ملف PDF جديد - متاح لجميع المستخدمين
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf|max:10240', // 10MB max
            'description' => 'nullable|string|max:1000',
        ], [
            'file.required' => 'يرجى اختيار ملف PDF',
            'file.mimes' => 'يجب أن يكون الملف من نوع PDF',
            'file.max' => 'حجم الملف يجب أن يكون أقل من 10 ميجابايت',
            'title.required' => 'عنوان الملف مطلوب',
            'title.max' => 'عنوان الملف يجب أن يكون أقل من 255 حرف',
        ]);

        try {
            $file = $request->file('file');
            $fileName = Str::uuid() . '.pdf';
            $filePath = $file->storeAs('pdfs', $fileName, 'public');

            // التحقق من رفع الملف بنجاح
            if (!Storage::disk('public')->exists($filePath)) {
                return back()->withErrors(['file' => 'فشل في رفع الملف']);
            }

            PdfFile::create([
                'title' => $request->title,
                'original_name' => $file->getClientOriginalName(),
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'description' => $request->description,
                'uploaded_by' => auth()->id(),
            ]);

            return back()->with('success', 'تم رفع الملف بنجاح');

        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'حدث خطأ في رفع الملف: ' . $e->getMessage()]);
        }
    }

    /**
     * تحديث ملف PDF - متاح لصاحب الملف أو المدير
     */
    public function update(Request $request, PdfFile $pdf): RedirectResponse
    {
        // التحقق من أن المستخدم صاحب الملف أو مدير
        if ($pdf->uploaded_by !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'غير مصرح لك بتحديث هذا الملف');
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',
        ]);

        $pdf->update($request->only(['title', 'description', 'is_active']));

        return back()->with('success', 'تم تحديث الملف بنجاح');
    }

    /**
     * حذف ملف PDF - متاح لصاحب الملف أو المدير
     */
    public function destroy(PdfFile $pdf): RedirectResponse
    {
        // التحقق من أن المستخدم صاحب الملف أو مدير
        if ($pdf->uploaded_by !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'غير مصرح لك بحذف هذا الملف');
        }

        try {
            // حذف الملف من التخزين
            if (Storage::disk('public')->exists($pdf->file_path)) {
                Storage::disk('public')->delete($pdf->file_path);
            }

            // حذف السجل من قاعدة البيانات
            $pdf->delete();

            return back()->with('success', 'تم حذف الملف بنجاح');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'حدث خطأ في حذف الملف']);
        }
    }

    /**
     * تحميل ملف PDF - متاح للجميع
     */
    public function download(PdfFile $pdf): StreamedResponse
    {
        if (!Storage::disk('public')->exists($pdf->file_path)) {
            abort(404, 'الملف غير موجود');
        }

        return Storage::disk('public')->download(
            $pdf->file_path,
            $pdf->original_name,
            [
                'Content-Type' => 'application/pdf',
            ]
        );
    }

}
