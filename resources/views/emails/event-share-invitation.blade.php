@component('emails.layouts.brand')
  <h2>{{ __('Event Invitation') }}</h2>
  <p>{{ $message ?? '' }}</p>
  <ul>
    @if(isset($event->title))
      <li><strong>{{ __('Event') }}:</strong> {{ $event->title }}</li>
    @endif
    @if(isset($event->event_date))
      <li><strong>{{ __('Date') }}:</strong> {{ $event->event_date }}</li>
    @endif
    @if(isset($event->start_time))
      <li><strong>{{ __('Time') }}:</strong> {{ $event->start_time }}</li>
    @endif
    @if(isset($event->location))
      <li><strong>{{ __('Location') }}:</strong> {{ $event->location }}</li>
    @endif
  </ul>
  @if(isset($token))
    <p>
      <a href="{{ url('/api/shared-events/'.$token) }}" style="color:#0ea5e9;">{{ __('View Event') }}</a>
    </p>
  @endif
@endcomponent



