---
title: "Contract Migration"
date: 2017-09-21T12:00:00-06:00
---

As bugs are discovered or new functionality needs to be added to contracts, we will need a method of migrating from a predecessor to a descendant. As mentioned in the [Contract Lineage](/abstract/contractlineage) section, we will try to keep contracts simple and we will create a linked-list of lineage so other contracts and scripts both on and off the blockchain can follow a chain of addresses to the latest version. For contracts with large data stores, we may need to pause the contracts and slowly migrate the data, but for this example we will just add some new functionality.

Let's start with a new contract called **Store** that will hold current prices of the top cryptocurrencies (This will also help demonstrate some aspects of an oracle). This contract will use the **mapping** datatype to store a **bytes32** => **uint** relation called **price**:

```
pragma solidity ^0.4.11;

import 'zeppelin-solidity/contracts/ownership/Ownable.sol';
import "Predecessor.sol";

contract Store is Ownable,Predecessor {

    //string to hold source url of price information for reference
    string public source;
    
    //prices mapped by SYMBOL => price in USD
    mapping (bytes32 => uint) price;

    function Store(string _source) {
      source = _source;
    }

    //only the owner can set prices by symbol
    function setPrice(bytes32 _symbol,uint _price) onlyOwner {
      //setPrice should never get called once a descendant is set
      assert(descendant==address(0));
      price[_symbol]=_price;
    }

    //anyone can get any price by symbol
    function getPrice(bytes32 _symbol) constant returns (uint) { /*whenNotMigrating*/
      //if there is a descendant, pass the call on
      if(descendant!=address(0)) {
        return Store(descendant).getPrice(_symbol);
      }
      return price[_symbol];
    }
}

```
Only the owner of the contract will be able to run a miner that will call the **setPrice()** function to update the **price** mapping.
Then, other contracts on the the blockchain can call **getPrice()** to retrieve the price of a certain currency. We'll use the internet endpoint https://api.coinmarketcap.com/v1/ticker/ to retrieve data. This url will be passed to the **Store** constructor to signal to miners where to get their data.

We will also extend a new contract called **Predecessor** that enables the owner to define a chain of **descendant** addresses:

```
pragma solidity ^0.4.11;

contract Predecessor is Ownable{
    function Predecessor() {}
    address public descendant;
    function setDescendant(address _descendant) onlyOwner {
      descendant=_descendant;
    }
}

import 'zeppelin-solidity/contracts/ownership/Ownable.sol';

```
Notice that the **price** mapping in **Store** is not public like previous contracts and we have a **getPrice()** function instead. This allows us a little more control when data is requested. Instead of just delivering the price immediately, we can look to see if a **descendant** is set and forward the **getPrice()** along.

Let's compile and deploy the **Store**:

```bash
node compile Store
node deploy Store
```

(Deployment transaction on <a href="https://ropsten.etherscan.io/tx/0x47385af159170c14e2fa99433ee768659ab9a4157c087b64aec65396f8917ac2" target="_blank">etherscan.io</a>)

```bash
0x68a3724eb459c0d70c7f3148b86a1b42e718c27b
```

Let's check the current state of the newly deployed contract:

```bash
node contract getState Store

OWNER:0xA3EEBd575245E0bd51aa46B87b1fFc6A1689965a
SOURCE:https://api.coinmarketcap.com/v1/ticker/
DESCENDANT:0x0000000000000000000000000000000000000000
```

