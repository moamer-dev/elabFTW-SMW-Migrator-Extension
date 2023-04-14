# elabFTW-SMW-Migrator-Extension
Mediawiki Extension to migrate the protocols data between from elabFTW to SMW.

The process of transferring data from elabFTW to SMW involves the use of a protocol template within the elabFTW platform. This template includes metadata that is also used in the SMW protocol template, as well as a link to transfer the protocol data to SMW. As a user of elabFTW, one would click on this link to initiate the transfer process. Once the transfer is complete, the SMW protocol page is automatically generated, and it includes a back-link to the original elabFTW protocol.


**The work stream for transferring data from elabFTW to SMW involves the following steps:**

Creating a generic test elabFTW template that includes some of the metadata used in the SMW protocol template and a link to the SMW special page. 
Developing an SMW extension that is called on the SMW special page for the purpose of migrating data.

### Extension info
* Backend:
    - php: 7.3 +

* Libraries:
    - [Addwiki](https://github.com/addwiki) 2.8.0
    - [PHP Simple HTML DOM Parser](https://simplehtmldom.sourceforge.io/docs/1.9/index.html)
    
    
* Dependencies:
    - [mediawiki-api](https://github.com/addwiki/mediawiki-api) 2.8.0 
    - [mediawiki-api-base](https://github.com/addwiki/mediawiki-api-base) 2.8.0 
    - [mediawiki-datamodel](https://github.com/addwiki/mediawiki-datamodel) 2.8.0
    
Please be aware that:
- Version 2.8.0 works with =< PHP 7.3
- Version 3.0.0 works with PHP 7.4 +

### Extension Functionalities

As a first step, these functionalities have been installed and activted:

- Collecting the elabFTW experiment ID from the user. 
- Checking if the ID exists on elabFTW:
 	- If it does not, return "Not Exist ID". 
	- If the ID exists, getting the experiment data and metadata:
		- Creating a protocol page for the experiment on SMW.
		- Return the protocol URL and display it to the user.
		- Return a back-link to the protocol on SMW and insert it into Experiment page on elabFTW.
	- If the experiment has been migrated before:
		- The correponding protocol content is updated.
		- Return the updated protocol URL and display it to the user. (The URL and Proocol name remain the same).

