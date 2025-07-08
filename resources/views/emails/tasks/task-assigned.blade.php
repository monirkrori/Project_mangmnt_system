@component('mail::message')
    # You Have New Task

    Ypu have ane task : **{{ $task->title }}** :

    > **{{ ucfirst($task->status) }}**

    @component('mail::button', ['url' => url('/tasks/' . $task->id)])
        View Task
    @endcomponent

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
