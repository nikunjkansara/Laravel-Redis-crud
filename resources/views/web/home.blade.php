@extends('layout.layout')

@section('Title', 'Webscraping Test')

@section('content')
    <h1>Welcome to our website!</h1>
    <p><a href="{{route('url.create')}}">Create</a></p>
    <table width="100%" border="1">
        <tr>
            <th width="10%">#</th>
            <th width="40%">URLs</th>
            <th width="30%">Status</th>
            <th width="20%">Action</th>
        </tr>
        
        @foreach($urls as $key => $url)
       
        <tr>
            <td>{{ $key }}</td>
            <td>{{ isset($url['url'])?$url['url']: '' }}</td>
            <td>{{ isset($url['status'])?$url['status']: '' }}</td>
            <td><a href="{{ route('url.edit', ['key' =>$url['id']]) }}">Edit</a>&nbsp;
            <a href="{{ route('url.scrap', ['key' => $url['id']]) }}">Scrap</a> &nbsp;
            <form action="{{ route('url.destroy', ['key' => $url['id']]) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit">Delete</button>
            </form>
            </td>
        </tr>
        @endforeach
    </table>
@endsection