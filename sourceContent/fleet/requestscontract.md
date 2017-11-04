---
title: "Requests Contract"
date: 2017-09-21T07:00:00-06:00
---
The **Requests** contract is the datastore for requests that signal miners. Developers and external contracts call the **addRequest()** function and then **reserve()** tokens behind that request to incentivize miners. The **request** *(string)* can be anything and it's up to the miners to perform different tasks based on the **protocol** *(bytes32)*. **Responses** are then aggregated in the **combiner** *(address)* contract and delivered to the **callback** *(address)* contract.

<img src="/images/requests.png" width="100%"/>

<!--RQC CODE solidity Requests/Requests.sol -->

Current address ( http://relay.concurrence.io/address/Requests ):
<!--RQC ADDRESS Requests/Requests.address -->

Current ABI ( http://relay.concurrence.io/abi/Requests ):
<!--RQC ABI Requests/Requests.abi -->
