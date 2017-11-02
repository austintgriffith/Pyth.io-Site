---
title: "Concurrence.js"
date: 2017-09-02T17:00:00-06:00
---
**Concurrence.js** is an open source Javascript library used to interact with the fleet of **Concurrence** smart contracts. For now, **Concurrence.js** requires that you already have <a href="https://github.com/ethereum/go-ethereum/wiki/geth" target="_blank">Geth</a> configured and running at a specific IPC or RPC location (http://localhost:8545 is the default). For more information on getting attached to <a href="https://github.com/ethereum/go-ethereum/wiki/geth" target="_blank">Geth</a>, read through the [provisioning](http://localhost:1313/exploration/provisioning/) section.

-------------------------------------------------------

### install

```bash
npm install concurrence
```

-------------------------------------------------------


### example


```Javascript
let concurrence = require("concurrence")
concurrence.init({DEBUG:true},(err)=>{
  console.log(concurrence.version)
});
```

```
0.0.1
```
-------------------------------------------------------

### add a request  

<!--RQC CODE Javascript concurrence.js/examples/exAddRequest.js -->


-------------------------------------------------------

### simple miner

<!--RQC CODE Javascript concurrence.js/examples/exRawRequestMiner.js -->
