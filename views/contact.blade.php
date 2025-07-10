@pushOnce('css')
<link type="text/css" rel="stylesheet" href="{{ cmsasset('vendor/cms/contact.css') }}">
@endPushOnce

@pushOnce('js')
<script defer src="{{ cmsasset('vendor/cms/contact.js') }}"></script>
@endPushOnce

<h2 class="title">{{ @$data->title }}</h2>

<form action="{{ route('cms.api.contact') }}" method="POST">
    @csrf

    <input type="text" name="name" placeholder="{{ _('Your name') }}" required />
    <input type="email" name="email" placeholder="{{ _('Your e-mail address') }}" required />
    <textarea name="message" placeholder="{{ _('Your message') }}" required rows="6"></textarea>

    @if(!app()->environment('local') && config('services.hcaptcha.sitekey'))
        <div class="h-captcha" data-sitekey="{{ config('services.hcaptcha.sitekey') }}"></div>
        <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
    @endif

    <div class="errors"></div>

    <button type="submit" class="btn">
        <span class="send">{{ _('Send message') }}</span>
        <span class="sending hidden"aria-busy="true">{{ _('Sending message') }}</span>
        <span class="success hidden">{{ _('Success') }}</span>
        <span class="failure hidden">{{ _('Failure') }}</span>
    </button>
</form>
