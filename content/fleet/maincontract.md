---
title: "Main Contract"
date: 2017-09-21T09:00:00-06:00
---
The **Main** contract keeps a **contract** *(address)* for any **id** *(uint32)*. This allows for old contracts to be replaced with better versions while keeping the main contract address the same.

```
pragma solidity ^0.4.11;

import 'zeppelin-solidity/contracts/ownership/HasNoEther.sol';
import 'zeppelin-solidity/contracts/ownership/Contactable.sol';
import 'Predecessor.sol';

contract Auth { mapping ( address => mapping ( bytes32 => bool ) ) public permission; }

contract Main is HasNoEther, Contactable, Predecessor {

    event SetContract(bytes32 name,address contractAddress,address whoDid);

    mapping(bytes32 => address) contracts;

    function Main(address _authContract) {
      contracts['Auth']=_authContract;
    }

    function setContract(bytes32 _name,address _contract) public returns (bool) {
      Auth authContract = Auth(contracts['Auth']);
      require( authContract.permission(msg.sender,'setContract') );
      contracts[_name]=_contract;
      SetContract(_name,_contract,msg.sender);
      return true;
    }

    function getContract(bytes32 _name) public constant returns (address) {
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
0xF3dB0725228a10D2105fC606E2Dd1312814f3b9a
```
Current ABI:
```
[{"constant":true,"inputs":[],"name":"contactInformation","outputs":[{"name":"","type":"string"}],"payable":false,"type":"function"},{"constant":false,"inputs":[{"name":"_name","type":"bytes32"},{"name":"_contract","type":"address"}],"name":"setContract","outputs":[{"name":"","type":"bool"}],"payable":false,"type":"function"},{"constant":true,"inputs":[],"name":"owner","outputs":[{"name":"","type":"address"}],"payable":false,"type":"function"},{"constant":false,"inputs":[],"name":"reclaimEther","outputs":[],"payable":false,"type":"function"},{"constant":false,"inputs":[{"name":"info","type":"string"}],"name":"setContactInformation","outputs":[],"payable":false,"type":"function"},{"constant":false,"inputs":[{"name":"_descendant","type":"address"}],"name":"setDescendant","outputs":[],"payable":false,"type":"function"},{"constant":true,"inputs":[],"name":"descendant","outputs":[{"name":"","type":"address"}],"payable":false,"type":"function"},{"constant":true,"inputs":[{"name":"_name","type":"bytes32"}],"name":"getContract","outputs":[{"name":"","type":"address"}],"payable":false,"type":"function"},{"constant":false,"inputs":[{"name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"payable":false,"type":"function"},{"inputs":[{"name":"_authContract","type":"address"}],"payable":false,"type":"constructor"},{"payable":false,"type":"fallback"},{"anonymous":false,"inputs":[{"indexed":false,"name":"name","type":"bytes32"},{"indexed":false,"name":"contractAddress","type":"address"},{"indexed":false,"name":"whoDid","type":"address"}],"name":"SetContract","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"previousOwner","type":"address"},{"indexed":true,"name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"}]
```
