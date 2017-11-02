---
title: "Main Contract"
date: 2017-09-21T09:00:00-06:00
---
**Main** keeps a **contract** *(address)* for any **name** *(bytes32)* and is deployed with the **Auth** *(address)* initialized. This allows for old contracts to be replaced with better versions while keeping the main contract address the same. **Main** also implements the **Predecessor** concept where a **descendant** is set when a new version of **Main** is deployed. Then, if a developer contract attempts to interface with an old version of the **Main** contract, the current **descendant** **Main** receives requests by proxy.  

<img src="/images/main.png" width="100%"/>

```
pragma solidity ^0.4.11;

contract Main is HasNoEther, Contactable, Predecessor {

    event SetContract(bytes32 name,address contractAddress,address whoDid);

    mapping(bytes32 => address) contracts;

    function Main(address _authContract) {
      contracts['Auth']=_authContract;
    }

    function setContract(bytes32 _name,address _contract) public returns (bool) {
      Auth authContract = Auth(contracts['Auth']);
      require( authContract.getPermission(msg.sender,'setContract') );
      contracts[_name]=_contract;
      SetContract(_name,_contract,msg.sender);
      return true;
    }

    function getContract(bytes32 _name) public constant returns (address) {
      if(descendant!=address(0)) {
        return Main(descendant).getContract(_name);
      }else{
        return contracts[_name];
      }
    }

}

contract Auth { function getPermission( address _account , bytes32 _permission) constant public returns (bool) { } }

import 'zeppelin-solidity/contracts/ownership/HasNoEther.sol';
import 'zeppelin-solidity/contracts/ownership/Contactable.sol';
import 'Predecessor.sol';

```
Current address:
```

```
Current ABI:
```

```
