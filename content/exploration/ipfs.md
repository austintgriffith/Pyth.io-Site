---
title: "IPFS"
date: 2017-09-21T10:00:00-06:00
---
When building a fully decentralized system, a peer-to-peer network is essential. No centralized server (or even just distributed network) can be relied upon. To move files from one node to another, we will use **<a href="https://ipfs.io" target="_blank">IPFS</a>**. The javascript implementation and documentation is located <a href="https://github.com/ipfs/js-ipfs" target="_blank">here</a>.

First, let's install the npm package:

```bash
npm install ipfs --save
```

Next, let's download the image **QmW2WQi7j6c7UgJTarActp7tDNikE4B2qXtFCfLPdsgaTQ/cat.jpg** that has come to represent IPFS demos:

```javascript
const IPFS = require('ipfs')
const ipfs = new IPFS()
const fs = require("fs")
ipfs.on('ready', () => {
  let multihashStr = "QmW2WQi7j6c7UgJTarActp7tDNikE4B2qXtFCfLPdsgaTQ/cat.jpg"
  console.log("Getting "+multihashStr)
  ipfs.files.get(multihashStr, function (err, stream) {
    stream.on('data', (file) => {
      var writeStream = fs.createWriteStream(file.path);
      file.content.pipe(writeStream).on('finish', function () {
        console.log("content written to "+file.path)
        ipfs.stop(() => {
          console.log("DONE")
          process.exit(0)
        })
      })
    })
  })
})
ipfs.on('error', (err) => {
  console.log("ipfs error:",err)
})

```
```bash
node getCat.js

Swarm listening on /ip4/127.0.0.1/tcp/4003/ws/ipfs/QmPtcBsioKv9hZXnJrneye5Vc2qxu3pJnn4xczVReSA6fC
Swarm listening on /ip4/127.0.0.1/tcp/4002/ipfs/QmPtcBsioKv9hZXnJrneye5Vc2qxu3pJnn4xczVReSA6fC
Swarm listening on /ip4/172.31.9.222/tcp/4002/ipfs/QmPtcBsioKv9hZXnJrneye5Vc2qxu3pJnn4xczVReSA6fC
Getting QmW2WQi7j6c7UgJTarActp7tDNikE4B2qXtFCfLPdsgaTQ/cat.jpg
content written to cat.jpg
```

And now the content of cat.jpg is:

<img src="https://ipfs.io/ipfs/QmW2WQi7j6c7UgJTarActp7tDNikE4B2qXtFCfLPdsgaTQ/cat.jpg" />

Note, the above image is actually being displayed through an ipfs bridged:

```
https://ipfs.io/ipfs/QmW2WQi7j6c7UgJTarActp7tDNikE4B2qXtFCfLPdsgaTQ/cat.jpg
```

Let's try adding some content of our own:

```javascript
const IPFS = require('ipfs')
const ipfs = new IPFS()
const fs = require("fs")
ipfs.on('ready', () => {
  let filepath = process.argv[2];
  console.log("Adding "+filepath)
  const files = [
    {
      path: filepath,
      content: fs.createReadStream(filepath)
    }
  ]
  ipfs.files.add(files, function (err, files) {
    console.log("ADDED!",err,files)
  })
})
ipfs.on('error', (err) => {
  console.log("ipfs error:",err)
})

```
```bash
echo "Can you smell what the rock is cooking?" > quote.txt

node putFile.js quote.txt
```

```bash
Swarm listening on /ip4/127.0.0.1/tcp/4003/ws/ipfs/QmPtcBsioKv9hZXnJrneye5Vc2qxu3pJnn4xczVReSA6fC
Swarm listening on /ip4/127.0.0.1/tcp/4002/ipfs/QmPtcBsioKv9hZXnJrneye5Vc2qxu3pJnn4xczVReSA6fC
Swarm listening on /ip4/172.31.9.222/tcp/4002/ipfs/QmPtcBsioKv9hZXnJrneye5Vc2qxu3pJnn4xczVReSA6fC
Adding quote.txt
ADDED! null
[
  {
    path: 'quote.txt',
    hash: 'QmcCcXYyNBzDH9ZqH72Fv3FmpoRvq5Q5mUNerJdupGMVuv',
    size: 48
  }
]
```

Now we should be able to get that file back with:

```javascript
const IPFS = require('ipfs')
const ipfs = new IPFS()
const fs = require("fs")
ipfs.on('ready', () => {
  let multihashStr = process.argv[2];
  console.log("Getting "+multihashStr)
  ipfs.files.get(multihashStr, function (err, stream) {
    stream.on('data', (file) => {
      file.content.pipe(process.stdout)
    })
  })
})
ipfs.on('error', (err) => {
  console.log("ipfs error:",err)
})

```
```bash
node getFile QmcCcXYyNBzDH9ZqH72Fv3FmpoRvq5Q5mUNerJdupGMVuv
```

