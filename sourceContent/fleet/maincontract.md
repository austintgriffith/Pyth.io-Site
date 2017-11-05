---
title: "Main"
date: 2017-09-21T09:00:00-06:00
---
**Main** keeps a **contract** *(address)* for any **name** *(bytes32)* and is deployed with the **Auth** *(address)* initialized. This allows for old contracts to be replaced with better versions while keeping the main contract address the same. **Main** also implements the **Predecessor** concept where a **descendant** is set when a new version of **Main** is deployed. Then, if a developer contract attempts to interface with an old version of the **Main** contract, the current **descendant** **Main** receives requests by proxy.  

<img src="/images/main.png" width="100%"/>

<!--RQC CODE solidity Main/Main.sol -->

Current address ( http://relay.concurrence.io/address/Main ):
<!--RQC ADDRESS Main/Main.address -->

Current ABI ( http://relay.concurrence.io/abi/Main ):
<!--RQC ABI Main/Main.abi -->