(We won't be including the javascript code necessary to make these queries, they are almost exactly the same as others in previous sections.)

And if we ask it for the current price of a currency, we should get 0x0 back:

```bash
node contract getPrice Store null ETH

PRICE OF [ETH]: $0 USD
```

Time to build our first version of a request miner to power this simplified, centralized oracle:

```javascript
//
// usage: node contract minePrice Store null #SYMBOL#
//
// ex: node contract minePrice Store null BTC,ETH,XRP,BCH,LTC
//
const SHIFT = 1000000000000//shift price from float to uint
const Request = require('request');
let ACCOUNT_INDEX = 1
module.exports = (contract,params,args)=>{
  console.log("**== loading source from contract...")
  contract.methods.source().call().then((source)=>{
    console.log("** Calling price source url:"+source)
    let symbols = args[5].split(",")
    console.log("** Symbols:",symbols)
    Request(source,(error, response, body)=>{
      if(error){console.log(error)}else{
        try{
          let data = JSON.parse(body)
          for(let i in data){
            if(symbols.includes(data[i].symbol)){
              let shiftedPrice = data[i].price_usd*SHIFT;
              console.log("**== setting price for "+data[i].symbol+" to "+shiftedPrice)
              contract.methods.setPrice(params.web3.utils.fromAscii(data[i].symbol),shiftedPrice).send({
                from: params.accounts[ACCOUNT_INDEX],
                gas: params.gas,
                gasPrice:params.gasPrice
              }).then((tx)=>{console.log(data[i].symbol,tx.transactionHash)})
            }
          }
        }catch(e){console.log(e)}
      }
    })
  })
}

```
If we run that, it should retrieve the prices and update the contract:

```bash
node contract minePrice Store null BTC,ETH,XRP,BCH,LTC

** Calling price source url:https://api.coinmarketcap.com/v1/ticker/
** Symbols: [ 'BTC', 'ETH', 'XRP', 'BCH', 'LTC' ]
**== setting price for BTC to 5678190000000000
**== setting price for ETH to 336595000000000
**== setting price for XRP to 263983000000.00003
**== setting price for BCH to 314947000000000
**== setting price for LTC to 65467400000000

ETH 0x40fd6e3097c25fab8f81caae9bd16d44c5de917393e451b27d09b37bd86fdc64
LTC 0x85c55ab4275129132c3f044933ebcca5dec3798a2bc9cf8ba616bee86909774e
BCH 0x676aafbfbe07f0d4e0e49599b686d473438113b5358ada27f4349f018ef23ccd
XRP 0x1f40ea0c0d776525fc2cd675578f4013d6c00ed4e13feeeb001dccc90168c4df
BTC 0xb57d91a2fe56b5c48d9865449e96ef6e171da084bc94a6186ee8d29000517364
```

(Example of one of the Miner transactions [LTC] on <a href="https://ropsten.etherscan.io/tx/0x85c55ab4275129132c3f044933ebcca5dec3798a2bc9cf8ba616bee86909774e" target="_blank">etherscan.io</a>)

Yikes, if this was on the mainnet that would be pretty expensive; it just cost $0.32 to set the price of just the [LTC] mapping. This gives us an idea of what future miner expenses will be and helps us plan our infrastructure. As mentioned in the [Off-chain Consensus](/abstract/offchainconsensus) section, we will want to do as much as we can off-chain, but eventually, in order to make information available to other contracts, we'll have to write to the blockchain.

Let's ask our contract what the current price of Litecoin is:

```bash
node contract getPrice Store null LTC

PRICE OF [LTC]: $65.4674 USD
```

And then let's run another round of mining:

```bash
node contract minePrice Store null BTC,ETH,XRP,BCH,LTC

** Calling price source url:https://api.coinmarketcap.com/v1/ticker/
** Symbols: [ 'BTC', 'ETH', 'XRP', 'BCH', 'LTC' ]
**== setting price for BTC to 5687570000000000
**== setting price for ETH to 336711000000000
**== setting price for XRP to 263952000000.00003
**== setting price for BCH to 315263000000000
**== setting price for LTC to 65718300000000

BTC 0x71a874871cfb9083598a2ed50c49b525bcbcad1a73f8f2214312460d73fedba5
BCH 0x0f2891051e472b915cfadb91f3691bd4733768f129e59ca8709f0deabddf2ee1
LTC 0x291da538e1db6545e34edb73fefea4806bfa0f3155ba6cf7b28665f9b105762c
XRP 0xdef369acd5b111e31df1c90dbc5afb67e76d7099f24329fa8e325129b50f625f
ETH 0xc767d9a6ca3d782b0daf810de60cd03ce3edde2412fef40172786bccdf9f83a5
```

We should see that the price of Litecoin went up slightly:

```bash
node contract getPrice Store null LTC

PRICE OF [LTC]: $65.7183 USD
(previously $65.4674)
```

Awesome, we have good data on the chain, let's write a *client* contract called **EthVsBch** that will tell us if Ethereum or Bitcoin Cash is worth more:

```
pragma solidity ^0.4.11;

/*
A simple 'request oracle client' that needs to know the price of Eth and Bch
*/

//simple Store interface with just the function we need
contract Store{function getPrice(bytes32 _symbol) constant returns (uint) {}}

contract EthVsBch {

    //string to hold source address of oracle
    address public source;

    function EthVsBch(address _source) {
      source = _source;
    }

    //anyone can get any price by symbol
    function whoIsWinning() constant returns (string,uint) { /*whenNotMigrating*/
      Store store = Store(source);
      uint priceOfEth = store.getPrice("ETH");
      uint priceOfBch = store.getPrice("BCH");
      if( priceOfEth > priceOfBch ){
        return ("ETH",priceOfEth);
      }else if ( priceOfEth < priceOfBch ){
        return ("BCH",priceOfBch);
      }else{
        return ("TIE!",priceOfEth);
      }
    }
}

```
Let's compile and deploy **EthVsBch** with the **Store** address *hardcoded* in **arguments.js**:

```javascript
module.exports = ["0x68a3724eb459c0d70c7f3148b86a1b42e718c27b"]
```

```bash
node compile EthVsBch
node deploy EthVsBch
```

(Deployment transaction on <a href="https://ropsten.etherscan.io/tx/0x756d189e11df779f123a751ded6bfa18627c69d3fcfa9a3512f361b2eb83314e" target="_blank">etherscan.io</a>)

```bash
0xc46A9BAE68Fb60402049202521279494D440F493
```

Now let's get the state of **EthVsBch** and it will hit the **Store** and perform its logic:

```bash
node contract getState EthVsBch

CURRENT WINNER: Result { '0': 'ETH', '1': '336711000000000' }
```

Assuming BCH and ETH prices aren't going to cross during this exploration, we can't really test all the possibilities, but let's go run another round of mining and then check back in:

```bash
node contract minePrice Store null BTC,ETH,XRP,BCH,LTC

** Calling price source url:https://api.coinmarketcap.com/v1/ticker/
** Symbols: [ 'BTC', 'ETH', 'XRP', 'BCH', 'LTC' ]
**== setting price for BTC to 5699690000000000
**== setting price for ETH to 336907000000000
**== setting price for XRP to 263843000000
**== setting price for BCH to 316191000000000
**== setting price for LTC to 65846500000000.01

BTC 0xbd5f2ee16e87b7b7d1fa3bf0980af6defd3c0a6033b8d1af7461a7fd56ca78c8
XRP 0x73df00e7bca3f4bb1747056210d904c6badf9ae13e06639f0434e8db2ba0d9b7
ETH 0x92d0ffb887b7967c50d6dea88d11d8c59e93532fd50ab348f7b3ce7b318fbf4d
BCH 0x7fb8f2ae02a88a1d0d2d6618624e2ba1dada2135dfdf976350a141c80caffd8c
LTC 0xe3b60849d769d3351f445d9620166020993e25c59432935dbe1cdebbf997878a
```

```bash
node contract getState EthVsBch

CURRENT WINNER: Result { '0': 'ETH', '1': '336907000000000' }
(previously '336711000000000')
```

Great, so as our miner continues to supply the **Store** contract with data, other contracts on the blockchain can source that data to make decisions. But, how would a contract know if the data in **Store** has grown stale? What if, down the road, we got requests from developers to add in a **uint** that would tell them how old the prices were? Let's do that now:

```
pragma solidity ^0.4.11;

import 'zeppelin-solidity/contracts/ownership/Ownable.sol';
import "Predecessor.sol";

contract Store is Ownable,Predecessor {

    //string to hold source url of price information for reference
    string public source;

    //prices mapped by SYMBOL => price in USD
    mapping (bytes32 => uint) price;

    function Store(string _source) {
      source = _source;
    }

    //only the owner can set prices by symbol
    function setPrice(bytes32 _symbol,uint _price) onlyOwner {
      //setPrice should never get called once a descendant is set
      assert(descendant==address(0));
      price[_symbol]=_price;
      //--- keep track of block number of last update
      lastUpdate=block.number;
    }

    //anyone can get any price by symbol
    function getPrice(bytes32 _symbol) constant returns (uint) { /*whenNotMigrating*/
      //if there is a descendant, pass the call on
      if(descendant!=address(0)) {
        return Store(descendant).getPrice(_symbol);
      }
      return price[_symbol];
    }
    //--- lastUpdate is block number of last update
    uint public lastUpdate;
}

```
Notice this new version of **Store** keeps a **lastUpdate** **uint** that is set to the **block.number** as a miner runs an update. This contract also has to be built to be backwards compatible because the first version of **EthVsBch** needs to continue to work with the previous **Store** hardcoded in, but we only want the miner to update one contract due to gas costs and complexity. Let's compile and deploy the next version of **Store**:

```bash
node compile Store
node deploy Store
```

(Deployment transaction on <a href="https://ropsten.etherscan.io/tx/0xcb9a83f1f157e45e1edb55c584a7180bb4c373c366ce482d03684ae6eed5b49d" target="_blank">etherscan.io</a>)

```
0xD0557B2c5A11F8B5F2635Bfa57dEb8dCF6021475
```

```bash
node contract getState Store

OWNER:0xA3EEBd575245E0bd51aa46B87b1fFc6A1689965a
SOURCE:https://api.coinmarketcap.com/v1/ticker/
DESCENDANT:0x0000000000000000000000000000000000000000

node contract getPrice Store null ETH

PRICE OF [ETH]: $0 USD

node contract getLastUpdate Store

LastUpdate:0
```

So the new contract is empty and the old contract is still chugging along fine. The transition process requires that we get some data in the new contract before setting up the lineage so external developers' contracts don't get empty data. Let's mine into the new **Store**:

```bash
node contract minePrice Store null BTC,ETH,XRP,BCH,LTC

** Calling price source url:https://api.coinmarketcap.com/v1/ticker/
** Symbols: [ 'BTC', 'ETH', 'XRP', 'BCH', 'LTC' ]
**== setting price for BTC to 5681640000000000
**== setting price for ETH to 336505000000000
**== setting price for XRP to 264403000000
**== setting price for BCH to 315928000000000
**== setting price for LTC to 65956700000000

BCH 0x0fd5fe447795cbff9b33f0a349caa8545bd8fb4db3149a2ed75f36e9dfacd409
ETH 0x6b134c98fe962a7bbfb6f27fb155b49dce980d7ca3eab6a22b6c299465efcc7e
BTC 0x6102e186a2f84447dd9143e5a11a943ddddb0820e4eccb06184c0f616b8986e8
XRP 0x2b7cad141ebc3f4e9687202c7df8d23460f5fa65a34711d88b9ced1b6553374d
LTC 0x6da8151bb660e489d94bb592cc42c526cb88b70b15d3452273d82bf12e1b67d7
```

```bash
node contract getPrice Store null ETH

PRICE OF [ETH]: $336.505 USD

node contract getLastUpdate Store

LastUpdate:1878626
```

So now we can get the last block number the prices were updated and we can use this in our external contracts to only execute functionality if we are dealing with current prices.

With the new contract populated with data, we are ready for legacy contracts to start interfacing with it instead of previous versions. To trigger this migration, we will call **setDescendant()** from the **Predecessor** to start passing **getPrice()** on to the latest version of **Store**:

```bash
node contract getDescendant Store previous

DESCENDANT:0x0000000000000000000000000000000000000000

node contract setDescendant Store previous
```

(Migration transaction on <a href="https://ropsten.etherscan.io/tx/0xf45124e60c86e9f26368f3cd05b185af4931464b6a3d26fed45e30fbd363a0d4" target="_blank">etherscan.io</a>)


```bash
node contract getDescendant Store previous

DESCENDANT:0xD0557B2c5A11F8B5F2635Bfa57dEb8dCF6021475
```

Fantastic, the predecessor **Store** now has a descendant **Store**, let's see if the **EthVsBch** contract is happy communicating with the new **Store**:

```bash
node contract getState EthVsBch

CURRENT WINNER: Result { '0': 'ETH', '1': '336505000000000' }
```

It is getting the latest price from the latest **Store** even with the old **Store** *hardcoded* in. Our migration is complete and the miner can continue updating only the latest **Store**.

Now, let's say the developer decides to implement the block number check in their **EthVsBch** contract:

```
pragma solidity ^0.4.11;

/*
A simple 'request oracle client' that needs to know the price of Eth and Bch
*/

//simple Store interface with just the function we need
contract Store{
  function getPrice(bytes32 _symbol) constant returns (uint) {}
  uint public lastUpdate;//--new public uint
}

contract EthVsBch {

    //string to hold source address of oracle
    address public source;

    function EthVsBch(address _source) {
      source = _source;
    }

    //anyone can get any price by symbol
    function whoIsWinning() constant returns (string,uint) { /*whenNotMigrating*/
      Store store = Store(source);

      //--- new piece that checks if the data is old
      uint lastUpdate = store.lastUpdate();
      if( lastUpdate < block.number-10 ){
        return ("OUTOFDATE",0);
      }

      uint priceOfEth = store.getPrice("ETH");
      uint priceOfBch = store.getPrice("BCH");
      if( priceOfEth > priceOfBch ){
        return ("ETH",priceOfEth);
      }else if ( priceOfEth < priceOfBch ){
        return ("BCH",priceOfBch);
      }else{
        return ("TIE!",priceOfEth);
      }
    }
}

```
Notice the developer is checking to see if the price information is 10 blocks stale and returning an error if so.

Let's compile and deploy this to test it out. Again, the developer will *hardcode* (bruh, isn't there a better way?) the new **Store** address:

