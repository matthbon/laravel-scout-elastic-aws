<?php

namespace ScoutEngines\Elasticsearch\Commands;

use Aws\Credentials\CredentialProvider;
use Aws\Credentials\Credentials;
use Illuminate\Console\Command;
use Aws\ElasticsearchService\ElasticsearchPhpHandler;
use Elasticsearch\ClientBuilder;

class CreateIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:create-index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create index required for Scout';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $provider = config('laravel-scout-elastic.provider', 'elastic');

        switch ($provider) {
            case 'aws':
                // Default credentials
                $provider = CredentialProvider::defaultProvider();

                // Set credentials
                if (config('laravel-scout-elastic.credentials.key')) {
                    $provider = CredentialProvider::fromCredentials(
                        new Credentials(
                            config('laravel-scout-elastic.credentials.key'),
                            config('laravel-scout-elastic.credentials.secret'),
                            config('laravel-scout-elastic.credentials.token')
                        )
                    );
                }

                // Create a handler (with the region of your Amazon Elasticsearch Service domain)
                $handler = new ElasticsearchPhpHandler(config('laravel-scout-elastic.region', 'us-west-2'), $provider);

                // Use this handler to create an Elasticsearch-PHP client
                $client = ClientBuilder::create()
                            ->setHandler($handler)
                            ->setHosts(config('scout.elasticsearch.hosts'))
                            ->build();
                break;
            case 'elastic':
            default:
                $client = ClientBuilder::create()
                    ->setHosts(config('scout.elasticsearch.hosts'))
                    ->build();
                break;
        }
        
        if(!$client->indices()->exists(['index' => config('scout.elasticsearch.index')])) {
            $params = [
                'index' => config('scout.elasticsearch.index'),
            ];
            $client->indices()->create($params);
        }
    }
}
