<?php

/*

 */

/**
 * Description of UkPostcodes
 *
 * @author JaChNO
 * used to import postcode data from https://www.ordnancesurvey.co.uk/opendatadownload/products.html.
 */
class UkPostcodes
{
    
    public function __construct()
    {
        $this->CI =& get_instance();
        
        //I split the phpcoord in to two files so I could reference the classes, I could not find a way to ref the clases if they were in the same file.
        $this->CI->load->model('postcode_model');
        $this->CI->load->library('phpcoord');
        
        $this->CI->load->library('phpCoordOSRef');
        
        //removes max execution time limit just for this script. on a 2009 macbook pro 2.4 ghx core duo with 8gb's of ram a full import took
        //Load all files: 1,456.9061 or 24.2818 minutes.
        //Total Records Loaded: 1,684,884
        //Not sure how to increase performance, it might be quicker to import the file directly to mysql then do the lat/long cals.
        ini_set('max_execution_time', 0);
        
    }
    
    
    
    // Important: without changing the global timeout of 300 this will fail trying to process all of the data
    
    var $postcodefiles = '/Users/JamieNorman/Downloads/CSV'; //Location of the downloaded postcode CSV files; 		
    var $postcode_table = 'postcodes'; //TableName
    var $tableOverwrite = TRUE; // Set to true to reimport data, this is drop and recreate the table
    
    
    function loadData()
    {
        
        //Loads the datafiles from the specfied location, trims the " from the fields and sticks it all in the specfied table
        //Checks for the table specfied in the $postcode_table var if not present it creates it.
        
        
        //start the benchmark test
        $this->CI->benchmark->mark('code_start');
        $this->CI->benchmark->mark('table_start');
        if ($this->CI->postcode_model->checkTable($this->postcode_table, $this->tableOverwrite) == TRUE) {
            //Means table was not present or was dropped and recreated using the overwrite flag
            //Mark the end of the table phase
            $this->CI->benchmark->mark('table_end');
            
            
            echo 'Time to Check and or recreate the table: ' . $this->CI->benchmark->elapsed_time('table_start', 'table_end') . '<BR>';
            
            
            $this->CI->benchmark->mark('files_start');
            
            //grab all the files in the target Dir
            $pFiles = scandir($this->postcodefiles);
            
            $this->CI->benchmark->mark('files_end');
            
            
            echo 'Scan the files directory: ' . $this->CI->benchmark->elapsed_time('files_start', 'files_end') . '<BR>';
            
            
            $this->CI->benchmark->mark('load_all_start');
            $total = 0;
            
            foreach ($pFiles as $file) {
                //only deal with files with the csv extenstion
                
                $this->CI->benchmark->mark($file . '_start');
                
                
                $ext = substr($file, strrpos($file, '.') + 1);
                if (in_array($ext, array(
                    "csv"
                ))) {
                    
                    
                    //check we can open and read the file       
                    $row = 0;
                         
                   $insertArr = array();
                    if (($handle = fopen($this->postcodefiles . '/' . $file, "r")) !== FALSE) {
                        
                        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                            $num = count($data);
                            
                            //data should contain a row with data similar to the below string
                            //array ( 0 => 'AB101AA', 1 => '10', 2 => '394251', 3 => '806376', 4 => 'S92000003', 5 => '', 6 => 'S08000006', 7 => '', 8 => 'S12000033', 9 => 'S13002483', )
                            //for this libary we only care about the postcode, easting and northing so we need to grab items 0(postcode) 2(easting) and 3(northing) 
                            
                            $postcode = $data[0];
                            $easting  = $data[2];
                            $northing = $data[3];
    
                           $ret = $this->GetLatLong($data[2], $data[3]);
          
                           //strip the () off the returned string
                            $ret    = str_replace('(', '', $ret);
                           $ret    = str_replace(')', '', $ret);
                            //split the string in to an array so we can grab the two elements
                           $retArr = explode(',', $ret);
                       
                            //I know you could just pass this straight in to the array but this is for demo purposes only to help as a teaching aid
                            $lat = $retArr[0];
                            
                            $long = $retArr[1];
                            
                            
                            //build and array of data for the whole file
                            
                            $insertArr [$row]['postcode'] = $postcode;
                            $insertArr [$row]['eastings'] = $easting;
                            $insertArr [$row]['northings'] = $northing;
                            $insertArr [$row]['lat'] = $lat;
                            $insertArr [$row]['long'] = $long;
  
                            
                            $row++;
                    
                        }
                     
                          $this->CI->benchmark->mark($file . 'insert_start');
                          //pass the array to the batch inserter. this splits it up in to chunck to insert
                          $this->CI->postcode_model->insertbatch($this-> postcode_table,$insertArr);
                          $this->CI->benchmark->mark($file . 'insert_end');               //        echo $this->CI->postcode_model->bulk_insert_rows($this->postcode_table, array('Postcode', 'Eastings', 'Northings', 'Lat','Long'), $insertArr);

                 

                        $this->CI->benchmark->mark($file . '_end');
                        
                        echo '<br>';
                        echo 'File: ' . $file;
                        echo '<br>';
                        echo 'Record Count: ' . $row;
                        echo '<br>';
                        echo 'Insert Duration: ' . $this->CI->benchmark->elapsed_time($file . 'insert_start', $file . 'insert_end');
                        echo '<br>';
                        echo  ' File Duration: ' . $this->CI->benchmark->elapsed_time($file . '_start', $file . '_end') . '<BR>';
                        echo '<p>';
                        
                        
                        $total = $total + $row . '<br>';    
         fclose($handle);
                    }
                    
                    
                    
                    
                }
                 
               
            }
                $this->CI->benchmark->mark('load_all_end');
                echo 'Load all files: ' . $this->CI->benchmark->elapsed_time('load_all_start', 'load_all_end') . '<BR>';
                echo 'Total Records Loaded: ' . $total;
                $this->CI->benchmark->mark('code_end');
                echo 'Total execution time : ' . $this->CI->benchmark->elapsed_time('code_start', 'code_end') . '<BR>';
        }
    }
    
    
    
    
    
    
    
    
    function ConvertToLatLong()
    {
        //Converts all the easting and northings in the database to Lat Longs
    }
    
    function geoCodePostcode()
    {
        
    }
    
    function GetLatLong($easting, $northing)
    {
        
        //* I used www.gridreferencefinder.com to check the inputs vs the outputs to make sure it was acurate
        //set the osref class vars for easting and northing
        $this->CI->phpcoordosref->osref($easting, $northing);
        
        //call the class to convert the easting/northing to LatLong      
        $LatLng = $this->CI->phpcoordosref->toLatLng();
        
        //Set the phpcoord vars to the returned lat/longs I could pass them straight in but this makes easier reading
        $this->CI->phpcoord->latlng($LatLng[0], $LatLng[1]);
        //call the php coord to convert them from OSGB36 to WGS84. Black magic as far as I am concered but it make the plotting on google maps more accurate
        $this->CI->phpcoord->OSGB36ToWGS84();
        //call toString to return the updated values.
        return $this->CI->phpcoord->toString();
        
        
    }
    
}

?>
