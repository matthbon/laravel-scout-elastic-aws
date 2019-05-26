<?php

namespace ScoutEngines\Elasticsearch;

use Aws\Credentials\CredentialProvider;
use Aws\Credentials\Credentials;
use Aws\ElasticsearchService\ElasticsearchPhpHandler;
use Laravel\Scout\EngineManager;
use Illuminate\Support\ServiceProvider;
use Elasticsearch\ClientBuilder as ElasticBuilder;

class ElasticsearchProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/laravel-scout-elastic.php' => config_path('laravel-scout-elastic.php'),
        ]);

        $provider = config('laravel-scout-elastic.provider', 'elastic');

        switch ($provider) {
            case 'aws':
                app(EngineManager::class)->extend('elasticsearch', function($app) {
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

                    $handler = new ElasticsearchPhpHandler(config('laravel-scout-elastic.region', 'us-west-2'), $provider);
                    return new ElasticsearchEngine(ElasticBuilder::create()
                        ->setHandler($handler)
                        ->setHosts(config('scout.elasticsearch.hosts'))
                        ->build(),
                        config('scout.elasticsearch.index')
                    );
                });
                break;
            case 'elastic':
            default:
                app(EngineManager::class)->extend('elasticsearch', function($app) {
                    return new ElasticsearchEngine(ElasticBuilder::create()
                        ->setHosts(config('scout.elasticsearch.hosts'))
                        ->build(),
                        config('scout.elasticsearch.index')
                    );
                });
                break;
        }
    }
}
