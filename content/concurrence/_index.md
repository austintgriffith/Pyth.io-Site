---
title: "Concurrence.js"
date: 2017-09-02T17:00:00-06:00
---
**Concurrence.js** is an open source Javascript library used to interact with the fleet of **Concurrence** smart contracts. For now, **Concurrence.js** requires that you already have geth configured and running at a specific IPC or RPC location (http://localhost:8545 is the default). For more information on getting attached to Geth, read through the [provisioning](http://localhost:1313/exploration/provisioning/) section.

```bash
npm install concurrence
```

```Javascript
let concurrence = require("concurrence")
concurrence.init({DEBUG:true},(err)=>{
  console.log(concurrence.version)
});
```

```
0.0.1
```

---------------------------------------------

### End-to-End Concurrence.js Example


Let's have miners come to a **concurrence** on the current timestamp in UTC. As one of many data sources, we could use <a href='http://relay.concurrence.io/time' target='_blank'>http://relay.concurrence.io/time</a>. You can get the timestamp of each block from within a contract, but let's say we want to be more accurate. We will run through the **Concurrence.js** functionality using the small scripts in the **examples/** directory, but normally this code would be integrated directly into developers' code bases.

First, we must register this new request using **addRequest.js**:

```Javascript
/*
  Add a request to signal miners

  usage:
  node addRequest
*/

const fs = require("fs")

let request = { url: "http://relay.concurrence.io/time" }
let protocol = "raw"
let combiner = fs.readFileSync("../../Combiner/basic/Combiner.address").toString().trim()
let callback = fs.readFileSync("../../Callback/Callback.address").toString().trim()

let concurrence = require("concurrence")
concurrence.init({},(err)=>{
  concurrence.selectAccount(2)
  concurrence.addRequest(combiner,request,protocol,callback).then((addResult)=>{
    console.log("TX:"+addResult.transactionHash)
    console.log(addResult.events.AddRequest.returnValues)
  })
});

```
```bash
node addRequest

TX:0x3fab0c9f2748b6fed884ed7f9f1803b63885d6987817d1e94106e86c621769a0
{
  sender: '0x26c7609fDc2607806715b3511055Fa36D3fd30F4',
  id: '0x92b52bf7d21dce26c67cfab721d385028dd4f97edf9bf56a13181fbc2abfce82',
  combiner: '0x10c5eE2F7A67faFBb5dDd36aaf3D8bdd93591097',
  request: '{"url":"http://relay.concurrence.io/time"}',
  protocol: '0x7261770000000000000000000000000000000000000000000000000000000000',
  callback: '0xa2461fb7aCAC31D22e41F9A7cB05655f51EDcc30',
  count: '0'
}
```

The request is now entered onto the blockchain as: **0x92b52bf7d21dce26c67cfab721d385028dd4f97edf9bf56a13181fbc2abfce82**

Requests without any tokens reserved won't be mined. In order to incentivize miners, we will need to reserve some tokens for this with request using **reserveTokens.js**

```Javascript
/*
  Reserve tokens behind a request to incentivize miners

  usage:
  nnode reserveTokens ##REQUESTID## ##AMOUNT##

  example:
  node reserveTokens 0x0b54d5da066a0c753ddd0510d445e131cb21f9373f4c3609100419ae4d95908d 100
*/

const fs = require("fs")

if(!process.argv[2]){
  console.log("Please provide a request id.")
  process.exit(1)
}
let requestId = process.argv[2];

if(!process.argv[3]){
  console.log("Please provide a number of tokens to reserve.")
  process.exit(1)
}
let tokens = process.argv[3];

let concurrence = require("concurrence")
concurrence.init({},(err)=>{
  concurrence.selectAccount(2)
  concurrence.reserve(requestId,tokens).then((reserveResult)=>{
    console.log(reserveResult)
  })
});

```
```bash
node reserveTokens.js 0x92b52bf7d21dce26c67cfab721d385028dd4f97edf9bf56a13181fbc2abfce82 1000

{ transactionHash: '0xce5ecb65fd67b8fc61d9564903d2fe1a63c7c19b6da5a6179596fc7d283cf0dd' }
```




An event is triggered every time a new request is added so miners can list requests with **listRequests.js**

```Javascript
/*
  List all the requests by events triggered

  usage:
  node listRequests
*/

let concurrence = require("concurrence")
concurrence.init({},(err)=>{
  concurrence.listRequests().then((requests)=>{
    console.log(requests)
  })
});

```
```bash
node listRequests.js

[
  {
    returnValues:
     Result {
       sender: '0x26c7609fDc2607806715b3511055Fa36D3fd30F4',
       id: '0x92b52bf7d21dce26c67cfab721d385028dd4f97edf9bf56a13181fbc2abfce82',
       combiner: '0x10c5eE2F7A67faFBb5dDd36aaf3D8bdd93591097',
       request: '{"url":"http://relay.concurrence.io/time"}',
       protocol: '0x7261770000000000000000000000000000000000000000000000000000000000',
       callback: '0xa2461fb7aCAC31D22e41F9A7cB05655f51EDcc30',
       count: '0' },
  }
]
```

Once they have request ids, they can start checking to see if any have tokens reserved with **getReserved.js**:

```Javascript
/*
  Get a request's details

  usage:
  node getReserved ##REQUESTID##

  example:
  node getReserved 0x147b833db65b9d9a4321ba3fa0f476265ebb178531de166477822204268f6d88
*/

if(!process.argv[2]){
  console.log("Please provide a request id.")
  process.exit(1)
}
let requestId = process.argv[2];

let concurrence = require("concurrence")
concurrence.init({},(err)=>{
  concurrence.reserved(requestId).then((reserved)=>{
    console.log("Request "+requestId+" has "+reserved+" "+concurrence.symbol+" reserved")
  })
});

```
```bash
node getReserved.js 0x92b52bf7d21dce26c67cfab721d385028dd4f97edf9bf56a13181fbc2abfce82

Request 0x92b52bf7d21dce26c67cfab721d385028dd4f97edf9bf56a13181fbc2abfce82 has 1000 CCCE reserved
```

