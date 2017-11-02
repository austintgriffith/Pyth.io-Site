---
title: "Auth Contract"
date: 2017-09-21T10:00:00-06:00
---
**Auth** keeps a **permission** *(bytes32)* for any **account** *(address)*. Other contracts can use this contract to determine the level of **permission** any **account** has by calling **getPermission()**. Any account with *"setContract"* **permission** *(bytes32)* can also call **setPermission()**.

<img src="/images/auth.png" width="100%"/>

<!--RQC CODE solidity Auth/Auth.sol -->

Eventually, the **Auth** contract will be extended to allow for more complex governance including voting and signaling for specific changes to the system. Governance changes could go through the system as a normal **request** but with a custom **protocol** *(bytes32)* with the purpose of allowing miners to vote and stake token on changes.

Current address:
<!--RQC ADDRESS Auth/Auth.address -->

Current ABI:
<!--RQC ABI Auth/Auth.abi -->
