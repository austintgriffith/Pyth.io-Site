---
title: "Main Contract"
date: 2017-09-21T09:00:00-06:00
---
**Main** keeps a **contract** *(address)* for any **name** *(bytes32)* and is deployed with the **Auth** *(address)* initialized. This allows for old contracts to be replaced with better versions while keeping the main contract address the same. **Main** also implements the **Predecessor** concept where a **descendant** is set when a new version of **Main** is deployed. Then, if a developer contract attempts to interface with an old version of the **Main** contract, the current **descendant** **Main** receives requests by proxy.  

<img src="/images/main.png" width="100%"/>

```
pragma solidity ^0.4.11;

contract Main is HasNoEther, Contactable, Predecessor {

    event SetContract(bytes32 name,address contractAddress,address whoDid);

    mapping(bytes32 => address) contracts;

    function Main(address _authContract) {
      contracts['Auth']=_authContract;
    }

    function setContract(bytes32 _name,address _contract) public returns (bool) {
      Auth authContract = Auth(contracts['Auth']);
      require( authContract.getPermission(msg.sender,'setContract') );
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

contract Auth { function getPermission( address _account , bytes32 _permission) constant public returns (bool) { } }

import 'zeppelin-solidity/contracts/ownership/HasNoEther.sol';
import 'zeppelin-solidity/contracts/ownership/Contactable.sol';
import 'Predecessor.sol';

```
Current address ( http://relay.concurrence.io/address/Main ):
```
0xfb15A576DB9D2D5cb3e7F7a3513FFb633B321E63
```
Current ABI ( http://relay.concurrence.io/abi/Main ):
```
[{"constant":true,"inputs":[],"name":"contactInformation","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_name","type":"bytes32"},{"name":"_contract","type":"address"}],"name":"setContract","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"owner","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[],"name":"reclaimEther","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"info","type":"string"}],"name":"setContactInformation","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"_descendant","type":"address"}],"name":"setDescendant","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"descendant","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"_name","type":"bytes32"}],"name":"getContract","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"inputs":[{"name":"_authContract","type":"address"}],"payable":false,"stateMutability":"nonpayable","type":"constructor"},{"payable":false,"stateMutability":"nonpayable","type":"fallback"},{"anonymous":false,"inputs":[{"indexed":false,"name":"name","type":"bytes32"},{"indexed":false,"name":"contractAddress","type":"address"},{"indexed":false,"name":"whoDid","type":"address"}],"name":"SetContract","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"previousOwner","type":"address"},{"indexed":true,"name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"}]
```
