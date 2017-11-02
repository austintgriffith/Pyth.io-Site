---
title: "Responses"
date: 2017-09-21T03:00:00-06:00
---

A collection of responses is needed to draw a consensus for any request. No single, centralized, source is safe enough to power smart contract logic. When a request goes out to the **Concurrence** network, different miners will create a list of different responses on-chain.

Let's assume we are a miner and we'll use account index 2:

```bash
> node getBalance 2

Balance: 1000 CCCE
```

Miners can list all available requests by following blockchain events:

```Javascript
//snippet from concurrence.js/examples/listRequests.js

  concurrence.listRequests().then((requests)=>{
    console.log(requests)
  })

```
```bash
> node listRequests.js

[
  {
    address: '0x9EC59480555d84e9D5bca93F4009c4d8225a9b39',
    ...
    returnValues:
     Result
     {
       sender: '0x4fFD642A057Ce33579a3CA638347b402B909f6D6',
       id: '0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f',
       combiner: '0x22530bf5e978bb88Bd36b914C19dE655605Bc1B8',
       request: '{"url":"http://relay.concurrence.io/email"}',
       protocol: '0x7261770000000000000000000000000000000000000000000000000000000000',
       callback: '0x0BAC8F1cF847F54bf8398e533Aa647a83869d14A',
       count: '0'
     },
    signature: '0x1581d870a6039ad81aaac454921e49e8966e1519426be461335cdf12bd03e06b',
  }
]
```

Then the miner should check the status of the combiner for **0xf14e...**:

```bash
node getCombinerStatus.js 0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f

COMBINER OPEN: true
COMBINER READY: false
MODE: 0
CONCURRENCE:
WEIGHT: 0
```

Since the combiner is open, it's ready to accept responses, let's make the off-chain request to *http://relay.concurrence.io/email* and get the result:

```Javascript
const Request = require('request');

if(!process.argv[2]){
  console.log("Please provide a url")
  process.exit(1)
}
let url = process.argv[2];

Request(url,(error, response, body)=>{
  console.log(error,body)
})

```
```bash
> node makeRawRequest http://relay.concurrence.io/email

null 'austin@concurrence.io'
```

Our miner (using account index 2) received a good result without error so it would be pretty confident this is correct, let's send it to the fleet:

```Javascript
//snippet from concurrence.js/examples/addResponse.js

  concurrence.selectAccount(accountIndex)
  concurrence.addResponse(requestId,response).then((result)=>{
    console.log("TX:"+result.transactionHash)
    console.log(result.events.AddResponse.returnValues)
  })

```
```bash
> node addResponse 2 0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f "austin@concurrence.io"

TX:0xd5efb9c2b6e2bdf74c3dc2b88e14fc7a65109171bc43897a9100d13a211096c8
Result {
  ...
  sender: '0x926dcc7318Df2D2b543836522f16c5e1f71370A0',
  request: '0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f',
  id: '0xcb333c091ca0e9a587d5dc4a140fc324b99c388b279ccf67fa245b79fcdb1c43',
  response: '0x61757374696e40636f6e63757272656e63652e696f0000000000000000000000',
  ...
}
```

We could leave our response there and be done, but our result won't affect the consensus because we have nothing staked on it. Since we are pretty confident with our result, and we have 1000 **CCCE** laying around, let's stake 200 of it:

```Javascript
//snippet from concurrence.js/examples/stake.js

  concurrence.selectAccount(accountIndex)
  concurrence.stake(requestId,responseId,tokens).then((result)=>{
    console.log(result)
  })

```
```bash
> node stake 2 0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f 0xcb333c091ca0e9a587d5dc4a140fc324b99c388b279ccf67fa245b79fcdb1c43 200
```

