---
title: "Requests"
date: 2017-09-21T04:00:00-06:00
---

**Concurrence** starts with a developer requesting certain information be mined into the blockchain. The **Concurrence** fleet is meant to be very generic so *any* request can go out with *any* protocol and miners can choose to fulfill the requests or not. For instance, you could make up a protocol called **sportsScores** and your request could be for a certain game or week. Conversely, you could come up with a protocol called **plainEnglish** and your request could be a question like, "Will it rain in Nebraska today". The power of this system is simplicity on the blockchain and complexity in the miners.

Adding a request to the blockchain is as easy as:

<!--RQC CODE Javascript concurrence.js/examples/addRequest.js -->

In this example we will ask that miners draw a consensus around the <a href="http://relay.concurrence.io/email" target="_blank">posted email address for concurrence.io</a>. We will add this request from the etherbase account (index 0):

```bash
> node addRequest.js

TX:0xd9b15936bf737dd67005d9623541ca88953cd9e7837d763c4f5b516e524acad6
Result {
  sender: '0x4fFD642A057Ce33579a3CA638347b402B909f6D6',
  id: '0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05',
  combiner: '0x22530bf5e978bb88Bd36b914C19dE655605Bc1B8',
  request: '{"url":"http://relay.concurrence.io/email"}',
  protocol: '0x7261770000000000000000000000000000000000000000000000000000000000',
  callback: '0x0BAC8F1cF847F54bf8398e533Aa647a83869d14A',
  count: '1'
}
```

We can also run a getRequest just to see that everything is on-chain correctly:

<!--RQC CODESNIP Javascript concurrence.js/examples/getRequest.js -->

```bash
> node getRequest 0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05

Result {
  '0': '0x22530bf5e978bb88Bd36b914C19dE655605Bc1B8',
  '1': '0x7261770000000000000000000000000000000000000000000000000000000000',
  '2': '0x0BAC8F1cF847F54bf8398e533Aa647a83869d14A'
}
PROTOCOL: raw
```

Working backwards, let's look at the final combiner status:

<!--RQC CODESNIP Javascript concurrence.js/examples/getCombinerStatus.js -->

```bash
>  node getCombinerStatus.js 0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05

COMBINER OPEN: false
COMBINER READY: false
MODE: 0
CONCURRENCE:
WEIGHT: 0
```

The combiner isn't even *open* yet, this is because just submitting a request is not good enough. We need the cryptoeconmics in our favor; we must incentivize! Let's **reserve** some **(CCCE)** behind the request id **0xafe8...**.

<!--RQC CODESNIP Javascript concurrence.js/examples/reserveTokens.js -->

```bash
> node reserveTokens 0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05 100
```

<!--RQC CODESNIP Javascript concurrence.js/examples/getReserved.js -->

```bash
> node getReserved 0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05

Request 0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05 has 100 CCCE reserved
```

Now when we check on the combiner it should be open for business:

```bash
> node getCombinerStatus.js 0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05

COMBINER OPEN: true
```

And our etherbase account (index 0) should be short the 100 **CCCE**:

```bash
> node getBalance 0

Balance: 900 CCCE
```
