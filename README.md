# Simple Altcoin JSON-RPC client based on GuzzleHttp


## Installation
Run ```php composer.phar require done/php-altcoinrpc``` in your project directory or add following lines to composer.json
```javascript
"require": {
    "done/php-altcoinrpc": "^2.1"
}
```
and run ```php composer.phar install```.

## Requirements
PHP 7.1 or higher  

## Usage
Create new object with url as parameter
```php
/**
 * Don't forget to include composer autoloader by uncommenting line below
 * if you're not already done it anywhere else in your project.
 **/
// require 'vendor/autoload.php';

use DOne\Altcoin\Client as AltcoinClient;

$altcoind = new AltcoinClient('http://rpcuser:rpcpassword@localhost:8332/');
```
or use array to define your altcoind settings
```php
/**
 * Don't forget to include composer autoloader by uncommenting line below
 * if you're not already done it anywhere else in your project.
 **/
// require 'vendor/autoload.php';

use DOne\Altcoin\Client as AltcoinClient;

$altcoind = new AltcoinClient([
    'scheme'        => 'http',                 // optional, default http
    'host'          => 'localhost',            // optional, default localhost
    'port'          => 8332,                   // optional, default 8332
    'user'          => 'rpcuser',              // required
    'password'      => 'rpcpassword',          // required
    'ca'            => '/etc/ssl/ca-cert.pem',  // optional, for use with https scheme
    'preserve_case' => false,                  // optional, send method names as defined instead of lowercasing them
]);
```
Then call methods defined in [Bitcoin Core API Documentation](https://bitcoin.org/en/developer-reference#altcoin-core-apis) with magic:
```php
/**
 * Get block info.
 */
$block = $altcoind->getBlock('000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f');

$block('hash')->get();     // 000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f
$block['height'];          // 0 (array access)
$block->get('tx.0');       // 4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b
$block->count('tx');       // 1
$block->has('version');    // key must exist and CAN NOT be null
$block->exists('version'); // key must exist and CAN be null
$block->contains(0);       // check if response contains value
$block->values();          // array of values
$block->keys();            // array of keys
$block->random(1, 'tx');   // random block txid
$block('tx')->random(2);   // two random block txid's
$block('tx')->first();     // txid of first transaction
$block('tx')->last();      // txid of last transaction

/**
 * Send transaction.
 */
$result = $altcoind->sendToAddress('mmXgiR6KAhZCyQ8ndr2BCfEq1wNG2UnyG6', 0.1);
$txid = $result->get();

/**
 * Get transaction amount.
 */
$result = $altcoind->listSinceBlock();
$altcoin = $result->sum('transactions.*.amount');
$satoshi = \DOne\Altcoin\to_satoshi($altcoin);
```
To send asynchronous request, add Async to method name:
```php
$altcoind->getBlockAsync(
    '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f',
    function ($response) {
        // success
    },
    function ($exception) {
        // error
    }
);
```

You can also send requests using request method:
```php
/**
 * Get block info.
 */
$block = $altcoind->request('getBlock', '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f');

$block('hash');            // 000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f
$block['height'];          // 0 (array access)
$block->get('tx.0');       // 4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b
$block->count('tx');       // 1
$block->has('version');    // key must exist and CAN NOT be null
$block->exists('version'); // key must exist and CAN be null
$block->contains(0);       // check if response contains value
$block->values();          // get response values
$block->keys();            // get response keys
$block->first('tx');       // get txid of the first transaction
$block->last('tx');        // get txid of the last transaction
$block->random(1, 'tx');   // get random txid

/**
 * Send transaction.
 */
$result = $altcoind->request('sendtoaddress', 'mmXgiR6KAhZCyQ8ndr2BCfEq1wNG2UnyG6', 0.06);
$txid = $result->get();

```
or requestAsync method for asynchronous calls:
```php
$altcoind->requestAsync(
    'getBlock',
    '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f',
    function ($response) {
        // success
    },
    function ($exception) {
        // error
    }
);
```

## Multi-Wallet RPC
You can use `wallet($name)` function to do a [Multi-Wallet RPC call](https://en.bitcoin.it/wiki/API_reference_(JSON-RPC)#Multi-wallet_RPC_calls):
```php
/**
 * Get wallet2.dat balance.
 */
$balance = $altcoind->wallet('walletfilename.dat')->getbalance();

echo $balance->get(); // 0.10000000
```

## Exceptions
* `DOne\Altcoin\Exceptions\BadConfigurationException` - thrown on bad client configuration.
* `DOne\Altcoin\Exceptions\BadRemoteCallException` - thrown on getting error message from daemon.
* `DOne\Altcoin\Exceptions\ConnectionException` - thrown on daemon connection errors (e. g. timeouts)


## Helpers
Package provides following helpers to assist with value handling.
#### `to_altcoin()`
Converts value in satoshi to altcoin.
```php
echo DOne\Altcoin\to_altcoin(100000); // 0.00100000
```
#### `to_satoshi()`
Converts value in altcoin to satoshi.
```php
echo DOne\Altcoin\to_satoshi(0.001); // 100000
```
#### `to_ubtc()`
Converts value in altcoin to ubtc/bits.
```php
echo DOne\Altcoin\to_ubtc(0.001); // 1000.0000
```
#### `to_mbtc()`
Converts value in altcoin to mbtc.
```php
echo DOne\Altcoin\to_mbtc(0.001); // 1.0000
```
#### `to_fixed()`
Trims float value to precision without rounding.
```php
echo DOne\Altcoin\to_fixed(0.1236, 3); // 0.123
```

## License

This product is distributed under MIT license.

