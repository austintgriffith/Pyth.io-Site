---
title: "Contract Inheritance"
date: 2017-09-21T13:00:00-06:00
---

Very intelligent people before us have found common patterns in Ethereum contracts and published standardized and hardened examples to extend. We will stand on their shoulders using contract inheritance for some parts of our fleet. Let's dig into an example of how this works.  

Instead of coding up our own layer of ownership and possibly introducing insecurities, let's look at inheriting <a href="https://github.com/OpenZeppelin/zeppelin-solidity" target="_blank">OpenZeppelin's zeppelin-solidity repo</a>.

We'll create a contract called **Inherit** that will explore inheriting OpenZeppelin's <a href="https://github.com/OpenZeppelin/zeppelin-solidity/blob/master/contracts/ownership/Ownable.sol" target="_blank">Ownable</a> contract:

```
pragma solidity ^0.4.11;

import 'zeppelin-solidity/contracts/ownership/Ownable.sol';

contract Inherit is Ownable {

    string public message;

    function Inherit(string _message) {
      message = _message;
    }

    function setMessage(string _message) onlyOwner {
        message = _message;
    }
}

```
This light-weight contract has only one state variable, the **message** string, and it can only be set by the owner. Instead of handling the logic ourselves, we use a modifier from the OpenZeppelin's <a href="https://github.com/OpenZeppelin/zeppelin-solidity/blob/master/contracts/ownership/Ownable.sol" target="_blank">Ownable</a> contract.

Also, before we can deploy, the **Inherit** contract will need to add a **dependencies.js**:

```javascript
const fs = require('fs');
module.exports = {
  'zeppelin-solidity/contracts/ownership/Ownable.sol': fs.readFileSync('zeppelin-solidity/contracts/ownership/Ownable.sol', 'utf8')
}

```
And an **arguments.js** to pass in a string to the **Inherit()** constructor.

```javascript
const fs = require('fs');
module.exports = ["Ethereum is totally rad!"]

```
Now we can compile and deploy **Inherit**:

```bash
node compile Inherit
node deploy Inherit
```

(Deployment transaction on <a href="https://ropsten.etherscan.io/tx/0x7a8bb50c31574bba53b394bd02ce7dffd208c20680ba08656fee4dc4cc5ef385" target="_blank">etherscan.io</a>)

Contract address on Ropsten testnet:

```
0x518730e223b89Fd13f1FD45e0BFd61CDE5D2f771
```
We'll want a script that can tell us what the current message is on the **Inherit** contract:

```javascript
// usage: node contract getMessage Inherit
//
// ex: node contract getMessage Inherit
//
module.exports = (contract,params,args)=>{
  contract.methods.message().call().then((message)=>{
    console.log("MESSAGE:"+message)
  })
}

```
```bash
node contract getMessage Inherit

MESSAGE:Ethereum is totally rad!
```

We'll also want a script that can tell us what account is the current owner:

```javascript
// usage: node contract getOwner Inherit
//
// ex: node contract getOwner Inherit
//
module.exports = (contract,params,args)=>{
  contract.methods.owner().call().then((owner)=>{
    console.log("OWNER:"+owner)
  })
}

```
```bash
node contract getOwner Inherit

OWNER:0xA3EEBd575245E0bd51aa46B87b1fFc6A1689965a
```

Awesome, so it looks like stuff is setup correctly. Now, let's see if it functions correctly when we try a **setMessage.js** script:

```javascript
// usage: node contract setMessage Inherit null #ACCOUNTINDEX# #MESSAGE#
//
// ex: node contract setMessage Inherit null 1 "WHAT'S GUCC'?"
//
module.exports = (contract,params,args)=>{
  console.log("**== setting message to "+args[6]+" with account "+params.accounts[args[5]])
  return contract.methods.setMessage(args[6]).send({
    from: params.accounts[args[5]],
    gas: params.gas,
    gasPrice:params.gasPrice
  })
}

```
```bash
node contract setMessage Inherit null 1 "WHAT'S GUCC'?"
```

(Transaction with status: '0x1' on <a href="https://ropsten.etherscan.io/tx/0x9137740dd961c3cfa3aa0e31337545557bf2e305d7e5ac381e6820df2e014e71" target="_blank">etherscan.io</a>)

```bash
node contract getMessage Inherit

MESSAGE:WHAT'S GUCC'?
```

Perfect, so the owner can change the message. Let's make sure the other account can't change the message:

```bash
node contract setMessage Inherit null 0 "Something nefarious..."
```

(Transaction with status: '0x0' on <a href="https://ropsten.etherscan.io/tx/0xffd37b5ceb5284a26d65f194068fa109f7fbb713b9b7c5a915d2a710aace6e34" target="_blank">etherscan.io</a>)

```bash
node contract getMessage Inherit

MESSAGE:WHAT'S GUCC'?
```

Great, we seem to be secure without having to write our own ownership functionality. One last test of inhertance would be to see if the **transferOwnership()** function built into the <a href="https://github.com/OpenZeppelin/zeppelin-solidity/blob/master/contracts/ownership/Ownable.sol" target="_blank">Ownable</a> contract will just work for our contract.

We'll need a **transferOwnership.js** script:

```javascript
// usage: node contract transferOwnership Inherit null #ACCOUNTINDEX# #ACCOUNTADDRESSOFNEWOWNER#
//
// ex: node contract transferOwnership Inherit null 1 0xXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
//
module.exports = (contract,params,args)=>{
  console.log("**== transferring ownership from "+params.accounts[args[5]]+" to "+args[6])
  return contract.methods.transferOwnership(args[6]).send({
    from: params.accounts[args[5]],
    gas: params.gas,
    gasPrice:params.gasPrice
  })
}

```
```bash
node contract transferOwnership Inherit null 1 0x4ffd642a057ce33579a3ca638347b402b909f6d6
```

(Transaction on <a href="https://ropsten.etherscan.io/tx/0x5081fdd66cc9822fe5b65ea72d1afa788f5c8b43ef5928c0a3fe0d5533ae6d73" target="_blank">etherscan.io</a>)

```bash
node contract getOwner Inherit

OWNER:0x4fFD642A057Ce33579a3CA638347b402B909f6D6
```

After writing Solidity for a while and *trying* to be super safe and working through as many possible scenarios as we can think of, it's really nice to be able to rely on a trusted third party that has already had plenty of eyes on their code. As the ecosystem grows, projects will have more and more audits and we'll be able to trust more and more libraries.

