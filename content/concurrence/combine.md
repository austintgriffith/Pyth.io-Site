---
title: "Combine"
date: 2017-09-21T02:00:00-06:00
---

After a bunch of responses come in, the chosen **combiner** will work through the results and find a consensus. In the <a href="https://github.com/austintgriffith/concurrence.io/blob/master/Combiner/basic/Combiner.sol" target="_blank">basic combiner</a> the consensus is reached by adding up all the different staked amounts of tokens for each answer and finding the result with the highest value. Miners are also incentivized to run the **combine()** function to completion with a small fraction of the reserved tokens. *Note: the basic combiner shouldn't be used in production because a single miner can control the consensus.*

<img src="/images/combinebanner.png" />

First, let's poke the combiner contract just to see the current status:

```bash
> node getCombinerStatus 0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05

COMBINER OPEN: true
COMBINER READY: true
MODE: 0
CONCURRENCE:
WEIGHT: 0
```

Let's create a brand new account (index 5) to run as combiner to prove you don't need any **CCCE** to start earning:

```bash
> personal.newAccount()

"0xf712e6189b05c21cc859b0a4efe888f1f09e780f"
```

```bash
> node getBalance 4

Balance: 0 CCCE
```

Now let's run the first round of combining:

```Javascript
//snippet from concurrence.js/examples/combine.js

  concurrence.selectAccount(accountIndex)
  concurrence.combine(requestId,combinerAddress).then((result)=>{
    console.log(result)
    concurrence.listDebug(result.events.Debug)
    concurrence.listDebug(result.events.DebugGas)
    concurrence.listDebug(result.events.DebugPointer)
  });

```
```bash
node combine 4 0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05


```

```bash
> node getCombinerStatus 0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05

COMBINER OPEN: false
COMBINER READY: true
MODE: 1
CONCURRENCE: me@austingriffith.com
WEIGHT: 200
```

The combiner isn't finished but we can see that it isn't *open* anymore and it is starting to draw a **concurrence**. It works through the result carefully and makes sure it doesn't run out of gas. For some large sets of results it make take many **combine()** calls before the combiner finishes.

```bash
node combine 4 0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05
```

```bash
> node getCombinerStatus 0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05

COMBINER OPEN: false
COMBINER READY: true
MODE: 2
CONCURRENCE: austin@concurrence.io
WEIGHT: 300
```

We are still in MODE: 2 (FEEDBACK), this is where the combiner pays out rewards and punishes back actors.

```bash
> node combine 4 0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f

> node getCombinerStatus 0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05

COMBINER OPEN: false
COMBINER READY: true
MODE: 3
CONCURRENCE: austin@concurrence.io
WEIGHT: 300
```

It is now in MODE: 3 (CALLBACK), but that is still in development so we will stop here.

Let's look at ending balances:

```bash
> node getBalance 0
Balance: 900 CCCE
```
Account index 0, representing the developer, has 900 **(CCCE)** because they spent 100 **(CCCE)** to incentivize the miners.

That 100 **(CCCE)** is then rewarded to the miner with the correct **response** (index 2) and the miner that ran the final **combine()** (index 4). Each miner, including the one that runs the **combine()**, receives and equal cut of the reward.

```bash
> node getBalance 2
Balance: 1050 CCCE

> node getBalance 4
Balance: 50 CCCE
```

Finally, the bad actor (index 3), was punished 10% of their 200 **(CCCE)** stake.

```bash
> node getBalance 3
Balance: 980 CCCE
```

Developer contracts on the Ropsten network can now ask contract **0x22530bf5e978bb88Bd36b914C19dE655605Bc1B8** for the consensus on request **0xafe816afaebbd039033c8bd6896e415d10de87c9f785d2adaf4e73766303ce05** and they will get **austin@concurrence.io**. This represents the first **concurrence** of off-chain data mined to the blockchain.

