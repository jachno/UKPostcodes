<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * Name: postcode_model
 *
 * no licenese, do what you want with it.
 * 
  */

class postcode_model extends CI_Model {

    public function __construct() {
        
    }

    public function checkTable($tableName = 'postcodeData', $overwrite = FALSE) {

        if ($this->db->table_exists($tableName) == FALSE) {
                      //Table not found so create it;
            $this->createTable($tableName);
            return TRUE;
    
        } else {
            //Table present check for $overwrite == true param this will drop and recreate the table
          
        
        if ($overwrite == FALSE)
        {
            echo $tableName .' is present aborting, set overwrite to false in order to drop and reimport the data';
        
            return FALSE;
        }
        else 
            {
            //drop table and recreate
            print_r( 'Dropping table ' . $tableName);
            print_r ('<br>');
              $this->dropTable($tableName);
            print_r( 'Creating table structure for ' . $tableName);
              $this->createTable($tableName);
            print_r ('<br>');
            return TRUE;
            }
            
            
        }
    
}




public function createTable($tableName){
    
    
    //create the postcode table, I discard most the data in the Code Point files as I don't need it, if you do just add the coloms here and update the loadData function in the main lib file
      $this->db->query('   CREATE TABLE `' . $tableName . '` ( 
                `Postcode` varchar(12),
                `Eastings` int(11) DEFAULT NULL,
                `Northings` int(11) DEFAULT NULL,
                `Lat` decimal(18,12) DEFAULT NULL,
                `Long` decimal(18,12) DEFAULT NULL
                )ENGINE=InnoDB DEFAULT CHARSET=utf8;');
    
}



public function dropTable($tableName){
    
//used to clear the table if the $overwrite var is set    
      $this->db->query('drop TABLE `' . $tableName . '`');
    
}


public function insertbatch($tableName,$data)

    {
    //slices them in to batches of 100 insert statments
        $this->db->insert_batch($tableName, $data); 
    
    }

}
?>
