<?php

namespace App\ViewComposers;

use App\Models\Contact;
use Illuminate\View\View;

class ContactsComposer
{
    public function compose(View $view){
        $contacts = Contact::get();
        $view->with('contacts', $contacts);
    }
}