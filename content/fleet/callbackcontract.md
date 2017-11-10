---
title: "Callback"
date: 2017-09-21T05:00:00-06:00
---
The **Callback** contract is simply a placeholder for examples. Normally, when a consensus has been reached, a call to the developer's contract is made with the final **concurrence**. But, for demonstration purposes, we will sometimes feed results to this contract for safe keeping.

```
pragma solidity ^0.4.11;

/*
this is used as an example contract to catch results from the combiner callback 
*/

contract Callback is Addressed {
  function Callback(address _mainAddress) Addressed (_mainAddress) { }

  mapping (bytes32 => bytes32) public results;

  function concurrence(bytes32 requestId,bytes32 result) public {
    Main mainContract = Main(mainAddress);
    Requests requestsContract = Requests(mainContract.getContract('Requests'));
    //make sure only the original combiner can call this
    require(msg.sender == requestsContract.getCombiner(requestId));
    results[requestId] = result;
  }
}

contract Requests {
  function getCombiner(bytes32 _id) public constant returns (address) { }
}

import 'Addressed.sol';

```
Current address ( http://relay.concurrence.io/address/Callback ):
```
0x0BAC8F1cF847F54bf8398e533Aa647a83869d14A
```
Current ABI ( http://relay.concurrence.io/abi/Callback ):
```
[{"constant":true,"inputs":[],"name":"mainAddress","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"","type":"bytes32"}],"name":"results","outputs":[{"name":"","type":"bytes32"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"requestId","type":"bytes32"},{"name":"result","type":"bytes32"}],"name":"concurrence","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"_mainAddress","type":"address"}],"name":"setMainAddress","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"inputs":[{"name":"_mainAddress","type":"address"}],"payable":false,"stateMutability":"nonpayable","type":"constructor"}]
```
