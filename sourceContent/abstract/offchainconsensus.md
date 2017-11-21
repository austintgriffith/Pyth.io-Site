---
title: "Off-chain Consensus"
date: 2017-09-21T20:00:00-06:00
---

A purely on-chain solution to the decentralized oracle problem faces an issue due to gas prices when reaching a consensus. If every miner has to post results to the blockchain, they will spend way more gas than economically viable. Plus, other miners could simply echo what previous miners posted and earn the token reward without actually making the request.

To solve this problem we'll use an emerging technology called [IPFS](https://ipfs.io/). In particular, we will use their [pubsub](https://ipfs.io/blog/25-pubsub/) functionality. As a new reserve of tokens is made available to miners for a request, a pubsub channel will open based on the hash of the request. Miners will signal to the channel as they work through the mining process.

To solve the problem of copycat miners, each miner will first post a hash of what they think the content value should be. Once enough miners come to the same conclusion, this hash will be written to the blockchain and the utility token will be used as stake. Finally, after a consensus of the hash of the content is statistically reached, the content itself can be revealed, hosted in IPFS, and written to the chain.
