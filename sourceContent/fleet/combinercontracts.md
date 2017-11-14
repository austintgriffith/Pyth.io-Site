---
title: "Combiner"
date: 2017-09-21T06:00:00-06:00
---
The **Combiner** contracts are the most dynamic and heady of the fleet. Their job is to traverse **responses** *(bytes32)* from miners and come to a **concurrence** *(bytes32)*. This *agreed upon consensus* is then used to **reward()** and **punish()** miners and is delivered to a developer's final **callback** *(address)* contract.

<img src="/images/combiners.png"/>

------------------------------------------------------

### Basic Combiner

The basic combiner waits until at least 1 **(CCCE)** is reserved behind a **request** and then it is *open* to **responses**. There is no limit to **responses** and a **concurrence** can be formed after a single result.

This combiner is for demonstration purposes only and shouldn't be used in production because a single miner can create the **concurrence**.

<!--RQC CODE solidity Combiner/basic/Combiner.sol -->

**Basic** Current address ( http://relay.concurrence.io/combiner/address/basic ):
<!--RQC ADDRESS Combiner/basic/Combiner.address -->

**Basic** Current ABI ( http://relay.concurrence.io/combiner/abi/basic ):
<!--RQC ABI Combiner/basic/Combiner.abi -->

------------------------------------------------------
