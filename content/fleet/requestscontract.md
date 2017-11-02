---
title: "Requests Contract"
date: 2017-09-21T07:00:00-06:00
---
The **Requests** contract is the main datastore for requests that signal miners. Developers and external contracts call the **addRequest()** function and then **reserve()** tokens behind that request to incentivize miners. The **request** *(string)* can be anything and it's up to the miners to perform different tasks based on the **protocol** *(bytes32)*. **Responses** are then aggregated in the **combiner** *(address)* contract and delivered to the **callback** *(address)* contract.

<img src="/images/requests.png" width="100%"/>

```
pragma solidity ^0.4.11;

contract Requests is HasNoEther, Addressed {

  function Requests(address _mainAddress) Addressed(_mainAddress) { }

  event AddRequest(address sender, bytes32 id, address combiner, string request, bytes32 protocol, address callback, uint256 count);

  uint256 public count = 0;

  struct Request{
    address combiner; //what combiner to use
    string request;   //the actual request, could be json object ----- in the future we may only store a hash of this on-chain
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

    AddRequest(msg.sender,id,requests[id].combiner,requests[id].request,requests[id].protocol,requests[id].callback,count);

    count=count+1;

    return true;
  }

  function getRequest(bytes32 _id) public constant returns (address,string,bytes32,address) {
    return (requests[_id].combiner,requests[_id].request,requests[_id].protocol,requests[_id].callback);
  }

  function getCombiner(bytes32 _id) public constant returns (address) {
    return requests[_id].combiner;
  }

  function getCallback(bytes32 _id) public constant returns (address) {
    return requests[_id].callback;
  }

}

contract Token { function balanceOf(address _owner) public constant returns (uint256 balance) { } }

import 'zeppelin-solidity/contracts/ownership/HasNoEther.sol';
import 'Addressed.sol';

```
Current address:
```

```
Current ABI:
```

```
