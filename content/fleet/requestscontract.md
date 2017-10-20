---
title: "Requests Contract"
date: 2017-09-21T07:00:00-06:00
---
The **Requests** contract is the main datastore for requests.

```
pragma solidity ^0.4.0;

import 'zeppelin-solidity/contracts/ownership/HasNoEther.sol';
import 'zeppelin-solidity/contracts/ownership/Contactable.sol';
import 'Addressed.sol';

contract Requests is HasNoEther, Contactable, Addressed {

  event ErrorString(string _str);
  event AddRequest(address _sender, bytes32 _id, address _combiner, string _request, bytes32 _flavor);
  event AttemptAddRequest(address _sender, bytes32 _id, address _combiner, string _url, bytes32 _flavor);

  struct Request{
    address combiner;
    string request;
    bytes32 flavor;
  }

  mapping (bytes32 => Request) public requests;

  function addRequest(bytes32 _id, address _combiner, string _request, bytes32 _flavor) public returns (bool) {
    AttemptAddRequest(msg.sender,_id,_combiner,_request,_flavor);
    if(requests[_id].combiner != address(0) || _combiner==address(0)){
      ErrorString("Request already exists");
      return false;
    }
    Main main = Main(mainAddress);
    Auth auth = Auth(main.getContract('Auth'));
    if( auth.permission(msg.sender,"addRequest") ){
      requests[_id].combiner=_combiner;
      requests[_id].request=_request;
      requests[_id].flavor=_flavor;
      AddRequest(msg.sender,_id,requests[_id].combiner,requests[_id].request,requests[_id].flavor);
      return true;
    }else{
      ErrorString("Failed to get permission");
      return false;
    }
  }

}

```
Current address:
```
0x1339f5d3b0FAcfc9262C3454D6253dB6608567B4

```
Current ABI:
```
[{"constant":true,"inputs":[],"name":"mainAddress","outputs":[{"name":"","type":"address"}],"payable":false,"type":"function"},{"constant":true,"inputs":[],"name":"contactInformation","outputs":[{"name":"","type":"string"}],"payable":false,"type":"function"},{"constant":true,"inputs":[],"name":"owner","outputs":[{"name":"","type":"address"}],"payable":false,"type":"function"},{"constant":true,"inputs":[{"name":"","type":"bytes32"}],"name":"requests","outputs":[{"name":"combiner","type":"address"},{"name":"request","type":"string"},{"name":"flavor","type":"bytes32"}],"payable":false,"type":"function"},{"constant":false,"inputs":[],"name":"reclaimEther","outputs":[],"payable":false,"type":"function"},{"constant":false,"inputs":[{"name":"info","type":"string"}],"name":"setContactInformation","outputs":[],"payable":false,"type":"function"},{"constant":false,"inputs":[{"name":"_id","type":"bytes32"},{"name":"_combiner","type":"address"},{"name":"_request","type":"string"},{"name":"_flavor","type":"bytes32"}],"name":"addRequest","outputs":[{"name":"","type":"bool"}],"payable":false,"type":"function"},{"constant":false,"inputs":[{"name":"_mainAddress","type":"address"}],"name":"setMainAddress","outputs":[],"payable":false,"type":"function"},{"constant":false,"inputs":[{"name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"payable":false,"type":"function"},{"payable":false,"type":"fallback"},{"anonymous":false,"inputs":[{"indexed":false,"name":"_str","type":"string"}],"name":"ErrorString","type":"event"},{"anonymous":false,"inputs":[{"indexed":false,"name":"_sender","type":"address"},{"indexed":false,"name":"_id","type":"bytes32"},{"indexed":false,"name":"_combiner","type":"address"},{"indexed":false,"name":"_request","type":"string"},{"indexed":false,"name":"_flavor","type":"bytes32"}],"name":"AddRequest","type":"event"},{"anonymous":false,"inputs":[{"indexed":false,"name":"_sender","type":"address"},{"indexed":false,"name":"_id","type":"bytes32"},{"indexed":false,"name":"_combiner","type":"address"},{"indexed":false,"name":"_url","type":"string"},{"indexed":false,"name":"_flavor","type":"bytes32"}],"name":"AttemptAddRequest","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"previousOwner","type":"address"},{"indexed":true,"name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"}]
```
