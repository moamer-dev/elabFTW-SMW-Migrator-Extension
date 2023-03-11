# elabFTW-SMW-Migrator-Extension
Mediawiki Extension to migrate the protocols data between from elabFTW to SMW.

The process of transferring data from elabFTW to SMW involves the use of a protocol template within the elabFTW platform. This template includes metadata that is also used in the SMW protocol template, as well as a link to transfer the protocol data to SMW. As a user of elabFTW, one would click on this link to initiate the transfer process. Once the transfer is complete, the SMW protocol page is automatically generated, and it includes a back-link to the original elabFTW protocol.


**The work stream for transferring data from elabFTW to SMW involves the following steps:**

Creating a generic test elabFTW template that includes some of the metadata used in the SMW protocol template and a link to the SMW special page. 
Developing an SMW extension that is called on the SMW special page for the purpose of migrating data.

## The main functionalities of the this extension are as follows:

* Front-end:
  - Collecting the elabFTW experiment ID from the user. 
  - Checking if the ID exists in elabFTW, and if it does not, returning "Not Exist ID".
  - A validation for the already migrated experiments. If the requested experiment has been already migrated this message is displayed returning the experiment link on SMW (Your experiment with ID = x has been migrated before. You can access it on SMW through this Link).  
  - If the ID exists, getting the experiment data and metadata. 
  - Creating the protocol page and its record on SMW. 
  - Returning a link to the protocol on SMW and a back-link to elabFTW if the protocol page and its record are created successfully. 

* Back-end:
  - Creating a Protocol assigned to a predefined protocol type on SMW using the data of the experiment. 
  - Creating a record for the protocol using the metadata of the experiment. 
  - Creating a reference link to the elabFTW experiment on the protocol page. 
  - Creating an SMW protocol backlink that is ingested to the elabFTW experiment. 
