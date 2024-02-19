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
            <form method="post" action="{{ route('url.update', ['key' => $key]) }}">
                @csrf
                @method('put')
                <label for="title">URL:</label><br>
                <input type="text" id="url" name="url" value="{{ isset($url['url'])?$url['url']: '' }}">
                <br>
                <label for="title">Scrap:</label><br>
                <textarea type="text" id="scrap" name="scrap">{{ isset($url['scrap'])?$url['scrap']: '' }}</textarea>
                <br>
                <label for="title">Status:</label><br>
                <select id="url" name="status">
                    <option value="">All</option>
                    <option value="active" @if($url['status'] == 'active') selected @endif>Active </option>
                    <option value="pending" @if($url['status'] == 'pending') selected @endif>Pending</option>
                </select>
                <br>

                <button type="submit">Submit</button>
            </form>
        </td>
    </tr>

</table>


@endsection