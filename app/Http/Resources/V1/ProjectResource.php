<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $progress = ($this->tasks_count > 0)
            ? round(($this->completed_tasks_count / $this->tasks_count) * 100)
            : 0;

        return [
            // --- البيانات الأساسية ---
             'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'due_date' => $this->due_date ,

            // --- بيانات محسوبة ومجمعة ---
            // 'progress_percentage' => $progress,
            // 'tasks_count' => $this->whenNotNull($this->tasks_count), // يظهر فقط إذا تم تحميله عبر withCount
            // 'completed_tasks_count' => $this->whenNotNull($this->completed_tasks_count),

            // --- العلاقات (يتم تحميلها بشكل مشروط) ---
            'team' => new TeamResource($this->whenLoaded('team')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            // 'members' => UserResource::collection($this->whenLoaded('members')),
            // 'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            // // سيعرض التعليقات والمرفقات فقط عند تحميلها (عادة في صفحة التفاصيل)
            // 'comments' => CommentResource::collection($this->whenLoaded('comments')),
            // 'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),

            // --- بيانات زمنية ---
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
