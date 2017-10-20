---
title: "Combiner Contracts"
date: 2017-09-21T06:00:00-06:00
---
The **Combiner** contracts are the most dynamic. In fact, they can even be written and deployed by a third party. Their job is to capture responses from miners, deliver rewards to good actors, and come to a consensus. External contracts will also then communicate with the respective combiner contracts to retrieve mined, internet data.

```
pragma solidity ^0.4.11;

contract Combiner{

    event AddResponseAttempt(address _miner,bytes32 _id,string _result);
    event AddResponse(address _miner,bytes32 _id,string _result,address head, address next);

    struct Response{
        address next;
        address miner;
        string result;
    }

    mapping(bytes32 => address ) public head;
    mapping(bytes32 => mapping (address => Response) ) public responses;

    function Combiner(){}

    function addResponse(bytes32 _id,string _result) public returns (bool){
        AddResponseAttempt(msg.sender,_id,_result);
    }

    function responseExists(bytes32 _id,address _miner) public constant returns (bool) {
      assert(_miner != address(0));
      address current = head[_id];
      while( current != address(0) ){
        if(responses[_id][current].miner == _miner)
        current = responses[_id][current].next;
      }
    }

    //could I create a foreach that runs a function on each element of the linked list
    //https://ethereum.stackexchange.com/questions/3342/pass-a-function-as-a-parameter-in-solidity

}

```
Current address:
```
0x748e669c1849DF623c5c278C15c6d901F9C1d5E0
```
Current ABI:
```
[{"constant":true,"inputs":[{"name":"_id","type":"bytes32"},{"name":"_miner","type":"address"}],"name":"responseExists","outputs":[{"name":"","type":"bool"}],"payable":false,"type":"function"},{"constant":true,"inputs":[{"name":"","type":"bytes32"},{"name":"","type":"address"}],"name":"responses","outputs":[{"name":"next","type":"address"},{"name":"miner","type":"address"},{"name":"result","type":"string"}],"payable":false,"type":"function"},{"constant":false,"inputs":[{"name":"_id","type":"bytes32"},{"name":"_result","type":"string"}],"name":"addResponse","outputs":[{"name":"","type":"bool"}],"payable":false,"type":"function"},{"constant":true,"inputs":[{"name":"","type":"bytes32"}],"name":"head","outputs":[{"name":"","type":"address"}],"payable":false,"type":"function"},{"inputs":[],"payable":false,"type":"constructor"},{"anonymous":false,"inputs":[{"indexed":false,"name":"_miner","type":"address"},{"indexed":false,"name":"_id","type":"bytes32"},{"indexed":false,"name":"_result","type":"string"}],"name":"AddResponseAttempt","type":"event"},{"anonymous":false,"inputs":[{"indexed":false,"name":"_miner","type":"address"},{"indexed":false,"name":"_id","type":"bytes32"},{"indexed":false,"name":"_result","type":"string"},{"indexed":false,"name":"head","type":"address"},{"indexed":false,"name":"next","type":"address"}],"name":"AddResponse","type":"event"}]
```
