<!DOCTYPE html>
<html>
<head>
    <title>Import Contacts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Import Contacts</h1>
        <form action="{{ route('contacts.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">XML File</label>
                <input type="file" name="xml_file" class="form-control" accept=".xml" required>
            </div>
            <button type="submit" class="btn btn-primary">Import</button>
            <a href="{{ route('contacts.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
</body>
</html>