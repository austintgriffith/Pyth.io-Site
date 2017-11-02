---
title: "Combiner Contracts"
date: 2017-09-21T06:00:00-06:00
---
The **Combiner** contracts are the most dynamic and heady of the fleet. Their job is to traverse **responses** *(bytes32)* from miners and come to a **concurrence** *(bytes32)*. This *agreed upon consensus* is then used to **reward()** and **punish()** miners and is delivered to a developer's final **callback** *(address)* contract.

<img src="/images/combiners.png" width="100%"/>


```
pragma solidity ^0.4.11;

contract Combiner is Ownable, Addressed{

  function Combiner(address _mainAddress) Addressed(_mainAddress) { }

  event Debug( string debug );
  event DebugGas( uint gas );
  event DebugPointer( bytes32 _pointer );

  enum Mode {
    INIT,
    COUNTING,
    FEEDBACK,
    CALLBACK,
    DONE
  }

  // ------------------------ concurrence ---------------------------------- //
  mapping (bytes32 => bytes32 ) public concurrence; //agreed upon consensus
  mapping (bytes32 => uint256 ) public weight; //amount staked on concurrence
  mapping (bytes32 => uint256 ) public timestamp; //amount staked on concurrence
  // ------------------------ ----------- ---------------------------------- //

  //req id            //result    //amount of token
  mapping (bytes32 => mapping (bytes32 => uint256)) public staked;
  mapping (bytes32 => mapping (bytes32 => uint32)) public miners;

  //req id   //current pointer
  mapping ( bytes32 => bytes32 ) public current;

  //req id   //current mode
  mapping (bytes32 => Mode ) public mode;

  mapping (bytes32 => uint32 ) public correctMiners;
  mapping (bytes32 => uint256 ) public reward;

  //a combiner is "open" if it is open to new responses
  function open(bytes32 _request) public constant returns (bool) {
    if(mode[_request] != Mode.INIT) return false;
    return isCombinerOpen(_request);
  }

  //a combiner is "ready" if it is ready to combine
  function ready(bytes32 _request) public constant returns (bool) {
    if(mode[_request] == Mode.DONE) return false;
    if(mode[_request] != Mode.INIT) return true;
    return isCombinerReady(_request);
  }

  //the main combine function finds a consensus, rewards miners, and delivers result
  function combine(bytes32 _request) public returns (Mode) {
    if(mode[_request] == Mode.INIT){
      initializeHead(_request);
      mode[_request] = Mode.COUNTING;
    }
    if(mode[_request] == Mode.COUNTING){
      countResponses(_request);
      if( current[_request]==0 ){
        mode[_request] = Mode.FEEDBACK;
        finalizeResponses(_request);
      }
    }
    if(mode[_request] == Mode.FEEDBACK && msg.gas>90000){
      feedbackLoop(_request);
      if( current[_request]==0 ){
        rewardCombinerMiner(_request);

        // TODO TRIGGER THE CALLBACK HERE
        // IT SHOULD SEND THE requestId, concurrence, weight, timestamp  to the callback contract at function "concurrence"

        mode[_request] = Mode.CALLBACK;
      }
    }
    if(mode[_request] == Mode.CALLBACK && msg.gas>90000){
      callback(_request);
      mode[_request] = Mode.DONE;
    }
    if(mode[_request] == Mode.DONE){

      // TODO RESET THE RESPONSES HERE TOO
      // YOU NEED TO GO SET THE HEAD FOR THIS REQUEST ID TO 0x0
      //correctMiners[_request] = 0;
      //reward[_request] = 0;
      //current[_request] = address(0);
      //mode[_request] = Mode.INIT;

      //instead let's just revert for now
      revert();
    }
    DebugPointer(current[_request]);
    return mode[_request];
  }


  //------------Internal functions -------------------------

  function isCombinerOpen(bytes32 _request) internal constant returns (bool) {
    //make sure that the request exists and there are tokens reserved for it
    Main mainContract = Main(mainAddress);
    Token tokenContract = Token(mainContract.getContract('Token'));
    Requests requestsContract = Requests(mainContract.getContract('Requests'));
    address requestCombiner = requestsContract.getCombiner(_request);
    if(requestCombiner==address(0)) return false;
    if(requestCombiner!=address(this)) return false;
    if(tokenContract.reserved(_request)<=0) return false;
    return true;
  }

  function isCombinerReady(bytes32 _request) internal constant returns (bool) {
    //make sure there is at least 1 token staked on at least 1 response to be ready to combine
    Main mainContract = Main(mainAddress);
    Token tokenContract = Token(mainContract.getContract('Token'));
    Responses responsesContract = Responses(mainContract.getContract('Responses'));
    bytes32 tmpCurrent = responsesContract.heads(_request);
    if(tmpCurrent==0) return false;
    uint256 tmpStaked = 0;
    address miner;
    bytes32 result;
    bytes32 next;
    while(tmpCurrent!=0) {
      (miner,result,next) = responsesContract.getResponse(tmpCurrent);
      tmpStaked += tokenContract.staked(miner,_request,tmpCurrent);
      tmpCurrent = next;
    }
    return (tmpStaked>0);
  }

  function initializeHead(bytes32 _request) internal {
    Debug("initializeHead");
    DebugGas(msg.gas);
    Main mainContract = Main(mainAddress);
    Responses responsesContract = Responses(mainContract.getContract('Responses'));
    current[_request] = responsesContract.heads(_request);
    DebugPointer(current[_request]);
  }

  function countResponses(bytes32 _request) internal {
    Debug("countResponses start");
    DebugGas(msg.gas);
    Main mainContract = Main(mainAddress);
    Token tokenContract = Token(mainContract.getContract('Token'));
    Responses responsesContract = Responses(mainContract.getContract('Responses'));
    address miner;
    bytes32 result;
    bytes32 next;
    //we want to drop out if gas is less than a safe amount to iterate again
    while(current[_request]!=0 && msg.gas>80000){
      Debug("countResponses iteration");
      DebugGas(msg.gas);
      (miner,result,next) = responsesContract.getResponse(current[_request]);
      //keep track of total staked amounts for all the different results
      staked[_request][result] += tokenContract.staked(miner,_request,current[_request]);
      miners[_request][result]++;
      //keep track of running best and how much is staked to it
      if(staked[_request][result]>weight[_request]){
        timestamp[_request] = block.timestamp;
        weight[_request] = staked[_request][result];
        concurrence[_request] = result;
        correctMiners[_request] = miners[_request][result];
      }
      current[_request] = next;
    }
    DebugPointer(current[_request]);
  }

  function feedbackLoop(bytes32 _request) internal {
    Debug("feedbackLoop start");
    DebugGas(msg.gas);
    Main mainContract = Main(mainAddress);
    Token tokenContract = Token(mainContract.getContract('Token'));
    Responses responsesContract = Responses(mainContract.getContract('Responses'));
    address miner;
    bytes32 result;
    bytes32 next;
    //we want to drop out if gas is less than a safe amount to iterate again
    while(current[_request]!=0 && msg.gas>100000){
      Debug("feedbackLoop iteration");
      DebugGas(msg.gas);
      (miner,result,next) = responsesContract.getResponse(current[_request]);
      uint256 amountStaked;
      if( concurrence[_request] == result ){
        //they got it right

        //return to them all of tokenContract.staked(miner,current[_request]);
        amountStaked = tokenContract.staked(miner,_request,current[_request]);
        if(amountStaked>0){
          tokenContract.release(_request,current[_request],miner,amountStaked);

          //reward with their split of the bounty
          if( tokenContract.reserved(_request) >= reward[_request] ){
            tokenContract.reward(_request,miner,reward[_request]);
          }
        }

      }
      else
      {
        //they got it wrong
        //take a 10th of what they staked tokenContract.staked(miner,current[_request])
        //I guess this can go back to the owner for now
        //but eventually that should go somewhere better
        amountStaked = tokenContract.staked(miner,_request,current[_request]);
        uint256 punishment = amountStaked/10;
        if(punishment<1) punishment=1;
        amountStaked-=punishment;
        tokenContract.punish(_request,current[_request],miner,punishment,owner);
        tokenContract.release(_request,current[_request],miner,amountStaked);
        //return the remaining amount of tokenContract.staked(miner,current[_request]); to them
      }
      current[_request] = next;
    }
    DebugPointer(current[_request]);
  }

  function finalizeResponses(bytes32 _request) internal {
    Main mainContract = Main(mainAddress);
    Token tokenContract = Token(mainContract.getContract('Token'));
    Responses responsesContract = Responses(mainContract.getContract('Responses'));

    //determine the reward split
    uint32 rewardableMiners = correctMiners[_request];

    //add a little on the top to incentivize miners to run the combine loop
    //this causes a little to be left over and the last miner to run combine
    //  will be rewarded with the same bounty of the actual request/consensus
    rewardableMiners++;

    //set the reward by splitting up the reserved token by how many miners
    //responded to the request... a little game theory will apply here,
    //if a request is too heavily mined it's not worth much
    reward[_request] = tokenContract.reserved(_request)/rewardableMiners;
    if(reward[_request]<1) reward[_request]=1;

    //reset the pointer back to the head so we can iterate through again
    current[_request] = responsesContract.heads(_request);
  }

  function rewardCombinerMiner(bytes32 _request) internal {
    Main mainContract = Main(mainAddress);
    Token tokenContract = Token(mainContract.getContract('Token'));
    uint256 amountLeft = tokenContract.reserved(_request);
    if(amountLeft>0){
      tokenContract.reward(_request,msg.sender,amountLeft);
    }
  }

  function callback(bytes32 _request) internal {
    Main mainContract = Main(mainAddress);
    Token tokenContract = Token(mainContract.getContract('Token'));
    Responses responsesContract = Responses(mainContract.getContract('Responses'));

    //determine the reward split
    uint32 rewardableMiners = correctMiners[_request];

    //add a little on the top to incentivize miners to run the combine loop
    //this causes a little to be left over and the last miner to run combine
    //  will be rewarded with the same bounty of the actual request/consensus
    rewardableMiners++;

    //set the reward by splitting up the reserved token by how many miners
    //responded to the request... a little game theory will apply here,
    //if a request is too heavily mined it's not worth much
    reward[_request] = tokenContract.reserved(_request)/rewardableMiners;
    if(reward[_request]<1) reward[_request]=1;

    //reset the pointer back to the head so we can iterate through again if we have some later mode
    //what will most likely happen is we move to the RESET mode and this is set back to address(0)
    current[_request] = responsesContract.heads(_request);
  }

}


contract Token {
  mapping (bytes32 => uint256) public reserved;
  mapping (address => mapping (bytes32 => mapping (bytes32 => uint256))) public staked;
  function balanceOf(address _owner) public constant returns (uint256 balance) { }
  function reward(bytes32 _request, address _miner, uint256 _value) public returns (bool) { }
  function release(bytes32 _request, bytes32 _response, address _miner, uint256 _value) public returns (bool) { }
  function punish(bytes32 _request, bytes32 _response, address _miner, uint256 _value, address _to) public returns (bool) { }
}
contract Responses{
  mapping (bytes32 => bytes32) public heads;
  function getResponse(bytes32 id) public constant returns (address,bytes32,bytes32) { }
}
contract Requests {
  function getCombiner(bytes32 _id) public constant returns (address) { }
  function getCallback(bytes32 _id) public constant returns (address) { }
}


import 'zeppelin-solidity/contracts/ownership/Ownable.sol';
import 'Addressed.sol';

```
Current address:
```
0x22530bf5e978bb88Bd36b914C19dE655605Bc1B8
```
Current ABI:
```
[{"constant":true,"inputs":[],"name":"mainAddress","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"","type":"bytes32"}],"name":"timestamp","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"","type":"bytes32"}],"name":"current","outputs":[{"name":"","type":"bytes32"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"","type":"bytes32"},{"name":"","type":"bytes32"}],"name":"staked","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"","type":"bytes32"}],"name":"reward","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"owner","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"","type":"bytes32"}],"name":"mode","outputs":[{"name":"","type":"uint8"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_request","type":"bytes32"}],"name":"combine","outputs":[{"name":"","type":"uint8"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"","type":"bytes32"},{"name":"","type":"bytes32"}],"name":"miners","outputs":[{"name":"","type":"uint32"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"_request","type":"bytes32"}],"name":"open","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_mainAddress","type":"address"}],"name":"setMainAddress","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"","type":"bytes32"}],"name":"concurrence","outputs":[{"name":"","type":"bytes32"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"","type":"bytes32"}],"name":"weight","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"","type":"bytes32"}],"name":"correctMiners","outputs":[{"name":"","type":"uint32"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"_request","type":"bytes32"}],"name":"ready","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"view","type":"function"},{"inputs":[{"name":"_mainAddress","type":"address"}],"payable":false,"stateMutability":"nonpayable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":false,"name":"debug","type":"string"}],"name":"Debug","type":"event"},{"anonymous":false,"inputs":[{"indexed":false,"name":"gas","type":"uint256"}],"name":"DebugGas","type":"event"},{"anonymous":false,"inputs":[{"indexed":false,"name":"_pointer","type":"bytes32"}],"name":"DebugPointer","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"previousOwner","type":"address"},{"indexed":true,"name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"}]
```
