---
title: "Linked Lists"
date: 2017-09-21T11:00:00-06:00
---

We need an efficient structure to store a large number of entries that we can quickly traverse. The **mapping** data type in solidity is wonderful for storing information, but very hard to look through if you don't already know the index of each item. That's where a linked list comes in handy because you store an index to the next piece of data in the current piece of data like a chain. When we think back to our computer science classes, we know that to make *writes* our most efficient operation, we want to add items to the front of the list by keeping track of a **head** index. When we want to add a new element we put the existing **head** index in as the new element's **next** index and then set the **head** to the new element. This operation costs the same computational amount for a list of any size. When we want to look through the data, we start at the **head** index and follow the trail of **next** indexes.

Let's create a contract called **LinkedList** that stores a list of **Objects** that are all linked together. An **Object** is a *struct* data type that holds an index to the **next** object along with a **name** and a **number**. For demonstration purposes, let's say this contract is meant to hold voting information from small, fictitious districts where **name** is a candidate's name and **number** is the number of votes that candidate received in the district.

```
pragma solidity ^0.4.11;

contract LinkedList {

  event AddEntry(bytes32 head,uint number,bytes32 name,bytes32 next);

  uint public length = 0;//also used as nonce

  struct Object{
    bytes32 next;
    uint number;
    bytes32 name;
  }

  bytes32 public head;
  mapping (bytes32 => Object) public objects;

  function LinkedList(){}

  function addEntry(uint _number,bytes32 _name) public returns (bool){
    Object memory object = Object(head,_number,_name);
    bytes32 id = sha3(object.number,object.name,now,length);
    objects[id] = object;
    head = id;
    length = length+1;
    AddEntry(head,object.number,object.name,object.next);
  }

  //needed for external contract access to struct
  function getEntry(bytes32 _id) public returns (bytes32,uint,bytes32){
    return (objects[_id].next,objects[_id].number,objects[_id].name);
  }


  //------------------ totalling stuff to explore list mechanics 

  function total() public constant returns (uint) {
    bytes32 current = head;
    uint totalCount = 0;
    while( current != 0 ){
      totalCount = totalCount + objects[current].number;
      current = objects[current].next;
    }
    return totalCount;
  }

  function setTotal() public returns (bool) {
    writtenTotal = total();
    return true;
  }

  function resetTotal() public returns (bool) {
    writtenTotal = 0;
    return true;
  }

  uint public writtenTotal;

}

```
Again, we will skip the compile, deploy, and scripting mechanics assuming readers have followed along from [previous explorations](/exploration/deployingacontract).

```bash
node compile LinkedList
node deploy LinkedList

0xFD400Ff1b9f23b105386350309C0616A50c969bE
```

Then, let's populate the contract with a bunch of transactions similar to:

```bash
node contract addEntry LinkedList null 93 "Bram"
```
(This entry says candidate **Bram** received **93** votes.)


Let's have a bunch of districts report in with their results:
```bash
node contract addEntry LinkedList null 26 "Hal"
node contract addEntry LinkedList null 23 "Julian"
node contract addEntry LinkedList null 27 "Hal"
node contract addEntry LinkedList null 33 "Eva"
node contract addEntry LinkedList null 23 "Julian"
node contract addEntry LinkedList null 42 "Eva"
node contract addEntry LinkedList null 34 "Hal"
node contract addEntry LinkedList null 12 "Julian"
node contract addEntry LinkedList null 57 "Hal"
```

Then, we can run our **getTotal()** function to count the total votes:

```bash
node contract getTotal LinkedList

TOTAL:370
```

It is free for us to run the getTotal off-chain, but if we need to change the state of the contract we will have to pay for it in gas. Let's explore the gas costs a little more to figure out what we are dealing with.

Let's run the **setTotal()** function which will run the **getTotal()** just like we did off-chain, but then it will write it to the **writtenTotal** *uint*. Because there is a change of state, every single contract in the network will have to iterate through our linked-list and deterministically reach the same total. This will cost us some gas:

```bash
node contract setTotal LinkedList
```

(Transaction on <a href="https://ropsten.etherscan.io/tx/0xb09dcff01d528aa00dd1cee5703dee4fc8ee6383afc310cedd279f13614dc9c0" target="_blank">etherscan.io</a>)

To run through the list and keep track of a total and eventually write that total to state, it cost **0.001034044** ether.

(See all 349 operations on <a href="https://ropsten.etherscan.io/vmtrace?txhash=0xb09dcff01d528aa00dd1cee5703dee4fc8ee6383afc310cedd279f13614dc9c0" target="_blank">etherscan.io</a>)

Now, if we change the same state without traversing the list using the **resetTotal()** function, let's see what that costs:

```bash
node contract resetTotal LinkedList
```

(Transaction on <a href="https://ropsten.etherscan.io/tx/0x8fc15b5fe628b203323d0fe6c9f43bbf931066bfcdaf34f4185e83181a5a88d9" target="_blank">etherscan.io</a>)

Wow, that was cheap, only **0.000292974** ether. So maybe we can subtract those two numbers and probably figure out how much it costs to traverse the list?

In USD, assuming ETH is about $300, paying 22 gwei in gas, it's about **$0.09** to set the value of our uint and about **$0.22** to traverse our list of 10 entries keeping a running total. Looking back at adding an entry, at the same rate, it cost about **$0.63**.

