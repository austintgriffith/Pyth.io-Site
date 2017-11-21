---
title: "Contract Lineage"
date: 2017-09-21T19:00:00-06:00
---
Inevitably, code will need to be upgraded as vulnerabilities are exposed and new functionality is needed.

The best strategy to provide upgradability is using many different, interconnected contracts that each have a simple job within a larger system. For example, one contract will be used to store requests and another will manipulate them. This allows for new functionality to be added to the manipulator while keeping the datastore intact.

Along with this "microservice" architecture, the [main contract](/fleet/maincontract/) will need to have lineage. As off-chain code interacts with contracts, they will follow a tree of ancestry to the most current descendant.
