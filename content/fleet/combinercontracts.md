---
title: "Combiner Contracts"
date: 2017-09-21T06:00:00-06:00
---
The **Combiner** contracts are the most dynamic. In fact, they can even be written and deployed by a third party. Their job is to capture responses from miners, deliver rewards to good actors, and come to a consensus. External contracts will also then communicate with the respective combiner contracts to retrieve mined, internet data.

```
pragma solidity ^0.4.11;

import 'Addressed.sol';

contract Token {
  mapping (address => mapping (bytes32 => uint256)) public staked;
  function balanceOf(address _owner) public constant returns (uint256 balance) { }
}
contract Responses{
  mapping (bytes32 => bytes32) public heads;
  function getResponse(bytes32 id) public constant returns (address,bytes32,bytes32) {}
}

contract Combiner is Addressed{

  function Combiner(address _mainAddress) Addressed(_mainAddress) { }

  event Debug( address sender, bytes32 request, bytes32 result , uint256 staked );

  enum Mode {
    INIT,
    COUNTING,
    DONE
  }
          //req id            //result    //amount of token
  mapping (bytes32 => mapping (bytes32 => uint256)) public staked;

          //req id   //current pointer
  mapping ( bytes32 => bytes32 ) public current;

          //req id   //current mode
  mapping (bytes32 => Mode ) public mode;

  //req id   //current mode
  mapping (bytes32 => bytes32 ) public bestResult;
  mapping (bytes32 => uint256 ) public mostStaked;


  function combine(bytes32 _request) public constant returns (bytes32) {

    Main mainContract = Main(mainAddress);
    Token tokenContract = Token(mainContract.getContract('Token'));
    Responses responsesContract = Responses(mainContract.getContract('Responses'));

    if(mode[_request] == Mode.INIT){
      current[_request] = responsesContract.heads(_request);
      mode[_request] = Mode.COUNTING;
    }

    if(mode[_request] == Mode.COUNTING){
      //you will need to add gas considerations here
      //if msg.gas is running low you drop out until next run
      while(current[_request]!=0){
        address miner;
        bytes32 result;
        bytes32 next;
        (miner,result,next) = responsesContract.getResponse(current[_request]);

        //keep track of total staked amounts for all the different results
        staked[_request][result] += tokenContract.staked(miner,_request);

        //keep track of running best and how much is staked to it
        if(staked[_request][result]>mostStaked[_request]){
          mostStaked[_request] = staked[_request][result];
          bestResult[_request] = result;
        }

        current[_request] = next;
      }

      if( current[_request]==0 ){
        //fire off an event to debug so far
        Debug( msg.sender , _request, bestResult[_request] , mostStaked[_request] );
        mode[_request] = Mode.DONE;
      }

    }

    if(mode[_request] == Mode.DONE){
      //probably need a third mode for reward/punish and then it goes to the done mode
      //the third mode could probably run out of gas too
    }


  }

}

```
Current address:
```
0x807E92AaE460a477611F0214438D1Fbba92369E3
```
Current ABI:
```
[{"constant":true,"inputs":[],"name":"mainAddress","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"","type":"bytes32"}],"name":"bestResult","outputs":[{"name":"","type":"bytes32"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"","type":"bytes32"}],"name":"current","outputs":[{"name":"","type":"bytes32"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"","type":"bytes32"},{"name":"","type":"bytes32"}],"name":"staked","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"","type":"bytes32"}],"name":"mode","outputs":[{"name":"","type":"uint8"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"_request","type":"bytes32"}],"name":"combine","outputs":[{"name":"","type":"bytes32"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_mainAddress","type":"address"}],"name":"setMainAddress","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"","type":"bytes32"}],"name":"mostStaked","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"inputs":[{"name":"_mainAddress","type":"address"}],"payable":false,"stateMutability":"nonpayable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":false,"name":"sender","type":"address"},{"indexed":false,"name":"request","type":"bytes32"},{"indexed":false,"name":"result","type":"bytes32"},{"indexed":false,"name":"staked","type":"uint256"}],"name":"Debug","type":"event"}]
```
