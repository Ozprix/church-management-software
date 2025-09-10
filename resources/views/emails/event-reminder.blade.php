@component('emails.layouts.brand')
    <h2>{{ __('emails.reminders.title') }}</h2>
    <p>{{ $message ?? __('emails.reminders.default', ['title' => ($event->title ?? __('emails.reminders.untitled'))]) }}</p>
    <ul>
        @if(isset($event->title))
            <li><strong>{{ __('emails.reminders.event') }}:</strong> {{ $event->title }}</li>
        @endif
        @if(isset($event->event_date))
            <li><strong>{{ __('emails.common.date') }}:</strong> {{ $event->event_date }}</li>
        @endif
        @if(isset($event->start_time))
            <li><strong>{{ __('emails.reminders.time') }}:</strong> {{ $event->start_time }}</li>
        @endif
        @if(isset($event->location))
            <li><strong>{{ __('emails.reminders.location') }}:</strong> {{ $event->location }}</li>
        @endif
    </ul>
    <p>{{ __('emails.reminders.closing') }}</p>
@endcomponent