```bash
Swarm listening on /ip4/127.0.0.1/tcp/4003/ws/ipfs/QmPtcBsioKv9hZXnJrneye5Vc2qxu3pJnn4xczVReSA6fC
Swarm listening on /ip4/127.0.0.1/tcp/4002/ipfs/QmPtcBsioKv9hZXnJrneye5Vc2qxu3pJnn4xczVReSA6fC
Swarm listening on /ip4/172.31.9.222/tcp/4002/ipfs/QmPtcBsioKv9hZXnJrneye5Vc2qxu3pJnn4xczVReSA6fC
Getting QmcCcXYyNBzDH9ZqH72Fv3FmpoRvq5Q5mUNerJdupGMVuv
Can you smell what the rock is cooking?
```

Cool, but that is just a local get, we need to test out global connectivity. On a different machine from a different network let's run:

```bash
node getFile.js QmcCcXYyNBzDH9ZqH72Fv3FmpoRvq5Q5mUNerJdupGMVuv

Swarm listening on /ip4/127.0.0.1/tcp/4003/ws/ipfs/QmNQnWcuDPcnNnL8ZpninnM5gxyp9FAHhZ81bisNHZ23Rx
Swarm listening on /ip4/127.0.0.1/tcp/4002/ipfs/QmNQnWcuDPcnNnL8ZpninnM5gxyp9FAHhZ81bisNHZ23Rx
Swarm listening on /ip4/10.230.36.84/tcp/4002/ipfs/QmNQnWcuDPcnNnL8ZpninnM5gxyp9FAHhZ81bisNHZ23Rx
Getting QmcCcXYyNBzDH9ZqH72Fv3FmpoRvq5Q5mUNerJdupGMVuv
```

It hangs there forever. Meaning, machine #2 can't see machine #1 and therefore can't get the file. What's interesting, is that we can visit <a href="https://ipfs.io/ipfs/QmcCcXYyNBzDH9ZqH72Fv3FmpoRvq5Q5mUNerJdupGMVuv" target="_blank">https://ipfs.io/ipfs/QmcCcXYyNBzDH9ZqH72Fv3FmpoRvq5Q5mUNerJdupGMVuv</a> and machine #2 will immediately receive the file it was looking for. But that means we are still relying on the centralized gateway server to move the content for us. Until the IPFS team can implement connection forwarding, this protocol is quite limited.

One thing we can try is linking the machines together manually. Let's create a file on machine #1 again:

```bash
echo "This is another file" > another.txt
node putFile.js another.txt

Swarm listening on /ip4/127.0.0.1/tcp/4003/ws/ipfs/QmPtcBsioKv9hZXnJrneye5Vc2qxu3pJnn4xczVReSA6fC
Swarm listening on /ip4/127.0.0.1/tcp/4002/ipfs/QmPtcBsioKv9hZXnJrneye5Vc2qxu3pJnn4xczVReSA6fC
Swarm listening on /ip4/172.31.9.222/tcp/4002/ipfs/QmPtcBsioKv9hZXnJrneye5Vc2qxu3pJnn4xczVReSA6fC
Adding another.txt
ADDED! null [ { path: 'another.txt',
    hash: 'QmPoyokqso3BKYCqwiU1rspLE59CPCv5csYhcPkEd6xvtm',
    size: 29 } ]
```

Then, on machine #2 we'll write a script that connects to a custom machine and then runs the get:

```javascript

```
```bash
node connectAndGet.js /ip4/18.221.254.251/tcp/4002/ipfs/QmPtcBsioKv9hZXnJrneye5Vc2qxu3pJnn4xczVReSA6fC QmPoyokqso3BKYCqwiU1rspLE59CPCv5csYhcPkEd6xvtm

Swarm listening on /ip4/127.0.0.1/tcp/4003/ws/ipfs/QmNQnWcuDPcnNnL8ZpninnM5gxyp9FAHhZ81bisNHZ23Rx
Swarm listening on /ip4/127.0.0.1/tcp/4002/ipfs/QmNQnWcuDPcnNnL8ZpninnM5gxyp9FAHhZ81bisNHZ23Rx
Swarm listening on /ip4/10.230.36.84/tcp/4002/ipfs/QmNQnWcuDPcnNnL8ZpninnM5gxyp9FAHhZ81bisNHZ23Rx
Connected?
Getting QmPoyokqso3BKYCqwiU1rspLE59CPCv5csYhcPkEd6xvtm
This is another file
```

Okay, if we do all the networking ourselves it works fine, but that defeats to point.

-----------------------------------------------------------------

Let's build a full <a href="https://ipfs.io" target="_blank">IPFS</a> node from scratch instead of using the javascript abstraction and see if we can poke around a little more. 

