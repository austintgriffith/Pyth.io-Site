---
title: "Deploying A Contract"
date: 2017-09-21T14:00:00-06:00
draft: true
---

Never before in history has a technology existed for anyone from anywhere to deploy code that will immediately and indefinitely run on hundreds of thousands of nodes simultaneously and deterministically. Further, thanks to cryptography and cryptoeconomics, this technology is ownerless, trustless, and incentivized to continue. Once a contract is deployed, it is effectively autonomous, eternal, and controlled only by the laws of machines.

Let's make our mark on the blockchain right now with a simple contract:

<!--RQC CODE solidity Simple/Simple.sol -->

This **Simple** contract has a count (**uint8**) that is initialized in the constructor and can be incremented from an **add()** function.

We will first compile this using our **compile.js** script:



Current address:
<!--RQC ADDRESS Simple/Simple.address -->

Current ABI:
<!--RQC ABI Simple/Simple.abi -->
