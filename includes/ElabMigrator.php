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

		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();

		# Get request data from, e.g.
		$param = $request->getText( 'param' );

		# Do stuff
		# ...
		//$wikitext = 'test';
		//$output->addWikiTextAsInterface( $wikitext );

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
            $experimentURL = 'https://elabftw.chemstorage.de/experiments.php?mode=view&id='.$id;
            $wikiURL = 'https://test.service.tib.eu/sfb1368/wiki/';
            $getter->elabFTW_GET($_POST['id']);
            $getter->elaburlGetter();

            foreach ($elaburlArray as $title => $url){
                if ($url == $experimentURL){
                    $isExist = true;
                    $wgOut->addHTML( '
                        <div>
                            <p style="color:red">
                            The experiment with ID = '.$_POST['id'].' has been migrated before.</p>'.
                            '<p>You can access it on SMW through this <a href="'.$wikiURL.$title.'" target="_blank">LINK</a>'.'<br> 
                        </div>'
                        );
                }
            }
            if(!empty($array['title']) &&  $isExist != true ){
            
                $getter->protocol_page();
                $getter->record_loop();
                $getter->record_page();
                $getter->elabFTW_POST($_POST['id']);
                
                $wgOut->addHTML( '
                <div>
                    <p style="color:green">
                    Your Protocol with ID = '.$_POST['id'].' has been successfully created.</p> '.
                    '<p>You can access it on SMW through this <a href="https://test.service.tib.eu/sfb1368/wiki/'.$p_name.'">LINK</a>'.'<br>'
                    .'<a href="https://elabftw.chemstorage.de/experiments.php?mode=view&id='.$_POST['id'].'">Back to elabFTW Expirement Page</a>
                    </p>
                </div>'
                );
            } 
    
            else if (empty($array['title'])){
                $wgOut->addHTML('<h5 style="color:red">Experiment is Not Exist</h5>');
            }
        }
	}

    private function elabFTW_GET($x)
    {
        
        $curl = curl_init();
        curl_setopt_array($curl, [
        CURLOPT_URL => "https://elabftw.chemstorage.de/api/v1/experiments/${x}?format=json",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
                "Authorization: 39c7bfddc95e3783cae79f038529b87b944f0f7b3eaa2e4f426c869a069e757ae223778d2909b890f4a5",
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
            global $array;
            $array = $data;
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

    }

    private function protocol_page() {
        require_once(__DIR__ . '/vendor/autoload.php');
	    
        // initiating an API call to get all protocol titles 
        $category_name = "Protocol";
        $api_url = "https://test.service.tib.eu/sfb1368/wiki/api.php?action=query&list=categorymembers&cmtitle=Category:" . urlencode($category_name) . "&cmlimit=max&format=json";

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
        global $array;
        global $metadata_arr;
        global $id;
        $p_name = $protocol_name;

        $protocol_content = '{{Protocol |ProjectCode='. $metadata_arr['extra_fields']['Project Code']['value'] .
            '|ProtocolType=PT1API
            |Person=' . $array['fullname'] .
            '|Date=' . $array['date'] .
            '|SpecimenList=' . $metadata_arr['extra_fields']['SpecimenMaterial']['value'] .
            '|Description=' . strip_tags($array['title']) . strip_tags($array['body']).
            '|elabFTWID= https://elabftw.chemstorage.de/experiments.php?mode=view&id='.$id.'}}';


        // Set up API authentication
        $auth = new \Mediawiki\Api\ApiUser('amerm', 'mhabeb41290Md=');

        // Create a new MediaWiki API instance
        $api = \Mediawiki\Api\MediawikiApi::newFromApiEndpoint('https://test.service.tib.eu/sfb1368/wiki/api.php', $auth);

        // Create a new MediaWiki API service factory
        $services = new \Mediawiki\Api\MediawikiFactory($api);
        $newContent = new \Mediawiki\DataModel\Content($protocol_content);
        $title = new \Mediawiki\DataModel\Title($protocol_name);
        $identifier = new \Mediawiki\DataModel\PageIdentifier($title);
        $revision = new \Mediawiki\DataModel\Revision($newContent, $identifier);
        $services->newRevisionSaver()->save($revision);

    }

    private function record_loop () {

        global $p_name;
        global $array;
        global $metadata_arr;
        global $loop_array;

        $loop_array = [];


        foreach ($metadata_arr['extra_fields'] as $key => $value) {
            $x = '{{Data |Variable=' . $key . '|Value=' . $value['value'] . '}}';
            if ($key == 'SpecimenMaterial' || $key == 'Project Code'){
                continue;
            }
            else{
                array_push($loop_array, $x);
            }
        }
        $loop_string = implode($loop_array);
        global $loop_string_values;
        $loop_string_values = $loop_string;

    }

    private function record_page() {
	require_once(__DIR__ . '/vendor/autoload.php');
	    
        global $p_name;
        global $array;
        global $metadata_arr;
        global $record_loop;
        global $loop_value;
        global $loop_string_values;

        $record_name = 'R_'.$p_name.'_'.$metadata_arr['extra_fields']['SpecimenMaterial']['value'];

        $record_content = '{{Record
            |Protocol=' . $p_name .
            '|Person=' . $array['fullname'] .
            '|Specimen=' . $metadata_arr['extra_fields']['SpecimenMaterial']['value'] . '}}'.$loop_string_values;

        $auth = new \Mediawiki\Api\ApiUser('amerm', 'mhabeb41290Md=');
        $api = \Mediawiki\Api\MediawikiApi::newFromPage('https://test.service.tib.eu/sfb1368/wiki/api.php',$auth);
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
        $url = "https://elabftw.chemstorage.de/api/v1/experiments/${x}";

        // Set the authorization token
        $token = "39c7bfddc95e3783cae79f038529b87b944f0f7b3eaa2e4f426c869a069e757ae223778d2909b890f4a5";

        // Set the request body data
        $data = array(
            'bodyappend' => '<p><a href="https://test.service.tib.eu/sfb1368/wiki/'.$p_name.'">Link of Expirement on SMW</a></p>'
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
        $url = "https://test.service.tib.eu/sfb1368/wiki/api.php?action=ask&query=[[dataResourceUrl::%2B]]|%3FdataResourceUrl|sort%3DdataResourceUrl|order%3Ddesc&format=json";
        $response = file_get_contents($url);

        // Parse the JSON response
        $data = json_decode($response, true);

        // Group the values of property "dataResourceUrl" into an array
        $result = array();

        $result = array();

        foreach ($data["query"]["results"] as $title => $page) {
            $url = $page["printouts"]["Data Resource URL"][0];
            $result[$title] = $url;
        }

        $elaburlArray = $result;
        }
    
    }

?>




