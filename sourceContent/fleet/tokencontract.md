---
title: "Token"
date: 2017-09-21T08:00:00-06:00
---
The **Concurrence** token **(CCCE)** is an extension of the <a href="https://github.com/OpenZeppelin/zeppelin-solidity/blob/master/contracts/token/StandardToken.sol" target="_blank">StandardToken (ERC20)</a> with a few additions. First, a developer can **reserve()** tokens behind a **request** *(bytes32)* to incentivize miners. Second, a miner can **stake()** tokens on a **response** *(bytes32)* to a given **request** *(bytes32)*. Finally, a **combiner** contract can then **reward()**, **release()**, or **punish()** miners based on the final consensus.

<img src="/images/token.png" width="100%"/>

<!--RQC CODE solidity Token/Token.sol -->

Current address ( http://relay.concurrence.io/address/Token ):
<!--RQC ADDRESS Token/Token.address -->

Current ABI ( http://relay.concurrence.io/abi/Token ):
<!--RQC ABI Token/Token.abi -->
