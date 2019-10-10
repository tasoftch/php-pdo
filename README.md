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
The library also allows to map values from raw format of a data base into objects and backwards.

```php
$PDO->setTypeMapper( new DateMapper() );
// Now the methods selectWithObjects and injectWithObjects will convert raw values into their object values.

$record = $PDO->selectOneWithObjects("SELECT * FROM XXX");
print_r($record);
/*
Might look like:
Array (
    'the_date' => TASoft\Util\ValueObject\Date ... ,
    'the_date_time' => TASoft\Util\ValueObject\DateTime ... ,
    'the_time' => TASoft\Util\ValueObject\Time ...
)

// SQL:
CREATE TABLE XXX (
    the_date DATE DEFAULT NULL,
    the_date_time DATETIME DEFAULT NULL,
    the_time TIME DEFAULT NULL
)
*/

// This also works backward:
$newDate = new TASoft\Util\ValueObject\Date("1999-05-23");
$PDO->injectWithObjcts("UPDATE XXX SET the_date = ? WHERE ...")->send([$newDate, ...]);

// Using MapperChain allows to combine more than one type mapper.
```

Another advantage is the ability to perform transactions.  
Transactions combine several SQL statements and make sure that everyone of them is performed successfully.  
Like: All or nothing
```php
$PDO->transaction(function() {
    /** @var TASoft\Util\PDO $this */
    $this->inject(....);
    
    $this->exec( .... );
    ...
});
```
If anywhere inside the code an exception occures, the transaction will be cancelled (rollBack).