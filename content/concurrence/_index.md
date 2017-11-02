---
title: "Concurrence.js"
date: 2017-09-02T17:00:00-06:00
---
**Concurrence.js** is an open source Javascript library used to interact with the fleet of **Concurrence** smart contracts. For now, **Concurrence.js** requires that you already have <a href="https://github.com/ethereum/go-ethereum/wiki/geth" target="_blank">Geth</a> configured and running at a specific IPC or RPC location (http://localhost:8545 is the default). For more information on getting attached to <a href="https://github.com/ethereum/go-ethereum/wiki/geth" target="_blank">Geth</a>, read through the [provisioning](http://localhost:1313/exploration/provisioning/) section.

-------------------------------------------------------

### install

```bash
npm install concurrence
```

-------------------------------------------------------


### example


```Javascript
let concurrence = require("concurrence")
concurrence.init({DEBUG:true},(err)=>{
  console.log(concurrence.version)
});
```

```
0.0.1
```
-------------------------------------------------------

### add a request  

```Javascript
let request = { url: "https://api.coinmarketcap.com/v1/ticker/ethereum/" }
let protocol = "json.price_usd"
let combiner = "0x22530bf5e978bb88Bd36b914C19dE655605Bc1B8"
let callback = "0x0BAC8F1cF847F54bf8398e533Aa647a83869d14A"
let concurrence = require("concurrence")
concurrence.init({},(err)=>{
  concurrence.addRequest(combiner,request,protocol,callback).then((addResult)=>{
    console.log("TX:"+addResult.transactionHash)
    console.log(addResult.events.AddRequest.returnValues)
  })
});

```

-------------------------------------------------------

### simple miner

```Javascript
const Request = require('request');
let concurrence = require("concurrence")
concurrence.init({DEBUG:true},(err)=>{
  concurrence.selectAccount(1)
  concurrence.balanceOf().then((balance)=>{
    console.log("Current balance: "+balance)
    let stake = balance*0.1
    if(stake>100) stake=100;
    if(balance<=0){
      console.log("No token to stake")
      stake=0
    }else{
      console.log("Willing to stake "+stake)
      concurrence.listRequests().then((requests)=>{
        for(let r in requests){
          let id = requests[r].returnValues.id
          let combiner = requests[r].returnValues.combiner
          let request = requests[r].returnValues.request
          let protocol = requests[r].returnValues.protocol
          let callback = requests[r].returnValues.callback
          console.log("Found request "+id)
          console.log("First let's check if the combiner is ready...")
          concurrence.isCombinerReady(id,combiner).then((ready)=>{
            if(ready){
              console.log("Combiner "+combiner+" is ready to combine.")
              concurrence.combine(id,combiner).on('error',(err)=>{
                if(err){
                  console.log(err)
                }
              }).then((result)=>{
                console.log("COMBINE DONE",result)
                console.log("Getting new state...")
                concurrence.getCombinerMode(id,combiner).then((mode)=>{
                  console.log("Combiner Mode: ",mode)
                })
              })
            }else{
              console.log("Combiner "+combiner+" is not ready to combine, let's see if it's open...");
              concurrence.isCombinerOpen(id,combiner).then((open)=>{
                if(open){
                  console.log("Combiner open, mining request "+request)
                  try{
                    request = JSON.parse(request)
                    console.log(request)
                    Request(request.url,(error, response, body)=>{
                      console.log("Adding Response:",body)
                      if(response.statusCode == 200 && body.length>0 && body.length<50){
                        console.log("Looks good enough...")
                        concurrence.addResponse(id,body).then((result)=>{
                          console.log("TX:"+result.transactionHash)
                          console.log(result.events.AddResponse.returnValues)
                          console.log("Now we need to stake some amount of token on our answer...")
                          let responseId = result.events.AddResponse.returnValues.id
                          concurrence.stake(id,responseId,stake).then((result)=>{
                            console.log(result)
                          })
                        })
                      }else{
                        console.log("Skipping response because something seems wrong...")
                      }
                    })
                  }catch(e){console.log(e)}
                }else{
                  console.log("Combiner is closed.")
                }
              })
            }
          })
        }
      })
    }
  })
});

```
