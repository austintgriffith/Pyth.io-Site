---
title: "Auth Contract"
date: 2017-09-21T10:00:00-06:00
---
**Auth** keeps a **permission** *(uint8)* for any **account** *(address)*. Other contracts can use this contract to determine the level of **permission** any **account** has by calling **getPermission(*address*)**. Any account with enough **permission** *(uint8)* can also call **setPermission(*address*,*permission*)**.

<!--RQC CODE solidity Auth/Auth.sol -->

Eventually, the **Auth** contract will be extended to allow for more complex governance including voting and signaling for specific changes to the system.


Current address:
<!--RQC ADDRESS Auth/Auth.address -->

Current ABI:
<!--RQC ABI Auth/Auth.abi -->
