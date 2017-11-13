---
title: "Introduction"
date: 2017-09-21T05:00:00-06:00
---

<img src="/images/intro.png" />

In the following documents, we will explore building out an end-to-end example of **Concurrence**. Using only our <a href="https://www.npmjs.com/package/concurrence" target="_blank">Javascript NPM package</a> we will add a request to the testnet fleet asking miners to come to a consensus on an internet endpoint. We will reserve some **CCCE** token behind our request to incentivize miners. We will then mine the request with a couple different accounts. Finally, we'll trigger the **combiner** to draw a consensus, reward miners, and callback.

Let's run a few commands on the **Concurrence** Javascript package to test that everything is working correctly. First, let's get the version and the main contract address with:

<!--RQC CODESNIP Javascript concurrence.js/examples/version.js -->

```bash
> node version

0.0.1
0xfb15A576DB9D2D5cb3e7F7a3513FFb633B321E63
```

Nice, looks like we are all connected correctly. Next, let's look at the local accounts and their balances in **CCCE**:

<!--RQC CODESNIP Javascript concurrence.js/examples/accounts.js -->

```bash
> node accounts

[ '0x4fFD642A057Ce33579a3CA638347b402B909f6D6',
  '0xA3EEBd575245E0bd51aa46B87b1fFc6A1689965a',
  '0x926dcc7318Df2D2b543836522f16c5e1f71370A0',
  '0x78782428cDbD1cF877afDB0AB709476aefaE4B35' ]
```

<!--RQC CODESNIP Javascript concurrence.js/examples/getBalance.js -->

```bash
> node getBalance 1

Asking concurrence.js what my (etherbase) balance is...
Balance: 1000000000000000000 CCCE


> node getBalance 2

Asking concurrence.js what my (etherbase) balance is...
Balance: 0 CCCE


> node getBalance 3

Asking concurrence.js what my (etherbase) balance is...
Balance: 0 CCCE
```

So the account at index **1** has all the **CCCE** at this point, let's distribute that out a little:

<!--RQC CODESNIP Javascript concurrence.js/examples/transfer.js -->

```bash
> node transfer 1 0x4fFD642A057Ce33579a3CA638347b402B909f6D6 1000
> node transfer 1 0x926dcc7318Df2D2b543836522f16c5e1f71370A0 1000
> node transfer 1 0x78782428cDbD1cF877afDB0AB709476aefaE4B35 1000
```

Now our other accounts have some **CCCE**:

```bash
> node getBalance 2

Asking concurrence.js what my (etherbase) balance is...
Balance: 1000 CCCE
```
