---
title: "Requests Contract"
date: 2017-09-21T07:00:00-06:00
---
The **Requests** contract is the main datastore for requests.

```
pragma solidity ^0.4.11;

import 'zeppelin-solidity/contracts/ownership/HasNoEther.sol';
import 'Addressed.sol';

//contract Token { function balanceOf(address _owner) public constant returns (uint256 balance) { } }

contract Requests is HasNoEther, Addressed {

  function Requests(address _mainAddress) Addressed(_mainAddress) { }

  event AddRequest(address sender, bytes32 id, address combiner, string request, string parser, uint256 count);

  uint256 public count = 0;

  struct Request{
    address combiner;
    string request;
    string parser;
    bool active;
  }

  mapping (bytes32 => Request) public requests;

  function addRequest(address _combiner, string _request, string _parser) public returns (bytes32) {

    bytes32 id = sha3(now,count,_combiner,_request,_parser);
    assert(!requests[id].active);//a collision should never happen

    //Main mainContract = Main(mainAddress);
    //Token token = Token(mainContract.getContract('Token'));

    //you must have some of the token to add a request
    //require(token.balanceOf(msg.sender)>0);

    requests[id].combiner=_combiner;
    requests[id].request=_request;
    requests[id].parser=_parser;
    requests[id].active=true;

    AddRequest(msg.sender,id,requests[id].combiner,requests[id].request,requests[id].parser,count);

    count=count+1;

    return id;
  }


  function getRequest(bytes32 _id) public constant returns (address,string,string) {
    return (requests[_id].combiner,requests[_id].request,requests[_id].parser);
  }
}

```
Current address:
```
0x6C196CDA558E10C9DF45C1b91F2AB5E8F6853FA9
```
Current ABI:
```
[{"constant":true,"inputs":[],"name":"count","outputs":[{"name":"","type":"uint256"}],"payable":false,"type":"function"},{"constant":true,"inputs":[],"name":"mainAddress","outputs":[{"name":"","type":"address"}],"payable":false,"type":"function"},{"constant":true,"inputs":[],"name":"owner","outputs":[{"name":"","type":"address"}],"payable":false,"type":"function"},{"constant":true,"inputs":[{"name":"","type":"bytes32"}],"name":"requests","outputs":[{"name":"combiner","type":"address"},{"name":"request","type":"string"},{"name":"parser","type":"string"},{"name":"active","type":"bool"}],"payable":false,"type":"function"},{"constant":false,"inputs":[],"name":"reclaimEther","outputs":[],"payable":false,"type":"function"},{"constant":false,"inputs":[{"name":"_mainAddress","type":"address"}],"name":"setMainAddress","outputs":[],"payable":false,"type":"function"},{"constant":false,"inputs":[{"name":"_combiner","type":"address"},{"name":"_request","type":"string"},{"name":"_parser","type":"string"}],"name":"addRequest","outputs":[{"name":"","type":"bytes32"}],"payable":false,"type":"function"},{"constant":false,"inputs":[{"name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"payable":false,"type":"function"},{"constant":true,"inputs":[{"name":"_id","type":"bytes32"}],"name":"getRequest","outputs":[{"name":"","type":"address"},{"name":"","type":"string"},{"name":"","type":"string"}],"payable":false,"type":"function"},{"inputs":[{"name":"_mainAddress","type":"address"}],"payable":false,"type":"constructor"},{"payable":false,"type":"fallback"},{"anonymous":false,"inputs":[{"indexed":false,"name":"sender","type":"address"},{"indexed":false,"name":"id","type":"bytes32"},{"indexed":false,"name":"combiner","type":"address"},{"indexed":false,"name":"request","type":"string"},{"indexed":false,"name":"parser","type":"string"},{"indexed":false,"name":"count","type":"uint256"}],"name":"AddRequest","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"previousOwner","type":"address"},{"indexed":true,"name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"}]
```
