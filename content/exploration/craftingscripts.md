---
title: "Crafting Scripts"
date: 2017-09-21T15:00:00-06:00
---
Instead of interacting with the network directly from the command line, we put together a handful of useful scripts to help abstract some of the functionality. There are also some prebuilt packages out there like <a href="http://truffleframework.com/" target="_blank">Truffle</a> and <a href="https://openzeppelin.org/" target="_blank">OpenZeppelin</a>.

---------------------------------------------------


First, let's get our environment prepared:

```bash
npm install solc web3
```

Let's initialize a few global variables:
```bash
echo "20" > gasprice.int
echo "300" > ethprice.int
echo "2000000" > deploygas.int
echo "200000" > xfergas.int
```

send.js
------------------
*sends ether from one account to another*

```javascript
const fs = require('fs');
const Web3 = require('web3');
const web3 = new Web3(new Web3.providers.HttpProvider("http://localhost:8545"));
console.log(" ### SEND")

let count = 1
if(process.argv[2]) count = process.argv[2]
let to = 2
let from = 1
let amount = 0.1
if(process.argv[2]){amount=process.argv[2]}
if(process.argv[3]){from=process.argv[3]}
if(process.argv[4]){to=process.argv[4]}

let gasPrice = fs.readFileSync("gasprice.int").toString().trim()
let gas = fs.readFileSync("xfergas.int").toString().trim()
let gaspricegwei = web3.utils.toWei(gasPrice,'gwei')

web3.eth.getAccounts().then((accounts)=>{
  let params = {
    from: accounts[from],
    to: accounts[to],
    value: web3.utils.toWei(amount, "ether"),
    gas: gas,
    gasPrice: gaspricegwei
  }
  console.log(params)
  web3.eth.sendTransaction(params,(error,transactionHash)=>{
    console.log(error,transactionHash)
    setInterval(()=>{
      web3.eth.getTransactionReceipt(transactionHash,(error,result)=>{
        if(result&&result.to&&result.from){
          console.log(result)
          process.exit(0);
        }else{
          console.log(".")
        }
      })
    },10000)
  })
})

```
compile.js
------------------
*compiles a contract*

```javascript
const fs = require('fs');
const solc = require('solc');
console.log(" ### COMPILE")

let startSeconds = new Date().getTime() / 1000;
let contractdir = process.argv[2]
let contractname = process.argv[3]
if(!contractname) contractname=contractdir
console.log("Compiling "+contractdir+"/"+contractname+".sol ["+solc.version()+"]...")
const input = fs.readFileSync(contractdir+"/"+contractname+'.sol');
if(!input){
  console.log("Couldn't load "+contractdir+"/"+contractname+".sol")
}else{
  let dependencies
  try{
    let path = "./"+contractdir+"/dependencies.js"
    if(fs.existsSync(path)){
      console.log("looking for dependencies at ",path)
      dependencies=require(path)
    }
  }catch(e){console.log(e)}
  if(!dependencies) dependencies={}
  dependencies[contractdir+"/"+contractname+".sol"] = fs.readFileSync(contractdir+"/"+contractname+".sol", 'utf8');
  const output = solc.compile({sources: dependencies}, 1);
  console.log(output)
  const bytecode = output.contracts[contractdir+"/"+contractname+".sol:"+contractname].bytecode;
  const abi = output.contracts[contractdir+"/"+contractname+".sol:"+contractname].interface;
  fs.writeFile(contractdir+"/"+contractname+".bytecode",bytecode)
  fs.writeFile(contractdir+"/"+contractname+".abi",abi)
  console.log("Compiled!")
}

```
deploy.js
------------------
*deploys a contract*

