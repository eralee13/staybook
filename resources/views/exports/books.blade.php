<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Название</th>
        <th>Дата создания</th>
        <th>Взрослые</th>
        <th>Дети</th>
        <th>Дата заезда</th>
        <th>Дата выезда</th>
        <th>Цена</th>
        <th>Сумма</th>
        <th>Валюта</th>
        <th>Тел. номер</th>
        <th>Почта</th>
    </tr>
    </thead>
    <tbody>
    @foreach($ebooks as $book)
        <tr>
            <td>{{ $book->id }}</td>
            <td>{{ $book->title }} {{ $book->title2 }}</td>
            <td>{{ $book->created_at }}</td>
            <td>{{ $book->adult }}</td>
            <td>{{ preg_replace('/\D/', '', $book->child) }}</td>
            <td>{{ $book->arrivalDate }}</td>
            <td>{{ $book->departureDate }}</td>
            <td>{{ $book->price }}</td>
            <td>{{ $book->sum }}</td>
            <td>{{ $book->currency }}</td>
            <td>{{ preg_replace('/\D/', '', $book->phone) }}</td>
            <td>{{ $book->email }}</td>
        </tr>
    @endforeach
    </tbody>
</table>