--------------------------------------------------------------------------------------

Keeping *separation of concerns* in mind, let's create a second contract that will interact with the **LinkedList** contract instead of writing the functionality directly into the **LinkedList** contract. This contract will be called the **Teller** and will count the votes once a **quorum** is reached.

```
pragma solidity ^0.4.11;

//simple interface to the LinkedList
contract LinkedList {
  struct Object{ bytes32 next;uint number;bytes32 name;}
  bytes32 public head;
  mapping (bytes32 => Object) public objects;
  function total() public constant returns (uint) {}
  function getEntry(bytes32 _id) public returns (bytes32,uint,bytes32){}
}

contract Teller {

  uint16 public quorum = 400;
  bytes32 public winningName;
  uint public winningVotes;

  enum States {
        WaitingForQuorum,
        CountingVotes,
        ElectionFinished
  }

  States public state = States.WaitingForQuorum;

  function Teller(){ }

  bytes32 public currentPointer;
  mapping (bytes32 => uint) public totals;
  uint public counted=0;

  function countVotes(address _linkedListAddress) public returns (bool){
    LinkedList linkedList = LinkedList(_linkedListAddress);
    if( state == States.WaitingForQuorum ){
      require( linkedList.total() >= quorum );
      state = States.CountingVotes;
      return true;
    }else if( state == States.CountingVotes ){
      if( currentPointer==0 ){
        currentPointer=linkedList.head();
      }
      uint8 limitPerTurn = 4;
      while ( limitPerTurn-- > 0 && currentPointer!=0 ){
        uint thisNumber;
        bytes32 thisName;
        (currentPointer,thisNumber,thisName) = linkedList.getEntry(currentPointer);
        totals[thisName] += thisNumber;
        if(totals[thisName] > winningVotes){
          winningVotes=totals[thisName];
          winningName=thisName;
        }
        counted++;
      }
      if( currentPointer==0 ){
        state = States.ElectionFinished;
        return true;
      }else{
        return false;
      }
    }else{
      revert();
    }
  }
}

```
Using the *enum* data type we can code the Teller to perform like a state machine. Notice how the **countVotes()** function changes the state as it detects new conditions. The most important concept here is the **limitPerTurn**. Because of gas limits, we won't be able to traverse the entire list in one transaction. What we have to do is move through part of it and then keep track of the running totals between transactions. In production, we will use the **msg.gas** variable, but for demonstration purposes, we will just count four at a time.

```bash
node compile Teller
node deploy Teller

0x3c67a0e63a967810fcC5e48F9a94c6D561D9a7cd
```

The current **getTotal()** of the **LinkedList** contract is returning **370**. So if we run the **countVotes()** function against the **LinkedList** contract address, the **Teller** should stay in state **0**:

```bash
node contract countVotes Teller null 0xFD400Ff1b9f23b105386350309C0616A50c969bE
node contract getState Teller

STATE:0
```

Let's throw in one last vote count to push the total past the require **quorum** of **400**:

```bash
node contract addEntry LinkedList null 32 "Bram"
node contract getTotal LinkedList

TOTAL:402
```

Now, when we trigger the **countVotes()** function, the state should change to **1** which represents the state of **CountingVotes**:

```bash
node contract countVotes Teller null 0xFD400Ff1b9f23b105386350309C0616A50c969bE
node contract getState Teller

STATE:1
```

Let's inspect a few variables in the **Teller** contract before we start tallying votes:

```bash
node contract getCounted Teller
COUNTED:0

node contract getCurrentPointer Teller
CURRENTPOINTER:0x0000000000000000000000000000000000000000000000000000000000000000
```

Now let's fire off the first round of vote counting which should total up the first four votes:

```bash
node contract countVotes Teller null 0xFD400Ff1b9f23b105386350309C0616A50c969bE

node contract getCounted Teller
COUNTED:4

node contract getCurrentPointer Teller
CURRENTPOINTER:0xa286649c24c2fe84cceb42001867f0d66be3fcc1e9612f9974ed74d6fb86375f
```

We can also see who is in the lead after the first four votes are totaled but since the state is still **CountingVotes**, we know the election isn't finished:

```bash
node contract getWinningName Teller
WINNINGNAME:Hal

node contract getWinningVotes Teller
WINNINGVOTES:91

node contract getState Teller
STATE:1
```

Let's finish the election off by running the **countVotes()** function until the state changes to **2** (**ElectionFinished**):

```bash
node contract countVotes Teller null 0xFD400Ff1b9f23b105386350309C0616A50c969bE
node contract countVotes Teller null 0xFD400Ff1b9f23b105386350309C0616A50c969bE

node contract getState Teller
STATE:2
```

Now we can be confident that we have selected our true winner and our election is finished:

```bash
node contract getWinningName Teller
WINNINGNAME:Hal

node contract getWinningVotes Teller
WINNINGVOTES:144

node contract getTotal Teller null Bram
TOTAL [Bram]:125

node contract getTotal Teller null Eva
TOTAL [Eva]:75

node contract getTotal Teller null Julian
TOTAL [Julian]:58
```

This linked list and partial traversing concept will be the cornerstone of how we will draw a consensus on the blockchain. Miners will make requests off-chain and post their results on-chain. Then, we will iterate through a list adding up "staked" tokens with respect to their results to find the best answer.  

