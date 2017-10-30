---
title: "Token Contract"
date: 2017-09-21T08:00:00-06:00
---
The **Concurrence** **Token** **(CCCE)** is an extension of a <a href="https://github.com/OpenZeppelin/zeppelin-solidity/blob/master/contracts/token/StandardToken.sol" target="_blank">StandardToken (ERC20)</a> with a few additions. First, a developer can **reserve()** tokens behind a **request** *(bytes32)* to incentivize miners. Second, a miner can **stake()** tokens on a **response** *(bytes32)* to a given **request** *(bytes32)*. Finally, a **combiner** contract can then **reward()**, **release()**, or **punish()** miners based on the final consensus.

<img src="/images/token.svg" width="100%"/>

```
pragma solidity ^0.4.11;

contract Token is StandardToken, Ownable, HasNoEther, Contactable, Addressed {

  string public constant name = "Concurrence";
  string public constant symbol = "CCCE";
  uint8 public constant decimals = 9;
  uint256 public constant INITIAL_SUPPLY = 10**18;

  function Token(address _mainAddress) Addressed(_mainAddress) {
    totalSupply = INITIAL_SUPPLY;
    balances[msg.sender] = INITIAL_SUPPLY;
  }

  //event debug(address sender, uint256 balance, bytes32 request, uint256 amount);
  event Reserve(address sender, bytes32 request, uint256 value, uint256 total);

  mapping (bytes32 => uint256) public reserved;

  //reservations are one directional; once they go in, the only way
  //to get them out is through mining and consensus
  function reserve(bytes32 _request, uint256 _value) public returns (bool) {
    require(_value <= balances[msg.sender]);
    balances[msg.sender] = balances[msg.sender].sub(_value);
    reserved[_request] = reserved[_request].add(_value);
    Reserve(msg.sender,_request,_value,reserved[_request]);
    return true;
  }

  function reward(bytes32 _request, address _miner, uint256 _value) public returns (bool) {
    Main mainContract = Main(mainAddress);
    Requests requestsContract = Requests(mainContract.getContract('Requests'));
    //the only account that can move the reserved token around
    //is the combiner defined in the request
    require(msg.sender == requestsContract.getCombiner(_request));
    require(_value <= reserved[_request]);
    reserved[_request] = reserved[_request].sub(_value);
    balances[_miner] = balances[_miner].add(_value);
    return true;
  }

  event Stake(address indexed sender, bytes32 indexed request, bytes32 indexed response, uint256 value, uint256 total);
  //event Unstake(address indexed sender, bytes32 indexed response, uint256 value, uint256 total);

  mapping (address => mapping (bytes32 => mapping (bytes32 => uint256))) public staked;

  function stake(bytes32 _request,bytes32 _response, uint256 _value) public returns (bool) {
    require(_value <= balances[msg.sender]);
    balances[msg.sender] = balances[msg.sender].sub(_value);
    staked[msg.sender][_request][_response] = staked[msg.sender][_request][_response].add(_value);
    Stake(msg.sender,_request,_response,_value,staked[msg.sender][_request][_response]);
    return true;
  }

  function release(bytes32 _request, bytes32 _response, address _miner, uint256 _value) public returns (bool) {
    Main mainContract = Main(mainAddress);
    Requests requestsContract = Requests(mainContract.getContract('Requests'));
    //the only account that can move the staked token around
    //is the combiner defined in the request
    require(msg.sender == requestsContract.getCombiner(_request));
    require(_value <= staked[_miner][_request][_response]);
    staked[_miner][_request][_response] = staked[_miner][_request][_response].sub(_value);
    balances[_miner] = balances[_miner].add(_value);
    return true;
  }

  function punish(bytes32 _request, bytes32 _response, address _miner, uint256 _value, address _to) public returns (bool) {
    Main mainContract = Main(mainAddress);
    Requests requestsContract = Requests(mainContract.getContract('Requests'));
    //the only account that can move the staked token around
    //is the combiner defined in the request
    require(msg.sender == requestsContract.getCombiner(_request));
    require(_value <= staked[_miner][_request][_response]);
    staked[_miner][_request][_response] = staked[_miner][_request][_response].sub(_value);
    balances[_to] = balances[_to].add(_value);
    return true;
  }

}

contract Requests {function getCombiner(bytes32 _id) public constant returns (address) {}}

import 'zeppelin-solidity/contracts/ownership/Ownable.sol';
import 'zeppelin-solidity/contracts/ownership/HasNoEther.sol';
import 'zeppelin-solidity/contracts/ownership/Contactable.sol';
import 'zeppelin-solidity/contracts/token/StandardToken.sol';
import 'Addressed.sol';

```
Current address:
```
0xEa4f0053D0e326E521c6B7e039e75a2F9ECD2e55
```
Current ABI:
```
[{"constant":true,"inputs":[],"name":"name","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_spender","type":"address"},{"name":"_value","type":"uint256"}],"name":"approve","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"mainAddress","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_from","type":"address"},{"name":"_to","type":"address"},{"name":"_value","type":"uint256"}],"name":"transferFrom","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"INITIAL_SUPPLY","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"name":"","type":"uint8"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"contactInformation","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_request","type":"bytes32"},{"name":"_miner","type":"address"},{"name":"_value","type":"uint256"}],"name":"reward","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"","type":"address"},{"name":"","type":"bytes32"},{"name":"","type":"bytes32"}],"name":"staked","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_spender","type":"address"},{"name":"_subtractedValue","type":"uint256"}],"name":"decreaseApproval","outputs":[{"name":"success","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"_owner","type":"address"}],"name":"balanceOf","outputs":[{"name":"balance","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_request","type":"bytes32"},{"name":"_response","type":"bytes32"},{"name":"_value","type":"uint256"}],"name":"stake","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"_request","type":"bytes32"},{"name":"_response","type":"bytes32"},{"name":"_miner","type":"address"},{"name":"_value","type":"uint256"}],"name":"release","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"owner","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"","type":"bytes32"}],"name":"reserved","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"symbol","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[],"name":"reclaimEther","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"_to","type":"address"},{"name":"_value","type":"uint256"}],"name":"transfer","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"info","type":"string"}],"name":"setContactInformation","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"_request","type":"bytes32"},{"name":"_response","type":"bytes32"},{"name":"_miner","type":"address"},{"name":"_value","type":"uint256"},{"name":"_to","type":"address"}],"name":"punish","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"_spender","type":"address"},{"name":"_addedValue","type":"uint256"}],"name":"increaseApproval","outputs":[{"name":"success","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"_mainAddress","type":"address"}],"name":"setMainAddress","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"_owner","type":"address"},{"name":"_spender","type":"address"}],"name":"allowance","outputs":[{"name":"remaining","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_request","type":"bytes32"},{"name":"_value","type":"uint256"}],"name":"reserve","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"inputs":[{"name":"_mainAddress","type":"address"}],"payable":false,"stateMutability":"nonpayable","type":"constructor"},{"payable":false,"stateMutability":"nonpayable","type":"fallback"},{"anonymous":false,"inputs":[{"indexed":false,"name":"sender","type":"address"},{"indexed":false,"name":"request","type":"bytes32"},{"indexed":false,"name":"value","type":"uint256"},{"indexed":false,"name":"total","type":"uint256"}],"name":"Reserve","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"sender","type":"address"},{"indexed":true,"name":"request","type":"bytes32"},{"indexed":true,"name":"response","type":"bytes32"},{"indexed":false,"name":"value","type":"uint256"},{"indexed":false,"name":"total","type":"uint256"}],"name":"Stake","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"previousOwner","type":"address"},{"indexed":true,"name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"owner","type":"address"},{"indexed":true,"name":"spender","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Approval","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"from","type":"address"},{"indexed":true,"name":"to","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Transfer","type":"event"}]
```
