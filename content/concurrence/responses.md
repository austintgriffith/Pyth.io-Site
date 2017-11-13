---
title: "Responses"
date: 2017-09-21T03:00:00-06:00
---

A collection of **responses** is needed to draw a consensus for any **request**. No single, centralized, source is safe enough to power smart contract logic. When a **request** goes out to the **Concurrence** network, different miners will create a list of different responses on-chain.

<img  src="/images/responsesheader.png" >

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
  ...
  {
    address: '0xf4361802D857cd067C3C491e59e790046EaC5552',
    blockNumber: 2005095,
    transactionHash: '0xd9b15936bf737dd67005d9623541ca88953cd9e7837d763c4f5b516e524acad6',
    returnValues:
     Result {
       sender: '0x4fFD642A057Ce33579a3CA638347b402B909f6D6',
       id: '0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05',
       combiner: '0x22530bf5e978bb88Bd36b914C19dE655605Bc1B8',
       request: '{"url":"http://relay.concurrence.io/email"}',
       protocol: '0x7261770000000000000000000000000000000000000000000000000000000000',
       callback: '0x0BAC8F1cF847F54bf8398e533Aa647a83869d14A',
       count: '1' },
    event: 'AddRequest',
    signature: '0x1581d870a6039ad81aaac454921e49e8966e1519426be461335cdf12bd03e06b',
  },
  ...
]
```

Then the miner should check the status of the combiner for **0xafe8...**:

```bash
node getCombinerStatus.js 0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05

COMBINER OPEN: true
COMBINER READY: false
MODE: 0
CONCURRENCE:
WEIGHT: 0
```

Since the combiner is open, it's ready to accept **responses**, let's make the off-chain request to *http://relay.concurrence.io/email* and get the result:

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
> node addResponse 2 0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05 "austin@concurrence.io"

TX:0xd919bce6aab2207c59484c824613e8dc11405207ffc01e48b12f4f62ab52d4e5
Result {
  sender: '0x926dcc7318Df2D2b543836522f16c5e1f71370A0',
  request: '0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05',
  id: '0xefc4c8dfccb5ba2354c8582113901da50ec33297cf6700480b20c11681352cb1',
  response: '0x61757374696e40636f6e63757272656e63652e696f0000000000000000000000',
  count: '1'
}
```

We could leave our **response** there and be done, but our result won't affect the consensus because we have nothing staked on it. Since we are pretty confident with our result, and we have 1000 **CCCE** laying around, let's stake 200 of it:

```Javascript
//snippet from concurrence.js/examples/stake.js

  concurrence.selectAccount(accountIndex)
  concurrence.stake(requestId,responseId,tokens).then((result)=>{
    console.log(result)
  })

```
```bash
> node stake 2 0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05 0xefc4c8dfccb5ba2354c8582113901da50ec33297cf6700480b20c11681352cb1 200
```

```Javascript
//snippet from concurrence.js/examples/getStaked.js

  concurrence.selectAccount(accountIndex)
  concurrence.staked(concurrence.selectedAddress,requestId,responseId).then((staked)=>{
    console.log("Account "+concurrence.selectedAddress+" has "+staked+" "+concurrence.symbol+" staked on response "+responseId+" to request "+requestId)
  })

```
```bash
> node getStaked 2 0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05 0xefc4c8dfccb5ba2354c8582113901da50ec33297cf6700480b20c11681352cb1

Account 0x926d... has 200 CCCE staked on response 0xefc4... to request 0xafe8...
```

Now if we check in on the **combiner** it is probably ready to rumble:

```bash
> node getCombinerStatus 0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05

COMBINER OPEN: true
COMBINER READY: true
```

It's ready to combine the results, but let's add a few more to make it clear how this is working. Lets have account index 3 make a "bad actor" response with the wrong email address just to mess with the system:

```bash
> node addResponse 3 0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05 "me@austingriffith.com"

TX:0x8fa4583d44f554f34f4632bf2363fbd7cbc167ee11d0b0c5960271d1233c74fa
Result {
  sender: '0x78782428cDbD1cF877afDB0AB709476aefaE4B35',
  request: '0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05',
  id: '0x095b538089f118bcef2773cda753b11379ecc6925cbecddcec54d4f2988133ad',
  response: '0x6d654061757374696e67726966666974682e636f6d0000000000000000000000',
  count: '2'
}
```

Miners will watch their results along with the results of other miners. Let's say our first miner sees the incorrect response from the bad actor and decides to out vote the incorrect result by staking a little more:

```bash
> node stake 2 0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05 0xefc4c8dfccb5ba2354c8582113901da50ec33297cf6700480b20c11681352cb1 100
```

Stake is incremented with multiple calls and we can now see that this account has 300 staked behind their **response**:
```bash
> node getStaked 2 0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05 0xefc4c8dfccb5ba2354c8582113901da50ec33297cf6700480b20c11681352cb1

Account 0x926dcc7318Df2D2b543836522f16c5e1f71370A0 has 300 CCCE staked on response 0xefc4c8dfccb5ba2354c8582113901da50ec33297cf6700480b20c11681352cb1 to request 0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05
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
node listResponses 0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05


{ address: '0x405c02482113a1f4fAE43A1FC3A35598e7F6208a',
  blockNumber: 2005125,
  transactionHash: '0xd919bce6aab2207c59484c824613e8dc11405207ffc01e48b12f4f62ab52d4e5',
  returnValues:
   Result {
     sender: '0x926dcc7318Df2D2b543836522f16c5e1f71370A0',
     request: '0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05',
     id: '0xefc4c8dfccb5ba2354c8582113901da50ec33297cf6700480b20c11681352cb1',
     response: '0x61757374696e40636f6e63757272656e63652e696f0000000000000000000000',
     count: '1'
   },
}
RESPONSE:austin@concurrence.io

{ address: '0x405c02482113a1f4fAE43A1FC3A35598e7F6208a',
  blockNumber: 2005142,
  transactionHash: '0x8fa4583d44f554f34f4632bf2363fbd7cbc167ee11d0b0c5960271d1233c74fa',
   Result {
     sender: '0x78782428cDbD1cF877afDB0AB709476aefaE4B35',
     request: '0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05',
     id: '0x095b538089f118bcef2773cda753b11379ecc6925cbecddcec54d4f2988133ad',
     response: '0x6d654061757374696e67726966666974682e636f6d0000000000000000000000',
     count: '2'
   },
}
RESPONSE:me@austingriffith.com
```

