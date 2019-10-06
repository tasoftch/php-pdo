# Php PDO Extension
This library extends the builtin PDO object by several useful features.

It adds Php generators to fetch rows and insert or update records.

### Simple SELECT
```php
$gen = $PDO->select("SELECT * FROM XXX");
// Now $gen is a generator, that means nothing happens until you want to fetch the records.

// ... more code

// Now, fetch the SQL records:
foreach($gen as $record) {
    // ... 
}
```

also allowed is:
```php
$gen = $PDO->select("SELECT * FROM XXX WHERE id = ?", [$objectID]);
// For secure SQL request.
```

### Injecting Records

```php
$gen = $PDO->inject("INSERT INTO XXX (name, email) VALUES (?, ?)");
// Now again nothing happend yet.

// To insert records, just use.

$gen->send(["Thomas", "email@example.org"]);
// As many times you want!
```

Please note that the SQL syntax is directly passed to the built-in Php PDO object. See those documentation for more information about the SQL scripting language.

### Mapping
The library also allows to 