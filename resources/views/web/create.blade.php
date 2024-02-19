@extends('layout.layout')

@section('Title', 'Webscraping Test')

@section('content')
<h1>Create New Url For Scraping</h1>
<table>
    <tr>
        <td>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </td>
    </tr>
    <tr>
        <td>
            <form method="POST" action="{{ route('url.store') }}">
                @csrf

                <label for="title">URL:</label><br>
                <input type="text" id="url" name="url"><br>

                <label for="title">Status:</label><br>
                <select id="url" name="status">
                    <option value="">All</option>
                    <option value="active">Active </option>
                    <option value="pending">Pending</option>
                </select>
                <br>

                <button type="submit">Submit</button>
            </form>
        </td>
    </tr>

</table>


@endsection