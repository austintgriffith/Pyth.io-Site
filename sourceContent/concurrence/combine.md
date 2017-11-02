---
title: "Combine"
date: 2017-09-21T02:00:00-06:00
---

After a flock of responses come in, the chosen **combiner** will work through the results and find a consensus. In the <a href="https://github.com/austintgriffith/concurrence.io/blob/master/Combiner/basic/Combiner.sol" target="_blank">basic combiner</a> the consensus is reached by adding up all the different staked amounts of tokens for each answer and finding the result with the highest value. Miners are also incentivized to run the **combine()** function to completion with a small fraction of the reserved tokens.

First, let's poke the combiner contract just to see the current status:

```bash
> node getCombinerStatus 0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f

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

<!--RQC CODESNIP Javascript concurrence.js/examples/combine.js -->

```bash
node combine 4 0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f

0x51a7626262ddfd261df3b00671e4acf7d7b069ce845ceb5d543e521ace0ed132
```

```bash
> node getCombinerStatus 0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f
COMBINER OPEN: false
COMBINER READY: true
MODE: 1
CONCURRENCE: austin@concurrence.io
WEIGHT: 50
```

The combiner isn't finished be we can see that it isn't *open* anymore and it is starting to draw a **concurrence**. It works through the result carefully and makes sure it doesn't run out of gas. For some large sets of results it make take many **combine()** calls before the combiner finishes.

```bash
node combine 4 0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f

0x4f26be93b27df2c5780131775792d134913cb396e0530a96f3cc4baefe337500
```

```bash
> node getCombinerStatus 0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f
COMBINER OPEN: false
COMBINER READY: true
MODE: 2
CONCURRENCE: austin@concurrence.io
WEIGHT: 250
```

We are still in MODE: 2, keep going...

```bash
node combine 4 0xf14e3babd1c1d7c33b171f789914eecf9451ae9d3e9bdc2d3d0fde1b4dda6f2f

0x59431cfd030ad234dfa81f963d7b2a3f2dd2f95c54001f2f1c16df62ca79db5a
```

Eventually, we reach the final concurrence but the contracts aren't built to callback yet:
```bash
CONCURRENCE: austin@concurrence.io
WEIGHT: 250
```

*MORE TO COME SOON*
