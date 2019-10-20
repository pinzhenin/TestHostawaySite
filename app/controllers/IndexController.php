<?php

use Phalcon\Mvc\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class IndexController extends Controller
{

    public function indexAction()
    {
        
    }

    public function listAction()
    {
        $client = $this->getGuzzleClient();
        $response = $client->get('/api/contacts');
        $responseBody = $response->getBody();
        $this->view->request = 'GET /api/contacts';
        $this->view->json = $responseBody;
        $this->view->response = json_decode($responseBody, TRUE);
    }

    public function readAction()
    {
        $request = $this->getDI()->getRequest();
        $id = $request->get('id');

        $client = $this->getGuzzleClient();
        $response = $client->get("/api/contacts/{$id}");
        $responseBody = $response->getBody();
        $this->view->request = "GET /api/contacts/{$id}";
        $this->view->json = $responseBody;
        $this->view->response = json_decode($responseBody, TRUE);
    }

    public function searchAction()
    {
        $request = $this->getDI()->getRequest();
        $name = $request->get('name');

        $client = $this->getGuzzleClient();
        $response = $client->get("/api/contacts/search/{$name}");
        $responseBody = $response->getBody();
        $this->view->request = "GET /api/contacts/search/{$name}";
        $this->view->json = $responseBody;
        $this->view->response = json_decode($responseBody, TRUE);
        $this->view->input = ['name' => $name];
    }

    public function createAction()
    {
        $request = $this->getDI()->getRequest();
        $input = $request->get('contact');
        if (empty($input)) {
            return;
        }
        $inputJson = json_encode($input, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $client = $this->getGuzzleClient();
        try {
            $response = $client->post('/api/contacts', ['json' => $input]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
        }
        $responseBody = $response->getBody();
        $this->view->input = $input;
        $this->view->request = "POST /api/contacts body=$inputJson";
        $this->view->json = $responseBody;
        $this->view->response = json_decode($responseBody, TRUE);
    }

    public function updateAction()
    {
        $request = $this->getDI()->getRequest();
        $input = $request->get('contact');
        $id = $input['id'] ?? $request->get('id');

        // First entering: read contact
        if (empty($input)) {
            $client = $this->getGuzzleClient();
            $response = $client->get("/api/contacts/{$id}");
            $responseBody = $response->getBody();
            $this->view->request = "GET /api/contacts/{$id}";
            $this->view->json = $responseBody;
            $this->view->response = json_decode($responseBody, TRUE);
            $this->view->input = $input;
            $this->view->start = TRUE;
            return;
        }

        // Second entering: update contact
        $inputJson = json_encode($input, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $client = $this->getGuzzleClient();
        try {
            $response = $client->put("/api/contacts/{$id}", ['json' => $input]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
        }
        $responseBody = $response->getBody();
        $this->view->request = "PUT /api/contacts/{$id} body=$inputJson";
        $this->view->json = $responseBody;
        $this->view->response = json_decode($responseBody, TRUE);
        $this->view->input = $input;
        $this->view->start = FALSE;
    }

    public function deleteAction()
    {
        $request = $this->getDI()->getRequest();
        $id = $request->get('id');

        $client = $this->getGuzzleClient();
        $response = $client->delete("/api/contacts/{$id}");
        $responseBody = $response->getBody();
        $this->view->request = "DELETE /api/contacts/{$id}";
        $this->view->json = $responseBody;
        $this->view->response = json_decode($responseBody, TRUE);
    }

    public function getGuzzleClient()
    {
        $client = new Client(
            [
            'base_uri' => 'http://api.pinzhenin.ru',
            'timeout' => 3
            ]
        );
        return $client;
    }
}
