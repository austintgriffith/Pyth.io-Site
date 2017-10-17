---
title: "Auth Contract"
date: 2017-09-21T10:00:00-06:00
---
**Auth** keeps a **permission** *(uint8)* for any **account** *(address)*. Other contracts can use this contract to determine the level of **permission** any **account** has by calling **getPermission(*address*)**. Any account with enough **permission** *(uint8)* can also call **setPermission(*address*,*permission*)**.

```
pragma solidity ^0.4.0;

/*
  >= 240 give permissions to other addresses (Auth admin)
  >= 200 setContractAddress (Main admin)
*/

import 'zeppelin-solidity/contracts/ownership/Ownable.sol';
import 'zeppelin-solidity/contracts/ownership/HasNoEther.sol';
import 'zeppelin-solidity/contracts/ownership/HasNoTokens.sol';
import 'zeppelin-solidity/contracts/ownership/HasNoContracts.sol';

contract Auth is Ownable, HasNoEther, HasNoTokens, HasNoContracts  {

    mapping ( address => uint8 ) public permission;

    function Auth() {
        permission[owner] = 255;
    }

    event SetPermission( address _sender, address _address , uint8 _permission );

    function setPermission( address _address , uint8 _permission) {
        require( permission[msg.sender] >= 240 );
        assert( _address != owner);
        permission[_address] = _permission;
        SetPermission(msg.sender,_address,_permission);
    }

}

```
Eventually, the **Auth** contract will be extended to allow for more complex governance including voting and signally for specific changes to the system.


Current address:
```
0xAcb7113DE131c119dDD0b78A261081616239a241
```
Current ABI:
```
[{"constant":true,"inputs":[{"name":"","type":"address"}],"name":"permission","outputs":[{"name":"","type":"uint8"}],"payable":false,"type":"function"},{"constant":true,"inputs":[],"name":"owner","outputs":[{"name":"","type":"address"}],"payable":false,"type":"function"},{"constant":false,"inputs":[{"name":"_address","type":"address"},{"name":"_permission","type":"uint8"}],"name":"setPermission","outputs":[],"payable":false,"type":"function"},{"constant":false,"inputs":[{"name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"payable":false,"type":"function"},{"inputs":[],"payable":false,"type":"constructor"},{"anonymous":false,"inputs":[{"indexed":false,"name":"_sender","type":"address"},{"indexed":false,"name":"_address","type":"address"},{"indexed":false,"name":"_permission","type":"uint8"}],"name":"SetPermission","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"previousOwner","type":"address"},{"indexed":true,"name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"}]
```
