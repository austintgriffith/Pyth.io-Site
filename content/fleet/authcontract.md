---
title: "Auth Contract"
date: 2017-09-21T10:00:00-06:00
---
**Auth** keeps a **permission** *(uint8)* for any **account** *(address)*. Other contracts can use this contract to determine the level of **permission** any **account** has by calling **getPermission(*address*)**. Any account with enough **permission** *(uint8)* can also call **setPermission(*address*,*permission*)**.

```
pragma solidity ^0.4.11;

import 'zeppelin-solidity/contracts/ownership/Ownable.sol';
import 'zeppelin-solidity/contracts/ownership/HasNoEther.sol';

contract Auth is Ownable, HasNoEther  {

    event SetPermission( address _sender, address _address, bytes32 _permission, bool _value );

    mapping ( address => mapping ( bytes32 => bool ) ) public permission;

    function Auth() {
        permission[owner]['setPermission'] = true;
        permission[owner]['setContract'] = true; //Main.sol
        permission[owner]['setMainAddress'] = true; //Token.sol and Requests.sol
        permission[owner]['addRequest'] = true; //Requests.sol
    }

    function setPermission( address _address , bytes32 _permission, bool _value) public returns (bool) {
        require( permission[msg.sender]['setPermission'] );
        require( _address!=owner || _permission!='setPermission');
        permission[_address][_permission] = _value;
        SetPermission(msg.sender,_address,_permission,_value);
        return true;
    }

}

```
Eventually, the **Auth** contract will be extended to allow for more complex governance including voting and signaling for specific changes to the system.


Current address:
```

```
Current ABI:
```

```
