@component('mail::message')
@lang('messages.reports.hi') {{$user}},<br>

@lang('messages.reports.messageMail')
<br>
<b >
    (@lang('messages.reports.expired_at'))
</b>


@component('mail::button', ['url' => $file])
    @lang('messages.reports.download')
@endcomponent


Thanks,<br>
Silosys Team!
@endcomponent
