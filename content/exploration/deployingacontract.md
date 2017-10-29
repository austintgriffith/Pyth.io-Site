---
title: "Deploying A Contract"
date: 2017-09-21T14:00:00-06:00
---

Never before in history has a technology existed where anyone from anywhere can deploy code that will immediately and indefinitely run on hundreds of thousands of nodes simultaneously and deterministically. Further, thanks to cryptography and cryptoeconomics, this technology is ownerless, trustless, and incentivized to continue. Once a contract is deployed, it is effectively autonomous, eternal, and controlled only by the laws of machines.

Let's make our mark on the blockchain right now with a simple contract:

```
pragma solidity ^0.4.11;

contract Simple {

    uint8 public count;

    function Simple(uint8 _amount) {
      count = _amount;
    }

    function add(uint8 _amount) {
        count += _amount;
    }
}

```
This **Simple** contract has a count (**uint8**) that is initialized in the constructor and can be incremented from an **add()** function.

We will first compile this using our **compile.js** script:

```bash
node compile Simple
```

This creates the **bytecode**:

```
6060604052341561000f57600080fd5b60405160208061011a833981016040528080516000805460ff90921660ff19909216919091179055505060d3806100476000396000f300606060405263ffffffff7c010000000000000000000000000000000000000000000000000000000060003504166302067e6a8114604557806306661abd14605d57600080fd5b3415604f57600080fd5b605b60ff600435166083565b005b3415606757600080fd5b606d609e565b60405160ff909116815260200160405180910390f35b6000805460ff19811660ff9182169390930116919091179055565b60005460ff16815600a165627a7a723058203b071253b061d82c255e5006105dfcb6234b29200503a45f7a55c842c0dbc10a0029
```
(You can reference the various OPCODES <a href="https://ethereum.stackexchange.com/questions/119/what-opcodes-are-available-for-the-ethereum-evm" target="_blank">here</a> and dig further into gas prices in the <a href="http://yellowpaper.io/" target="_blank">Ethereum yellow paper</a>.)

Next, let's deploy the contract to the testnet:

```bash
node deploy Simple
```

We are deploying with an **arguments.js** file of:

```javascript
module.exports = [253]

```
That means the integer **253** will be passed into the constructor **Simple(*_amount*)** on deployment.

```
Success
{
  blockHash: '0x4cc594ee907efa2002620f30380a2764de7f5f7420541f4f3068329e051322cd',
  blockNumber: 1856513,
  contractAddress: '0xD68eF7611913d0AfF3627a92F5e502696887D626',
  cumulativeGasUsed: 618103,
  from: '0xa3eebd575245e0bd51aa46b87b1ffc6a1689965a',
  gasUsed: 132137,
  logs: [],
  logsBloom: '0x00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000',
  status: '0x1',
  to: null,
  transactionHash: '0x312ff4b014459cd5ce7ea56dbffb57814333c93c93897bade0fe08fbc60bb2d2',
  transactionIndex: 3
}
```

View transaction on <a href="https://ropsten.etherscan.io/tx/0x312ff4b014459cd5ce7ea56dbffb57814333c93c93897bade0fe08fbc60bb2d2" target="_blank">etherscan.io</a>:

