---
title: "Linked Lists"
date: 2017-09-21T11:00:00-06:00
---

We need an efficient structure to store a large number of entries that we can quickly traverse. The **mapping** data type in solidity is wonderful for storing information, but very hard to look through if you don't already know the index of each item. That's where a linked list comes in handy because you store an index to the next piece of data in the current piece of data like a chain. When we think back to our computer science classes, we know that to make *writes* our most efficient operation, we want to add items to the front of the list by keeping track of a **head** index. When we want to add a new element we put the existing **head** index in as the new element's **next** index and then set the **head** to the new element. This operation costs the same computational amount for a list of any size. When we want to look through the data, we start at the **head** index and follow the trail of **next** indexes.

Let's create a contract called **LinkedList** that stores a list of **Objects** that are all linked together. An **Object** is a *struct* data type that holds an index to the **next** object along with a **name** and a **number**. For demonstration purposes, let's say this contract is meant to hold voting information from small, fictitious districts.

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
