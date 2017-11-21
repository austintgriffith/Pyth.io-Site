---
title: "Addressed"
date: 2017-09-21T04:00:00-06:00
---
All contracts except **Auth** and **Main** inherit from the **Addressed** contract so they can call **setMainAddress()** to keep track of the **Main** contract.

```
pragma solidity ^0.4.11;

contract Addressed {

  address public mainAddress;

  function Addressed(address _mainAddress) {
    mainAddress=_mainAddress;
  }

  function setMainAddress(address _mainAddress){
    Main main = Main(mainAddress);
    Auth auth = Auth(main.getContract('Auth'));
    if( auth.getPermission(msg.sender,'setMainAddress') ){
      mainAddress=_mainAddress;
    }
  }

}

contract Auth { function getPermission( address _account , bytes32 _permission) constant public returns (bool) { } }
contract Main { function getContract(bytes32 _name) constant returns (address) {} }

```