```Javascript
//snippet from concurrence.js/examples/getStaked.js

  concurrence.selectAccount(accountIndex)
  concurrence.staked(concurrence.selectedAddress,requestId,responseId).then((staked)=>{
    console.log("Account "+concurrence.selectedAddress+" has "+staked+" "+concurrence.symbol+" staked on response "+responseId+" to request "+requestId)
  })

```
```bash
> node getStaked 2 0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f 0xcb333c091ca0e9a587d5dc4a140fc324b99c388b279ccf67fa245b79fcdb1c43

Account 0x926d... has 200 CCCE staked on response 0xcb333... to request 0xf14e...
```

Now if we check in on the combiner it is probably ready to rumble:

```bash
> node getCombinerStatus 0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f

COMBINER OPEN: true
COMBINER READY: true
```

It's ready to combine the results, but let's add a few more to make it clear how this is working. Lets have account index 1 make a "bad actor" response with the wrong email address just to mess with the system:

```bash
> node addResponse 1 0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f "me@austingriffith.com"

0xc00aae8414691b8f7bf4c32ee70e2ba8c7a792b6ba5de75d853c788f18db4bd4
```

And let's say the this account is able to also stake 200 on it:

```bash
> node stake 1 0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f 0xc00aae8414691b8f7bf4c32ee70e2ba8c7a792b6ba5de75d853c788f18db4bd4 200
```

Let's have one more account break the tie:

```bash
> node addResponse 3 0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f "austin@concurrence.io"

0xc0c354cc23d0e7b45f9c05d9865806a4ea8a397ae8cd8742ad6476430b8bdcb4
```

And this account will only stake 50 on it, but that will be enough:

```bash
node stake 3 0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f 0xc0c354cc23d0e7b45f9c05d9865806a4ea8a397ae8cd8742ad6476430b8bdcb4 50
```

We can list all the responses by following the events:

```Javascript
//snippet from concurrence.js/examples/listResponses.js

console.log("Listing responses to request:"+requestId)
  concurrence.listResponses(requestId).then((responses)=>{
    for(let r in responses){
      console.log(responses[r])
      console.log("RESPONSE:"+concurrence.web3.utils.toAscii(responses[r].returnValues['response']))
    }
  })

```
```bash
node listResponses 0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f
Listing responses to request:0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f
{
  address: '0xE9044A96ee3A9fCcb10E32645F5d1Cbf174b9476',
  transactionHash: '0xd5efb9c2b6e2bdf74c3dc2b88e14fc7a65109171bc43897a9100d13a211096c8',
  returnValues:
   Result {
     sender: '0x926dcc7318Df2D2b543836522f16c5e1f71370A0',
     request: '0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f',
     id: '0xcb333c091ca0e9a587d5dc4a140fc324b99c388b279ccf67fa245b79fcdb1c43',
     response: '0x61757374696e40636f6e63757272656e63652e696f0000000000000000000000',
     count: '0'
   },
}
RESPONSE:austin@concurrence.io

{
  address: '0xE9044A96ee3A9fCcb10E32645F5d1Cbf174b9476',
  transactionHash: '0x21801ccd31292e256845add162b8407743077bc567089ea72f12a4355e66130b',
  returnValues:
   Result {
     sender: '0xA3EEBd575245E0bd51aa46B87b1fFc6A1689965a',
     request: '0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f',
     id: '0xc00aae8414691b8f7bf4c32ee70e2ba8c7a792b6ba5de75d853c788f18db4bd4',
     response: '0x6d654061757374696e67726966666974682e636f6d0000000000000000000000',
     count: '1'
   },
 }
RESPONSE:me@austingriffith.com

{
  address: '0xE9044A96ee3A9fCcb10E32645F5d1Cbf174b9476',
  transactionHash: '0xce06f17c07dd674cfab21b8ce331c9b9f530ac001791b6ad9929400f11cd6a76',
  returnValues:
   Result {
     sender: '0x78782428cDbD1cF877afDB0AB709476aefaE4B35',
     request: '0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f',
     id: '0xc0c354cc23d0e7b45f9c05d9865806a4ea8a397ae8cd8742ad6476430b8bdcb4',
     response: '0x61757374696e40636f6e63757272656e63652e696f0000000000000000000000',
     count: '2' },
}
RESPONSE:austin@concurrence.io
```