![deploysimplecontract](http://s3.amazonaws.com/rqcassets/deploysimplecontract.png)

Our handy deploy script also gives us a report of on how expensive it was to deploy the contract:
```
paying a max of 2000000 gas @ the price of 22 gwei (22000000000)
...
==ETHER COST: 0.0029070139999999994 $1.0174548999999997
```

At the current price of Ether it cost about $1.02 to release that code to the world. Pretty rad!

Anyone, for the rest of the existence of the Ropsten testnet, can interface with this specific instance of the contract here:

```

```
(You can follow all interactions with this contract on <a href="https://ropsten.etherscan.io/address/0xd68ef7611913d0aff3627a92f5e502696887d626" target="_blank">etherscan.io</a>)

Let's poke around on this contract and see what shakes loose. We'll want to craft up a few scripts so we  aren't fumbling around on the command line:

```javascript
//
// usage: node contract getCount Simple
//
module.exports = (contract,params,args)=>{
  contract.methods.count().call().then((count)=>{
    console.log("COUNT:"+count)
  })
}

```
```bash
node contract getCount Simple
```

```bash
COUNT:253
```
Neat, so our current count is **253** and that's what we deployed the contract with.
*Note: this was a read-only action, we didn't change the state of the contract so it's free for anyone to do as long as they are connected to the Ethereum blockchain.*

Let's run the **add()** function on the contract to actually change the state. Might as well create a script for this too:
```javascript
//
// usage: node contract add Simple null #AMOUNT#
//
// ex: node contract add Simple null 1
//
module.exports = (contract,params,args)=>{
  console.log("**== adding "+args[5])
  return contract.methods.add(args[5]).send({
    from: params.accounts[0],
    gas: params.gas,
    gasPrice:params.gasPrice
  })
}

```
```bash
node contract add Simple null 1
```

```bash
Success
{
  blockHash: '0x78ad780d0bf95737baec82f974e067d4ee67708829cbd4dad4fda15c3de39a51',
  blockNumber: 1856672,
  contractAddress: null,
  cumulativeGasUsed: 26840,
  from: '0x4ffd642a057ce33579a3ca638347b402b909f6d6',
  gasUsed: 26840,
  logs: [],
  logsBloom: '0x00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000',
  status: '0x1',
  to: '0xd68ef7611913d0aff3627a92f5e502696887d626',
  transactionHash: '0x78a472410ada67aa2345d37fb4d695e1e07702940aae51616efacb7e4b01621c',
  transactionIndex: 0
}
```

View transaction on <a href="https://ropsten.etherscan.io/tx/0x78a472410ada67aa2345d37fb4d695e1e07702940aae51616efacb7e4b01621c" target="_blank">etherscan.io</a>.

Now let's do another **getCount** and see what the state of the contract is:

```bash
node contract getCount Simple
```

```bash
COUNT:254
```

According to Etherscan, to add 1 to that uint it costs about $0.17. That might seem kind of expensive, but what's actually going on there?

Well, we broadcast to the network that we want to make a transaction, some lucky miner is able to find the correct nonce through brute force cpu power, mines the block containing our transaction and others, broadcasts that to the rest of the network, and then *every* miner in the world runs our transaction against their version of our contract and gets the same result. We could then ask *any* of them what our **count** is and it would be the same. Even as banks, businesses, and governments rise and fall, our **count** stays exactly where it's instructed to stay. That's pretty freakin' awesome.

Let's play around with gas cost a little more because contract interaction cost plays a huge role in how **Concurrence** will work.

Let's lower what we are willing to pay by 1/10:

```bash
echo "2" > gasprice.int
```

Now let's run the same transaction again:
```bash
node contract add Simple null 1
```

Checking on the count now, we see:
```bash
COUNT:255
```

View transaction on <a href="https://ropsten.etherscan.io/tx/0x2af7ef9e4c20b10e8fa0b5252bb9c2ab1df01b81058c3e99e87562bae47fa97d" target="_blank">etherscan.io</a>.

Last time it took about 25 seconds to go through. This time it took 55 seconds but the cost was $0.017 or so. This is because the miners are not only incentivized by block mining rewards but also the gas used to run the transactions. It's up to them to determine which transactions are worth mining. We can trade cost for speed depending on our needs.

(*Another neat tool provided by etherscan.io is the <a href="https://ropsten.etherscan.io/vmtrace?txhash=0x2af7ef9e4c20b10e8fa0b5252bb9c2ab1df01b81058c3e99e87562bae47fa97d" target="_blank">vmtrace</a>. You can see each step of the transaction including OPCODE and cost. Check out how much SSTORE is compared to everything else.*)

We should touch on security and bugs. This contract is public, so anyone can run the add function and anyone can see the current count. That's fine for now, but what if there were 100 million USD at stake... yikes! This makes the job of contract developers *extremely* difficult because everything you do is at the mercy of every bad actor for the rest of time. Every contract interaction is deterministic; we *can* determine for sure what will happen given a state and an action, but just like a move in chess, predicting every single possible outcome on a chess board is also relatively difficult.

So what happens if we run **add()** one more time? Assuming no one else has already hit our contract, the count will go up just like before and we should see **256** right? Let's try it:

```bash
node contract add Simple null 1
```

View transaction on <a href="https://ropsten.etherscan.io/tx/0xee07a309049d3ee0d78ba66f186fd0a3727cd8b552ec4b4b331a626dc3570815" target="_blank">etherscan.io</a>.

Checking on the count now, we see:
```bash
COUNT:0
```

Oh shart! Our account balance is 0! H4ckers! Well, not exactly, more like uint8 <a href="https://en.wikipedia.org/wiki/Integer_overflow" target="_blank">overflow</a>, but you get the idea. It is incredibly important that you understand every aspect of every possibility your contract can encounter because *a lot* of real money is on the line.

