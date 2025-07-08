@component('mail::message')
    # Task Status Updated

    The status of the task **{{ $task->title }}** has been changed to:

    > **{{ ucfirst($task->status) }}**

    @component('mail::button', ['url' => url('/tasks/' . $task->id)])
        View Task
    @endcomponent

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
