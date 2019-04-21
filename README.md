# json-rpm-socket

This project provider JsonRpc server and support terminal for launch commands.

This can also create custom telnet server for TCP

## Starting

### Create server

`make` This command create docker container

`make run` Start server with docker daemon 

`make restart` Reset server

`make destroy` Delete container and image

`make logs` Show php logs 

#### Ports:

2080: JsonRpc server

2081: Terminal for JsonRpc support

2024: Telnet custom server with example

#### In example

* JSON-RPC server running on 2080, you execute for example 'nc 127.0.0.1 2080 < res.json';

* TERMINAL server running on 2081, you execute for example 'nc 127.0.0.1 2081' and type 'sum 1 2';

* CUSTOM TELNET server running on 2024, you execute for example 'nc 127.0.0.1 2024' and type '.h';