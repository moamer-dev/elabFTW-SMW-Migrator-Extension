<?php
/**
 * Class for the page Special:AdminLinks
 *
 * @author Yaron Koren
 */
//require_once(__DIR__ . '/vendor/autoload.php');

error_reporting(E_ERROR | E_WARNING | E_PARSE);

class ElabMigrator extends SpecialPage {

    
	/**
	 * Constructor
	 */

     
	function __construct() {
		parent::__construct( 'ElabMigrator' );

        //echo "elabFTW";
	}

    public $array;
    public $metadata_arr = [];
    public $keys_arr = [];
    public $meta_extra_fields = [];
    public $meta_extra_fields_value = "";
    public $p_name;
    public $loop_string_values;
    public $loop_array = [];
    public $protocol_content;
    public $id;
    public $codes;
    public $newelabidArray;
    public $expTable;
    public $expBody;
    public $values;
    public $htmlBody;
    public $expTags;
    public $elaburlArray;

    public function execute( $par ) {
        global $wgOut;
        global $array;
        global $metadata_arr;
        global $keys_arr;
        global $meta_extra_fields;
        global $meta_extra_fields_value;
        global $p_name;
        global $loop_string_values;
        global $loop_array;
        global $protocol_content;
        global $id;
        global $elaburlArray;
        global $expTable;
        global $expBody;
        global $values;
        global $htmlBody;
        global $expTags;
        

		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();

		# Get request data from, e.g.
		$param = $request->getText( 'param' );



        $wgOut->addHTML( '
                        <h2 >GET EXPERIMENT DETAILS</h2>
                        <p>Please Type the experiment ID and press "Get Details"</p>
                        
                    
                        <form method="post" class="row row-cols-lg-auto g-3" >
                            <div class="col">
                                <input id= "exp-id" name="id" type="text" class="form-control" value = "" placeholder="Please Enter the Experiment ID" aria-label="Please Enter the Experiment ID" aria-describedby="button-addon2" required>
                            </div>
                            <div class="col">
                                <button type="submit" class="btn btn-primary waves-effect waves-light">Get details</button>
                            </div>              
                        </form>'
                     );

        
        $getter = new ElabMigrator();


        if (isset($_POST['id'])) {

            $id = $_POST['id'];
            $experimentURL = '#'.$id;
            $wikiURL = '#';
            //$getter->elabFTW_GET($_POST['id']);
            
            $getter->tablesRows($_POST['id']);
            $getter->elaburlGetter();



            foreach ($elaburlArray as $title => $url){
                if ($url == $experimentURL){
                    $isExist = true;
                    $getter->update_protocol_page($title); 
                    $wgOut->addHTML( '
                        <div>
                            <p style="color:red">
                            The experiment with ID = '.$_POST['id'].' has been migrated before. <span style="color:green">Therefore, its content has been updated</span></p>'.
                            '<p>You can access it on SMW through this <a href="'.$wikiURL.$title.'" target="_blank">LINK</a>'.'<br> 
                        </div>'
                        );
                       
                }
            }
            if(!empty($array['title']) &&  $isExist != true ){
            
                $getter->new_protocol_page();
                //$getter->new_record_loop();
                //$getter->new_record_page();
                $getter->elabFTW_POST($_POST['id']);
                
                $wgOut->addHTML( '
                <div>
                    <p style="color:green">
                    Your Protocol with ID = '.$_POST['id'].' has been successfully created.</p> '.
                    '<p>You can access it on SMW through this <a href="#'.$p_name.'">LINK</a>'.'<br>'
                    .'<a href="https://#/experiments.php?mode=view&id='.$_POST['id'].'">Back to elabFTW Expirement Page</a>
                    </p>
                </div>'
                );
            } 
    
            else if (empty($array['title']) &&  $isExist != true){
                $wgOut->addHTML('<h5 style="color:red">Experiment is Not Exist</h5>');
            }
        }
	}

