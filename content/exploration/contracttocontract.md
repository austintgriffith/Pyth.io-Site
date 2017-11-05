---
title: "Contract To Contract"
date: 2017-09-21T13:30:00-06:00
---

<img  src="/images/contracttocontract.png" />

Contract to contract communication is an essential part of any fleet on the blockchain. As discussed in the <a href="/posts/contractlineage/" target="_blank">contract lineage section</a>, a complex project on Ethereum should be built using a collection of smaller microservices to keep complexity at bay.

We will also explore a few other features of smart contracts like ownership and events.

Let's analyze a second contract we'll call **Adjuster** which will interface with our **Simple** contract:

```
pragma solidity ^0.4.11;

import "Simple.sol";

contract Adjuster {

    address public owner;

    function Adjuster() {
      owner = msg.sender;
    }

    /*
      read the current value of the Simple count
      then, add the correct amount to get to _target
      if _target < count, overflow uint8
    */
    function adjustTo(address _contractAddress,uint8 _target) public returns (uint8) {
      //make sure the sender is the owner, only that address can use the Adjuster
      require(msg.sender == owner);
      //load the Simple contract based on the _contractAddress supplied
      Simple simpleContract = Simple(_contractAddress);
      //get the currentCount frim the Simple contract
      uint8 currentCount = simpleContract.count();
      //if the count is right already just return 0
      if(currentCount == _target) return 0;
      //adjust the Count as needed
      uint8 amount;
      if(currentCount < _target){
        amount = _target-currentCount;
      }else{
        amount = 255-(currentCount-_target-1);
      }
      //add the amount
      simpleContract.add(amount);
      //trigger the event
      Adjusted(_contractAddress,_target,amount);
      //return the amount added
      return amount;
    }

    event Adjusted(address _contractAddress,uint8 _target,uint8 _amount);
}

```
The **Adjuster** contract has an **adjustTo()** function that will add to the **Simple** contract's **count** to adjust it to the **_target** value.

There is also the concept of an **owner** address. This is set on contract deployment and when **adjustTo()** is called, we check to make sure the **msg.sender** is the **owner**.

There is also an *event* called **Adjusted** that is fired when the **adjustTo()** function makes a change to the **count**. Events are only visible off-chain but they are very useful for debugging, triggering off-chain actions, and even relatively cheap storage.

We can compile the **Adjuster** with:

```bash
node compile Adjuster
```

We'll also need to throw in a **dependencies.js** to include **Simple.sol**:

```javascript
const fs = require('fs');
module.exports = {
  'Simple.sol': fs.readFileSync('Simple/Simple.sol', 'utf8')
}

```
We then deploy the **Adjuster** with:

```bash
node deploy Adjuster
```

(Deployment transaction on <a href="https://ropsten.etherscan.io/tx/0xa9c86370a2d18c185803ce2fa80a10d12c1b7f9293040609fe2cd7ea8d509ae1" target="_blank">etherscan.io</a>)

This contract is a little bigger than the last so it was a little more expensive to deploy:

```
==ETHER COST: 0.005155854000000001 $1.8045489000000006
```

**Adjuster** address on Ropsten:
```
0x34dcf6e1fb7dc453f514a5c4760595af5e2e2ea9
```

Now let's write a few scripts to interact with this contract. First, we'll want a script called **getOwner.js** to be able to see who the owner is:

```javascript
//
// usage: node contract getOwner Adjuster
//
module.exports = (contract,params,args)=>{
  contract.methods.owner().call().then((owner)=>{
    console.log("OWNER:"+owner)
  })
}

```
```bash
node contract getOwner Adjuster
```

```bash
OWNER:0xA3EEBd575245E0bd51aa46B87b1fFc6A1689965a
```

Looking at our local accounts on the testnet, this is our second account, or index **1**:
```bash
> eth.accounts
["0x4ffd642a057ce33579a3ca638347b402b909f6d6", "0xa3eebd575245e0bd51aa46b87b1ffc6a1689965a"]
```

Another script needed is **adjustTo.js**. This allows us to adjust the **Simple** contract's **count** to a specific number using only the **add()** function as long as we are the owner of the **Adjuster** contract.

