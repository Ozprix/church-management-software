@component('emails.layouts.brand')
    <h2>{{ __('emails.donations.thank_you_title') }}</h2>
    <p>{{ __('emails.common.greeting_name', ['name' => trim(($donation->member?->first_name ?? '') . ' ' . ($donation->member?->last_name ?? ''))]) }}</p>
    <p>{{ __('emails.donations.thank_you_body') }}</p>
    <ul>
        <li><strong>{{ __('emails.donations.receipt_number') }}:</strong> {{ $donation->receipt_number }}</li>
        <li><strong>{{ __('emails.common.date') }}:</strong> {{ optional($donation->donation_date)->format('Y-m-d') }}</li>
        <li><strong>{{ __('emails.common.amount') }}:</strong> {{ number_format((float) $donation->amount, 2) }}</li>
        @if($donation->category)
            <li><strong>{{ __('emails.donations.category') }}:</strong> {{ $donation->category->name }}</li>
        @endif
        @if($donation->project)
            <li><strong>{{ __('emails.donations.project') }}:</strong> {{ $donation->project->name }}</li>
        @endif
        @if($donation->campaign)
            <li><strong>{{ __('emails.donations.campaign') }}:</strong> {{ $donation->campaign->name }}</li>
        @endif
        @if($donation->recipient)
            <li><strong>{{ __('emails.donations.gifted_to') }}:</strong> {{ $donation->recipient->first_name }} {{ $donation->recipient->last_name }}</li>
        @endif
    </ul>
    @if($donation->gift_message)
        <p><strong>{{ __('emails.donations.gift_message') }}:</strong> {{ $donation->gift_message }}</p>
    @endif
    <p>{{ __('emails.common.reply_note') }}</p>
    <p>{{ __('emails.common.signature_line', ['org' => config('app.organization_name', config('app.name'))]) }}</p>
@endcomponent


