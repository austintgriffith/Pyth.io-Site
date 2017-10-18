---
title: "Main Contract"
date: 2017-09-21T09:00:00-06:00
---
The **Main** contract keeps a **contract** *(address)* for any **id** *(uint32)*. This allows for old contracts to be replaced with better versions while keeping the main contract address the same.

```
pragma solidity ^0.4.0;

import 'zeppelin-solidity/contracts/ownership/Ownable.sol';
import 'zeppelin-solidity/contracts/ownership/HasNoEther.sol';
import 'Predecessor.sol';

contract Auth { mapping ( address => mapping ( bytes32 => bool ) ) public permission; }

contract Main is Ownable, HasNoEther, Predecessor {

    event SetContract(bytes32 _name,address _address);

    mapping(bytes32 => address) contracts;

    function Main(address _authContract) {
      contracts['Auth']=_authContract;
    }

    function setContract(bytes32 _name,address _address){
      Auth authContract = Auth(contracts['Auth']);
      require( authContract.permission(msg.sender,'setContract') );
      contracts[_name]=_address;
      SetContract(_name,_address);
    }

    function getContract(bytes32 _name) constant returns (address) {
      if(descendant!=address(0)) {
        return Main(descendant).getContract(_name);
      }else{
        return contracts[_name];
      }
    }

}

```
Current address:
```
0xB54A4f23204C46D6e5AB374588ef015C2D78C2Db
```
Current ABI:
```
[{"constant":false,"inputs":[{"name":"_name","type":"bytes32"},{"name":"_address","type":"address"}],"name":"setContract","outputs":[],"payable":false,"type":"function"},{"constant":true,"inputs":[],"name":"owner","outputs":[{"name":"","type":"address"}],"payable":false,"type":"function"},{"constant":false,"inputs":[],"name":"reclaimEther","outputs":[],"payable":false,"type":"function"},{"constant":false,"inputs":[{"name":"_descendant","type":"address"}],"name":"setDescendant","outputs":[],"payable":false,"type":"function"},{"constant":true,"inputs":[],"name":"descendant","outputs":[{"name":"","type":"address"}],"payable":false,"type":"function"},{"constant":true,"inputs":[{"name":"_name","type":"bytes32"}],"name":"getContract","outputs":[{"name":"","type":"address"}],"payable":false,"type":"function"},{"constant":false,"inputs":[{"name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"payable":false,"type":"function"},{"inputs":[{"name":"_authContract","type":"address"}],"payable":false,"type":"constructor"},{"payable":false,"type":"fallback"},{"anonymous":false,"inputs":[{"indexed":false,"name":"_name","type":"bytes32"},{"indexed":false,"name":"_address","type":"address"}],"name":"SetContract","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"previousOwner","type":"address"},{"indexed":true,"name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"}]
```
