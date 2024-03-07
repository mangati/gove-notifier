Gove Notifier
===============

Provides [Gove](https://gove.digital/) integration for Symfony Notifier.

DSN example
-----------

```
GOVE_DSN=gove://TOKEN:PASSWORD@default
```

where:
 - `EMAIL` is your Gove username (must be encoded, replacing `@` with `%40`)
 - `PASSWORD` is your Gove username


Usage
-----------

```php
$options = new GoveOptions('phone-number', 'template_name', [
    "my",
    "template",
    "variables",
]);

$message = new ChatMessage('Test', $options);

$notifier->send($message);
```
