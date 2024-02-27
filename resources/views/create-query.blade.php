<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400..700&family=Cute+Font&family=Pacifico&display=swap" rel="stylesheet">
    <title>Excel to Sql</title>
</head>
<body>
    <img class="excel-logo" src="{{ asset('images/excel-logo.png') }}" alt="" srcset="">
    <img class="sql-logo" src="{{ asset('images/sql-logo.png') }}" alt="" srcset="">
    <div class="outer-box">
        <h1>Set Table Format</h1>
        <div class="inner-box">
            <div class="set-data-type">
                <form action="{{ route('create.query') }}" method="post">
                    <table>
                        <thead>
                            <tr>
                                <th>Column Name</th>
                                <th>Data Type</th>
                                <th>Size</th>
                            </tr>
                        </thead>
                        <tbody>
                                @csrf
                                <div class="table-name">
                                    <label for="">Table Name</label>
                                    <input type="text" name="table_name" id="" value="{{ session('filename')}}">
                                </div>
                                @foreach (session('tablecolumn') as $tc)
                                    <tr>
                                        <td>
                                            <input type="text" name="column_name[]" id="" value="{{ $tc }}">
                                        </td>
                                        <td>
                                            <select name="data_type[]" id="">
                                                <option value="">Select One</option>
                                                <option value="varchar2">Varchar2</option>
                                                <option value="char">Char</option>
                                                <option value="date">Date</option>
                                                <option value="number">Number</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="size[]">
                                        </td>
                                    </tr>
                                @endforeach
                        </tbody>
                    </table>
                    <input type="submit" value="Generate Create Query" id="submit-btn">
                </form>
                @error('errorMsg')
                    <div style="font-size: 20px; color: white">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
    <p class="copyright">Copyright © 2024 Suman Debnath</p>
</body>
</html>