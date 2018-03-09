<?php


namespace calderawp\ghost;


class Run
{

    /** @var \WP_Query */
    protected $query;

    /** @var  array */
    protected $requests;
    /**
     * Tracks test URLs
     *
     * @var array
     */
    protected $urls;

    /**
     * @var string
     */
    protected $notify;

    /**
     * The UUID for this test run
     *
     * @var string
     */
    protected $uuid;

    public function __construct( $notify = '' )
    {
        $this->notify = filter_var( $notify, FILTER_VALIDATE_URL ) ? $notify : '';
        $this->uuid = uniqid( 'cgr-cf-' );
    }

    /**
     * Get all test urls
     *
     * @return array
     */
    public function getUrls()
    {
        if (!$this->urls) {
            $this->createRequests();
        }

        return is_array($this->urls) ? $this->urls : array();
    }

    /**
     * Set WP_Query
     * @return \WP_Query
     */
    public function setQuery()
    {
        if (!$this->query) {
            $this->query = new \WP_Query(
                array(
                    'post_type' => 'page',
                    'posts_per_page' => '999',
                    'meta_query' => array(
                        'key' => 'GCR',
                        'value' => 'yes',
                        'compare' => '='
                    )
                )
            );
        }

        return $this->query;

    }


    /**
     * Create array of api requests
     */
    public function createRequests()
    {
        $requests = array();
        /** @var \WP_Post $post */
        foreach ($this->setQuery()->get_posts() as $post) {
            $url = $this->url($post);
            if ($url) {
                $this->urls[] = $url
                $requests[] = array(
                    'url' => $url,
                    'type' => 'GET'
                );
            }
        }

        $this->requests = $requests;
    }


    public function makeRequests()
    {
        $results = array(
            'fails' => array(),
            'tests' => array(),
            'messages' => array()
        );
        $responses = \Requests::request_multiple($this->requests, [
            'timeout' => 120,
            //Certs could be an issue from Travis, don't verify
            'verify' => false
        ]);

        /** @var \Requests_Response $response */
        foreach ($responses as $response) {
            $data = [];
            if (is_a($response, 'Requests_Response')) {
                $data = json_decode($response->body);
                $data = array(
                    'passed' => 'SUCCESS' === $data->code,
                    'id' => $data->data->_id,
                    'info' => "https://app.ghostinspector.com/results/" . $data->data->_id,
                    'name' => $data->data->name,
                    'startUrl' => $data->data->startUrl,
                    'uuid' => $data->data->uuid,
                );

                $results['tests'][$data->data->_id] = $data;

                if ('SUCCESS' !== $data->code) {
                    $results['messages'][$data->data->_id] = 'Failed test: ' . $data['name'] . 'See:' . $data['info'];
                }

            }
        }

        return $results;
    }


    protected function url(\WP_Post $post)
    {
        $apiKey = isset($_ENV, $_ENV['CGRKEY']) ? $_ENV ['CGRKEY'] : CGRKEY;
        $pattern = 'https://api.ghostinspector.com/v1/tests/%s/execute/?%s';
        $id = get_post_meta($post->ID, 'CGR_ghostInspectorID', true);
        if (!$id) {
            return null;
        }
        $args = [
            'apiKey' => ($apiKey),
            'startUrl' => $this->startUrl($post)
        ];
        if ($this->notify) {
            $args['notify'] = ( $this->notifyUrl($post,$id) );
        }
        $url = sprintf($pattern, trim($id), http_build_query($args ) );
        return $url;
    }


    protected function notifyUrl(\WP_Post $post,$id ){
        return $this->notify . '?' . http_build_query([
            'runUuid' => $this->uuid,
            'startUrl' => $this->startUrl($post),
            'test_id' => $id,
        ]);
    }

    /**
     * @param \WP_Post $post
     * @return false|string
     */
    protected function startUrl(\WP_Post $post)
    {
        return (get_permalink($post));
    }

}