```javascript
const fs = require('fs');
const Web3 = require('web3');
const web3 = new Web3(new Web3.providers.HttpProvider("http://localhost:8545"));
console.log(" ### DEPLOY")

var ACCOUNT_INDEX = 1
var startSeconds = new Date().getTime() / 1000;
var contractdir = process.argv[2]
var contractname = process.argv[3]
if(!contractname) contractname=contractdir
var bytecode = fs.readFileSync(contractdir+"/"+contractname+".bytecode").toString()
var abi = false
if(!bytecode){
  console.log("Couldn't load "+contractdir+"/"+contractname+".bytecode")
}else{
  abi = JSON.parse(fs.readFileSync(contractdir+"/"+contractname+".abi"));
  if(!abi){
    console.log("Couldn't load "+contractdir+"/"+contractname+".abi")
  }else{
    var ethPrice = parseInt(fs.readFileSync("ethprice.int").toString().trim())
    web3.eth.getAccounts().then((accounts)=>{
      web3.eth.getBalance(accounts[ACCOUNT_INDEX]).then((balance)=>{
        if(balance < 10000000000000000000){
          web3.eth.personal.unlockAccount(accounts[1]).then((a,b,c)=>{
            deployContract(accounts,balance)
          })
        }else{
          deployContract(accounts,balance)
        }
      })
    })
  }
}

function deployContract(accounts,balance){
  let etherbalance = web3.utils.fromWei(balance,"ether");
  console.log(etherbalance+" $"+(etherbalance*ethPrice))
  console.log("\nLoaded account "+accounts[ACCOUNT_INDEX])
  console.log("Deploying...",bytecode,abi)
  let contract = new web3.eth.Contract(abi)
  let gasPrice = fs.readFileSync("gasprice.int").toString().trim()
  let gas = fs.readFileSync("deploygas.int").toString().trim()
  let gaspricegwei = gasPrice*1000000000
  console.log("paying a max of "+gas+" gas @ the price of "+gasPrice+" gwei ("+gaspricegwei+")")
  let contractarguments = []
  try{
    let path = "./"+contractdir+"/arguments.js"
    if(fs.existsSync(path)){
      console.log("looking for arguments in ",path)
      contractarguments=require(path)
    }
  }catch(e){console.log(e)}
  console.log("arguments:",contractarguments)
  let deployed = contract.deploy({
    data: "0x"+bytecode,
    arguments: contractarguments
  }).send({
    from: accounts[ACCOUNT_INDEX],
    gas: gas,
    gasPrice: gaspricegwei
  }, function(error, transactionHash){
    console.log("CALLBACK",error, transactionHash)
    setInterval(()=>{
      web3.eth.getTransactionReceipt(transactionHash,(error,result)=>{
        if(result && result.contractAddress && result.cumulativeGasUsed){
          console.log("Success",result)
          web3.eth.getBalance(accounts[ACCOUNT_INDEX]).then((balance)=>{
            let endetherbalance = web3.utils.fromWei(balance,"ether");
            let etherdiff = etherbalance-endetherbalance
            console.log("==ETHER COST: "+etherdiff+" $"+(etherdiff*ethPrice))
            console.log("Saving contract address:",result.contractAddress)
            let addressPath = contractdir+"/"+contractname+".address"
            if(fs.existsSync(addressPath)){
              fs.writeFileSync(contractdir+"/"+contractname+".previous.address",fs.readFileSync(addressPath).toString())
            }
            let headAddressPath = contractdir+"/"+contractname+".head.address"
            if(!fs.existsSync(headAddressPath)){
              fs.writeFileSync(headAddressPath,result.contractAddress)
            }
            fs.writeFileSync(addressPath,result.contractAddress)
            fs.writeFileSync(contractdir+"/"+contractname+".blockNumber",result.blockNumber)

            let endSeconds = new Date().getTime() / 1000;
            let duration = Math.floor((endSeconds-startSeconds))
            console.log("deploy time: ",duration)
            fs.appendFileSync("./deploy.log",contractdir+"/"+contractname+" "+result.contractAddress+" "+duration+" "+etherdiff+" $"+(etherdiff*ethPrice)+" "+gaspricegwei+"\n")
            process.exit(0);
          })
        }else{
          process.stdout.write(".")
        }
      })
    },1000)
  })
}

```
contract.js
------------------
*provides other scripts with an interface to contracts through abstraction*

