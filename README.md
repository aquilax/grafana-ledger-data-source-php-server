# grafana-ledger-data-source-php-server

Grafana server wrapper for ledger data

PHP Version of https://github.com/aquilax/grafana-ledger-data-source-server which uses `ledger-cli` directly and PHP as a server wrapper.

## Running

**Note**: Use this at your own risk.

```shell
LEDGER_FILE=data/accounting.ledger php -S 127.0.0.1:8080 server.php
```

Where:
`data/accounting.ledger` is the ledger file path
`127.0.0.1:8080` are the server's IP and port