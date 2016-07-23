<table width="100%" border="1">
    <tbody>
        <tr>
            <th>id</th>
            <th>name</th>
            <th>email</td>
        </tr>
    </tbody>
    @foreach ($users as $user)
        <tbody>
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
            </tr>
        </tbody>
    @endforeach
</table>

{!! $users->links() !!}
