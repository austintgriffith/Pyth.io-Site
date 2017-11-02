---
title: "Requests"
date: 2017-09-21T04:00:00-06:00
---

**Concurrence** starts with a developer requesting certain information be mined into the blockchain. The **Concurrence** fleet is meant to be very generic so *any* request can go out with *any* protocol and miners can choose to fulfill the requests or not. For instance, you could make up a protocol called **sportsScores** and your request could be for a certain game or week. Conversely, you could come up with a protocol called **plainEnglish** and your request could be a question like, "Will it rain in Nebraska today". The power of this system is simplicity in the blockchain and complexity in the miners.

Adding a request to the blockchain is as easy as:

<!--RQC CODE Javascript concurrence.js/examples/addRequest.js -->

In this example we will ask that miners draw a consensus around the <a href="http://relay.concurrence.io/email" target="_blank">posted email address for concurrence.io</a>. We will add this request from the etherbase account (index 0):

```bash
> node addRequest.js

TX:0xf525dfb2bb3e39fb6a968fcde9bf8a7af310462e42e14b886fa77608a4b8c935
{
  sender: '0x4fFD642A057Ce33579a3CA638347b402B909f6D6',
  id: '0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f',
  combiner: '0x22530bf5e978bb88Bd36b914C19dE655605Bc1B8',
  request: '{"url":"http://relay.concurrence.io/email"}',
  protocol: '0x7261770000000000000000000000000000000000000000000000000000000000',
  callback: '0x0BAC8F1cF847F54bf8398e533Aa647a83869d14A',
  count: '0'
}
```

We can also run a getRequest just to see that everything is on-chain correctly:

<!--RQC CODESNIP Javascript concurrence.js/examples/getRequest.js -->

```bash
> node getRequest 0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f

Result {
  '0': '0x22530bf5e978bb88Bd36b914C19dE655605Bc1B8',
  '1': '{"url":"http://relay.concurrence.io/email"}',
  '2': '0x7261770000000000000000000000000000000000000000000000000000000000',
  '3': '0x0BAC8F1cF847F54bf8398e533Aa647a83869d14A' }
PROTOCOL: raw
```

Working backwards, let's look at the final combiner status:

<!--RQC CODESNIP Javascript concurrence.js/examples/getCombinerStatus.js -->

```bash
> node getCombinerStatus.js 0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f

COMBINER OPEN: false
COMBINER READY: false
MODE: 0
CONCURRENCE:
WEIGHT: 0
```

The combiner isn't even *open* yet, this is because just submitting a request is not good enough. We need the cryptoeconmics in our favor, we must incentivize! Let's **reserve** some **CCCE** behind the request id **0xf14e...**.

<!--RQC CODESNIP Javascript concurrence.js/examples/reserveTokens.js -->

```bash
> node reserveTokens 0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f 100
```

<!--RQC CODESNIP Javascript concurrence.js/examples/getReserved.js -->

```bash
> node getReserved 0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f

Request 0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f has 100 CCCE reserved
```

Now when we check on the combiner it should be open for business:

```bash
> node getCombinerStatus.js 0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f

COMBINER OPEN: true
```

And our etherbase account (index 0) should be short the 100 **CCCE**:

```bash
> node getBalance 0

Balance: 900 CCCE
```
