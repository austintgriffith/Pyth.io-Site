---
title: "Responses Contract"
date: 2017-09-21T06:55:00-06:00
---
The **Responses** contract is the datastore for **responses** *(bytes32)*. As miners perform their duties, they call **addResponse()** to deliver their results to the fleet. This list of **responses** *(bytes32)* is tracked in a [linked list](/exploration/linkedlists/) that allows the **combiner** contracts to quickly traverse through results. Notice that whatever complex work the miner does off-chain, it must result in a single *bytes32* response on-chain.

<img src="/images/responses.png" width="100%"/>


<!--RQC CODE solidity Responses/Responses.sol -->

Current address ( http://relay.concurrence.io/address/Responses ):
<!--RQC ADDRESS Responses/Responses.address -->

Current ABI ( http://relay.concurrence.io/abi/Responses ):
<!--RQC ABI Responses/Responses.abi -->
