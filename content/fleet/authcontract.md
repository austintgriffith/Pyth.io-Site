---
title: "Auth Contract"
date: 2017-09-21T10:00:00-06:00
---
**Auth** keeps a **permission** *(uint8)* for any **account** *(address)*. Other contracts can use this contract to determine the level of **permission** any **account** has by calling **getPermission(*address*)**. Any account with enough **permission** *(uint8)* can also call **setPermission(*address*,*permission*)**.

```
pragma solidity ^0.4.0;

import 'zeppelin-solidity/contracts/ownership/Ownable.sol';
import 'zeppelin-solidity/contracts/ownership/HasNoEther.sol';

contract Auth is Ownable, HasNoEther  {

    event SetPermission( address _sender, address _address, bytes32 _permission, bool _value );

    mapping ( address => mapping ( bytes32 => bool ) ) public permission;

    function Auth() {
        permission[owner]['setPermission'] = true;
        permission[owner]['setContract'] = true; //Main.sol
    }

    function setPermission( address _address , bytes32 _permission, bool _value) {
        require( permission[msg.sender]['setPermission'] );
        require( _address!=owner || _permission!='setPermission');
        permission[_address][_permission] = _value;
        SetPermission(msg.sender,_address,_permission,_value);
    }

}

```
Eventually, the **Auth** contract will be extended to allow for more complex governance including voting and signaling for specific changes to the system.


Current address:
```
0x8F636787719939Da1173C821282753f587b5Ed28
```
Current ABI:
```
[{"constant":true,"inputs":[{"name":"","type":"address"},{"name":"","type":"bytes32"}],"name":"permission","outputs":[{"name":"","type":"bool"}],"payable":false,"type":"function"},{"constant":false,"inputs":[{"name":"_address","type":"address"},{"name":"_permission","type":"bytes32"},{"name":"_value","type":"bool"}],"name":"setPermission","outputs":[],"payable":false,"type":"function"},{"constant":true,"inputs":[],"name":"owner","outputs":[{"name":"","type":"address"}],"payable":false,"type":"function"},{"constant":false,"inputs":[],"name":"reclaimEther","outputs":[],"payable":false,"type":"function"},{"constant":false,"inputs":[{"name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"payable":false,"type":"function"},{"inputs":[],"payable":false,"type":"constructor"},{"payable":false,"type":"fallback"},{"anonymous":false,"inputs":[{"indexed":false,"name":"_sender","type":"address"},{"indexed":false,"name":"_address","type":"address"},{"indexed":false,"name":"_permission","type":"bytes32"},{"indexed":false,"name":"_value","type":"bool"}],"name":"SetPermission","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"previousOwner","type":"address"},{"indexed":true,"name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"}]
```
