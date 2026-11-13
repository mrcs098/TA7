@if ($errors->any())
    <div class="form-errors" role="alert" aria-labelledby="form-errors-heading">
        <h2 id="form-errors-heading">Please check the highlighted fields.</h2>
        <ul>
            @foreach ($errors->messages() as $field => $messages)
                @foreach ($messages as $message)
                    <li><a href="#{{ $field }}">{{ $message }}</a></li>
                @endforeach
            @endforeach
        </ul>
    </div>
@endif
