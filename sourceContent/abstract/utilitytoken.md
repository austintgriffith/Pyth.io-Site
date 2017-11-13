---
title: "Utility Token"
date: 2017-09-21T21:00:00-06:00
---

<img src="/images/tokenheader.png" />

The first thing to address in building a decentralized oracle network is the cryptoeconomics of incentivizing miners.

Developers will **reserve** token along with any generic request. Miners will drive a consensus by **staking** token with the off-chain data they collected. **Concurrence** is drawn based on defined combiner algorithms and miners are rewarded or punished based on the consensus. Developers contracts then receive this **concurrence** on-chain to power their smart contract logic.

--------------------------------------------------------------------

Thankfully, many brilliant minds have come together to build a safe and standardized token for Ethereum. One such token is the  [StandardToken](https://github.com/OpenZeppelin/zeppelin-solidity/blob/master/contracts/token/StandardToken.sol), and this will be extended for the use as the **Concurrence** token (**CCCE**).

```
string public constant name = "Concurrence";
string public constant symbol = "CCCE";
uint8 public constant decimals = 9;
uint256 public constant INITIAL_SUPPLY = 10**18;
```

```
// https://github.com/ethereum/EIPs/issues/20
contract ERC20 {
  function totalSupply() constant returns (uint totalSupply);
  function balanceOf(address _owner) constant returns (uint balance);
  function transfer(address _to, uint _value) returns (bool success);
  function transferFrom(address _from, address _to, uint _value) returns (bool success);
  function approve(address _spender, uint _value) returns (bool success);
  function allowance(address _owner, address _spender) constant returns (uint remaining);
  event Transfer(address indexed _from, address indexed _to, uint _value);
  event Approval(address indexed _owner, address indexed _spender, uint _value);
}
```

On top of this tried-and-true token standard we will need a few other features. First, we will need a mechanism for developers to *reserve* funds for the mining of a particular resource. Second, we will need a way for miners to *stake* some of their own token against the consensus. Later, we will want to have a reserved percentage of Ether stored in a contract to provide *liquidity* to the token.
