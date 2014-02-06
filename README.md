UKPostcodes
===========

CodeIgniter Library to upload the uk postcode data from OS and convert eastings and northings to Lat/Long and chuck it all in a database

Postcode data is avaialable from Ordance Survey

http://www.ordnancesurvey.co.uk/business-and-government/products/opendata-products.html

as of 6 Feb 2014 this file contains 1,684,884 records. On my 2009 Macbook pro 2.4ghz core duo it took around 25 minutes to upload.
Installation

Files in this release

UkPostcodes.php - Main codeingiter Lib file postcode_model.php - codeigniter model

These two files are derived from phpCoord written by Dr. Jonathan Stott http://www.jstott.me.uk/phpcoord/ I split the file in to the two following: phpCoordOSRef.php phpcoord.php

so I could access the classes in them, there might be away to access 2 classes in one file in codeigntier but I could not find it.

If you do use these in a comercial app then he has a license avaible on his site. These are only included for example.

Put:

UkPostcodes.php phpCoordOSRef.php phpcoord.php

in /Application/Libraries

put

postcode_model.php

in

/Application/Libraries
Usage

Fairly simple, with all the files in place. Unzip your postcode data file and get the path to the CSV Files

set the $postcodefiles in UKPostcodes to the csv file location

set the $postcode_table to the table name you want to use, it is set to postcodes by default

Option to set the $overwrite to TRUE, this will drop the existing table and recreate it. Use this is the import fails for any reason. I know I could spend ages writing a method to allow resumtion, but i can't be bothered.

then in a controller use the following

  $this->load->library('UkPostcodes');
  $this->ukpostcodes->loadData();