    private function tablesRows($x){

        global $expTable;
        global $expBody;
        global $array;
        global $htmlBody;
        global $expTags;

        $curl = curl_init();
        curl_setopt_array($curl, [
        CURLOPT_URL => "https://#/api/v1/experiments/${x}?format=json",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
                "Authorization: #",
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            
            $data = json_decode($response, TRUE);
            $array = $data;
            $html = $array['body'];
            $htmlBody = $html;
            $tags = $array['tags'];
            $expTags = $tags;
           


            if(!empty($array['title'])){
                $metadata = json_decode($data['metadata'], TRUE);
                global $metadata_arr;
                $metadata_arr = $metadata;

                $keys = $metadata['extra_fields'];
                global $keys_arr;
                $keys_arr = $keys;

                $meta_fields = $metadata['extra_fields'];
                global $meta_extra_fields;
                $meta_extra_fields = $meta_fields;
            }
            
        }

        include_once('simple_html_dom.php');

        $dom = str_get_html($html);

        // Extract the Experiment Body
        $bodyContent = array();
        $bodyText = "";

        if(!empty($array['title'])){
            foreach($dom->find('h1') as $h1) {
                if ($h1->parent()->tag != 'td') {
                    $bodyContent[] = $h1->plaintext;
                    $bodyText .= strip_tags($h1) . "\n" ;
                }
            }
        }
        $expBody = $bodyText;

        // Loop through each table and extract the data
        $tableData = array();
        if(!empty($array['title'])){
            foreach ($dom->find('table') as $table) {
                $rowData = array();
                $rows = $table->find('tr');
                foreach ($rows as $row) {
                    $cellData = array();
                    $cells = $row->find('td');
                    foreach ($cells as $cell) {
                        $cellData[] = $cell->plaintext;
                    }
                    $rowData[] = $cellData;
                }
                $tableData[] = $rowData;

                foreach ($tableData as &$level1) {
                    foreach ($level1 as &$level2) {
                        foreach ($level2 as &$value) {
                            // Trim the value and overwrite the original value
                            $value = trim($value);
                        }
                    }
                }

            }
        }

        $expTable = $tableData;
}

private function new_protocol_page() {
    require_once(__DIR__ . '/vendor/autoload.php');
    // initiating an API call to get all protocol titles 
    $category_name = "Protocol";
    $api_url = "https://#/wiki/api.php?action=query&list=categorymembers&cmtitle=Category:" . urlencode($category_name) . "&cmlimit=max&format=json";

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($result, true);
    $pages = $data["query"]["categorymembers"];
    $page_titles = array();
    $page_codes = array();

    foreach ($pages as $page) {
        array_push($page_titles, $page["title"]);
        $code = substr($page["title"], -4); //extracting the numerical digits in each protocol title
        array_push($page_codes, $code);     //Group them in array
    }

    $last_code = end($page_codes);  //get the last protocol title
    $protocol_name = "PT1API" . ($last_code + 1);   // increasing it by one and assign it to the newly created protocol
    global $p_name;
    $p_name = $protocol_name;
    global $id;
    global $expTable;
    global $expBody;
    global $array;
    global $metadata_arr;
    global $htmlBody;
    global $expTags;
    $projectCode;
    $delimiter = "|";
    $expTags_array = explode($delimiter, $expTags);

    $tagsDict = [
        'A01'=>'Project A01',
        'A02'=>'Project A02',
        'A03'=>'Project A03',
        'A04'=>'Project A04',
        'A05'=>'Project A05',
        'A06'=>'Project A06',
        'B02'=>'Project B02',
        'B03'=>'Project B03',
        'B04'=>'Project B04',
        'B05'=>'Project B05',
        'C01'=>'Project C01',
        'C02'=>'Project C02',
        'C03'=>'Project C03',
        'C04'=>'Project C04',
        'C05'=>'Project C05',
        'S01'=>'Project S01',
        'INF'=>'Project INF'
    ];

    $projectCode = "Unidentified"; // Set default value
    foreach ($tagsDict as $key => $value) {
        if (in_array($key, $expTags_array)) {
            $projectCode = $value;
            break; // Exit loop if match is found
        }
    }

    $doc = new DOMDocument();
    $doc->loadHTML($htmlBody);
    $styles = $doc->getElementsByTagName('style');
    foreach ($styles as $style) {
        $style->parentNode->removeChild($style);
    }
    // get all tables from the HTML
    $tables = $doc->getElementsByTagName('table');

    // create a new DOMDocument instance to store the extracted tables
    $tableDoc = new DOMDocument();
        // iterate over the tables and add them to the new document
    foreach ($tables as $table) {
        $clonedTable = $table->cloneNode(TRUE);
        $tableDoc->appendChild($tableDoc->importNode($clonedTable, TRUE));
    }
    // get the extracted tables as an HTML string
    $tableHtml = $tableDoc->saveHTML();

    $protocol_content = '{{Protocol |ProjectCode='. $projectCode .
        '|ProtocolType=PT1API
        |Person=' . $array['fullname'] .    //it was $expTable[0][2][1]
        '|Date=' . $array['date'] .     //it was $expTable[0][3][1]
        '|SpecimenList=' .      //it was $expTable[0][1][1] 
        '|Description=' . '<h3>'.$array['title'].'</h3>'.$tableHtml. //it was $expBody
        '|elabFTWID= https://#/experiments.php?mode=view&id='.$id.'}}';

    
    
    // Set up API authentication
    $auth = new \Mediawiki\Api\ApiUser('#', '#');

    // Create a new MediaWiki API instance
    $api = \Mediawiki\Api\MediawikiApi::newFromApiEndpoint('https://#/wiki/api.php', $auth);

    // Create a new MediaWiki API service factory
    $services = new \Mediawiki\Api\MediawikiFactory($api);
    $newContent = new \Mediawiki\DataModel\Content($protocol_content);
    $title = new \Mediawiki\DataModel\Title($protocol_name);
    $identifier = new \Mediawiki\DataModel\PageIdentifier($title);
    $revision = new \Mediawiki\DataModel\Revision($newContent, $identifier);
    $services->newRevisionSaver()->save($revision);
    

}

private function new_record_loop () {

    global $p_name;
    global $array;
    global $metadata_arr;
    global $loop_array;
    global $expTable;
    global $expBody;
    global $values;

    $loop_array = [];
    $tableHeads = array("XPS-Detail", "AFM", "CLSM", "XPS-Übersicht", "Spincoating", "Experimentator", "Probe", "Datum und Zeit", "Datum und Uhrzeit");

    foreach ($expTable as $outerArray){
        foreach ($outerArray as $innerArray){
            if (in_array(trim($innerArray[0]), $tableHeads) ){ 
                continue;
            }
            else{
                $loopValue = '{{Data |Variable=' . trim($innerArray[0]) . '|Value=' . trim($innerArray[1]) . '}}';
                array_push($loop_array, $loopValue);
            }
        }
    }

    $loop_string = "";
    foreach ($loop_array as $value){
        $loop_string .= $value;
    }

    $values = $loop_string;

}

private function new_record_page() {
    //require_once(__DIR__ . '/#/wiki/extensions/ElabMigrator/vendor/autoload.php');

    global $p_name;
    global $array;
    global $metadata_arr;
    global $record_loop;
    global $loop_value;
    global $expTable;
    global $values;

    $record_name = 'R_'.$p_name.'_'.$expTable[0][1][1];

    $record_content = '{{Record
        |Protocol=' . $p_name .
        '|Person=' . $array['fullname'] .
        '|Specimen=' . $expTable[0][1][1] . '}}'.$values;

    $auth = new \Mediawiki\Api\ApiUser('#', '#');
    $api = \Mediawiki\Api\MediawikiApi::newFromPage('https://#/sfb1368/wiki/api.php',$auth);
    $services = new \Mediawiki\Api\MediawikiFactory($api);


    $newContent = new \Mediawiki\DataModel\Content($record_content);
    $title = new \Mediawiki\DataModel\Title($record_name);
    $identifier = new \Mediawiki\DataModel\PageIdentifier($title);
    $revision = new \Mediawiki\DataModel\Revision($newContent, $identifier);
    $services->newRevisionSaver()->save($revision);

}

    private function elabFTW_POST($x)
    {
        global $p_name;
        $url = "https://#/api/v1/experiments/${x}";

        // Set the authorization token
        $token = "#";

        // Set the request body data
        $data = array(
            'bodyappend' => '<p><a href="https://#/wiki/'.$p_name.'">Link of Expirement on SMW</a></p>'
        );

        //$fields_string = http_build_query($data);
        // Initialize the cURL session
        $ch = curl_init();

        // Set the cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: $token"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute the cURL session
        $response = curl_exec($ch);

        // Close the cURL session
        curl_close($ch);

        // Print the response
        //echo $response;
    }

    private function elaburlGetter()
    {
        global $elaburlArray;;
        // Send the API request
        $url = "https://#/wiki/api.php?action=ask&query=[[dataResourceUrl::%2B]]|%3FdataResourceUrl|sort%3DdataResourceUrl|order%3Ddesc&format=json";
        $response = file_get_contents($url);

        // Parse the JSON response
        $data = json_decode($response, true);

        // Group the values of property "dataResourceUrl" into an array
        $result = array();
        $page_titles = array();

        foreach ($data["query"]["results"] as $title => $page) {
            $url = $page["printouts"]["Data Resource URL"][0];
            $result[$title] = $url;
        }

        $elaburlArray = $result;

    }

    

    private function update_protocol_page($x) {
        require_once(__DIR__ . '/vendor/autoload.php');
        // initiating an API call to get all protocol titles 
        $category_name = "Protocol";
        $api_url = "https://#/wiki/api.php?action=query&list=categorymembers&cmtitle=Category:" . urlencode($category_name) . "&cmlimit=max&format=json";
    
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
    
        $data = json_decode($result, true);
        $pages = $data["query"]["categorymembers"];
        $page_titles = array();
        $page_codes = array();
    
        foreach ($pages as $page) {
            array_push($page_titles, $page["title"]);
            $code = substr($page["title"], -4); //extracting the numerical digits in each protocol title
            array_push($page_codes, $code);     //Group them in array
        }
    
        $last_code = end($page_codes);  //get the last protocol title
        $protocol_name = "PT1API" . ($last_code + 1);   // increasing it by one and assign it to the newly created protocol
        global $p_name;
        $p_name = $protocol_name;
        global $id;
        global $expTable;
        global $expBody;
        global $array;
        global $metadata_arr;
        global $htmlBody;
        global $expTags;
        $projectCode;
        $delimiter = "|";
        $expTags_array = explode($delimiter, $expTags);
    
        $tagsDict = [
            'A01'=>'Project A01',
            'A02'=>'Project A02',
            'A03'=>'Project A03',
            'A04'=>'Project A04',
            'A05'=>'Project A05',
            'A06'=>'Project A06',
            'B02'=>'Project B02',
            'B03'=>'Project B03',
            'B04'=>'Project B04',
            'B05'=>'Project B05',
            'C01'=>'Project C01',
            'C02'=>'Project C02',
            'C03'=>'Project C03',
            'C04'=>'Project C04',
            'C05'=>'Project C05',
            'S01'=>'Project S01',
            'INF'=>'Project INF'
        ];
    
        $projectCode = "Unidentified"; // Set default value
        foreach ($tagsDict as $key => $value) {
            if (in_array($key, $expTags_array)) {
                $projectCode = $value;
                break; // Exit loop if match is found
            }
        }
    
        $doc = new DOMDocument();
        $doc->loadHTML($htmlBody);
        $styles = $doc->getElementsByTagName('style');
        foreach ($styles as $style) {
            $style->parentNode->removeChild($style);
        }
        // get all tables from the HTML
        $tables = $doc->getElementsByTagName('table');
    
        // create a new DOMDocument instance to store the extracted tables
        $tableDoc = new DOMDocument();
            // iterate over the tables and add them to the new document
        foreach ($tables as $table) {
            $clonedTable = $table->cloneNode(TRUE);
            $tableDoc->appendChild($tableDoc->importNode($clonedTable, TRUE));
        }
        // get the extracted tables as an HTML string
        $tableHtml = $tableDoc->saveHTML();
    
    
        $protocol_content = '{{Protocol |ProjectCode='. $projectCode .
            '|ProtocolType=PT1API
            |Person=' . $array['fullname'] .    //it was $expTable[0][2][1]
            '|Date=' . $array['date'] .     //it was $expTable[0][3][1]
            '|SpecimenList=' .      //it was $expTable[0][1][1] 
            '|Description=' . '<h3>'.$array['title'].'</h3>'.$tableHtml. //it was $expBody
            '|elabFTWID= https://#/experiments.php?mode=view&id='.$id.'}}';
    
        
        
        // Set up API authentication
        $auth = new \Mediawiki\Api\ApiUser('#', '#');
    
        // Create a new MediaWiki API instance
        $api = \Mediawiki\Api\MediawikiApi::newFromApiEndpoint('https://#/wiki/api.php', $auth);
        $services = new \Mediawiki\Api\MediawikiFactory($api);
        $newContent = new \Mediawiki\DataModel\Content($protocol_content);
        $title = new \Mediawiki\DataModel\Title($x);
        $identifier = new \Mediawiki\DataModel\PageIdentifier($title);
        $revision = new \Mediawiki\DataModel\Revision($newContent, $identifier);
        $services->newRevisionSaver()->save($revision);

    }


}

?>




