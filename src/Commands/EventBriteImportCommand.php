<?php
namespace Drupal\event_brite\Commands;

use Drush\Commands\DrushCommands;
use \Drupal\node\Entity\Node;

/**
 * A drush command file.
 *
 * @package Drupal\event_brite\Commands
 */
class EventBriteImportCommand extends DrushCommands {

  /**
   * Drush command that displays the given text.
   *
   * @param string $text
   *   Argument with message to be displayed.
   * @command event_brite:message
   * @aliases import-events
   * @option uppercase
   *   Uppercase the message.
   * @option reverse
   *   Reverse the message.
   * @usage event_brite:message --uppercase --reverse drupal8
   */
  public function message($text = 'Hello world!', $options = ['uppercase' => FALSE, 'reverse' => FALSE]) {
    
  	$auth_token = \Drupal::config('event_brite.settings')->get('auth_token');
  	
  	if( "" == $auth_token ) {
  		$this->output()->writeln("Enter your token");
  		return;
  	}
    
    $client = \Drupal::httpClient();
    $request = $client->request("GET", "https://www.eventbriteapi.com/v3/users/me/organizations/?token=$auth_token");

    $response = json_decode($request->getBody(),true);

    $organization_id = $response["organizations"][0]["id"];
    

    if( ! isset( $organization_id ) ) {
    	$this->output()->writeln("No organisation id found.");
  		return;
    }

    $request = $client->request("GET", "https://www.eventbriteapi.com/v3/organizations/547470966163/events/?token=$auth_token&expand=venue");

    $response = json_decode($request->getBody(),true);

    $events = $response["events"];

    foreach( $events as $event ) {
    	$this->create_event( $event );
    }

    $this->output()->writeln( "All events imported successfully" );
  }


  public function create_event( $event_data ) {
  	


  	$event_name = $event_data["name"]["text"];
  	
  	$query = \Drupal::entityQuery('node')
    ->condition('type', 'event')
    ->condition('title', $event_name, '=');
	$nids = $query->execute();
  	
	if( count( $nids) > 0) {
		return;
	}
	
	$event_description = $event_data["description"]["html"];
  	$event_url = $event_data["url"];

  	$event_start_date = $event_data["start"]["local"];
  	$event_end_date = $event_data["end"]["local"];

  	// address
  	$address1 = $event_data["venue"]["address"]["address_1"];
  	$address2 = $event_data["venue"]["address"]["address_2"];
  	$city = $event_data["venue"]["address"]["city"];
  	$region = $event_data["venue"]["address"]["region"];
  	$postal_code = $event_data["venue"]["address"]["postal_code"];
  	$country = $event_data["venue"]["address"]["country"];


  	$node = Node::create([
		"type"    	=> "event",
		"uid"	 => 1,
		"title"   	=> $event_name,
		"body" 		=> [
         	"value" => $event_description,
      	],
      	"field_event_start_date" => [
         	"value" => $event_start_date,
      	],
      	"field_event_end_date" => [
         	"value" => $event_end_date,
      	],
      	"field_event_url" => [
         	"value" => $event_url,
      	],
      	"field_event_venue" => [
         	"value" => "$address1, $address2, $city, $region, $country",
      	],
	]);
	
	$node->save();
  }
}