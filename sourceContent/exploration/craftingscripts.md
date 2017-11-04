---
title: "Crafting Scripts"
date: 2017-09-21T15:00:00-06:00
---
Instead of interacting with the network directly from the command line, we put together a handful of useful scripts to help abstract some of the functionality. There are also some prebuilt packages out there like <a href="http://truffleframework.com/" target="_blank">Truffle</a> and <a href="https://openzeppelin.org/" target="_blank">OpenZeppelin</a>.

---------------------------------------------------


First, let's get our environment prepared:

```bash
npm install solc web3
```

Let's initialize a few global variables:
```bash
echo "20" > gasprice.int
echo "300" > ethprice.int
echo "2000000" > deploygas.int
echo "200000" > xfergas.int
```

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

personal.js
------------------
*reports current account balances and unlocks accounts*

<!--RQC CODE javascript personal.js -->
