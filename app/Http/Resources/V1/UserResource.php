<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // هذا سيقوم بإرجاع البيانات الأساسية للمستخدم
        // يمكنك تخصيص الحقول كما تريد
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,

            // مثال على حقل إضافي إذا كنت تستخدم Laravel Jetstream أو لديك حقل لصورة الملف الشخصي
            // إذا لم يكن لديك هذا الحقل، يمكنك حذف السطر التالي أو تركه في تعليق
            // 'profile_photo_url' => $this->profile_photo_url,

            // تاريخ إنشاء الحساب (اختياري)
            // 'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
