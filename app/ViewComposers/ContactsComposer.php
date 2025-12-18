<?php

namespace App\ViewComposers;

use App\Models\Contact;
use Illuminate\View\View;

class ContactsComposer
{
    public function compose(View $view): void
    {
        $contacts = cache()->remember('contacts_public_v1', 3600, function () {
            $c = \App\Models\Contact::query()
                ->select(['id','address','phone','email','whatsapp','instagram'])
                ->orderByDesc('id')
                ->first();

            if (!$c) return null;

            return [
                'address'   => $c->address,
                'phone'     => $c->phone,
                'email'     => $c->email,
                'whatsapp'  => $c->whatsapp,
                'instagram' => $c->instagram,
            ];
        });

        $view->with('contacts', $contacts);
    }
}