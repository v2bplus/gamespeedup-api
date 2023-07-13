<?php

namespace Jobs;

class SyncCurl
{
    // eg get
    // $client->request('GET', 'http://httpbin.org', [
    //     'query' => ['foo' => 'bar']
    // ]);
    // eg post application/x-www-form-urlencoded data
    // $response = $client->request('POST', 'http://httpbin.org/post', [
    //     'form_params' => [
    //         'field_name' => 'abc',
    //         'other_field' => '123',
    //         'nested_field' => [
    //             'nested' => 'hello'
    //         ]
    //     ]
    // ]);
    public function setUp()
    {
        \Yaf_Registry::get('_logger')->addDebug(__CLASS__.' job begin');

        $this->client = new \GuzzleHttp\Client();
    }

    public function tearDown()
    {
        \Yaf_Registry::get('_logger')->info(__CLASS__.' job end');
    }

    public function perform($args)
    {
        $time = date('Y-m-d H:i:s');
        if (isset($args['url'], $args['data'])) {
            $url = $args['url'];
            $data = $args['data'];
            $method = 'GET';
            if (isset($args['headers'])) {
                $headers = $args['headers'];
            } else {
                $headers = [
                    'User-Agent' => PROJECT_NAME,
                ];
            }

            if (isset($args['method'])) {
                $method = strtoupper($args['method']);
            }
            $data['headers'] = $headers;
            $data['allow_redirects'] = [
                'max' => 3,
                'referer' => true,
            ];

            try {
                $response = $this->client->request($method, $url, $data);
                $status = $response->getStatusCode();
                $body = $response->getBody();
                $string = '';
                while (!$body->eof()) {
                    $string .= $body->read(8192);
                }
                fwrite(STDOUT, $time.' '.$url.' '.$status.' '.$string."\n");
            } catch (\Exception $e) {
                fwrite(STDOUT, $time.' '.$e->getMessage());
            }
            sleep(1);
        } else {
            fwrite(STDOUT, $time.' no url param');
        }
    }
}
