---
title: "Predecessor"
date: 2017-09-21T05:00:00-06:00
---
The **Main** contract inherits from the **Predecessor** contract the ability to **setDescendant()**. This creates a chain of deployed contracts that leads from the very first *address* to the most current *address*. This is helpful when replacing a contract that has an address that might be in use by other contracts. When those other contracts call the **getContract()** function on the original address, it will forward to the most recent version of the **Main** contract and return the correct response. [Read more](/exploration/contractmigration) about this concept in the exploration section. 

<!--RQC CODE solidity Predecessor/Predecessor.sol -->