```javascript
//
// usage: node contract adjustTo Adjuster null #CONTRACTADDRESS# #TARGET# #ACCOUNTINDEX#
//
// ex: node contract adjustTo Adjuster null 0xXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX 128 1
//
module.exports = (contract,params,args)=>{
  console.log("**== adjusting Simple contract at "+args[5]+" to "+args[6]+" using account "+params.accounts[args[7]])
  return contract.methods.adjustTo(args[5],args[6]).send({
    from: params.accounts[args[7]],
    gas: params.gas,
    gasPrice:params.gasPrice
  })
}

```
Let's go back to the **Simple** contract function from the previous section to get the current count:

```bash
node contract getCount Simple
```

```bash
COUNT:0
```

Now let's adjust it to **128** using the **Simple** contract's address using account index **1** which is the owner of the **Adjuster**:

```bash
node contract adjustTo Adjuster null 0xD68eF7611913d0AfF3627a92F5e502696887D626 128 1
```

(View transaction on <a href="https://ropsten.etherscan.io/tx/0xcdc8bb4b1fe7267bf4ded620c7501befb749301b7c42a4b1cb3cb5738dad4c13" target="_blank">etherscan.io</a>)

Now if we get a count we'll see:

```bash
COUNT:128
```

Let's write a quick script to read events off-chain:

```javascript
//
// usage: node contract eventsAdjusted Adjuster
//
module.exports = (contract,params,args)=>{
  contract.getPastEvents('Adjusted', {
      fromBlock: params.blockNumber,
      toBlock: 'latest'
  }, function(error, events){
    console.log(events);
  })
}

```
If we run that now, we should see all contract interaction so far:

```bash
{
  address: '0x34DCF6E1fB7DC453F514a5C4760595af5e2E2Ea9',
  transactionHash: '0xcdc8bb4b1fe7267bf4ded620c7501befb749301b7c42a4b1cb3cb5738dad4c13',
  returnValues:
   Result {
     '0': '0xD68eF7611913d0AfF3627a92F5e502696887D626',
     '1': '128',
     '2': '128',
     _contractAddress: '0xD68eF7611913d0AfF3627a92F5e502696887D626',
     _target: '128',
     _amount: '128' },
  event: 'Adjusted',
  signature: '0xafa2c40f4442ec5731ad257412e46d0e88b0d8f8398f575db15a4c9192d19e29'
}
```

We can see that if the original count was **0** and the **_target** is **128** then the **_amount** needed to get to the target is **128**.

It's always good to test every condition exhaustively and try to hit your contract with anything and everything, because a good hacker will do the same. Obviously, security doesn't really matter with this contract because the **Simple** contract is already completely open for manipulation, but it helps illustrate simple methods of testing. For production level contracts, along with open sourcing and extensive audits, we will need to have a full suite of tests.

For now, let's just make sure it overflows correctly when we **add()** past 255.

```bash
node contract adjustTo Adjuster null 0xD68eF7611913d0AfF3627a92F5e502696887D626 16 1
```

(View transaction on <a href="https://ropsten.etherscan.io/tx/0xc47dfe8a969b5e77a15d7b89b8313c96215573be6dd859c06067b5a4b4628642" target="_blank">etherscan.io</a>)

```bash
COUNT:16
```

Let's also make sure we can't run the **adjustTo()** function using a non-owner account. We'll use account index **0** instead of **1** to simulate a foreign account trying to run the **adjustTo()** function:

```bash
node contract adjustTo Adjuster null 0xD68eF7611913d0AfF3627a92F5e502696887D626 32 0
```

(View transaction with 'Fail' as 'TxReceipt Status' on <a href="https://ropsten.etherscan.io/tx/0xc89b8bdccefabc5ab77ecfcf8e8d13031d05582658d784206d7e7dc9f263ef9d" target="_blank">etherscan.io</a>)

The count remains:
```bash
COUNT:16
```

One last thing to mention about contract to contract communication is that when one contract accesses a function of another contract, the **msg.sender** on the accessed contract is that of the accessing contract, not the account triggering the interaction. This means we can code in security where only a particular contract can have permission to run certain functions on another contract. This will be important later.

*Note: Along those same lines, <a href="https://github.com/ethereum/EIPs/blob/master/EIPS/eip-7.md" target="_blank">DELEGATECALL</a> is an opcode that "propagates the sender and value from the parent scope to the child scope".*

