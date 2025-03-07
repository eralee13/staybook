<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use App\Models\Currency;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-contact|edit-contact|delete-contact', ['only' => ['index','show']]);
        $this->middleware('permission:create-contact', ['only' => ['create','store']]);
        $this->middleware('permission:edit-contact', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-contact', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contacts = Contact::get();
        return view('auth.contacts.index', compact('contacts'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact)
    {
        return view('auth.contacts.form', compact('contact'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ContactRequest $request, Contact $contact)
    {
        $params = $request->all();

        $contact->update($params);
        session()->flash('success', 'Контакты обновлены');
        return redirect()->route('contacts.index');
    }

}
