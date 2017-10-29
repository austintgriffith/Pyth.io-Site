---
title: "Example"
date: 2017-09-21T22:00:00-06:00
---

In the not-too-distant future, farmers all around the world could pay ether into a smart contract that would provide agricultural insurance against hail or drought. Then, throughout the year, as miners detect these specific weather conditions using multiple APIs and other internet sources, the contract would deterministically pay ether back to the farmers in need. This incredibly efficient system completely sidesteps an entire field of lawyers, insurance agents, and adjusters, immediately helping the farmers in need without any unnecessary overhead.

<i>This concept was introduced in the <a href="https://github.com/ethereum/wiki/wiki/White-Paper" target="_blank">Ethereum white paper</a>, but unfortunately is not possible in Ethereum yet. A **Concurrence** system is needed to draw on-chain consensus from off-chain data.</i>

-------------------------------------------------------

Let's dive into an oversimplified (and insecure) example contract just to understand the mechanics of how such a system would work.

First, we'll need a way to signal miners that a consensus is needed for a particular data point:
```
mapping (bytes32 => string) public requests;

function addRequest(bytes32 _id, string _url) returns (bool){
    requests[_id]=_url;
    AddRequest(msg.sender,_id,requests[_id]);
}
event AddRequest(address _sender,bytes32 _id, string _url);
```

With the **addRequest()** function we can store a request and trigger an event called **AddRequest** on the blockchain.

Miners, incentivized by a reserved token, then make requests to a number of internet endpoints, collect relevant data, and send it back to the contract.
```javascript
contract.getPastEvents('AddRequest', {
    fromBlock: params.blockNumber,
    toBlock: 'latest'
}, function(error, events){
  for(let e in events){
    request(events[e].returnValues._url, function (error, response, body) {
       contract.methods.addResponse(events[e].returnValues._id,body).send({
         from: params.account,
         gas: params.gas,
         gasPrice:params.gasPrice
       })
    })
  }
})
```

The **addResponse()** method is used to store a list responses from different miners.
```
mapping(bytes32 => string ) public responses;

function addResponse(bytes32 _id,string _result) returns (bool){
    responses[_id]=_result;
    AddResponse(msg.sender,_id,responses[_id]);
}
event AddResponse(address _sender,bytes32 _id,string _result);
```

Finally, a **Combiner** iterates through the **responses** and finds a consensus.

```
(miner,result,next) = responsesContract.getResponse(_request);
staked[_request][result] += tokenContract.staked(miner,_request,_request);

if( staked[_request][result] > weight[_request] ){
  weight[_request] = staked[_request][result];
  concurrence[_request] = result;
  correctMiners[_request] = miners[_request][result];
}

current[_request] = next;
```


This simplified example is the heart of a decentralized oracle network. We will build a more robust system around this idea in the following posts.

