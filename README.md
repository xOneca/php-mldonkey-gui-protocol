# php-mldonkey-gui-protocol

PHP implementation of the MLDonkey GUI protocol. This way, you can control MLDonkey from any PHP script or web interface.

A quick example of usage:

```php
<?php
header('Content-Type: text/plain');

// Load library
require_once('mlnet.class.php');

// Connect to MLDonkey via GUI port
$s = fsockopen('localhost', 4001);
if($s === false)
    die('Error connecting to MLDonkey.');

print("Connected.\n");

$answer = new COpcode_CoreProtocol( $s );
$answer->read();
$answer->expand();

printf("Protocol version: %u\n\n", $answer->version);

print("Sending version and logging in...\n");

$version = new COpcode_ProtocolVersion( $s );
$version->send();

$login = new COpcode_PassWord( $s, 'username', 'password' );
$login->send();

print("Waiting response...\n");
$response = new CMessage( $s );
while( $response->read() )
{
    $message = $response->convert();
    $message->expand();

    switch( $message->opcode )
    {
        case 1:
            print( "OptionsInfo.\n" );
            var_dump( $message->options );
            break;

        case 47:
            print( "Problems with authentication information!\n" );
            break;

        case 19:
            printf( "Server message: \"%s\"\n", trim($message->message) );
            break;

        case 20:
            printf( "NetworkInfo: %s", $message->network_name );
            printf( "  (U:%uMiB / D:%uMiB)\n",
                    $message->uploaded / 1024 / 1024,
                    $message->downloaded / 1024 / 1024
            );
            break;

        default:
            printf( "(!) OPCODE not implemented: %d\n", $message->opcode );
            var_dump( $message->raw_msg->data );
            break;
    }
}

fclose($s);
```

MLDonkey sends _lots_ of stuff, although we don't have asked. So you will have to filter received messages (by opcode) until you get the desired one(s).
