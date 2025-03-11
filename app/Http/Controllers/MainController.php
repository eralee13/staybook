<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use App\Mail\BookMail;
use App\Models\Book;
use App\Models\Contact;
use App\Models\Meal;
use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

class MainController extends Controller
{


    public function changeLocale($locale)
    {
        $availableLocales = ['ru', 'en'];
        if (!in_array($locale, $availableLocales)) {
            $locale = config('app.locale');
        }
        session(['locale' => $locale]);
        App::setLocale($locale);
        return redirect()->back();
    }

    public function contact_mail(Request $request)
    {
        Mail::to('info@timdjol.com')->send(new ContactMail($request));
        session()->flash('success', 'Заявка ' . $request->name . ' отправлена');
        return redirect()->route('contactspage');
    }

}
