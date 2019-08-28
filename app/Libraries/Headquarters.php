<?php namespace App\Libraries;

use App\Models\ServerModel;
use Config\Services;
use Tatter\Agents\Models\AgentModel;
use Tatter\Agents\Models\HashModel;
use Tatter\Agents\Models\ResultModel;

class Headquarters
{
	// The current CURLRequest instance (server-specific)
	protected $client;
	
	// The most recent response from $this->curl
	protected $response;
	
	// Any errors
	protected $errors = [];
	
	// Initiate the library, preload models
	public function __construct()
	{
		// Fire up the ole database
		$this->db = db_connect();
		
		// Preload the models
		$this->agents  = new AgentModel();
		$this->hashes  = new HashModel();
		$this->results = new ResultModel();
		$this->servers = new ServerModel();
	}
	
	public function getResponse()
	{
		return $this->response;
	}
	
	// Load each server and check for updates
	public function checkAll()
	{
		foreach ($this->servers->findAll() as $server)
		{
			$this->checkServer($server);
		}	
	}
	
	// Poll one server for updates
	public function checkServer($server)
	{
		// Make sure there is a valid URL
		if (! $server->url)
			return false;
		
		// Load the CURL library
		$options = ['base_uri' => $server->url . 'agents/'];
		$this->client = Services::curlrequest($options);
		
		// Determine active agents
		if ($agents = $this->curlRequest('agents'))
		{
			$agentIds = [];
			foreach ($agents as $row)
			{
				// Check for an existing agent with that UID
				if ($agent = $this->agents->where('uid', $row->uid)->first())
				{
					// Add the ID to the row so save() will update it
					$row->id = $agent->id;
					$this->agents->save($row);
				}
				// Create a new agent record
				else
				{
					$row->id = $this->agents->insert($row);
				}
				
				$server->addAgent($row->id);
				$agentIds[] = $row->id;
			}

			// Remove agents that are no longer active on this server
			$this->db->table('agents_servers')
				->where('server_id', $server->id)
				->whereNotIn('agent_id', $agentIds)
				->delete();
		}
		
	}
	
	// Retrieve data from an endpoint and parse the response
	protected function curlRequest($endpoint, $method = 'GET')
	{
		// Determine active agents
		try {
			$response = $this->client->request($method, $endpoint);
		}
		catch (\Exception $e)
		{
			$error = $e->getMessage();
			$this->errors[] = $error;
			log_message('error', "CURL request failed: {$error}");

			$this->response = null;
			return false;
		}
		$this->response = $response;
		
		// Verify the response
		if  ($response->getStatusCode() != 200)
		{
			$error = $response->getReason();
			$this->errors[] = $error;
			log_message('error', "Invalid CURL response: {$reason}");
			
			return false;
		}
		$body = $response->getBody();

		// Check header type
		if (strpos($response->getHeader('content-type'), 'application/json') !== false)
		{
			
			$body = json_decode($body);
		}
		
		return $body;
	}

	
}
