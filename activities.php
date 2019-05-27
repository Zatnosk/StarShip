<?php namespace StarShip {

/* The base parent class for all ActivityPub Activities */
class Activity {
  
  // define the elements that other stuff should be able to access
  public $actor;
  public $obj;
  public $address;
  public $type;
  // this is just to track whether or not an object has been initialized with content or is empty
  public $new = true;
  
  // load an activity's properties from the database into the Activity object
  function load($id) {
    if ($new === false) throw new Exception('requires a blank Activity object');
  }

  // Stores the properties of the Activity object into the database
  // use "update on duplicate key"
  function save() {
    if ($new === true) throw new Exception('requires a populated Activity object');
  }

  /* loads an activity's properties from an unserialized-JSON array into the Activity object */
  function fill($json_array) {
    if ($json_array === null) throw new Exception('no Activity data provided');
    // verify that certain required properties are set
    $valid = self::validateActivity($json_array);
    if ($valid === false) throw new Exception('invalid Activity data');

    // loads the array into the object, isn't this nice
    $this->activity_array = $json_array;
    $this->new = false;
    if (!isset($this->activity_array['id'])) $this->activity_array['id'] = "https://". $_SERVER['HTTP_HOST'] ."/ap/actor/". uniqid(); // need a proper path later

    // Should probably do something with the @context array to identify unrecognized properties and decide how to handle them
    // but that can wait until later, when the capability for handling a variety of things Actually Exists
    unset($this->activity_array['@context']);
    if (!isset($this->activity_array['object'])) $this->activity_array['object'] = null;

    // assign public values to appropriate variables
    $this->actor    = &$this->activity_array['actor'];
    $this->type     = &$this->activity_array['type'];
    $this->obj      = &$this->activity_array['object'];
    $this->address  = [];
    if (isset($this->activity_array['to']))  $this->address['to']  = &$this->activity_array['to'];
    if (isset($this->activity_array['cc']))  $this->address['cc']  = &$this->activity_array['cc'];
    if (isset($this->activity_array['bcc'])) $this->address['bcc'] = &$this->activity_array['bcc'];
  }
  
  // this is a function instead of a variable because changing the id is Very Bad
  public function id() {
    return $this->activity_array['id'];
  }
  
  /* Serializes the Activity object to a ActivityPub JSON-LD object */
  public function toJSON() {
    // this is gonna have to have an actual context string
    $this->activity_array['@context'] = "";
    $json = json_encode($this->activity_array);
    unset($this->activity_array['@context']);
    return $json;
  }

  protected $activity_array;
  protected $id;

  /* Check various key properties to make sure that they exist and have valid contents */
  protected function validateActivity(&$json_array) {
    $test_array = [ "Create", "Update", "Delete", "Follow", "Accept", "Reject",
                    "Add", "Remove", "Like", "Block", "Undo" ];
    // make sure it is an accepted ActivityPub activity type
    if (!isset($json_array['type']) || !in_array($json_array['type'],$test_array)) return false;
    // all Activities must have an actor and an id
/*
      I need to figure out a different way of handling the id check, because client-server
      Activities are NOT supposed to have an id on them already and the server is supposed to
      generate one, while server-server Activities are REQUIRED to have an id...
      
      I guess I could have a client vs. server flag in this function which tests
      appropriately, and have the flag passed from the constructor? That way, the constructor
      could generate the unique id if it's from a client.
*/   
    if (!isset($json_array['actor']) /* || !isset($json_array['id']) */) return false;
    // all Activities except for Deletes must have an object
    if ($json_array['type'] !== "Delete" && !isset($json_array['object'])) return false;
    // objects can be array or URI (treated as ID)
    if (isset($json_array['object']) && is_array($json_array['object'])) {
      // all Object arrays must have an ID and a type
      if (!isset($json_array['object']['id']) || !isset($json_array['object']['type'])) return false;
      // verify actor attribution
      if (isset($json_array['object']['attributedTo'])
        && $json_array['object']['attributedTo'] !== $json_array['actor']) return false;
      // verify addressing exists according to spec
      if (isset($json_array['object']['to'])) {
        if (!isset($json_array['to'])) return false;
        if ($json_array['to'] !== $json_array['object']['to']) return false;
      }
      if (isset($json_array['object']['cc'])) {
        if (!isset($json_array['cc'])) return false;
        if ($json_array['cc'] !== $json_array['object']['cc']) return false;
      }
      if (isset($json_array['object']['bcc'])) {
        if (!isset($json_array['bcc'])) return false;
        if ($json_array['bcc'] !== $json_array['object']['bcc']) return false;
      }
    }
    return true;
  }

}

/* class for Create Activities; see AP spec */
class Create extends Activity {

  function fill($json_array, $wrapped=true) {
    // $wrapped indicates whether $json_array has the Create activity information wrapped around the Object being created
    // true means that it does, false means that it does not
    if ($wrapped) parent::fill($json_array);

    // if $wrapped is false, $json_array is an AP Object which needs to be wrapped in a Create activity
    else { // verify valid ActivityPub object
      if ($json_array === null) throw new Exception('cannot create null Activity');
      $valid = self::validateObject($json_array);
      if ($valid === false) throw new Exception('invalid Create Object data');

      // initialize activity_array and start filling with the Activity properties
      $this->activity_array = [];
      $this->new = false;
      $this->activity_array['object'] = $json_array;
      $this->activity_array['type'] = "Create";
      // need proper generated IDs later
      unset($this->activity_array['object']['@context']);
      $this->activity_array['id'] = "https://". $_SERVER['HTTP_HOST'] ."/ap/actor/". uniqid(); // fix this URI generation
      $this->activity_array['object']['id'] = "https://". $_SERVER['HTTP_HOST'] ."/posts/actor/". uniqid(); // this one too

      $this->activity_array['actor'] = $this->activity_array['object']['attributedTo'];

      // assign the newly-applied Activity properties to the relevant variables
      $this->actor    = &$this->activity_array['actor'];
      $this->type     = &$this->activity_array['type'];
      $this->obj      = &$this->activity_array['object'];
      $this->address  = [];
      if (isset($this->activity_array['cc']))  $this->address['cc']  = &$this->activity_array['cc'];
      if (isset($this->activity_array['bcc'])) $this->address['bcc'] = &$this->activity_array['bcc'];

      if (isset($this->activity_array['object']['published'])) {
        $this->activity_array['published'] = $this->activity_array['object']['published'];
      }
      if (isset($this->activity_array['object']['to'])) {
        $this->activity_array['to'] = $this->activity_array['object']['to'];
        $this->address['to'] = &$this->activity_array['to'];
      }
      if (isset($this->activity_array['object']['cc'])) {
        $this->activity_array['cc'] = $this->activity_array['object']['cc'];
        $this->address['cc'] = &$this->activity_array['cc'];
      }
      if (isset($this->activity_array['object']['bcc'])) {
        $this->activity_array['bcc'] = $this->activity_array['object']['bcc'];
        $this->address['bcc'] = &$this->activity_array['bcc'];
      }
    }
  }  

  /* Skeleton function for verifying that required Object properties exist with valid content */
  protected function validateObject(&$object) {
    // types of objects that people can create. Will expand later.
    $test_array = [ "Note" ];
    // make sure it is an accepted ActivityPub object type
    if (!isset($object['type']) || !in_array($object['type'],$test_array)) return false;
    // require all objects to have a declared attribution
    if (!isset($object['attributedTo'])) return false;
    // there needs to be something actually being posted
    if (!isset($object['content'])) return false;
    
    return true;
  }

}

/* class for Follow Activities; see AP spec. Also handles Accept and Reject activities by local accounts */
class Follow extends Activity {  
  public function accept($actor) {
    // create a new Activity of type 'Accept', with the Follow id as the object, and return it
    $arr = [
              "type"    => "Accept",
              "object"  => $this->activity_array,
              "actor"   => $this->obj
           ];
    $accept = new Activity();
    $accept->fill($arr);
    // post new activity to the outbox
    return $accept; 
  }
  
  public function reject() {
    // create a new Activity of type 'Reject', with the Follow id as the object, and return it
  }
  
}

/*
class Update extends Activity {
  
}

class AddRemove extends Activity {
  
}
*/

}?>