```javascript
const fs = require('fs');
const Web3 = require('web3');
const web3 = new Web3(new Web3.providers.HttpProvider("http://localhost:8545"));
console.log(" ### CONTRACT")

var ACCOUNT_INDEX = 1
var startSeconds = new Date().getTime() / 1000;
var script = process.argv[2]
var contractdir = process.argv[3]
var contractname = process.argv[4]
if(!contractname || contractname=="null" ) contractname=contractdir
var address
var nextAddress
if(contractname=="previous" ){
  contractname=contractdir
  console.log("Reading for "+contractdir+"/"+contractname+".previous.address")
  address = fs.readFileSync(contractdir+"/"+contractname+".previous.address").toString().trim()
  nextAddress = fs.readFileSync(contractdir+"/"+contractname+".address").toString().trim()
}else{
  address = fs.readFileSync(contractdir+"/"+contractname+".address").toString().trim()
}
var blockNumber = 0
try{
   blockNumber = fs.readFileSync(contractdir+"/"+contractname+".blockNumber").toString().trim()
}catch(e){console.log(e)}
var abi = false
if(!address){
  console.log("Couldn't load "+contractdir+"/"+contractname+".address")
}else{
  abi = JSON.parse(fs.readFileSync(contractdir+"/"+contractname+".abi"));
  if(!abi){
    console.log("Couldn't load "+contractdir+"/"+contractname+".abi")
  }else{
    var ethPrice = parseInt(fs.readFileSync("ethprice.int").toString().trim())
    var gasPrice = fs.readFileSync("gasprice.int").toString().trim()
    var gas = fs.readFileSync("deploygas.int").toString().trim()
    var gaspricegwei = gasPrice*1000000000
    console.log("Loading accounts...")
    web3.eth.getAccounts().then((accounts)=>{
      web3.eth.getBalance(accounts[ACCOUNT_INDEX]).then((balance)=>{
        if(balance < 1000){
          web3.eth.personal.unlockAccount(accounts[1]).then((a,b,c)=>{
            interactWithContract(accounts,balance)
          })
        }else{
          interactWithContract(accounts,balance)
        }
      })
    })
  }
}

function interactWithContract(accounts,balance){
    console.log("Run script ",script," on ",contractname)
    let contract = new web3.eth.Contract(abi,address)

    console.log("paying a max of "+gas+" gas @ the price of "+gasPrice+" gwei ("+gaspricegwei+")")
    let scriptFunction
    try{
      let path = "./"+contractdir+"/"+script+".js"
      console.log("LOADING:",path)
      if(fs.existsSync(path)){
        console.log("looking for script at ",path)
        scriptFunction=require(path)
      }
    }catch(e){console.log(e)}
    if(scriptFunction){
      console.log("Loaded "+script+", running...")
      let params = {
        gas:gas,
        gasPrice:gaspricegwei,
        accounts:accounts,
        blockNumber:blockNumber,
      }
      //check for previous address to pass along
      let previousAddressFile = contractdir+"/"+contractname+".previous.address"
      if(fs.existsSync(previousAddressFile)){
        params.previousAddress = fs.readFileSync(previousAddressFile).toString()
      }
      if(nextAddress) params.nextAddress = nextAddress;
      console.log(params)
      params.web3=web3//pass the web3 object so scripts have the utils
      let scriptPromise = scriptFunction(contract,params,process.argv)
      if(!scriptPromise || typeof scriptPromise.once != "function"){
        console.log(""+script+" (no promise)")
      }
      else{
        let result = scriptPromise.once('transactionHash', function(transactionHash){
          console.log("transactionHash",transactionHash)
          setInterval(()=>{
            web3.eth.getTransactionReceipt(transactionHash,(error,result)=>{
              console.log(error,result)
              if(result){
                console.log(result.blockNumber,result.gasUsed)
              }
              if(result && result.blockNumber && result.gasUsed){
                console.log("Success",result)
                let etherdiff = result.gasUsed/100000000000000000
                console.log("==ETHER COST: "+etherdiff+" $"+(etherdiff*ethPrice))
                let endSeconds = new Date().getTime() / 1000;
                let duration = Math.floor((endSeconds-startSeconds))
                console.log("time: ",duration)
                fs.appendFileSync("./contract.log",contractdir+"/"+contractname+" "+script+" "+result.contractAddress+" "+duration+" "+etherdiff+" $"+(etherdiff*ethPrice)+" "+gaspricegwei+"\n")
                process.exit(0);
              }
            })
          },5000)
        })
      }
    }else{
      console.log("UNABLE TO LOAD SCRIPT "+script+" for "+contractname)
    }
}

```
personal.js
------------------
*reports current account balances and unlocks accounts*

```javascript
const fs = require('fs');
const Web3 = require('web3');
const web3 = new Web3(new Web3.providers.HttpProvider("http://localhost:8545"));
console.log(" ### PERSONAL")

let count = 1
if(process.argv[2]) count = process.argv[2]
web3.eth.getAccounts().then((accounts)=>{
  for(let i=0;i<count;i++){
    web3.eth.getBalance(accounts[i]).then((balance)=>{
      console.log(" ######### "+i+" # "+accounts[i]+" "+balance)
      try{
        web3.eth.personal.unlockAccount(accounts[i]).then((a,b,c)=>{
          console.log("unlocked "+i+": "+a)
        })
      }catch(e){console.log(e)}
    })
  }
})

```
