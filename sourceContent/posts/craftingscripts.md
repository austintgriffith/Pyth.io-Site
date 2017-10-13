---
title: "Crafting Scripts"
date: 2017-09-21T15:00:00-06:00
---
Instead of interacting with the network directly from the command line, we put together a handful of useful scripts to help abstract some of the functionality. There are also some prebuilt (and most likely better) packages out there like <a href="http://truffleframework.com/" target="_blank">Truffle</a>. For now, we'll stick to our simple scripts and see how far they get us. They will also require a couple dependancies:

```bash
npm install solc web3
```

Let's set up a few global variables:
```bash
echo "20" > gasprice.int
echo "300" > ethprice.int
echo "2000000" > deploygas.int
echo "200000" > xfergas.int
```
<!--
lib.js
------------------
*brings in global variables and prepares helper functions and dependancies*
-->



personal.js
------------------
*reports current account balances and unlocks accounts*

<!--RQC CODE javascript personal.js -->


send.js
------------------
*sends ether from one account to another*

<!--RQC CODE javascript send.js -->

compile.js
------------------
*compiles a contract*

<!--RQC CODE javascript compile.js -->

deploy.js
------------------
*deploys a contract*

<!--RQC CODE javascript deploy.js -->

contract.js
------------------
*provides other scripts with an interface to contracts through abstraction*

<!--RQC CODE javascript contract.js -->