```javascript
module.exports = ["0xD0557B2c5A11F8B5F2635Bfa57dEb8dCF6021475"]
```

```bash
node compile EthVsBch
node deploy EthVsBch
```

(Deployment transaction on <a href="https://ropsten.etherscan.io/tx/0xc9d574351948647bcf515605a90b0615f3ba1b5eed40b1d637b45f7e37a64a88" target="_blank">etherscan.io</a>)

```
0x8507E664d156d9deF72759F314629486783CDC49
```

With the new version of the contract out there, and no mining data posted to the **Store** for a while, we should get a message about stale data:

```bash
node contract getState EthVsBch

CURRENT WINNER: Result { '0': 'OUTOFDATE', '1': '0' }
```

Now let's run the miner and get that data updated:

```bash
node contract minePrice Store null BTC,ETH,XRP,BCH,LTC

** Calling price source url:https://api.coinmarketcap.com/v1/ticker/
** Symbols: [ 'BTC', 'ETH', 'XRP', 'BCH', 'LTC' ]
**== setting price for BTC to 5686980000000000
**== setting price for ETH to 338896000000000
**== setting price for XRP to 264500000000
**== setting price for BCH to 316889000000000
**== setting price for LTC to 65747699999999.99

BTC 0x5991181b611b27aea7ee199e80f8f94ecf25ff84846f5e8afe97404ed7c0d585
ETH 0xc1649f7d16562b796318ef3c34e69a97a66a0f0f804cc00aa2c7e36355750816
BCH 0x170fca205d15bf57227a38d09e903718a22461a6289f6760b05dd120902b93f7
LTC 0x492ad533be63dfe8892001f6cb9d7c64de03d95829c3ae7adb72c47db847939a
XRP 0xca81f55994fb58b30f1f7bcb42cb072ce5a6665f29e3710bd79577f3cf77f712
```


```bash
node contract getState EthVsBch

CURRENT WINNER: Result { '0': 'ETH', '1': '338896000000000' }
```

Neat! A full development cycle with both our contract and a developer's contract getting updated with new functionality without breaking the legacy versions.

