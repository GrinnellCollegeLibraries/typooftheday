# About this repo
This application has two parts:
- a php script that searches Sierra DNA for either a partial or full misspelling in the title index
- a python script that takes the results from the search above and fixes said misspellings

**This is still a work in progress.** This script will use the API for MARC data export once it is released. This also only takes care of the 245 field. See the TODO list below for information about what other fields will be supported.

This script takes into account "transcribed" misspellings in both AACR2 (via [sic]) and RDA (via 246 $i).

# Required disclaimer
**Everything here is as-is. Run at your own risk.** It is not my fault that your computer rickrolls you after running one of the scripts below.

In addition, the queries found in this repo are based on the local setup that my library has with their ILS. YMMV with several of these queries, and it would be worthwhile to ask someone who is familiar with the local data setup if you are looking to run a query on a particular piece of data.

# Required 
- pymarc
- python (2.7.3)
- php
- access to Sierra DNA

# A Note About the Queries
This app uses various SQL queries I have constructed for retrieving data from Sierra's postgresql database (SierraDNA). The table structure, as well as what data lives where, is in the [CS Direct Techdocs section](http://techdocs.iii.com/sierradna/) (CS Direct login required). You also might want to consult the [Sierra help documentation about DNA](http://csdirect.iii.com/sierrahelp/Default.htm#ssql_direct_access.html) for more information (again, login required).

One recommendation from III is to use the EXPLAIN command to determine the system cost of a particular query. This is especially important when you are modifying any queries found under this folder. **You will lock up the system if you are not careful with your queries.**

# TODO
- flesh out readme file (metatodo?)
- edit python record to fix misspellings in the following fields
-- 246
-- 505 $t
- create option to create a results file without having it go through python script, in case misspelling is more complex than "search and replace"
