---
title: "Auth"
date: 2017-09-21T10:00:00-06:00
---
**Auth** keeps a **permission** *(bytes32)* for any **account** *(address)*. Other contracts can use this contract to determine the level of **permission** any **account** has by calling **getPermission()**. Any account with *"setContract"* **permission** *(bytes32)* can also call **setPermission()**.

<img src="/images/auth.png" width="100%"/>

```
pragma solidity ^0.4.11;

contract Auth is Ownable, HasNoEther  {

    event SetPermission( address sender, address account, bytes32 permission, bool value );

    mapping ( address => mapping ( bytes32 => bool ) ) private permission;

    function Auth() {
        permission[owner]['setPermission'] = true;
        permission[owner]['setContract'] = true; //Main.sol
        permission[owner]['setMainAddress'] = true; //Token.sol and Requests.sol
        //permission[owner]['addRequest'] = true; //Requests.sol
    }

    function setPermission( address _account , bytes32 _permission, bool _value) public returns (bool) {
        require( permission[msg.sender]['setPermission'] );
        require( _account!=owner || _permission!='setPermission');//don't take setPermission away from owner
        permission[_account][_permission] = _value;
        SetPermission(msg.sender,_account,_permission,_value);
        return true;
    }

    function getPermission( address _account , bytes32 _permission) constant public returns (bool) {
        return permission[_account][_permission];
    }

}

import 'zeppelin-solidity/contracts/ownership/Ownable.sol';
import 'zeppelin-solidity/contracts/ownership/HasNoEther.sol';

```
Eventually, the **Auth** contract will be extended to allow for more complex governance including voting and signaling for specific changes to the system. Governance changes could go through the system as a normal **request** but with a custom **protocol** *(bytes32)* with the purpose of allowing miners to vote and stake token on changes.

Current address ( http://relay.concurrence.io/address/Auth ):
```
0x89eD8027914dd80d3B5F85a5a6D17F3a0fb300f7
```
Current ABI ( http://relay.concurrence.io/abi/Auth ):
```
[{"constant":false,"inputs":[{"name":"_account","type":"address"},{"name":"_permission","type":"bytes32"},{"name":"_value","type":"bool"}],"name":"setPermission","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"owner","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[],"name":"reclaimEther","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"_account","type":"address"},{"name":"_permission","type":"bytes32"}],"name":"getPermission","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"view","type":"function"},{"inputs":[],"payable":false,"stateMutability":"nonpayable","type":"constructor"},{"payable":false,"stateMutability":"nonpayable","type":"fallback"},{"anonymous":false,"inputs":[{"indexed":false,"name":"sender","type":"address"},{"indexed":false,"name":"account","type":"address"},{"indexed":false,"name":"permission","type":"bytes32"},{"indexed":false,"name":"value","type":"bool"}],"name":"SetPermission","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"previousOwner","type":"address"},{"indexed":true,"name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"}]
```
