<?php

use Aimeos\Cms\Mail\ContactMessage;
use Aimeos\Cms\Requests\ContactRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Routing\Controller;


class ContactController extends Controller
{
    public function send(ContactRequest $request)
    {
        Mail::to(config('mail.from.address'))->send(
            new ContactMessage($request->validated())
        );

        return response()->json(['message' => 'Message sent successfully']);
    }
}
