---
title: "Requests Contract"
date: 2017-09-21T07:00:00-06:00
---
The **Requests** contract is the main datastore for requests that signal miners. Developers and external contracts call the **addRequest()** function and then **reserve()** tokens behind that request to incentivize miners. The **request** *(string)* can be anything and it's up to the miners to perform different tasks based on the **protocol** *(bytes32)*. **Responses** are then aggregated in the **combiner** *(address)* contract and delivered to the **callback** *(address)* contract.

<img src="/images/requests.svg" width="100%"/>

```
pragma solidity ^0.4.11;

contract Requests is HasNoEther, Addressed {

  function Requests(address _mainAddress) Addressed(_mainAddress) { }

  event AddRequest(address sender, bytes32 id, address combiner, string request, bytes32 protocol, uint256 count);

  uint256 public count = 0;

  struct Request{
    address combiner; //what combiner to use
    string request;   //the actual request, could be json object
    bytes32 protocol; //the type of request can be anything, up to miners to understand
    address callback; //developer contract to __callback to when result is found
    bool active;      //bool used as meta data
  }

  mapping (bytes32 => Request) public requests;

  function addRequest(address _combiner, string _request, bytes32 _protocol, address _callback) public returns (bool) {

    bytes32 id = sha3(now,count,_combiner,_request,_protocol);
    assert(!requests[id].active);//a collision should never happen

    Main mainContract = Main(mainAddress);
    Token token = Token(mainContract.getContract('Token'));

    //you must have some of the token to add a request
    require(token.balanceOf(msg.sender)>0);

    requests[id].combiner=_combiner;
    requests[id].request=_request;
    requests[id].protocol=_protocol;
    requests[id].callback=_callback;
    requests[id].active=true;

    AddRequest(msg.sender,id,requests[id].combiner,requests[id].request,requests[id].protocol,count);

    count=count+1;

    return true;
  }


  function getRequest(bytes32 _id) public constant returns (address,string,bytes32) {
    return (requests[_id].combiner,requests[_id].request,requests[_id].protocol);
  }

  function getCombiner(bytes32 _id) public constant returns (address) {
    return requests[_id].combiner;
  }
}

contract Token { function balanceOf(address _owner) public constant returns (uint256 balance) { } }

import 'zeppelin-solidity/contracts/ownership/HasNoEther.sol';
import 'Addressed.sol';

```
Current address:
```
0x983222EaBA86f0138D9CfCcfa3E87ef6E577494F
```
Current ABI:
```
[{"constant":true,"inputs":[],"name":"count","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"mainAddress","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_combiner","type":"address"},{"name":"_request","type":"string"},{"name":"_protocol","type":"bytes32"},{"name":"_callback","type":"address"}],"name":"addRequest","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"_id","type":"bytes32"}],"name":"getCombiner","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"owner","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"","type":"bytes32"}],"name":"requests","outputs":[{"name":"combiner","type":"address"},{"name":"request","type":"string"},{"name":"protocol","type":"bytes32"},{"name":"callback","type":"address"},{"name":"active","type":"bool"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[],"name":"reclaimEther","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"_mainAddress","type":"address"}],"name":"setMainAddress","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"_id","type":"bytes32"}],"name":"getRequest","outputs":[{"name":"","type":"address"},{"name":"","type":"string"},{"name":"","type":"bytes32"}],"payable":false,"stateMutability":"view","type":"function"},{"inputs":[{"name":"_mainAddress","type":"address"}],"payable":false,"stateMutability":"nonpayable","type":"constructor"},{"payable":false,"stateMutability":"nonpayable","type":"fallback"},{"anonymous":false,"inputs":[{"indexed":false,"name":"sender","type":"address"},{"indexed":false,"name":"id","type":"bytes32"},{"indexed":false,"name":"combiner","type":"address"},{"indexed":false,"name":"request","type":"string"},{"indexed":false,"name":"protocol","type":"bytes32"},{"indexed":false,"name":"count","type":"uint256"}],"name":"AddRequest","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"previousOwner","type":"address"},{"indexed":true,"name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"}]
```
