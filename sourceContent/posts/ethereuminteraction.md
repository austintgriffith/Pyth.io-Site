---
title: "Ethereum Network Interaction"
date: 2017-09-21T16:00:00-06:00
draft: true
---
Let's interact with the Ethereum network.

With Geth running and up-to-date, we can attach to it from another ssh session.

```bash
geth attach http://:8545
```

```bash
Welcome to the Geth JavaScript console!

instance: Geth/v1.7.1-stable-05101641/linux-amd64/go1.9
coinbase: 0x0e1e9ce68b3254461a95916c185c37dd65468ab5
at block: 1856113 (Thu, 12 Oct 2017 13:25:06 UTC)
 modules: eth:1.0 net:1.0 personal:1.0 rpc:1.0 web3:1.0

>
```


A fresh installation of Geth will have an empty array of accounts.

```bash
eth.accounts
```

```bash
> eth.accounts
[]
```

Create a couple Ethereum test accounts:

```bash
web3.personal.newAccount()
```

```bash
> web3.personal.newAccount()
Passphrase:
Repeat passphrase:
"0x4ffd642a057ce33579a3ca638347b402b909f6d6"
>
> web3.personal.newAccount()
Passphrase:
Repeat passphrase:
"0xa3eebd575245e0bd51aa46b87b1ffc6a1689965a"
```

Check the balance of an account with
```bash
eth.getBalance("0x4ffd642a057ce33579a3ca638347b402b909f6d6")
```

```bash
> eth.getBalance("0x4ffd642a057ce33579a3ca638347b402b909f6d6")
0
```

Let's ask a testnet faucet for a little test ether:
```bash
curl -X POST  \
  -H "Content-Type: application/json" \
  -d "{\"toWhom\":\"0x4ffd642a057ce33579a3ca638347b402b909f6d6\"}" \
  https://ropsten.faucet.b9lab.com/tap
```

```bash
{
  "txHash" : "0x3e5bfb4bc071e4b0e70eedbc7b667ff6a5e7eb5397a21e16582b85848190ae98"
}
```

The faucet will return a transaction id and we can look it up using
<a href="https://ropsten.etherscan.io/tx/0x3e5bfb4bc071e4b0e70eedbc7b667ff6a5e7eb5397a21e16582b85848190ae98" target="_blank">etherscan.io</a>:

![etherscanfaucetgettesteth](http://s3.amazonaws.com/rqcassets/etherscanfaucetgettesteth.png)

After the transaction clears we will have some test ether:

```bash
> eth.getBalance("0x4ffd642a057ce33579a3ca638347b402b909f6d6")
500000000000000000
```

Next, let's send some test ether to the second account:

```bash
eth.sendTransaction(
  {
    from:"0x4ffd642a057ce33579a3ca638347b402b909f6d6",
    to:"0xa3eebd575245e0bd51aa46b87b1ffc6a1689965a",
    value:100000000000000000
  }
)
```

```bash
> personal.unlockAccount("0x4ffd642a057ce33579a3ca638347b402b909f6d6")
Unlock account 0x4ffd642a057ce33579a3ca638347b402b909f6d6
Passphrase:
true
>
> eth.sendTransaction({from:"0x4ffd642a057ce33579a3ca638347b402b909f6d6",to:"0xa3eebd575245e0bd51aa46b87b1ffc6a1689965a",value:100000000000000000})
"0x3b62b1434591bd6d7e9dc21594a28193c68ff0be756edff0e6f6e62e4cb3343f"
```

We can follow the transaction on <a href="https://ropsten.etherscan.io/tx/0x3b62b1434591bd6d7e9dc21594a28193c68ff0be756edff0e6f6e62e4cb3343f" target="_blank">etherscan.io</a>:
![ethoneaccounttoanotheretherscan](http://s3.amazonaws.com/rqcassets/ethoneaccounttoanotheretherscan.png)


Now if we look at the balances of each account we will see:

```bash
> eth.getBalance("0x4ffd642a057ce33579a3ca638347b402b909f6d6")
399370000000000000
>
> eth.getBalance("0xa3eebd575245e0bd51aa46b87b1ffc6a1689965a")
100000000000000000
```

We can also use the **fromWei()** function to view the balance in Ether:
```bash
> web3.fromWei(eth.getBalance("0x4ffd642a057ce33579a3ca638347b402b909f6d6"),"ether")
0.39937
```
