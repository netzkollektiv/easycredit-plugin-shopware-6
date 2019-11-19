<?php declare(strict_types=1);

namespace Netzkollektiv\EasyCredit\Api;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Netzkollektiv\EasyCredit\Setting\Service\SettingsServiceInterface;
use Netzkollektiv\EasyCreditApi\Client;
use Netzkollektiv\EasyCreditApi\Checkout;
use Netzkollektiv\EasyCreditApi\Client\HttpClientFactory;

class CheckoutFactory {
    
    public function __construct(
        SettingsServiceInterface $settingsService,
        LoggerInterface $logger,
        Session $session      
    ) {
        $this->settings = $settingsService;
        $this->logger = $logger;
        $this->session = $session;
    }
    
    public function create($salesChannelContext = null) {

        $salesChannelId = null;
        if ($salesChannelContext) {
            $salesChannelId = $salesChannelContext->getSalesChannel()->getId();
        }
        $settings = $this->settings->getSettings($salesChannelId);
        
        $logger = new Logger(
            $this->logger,
            $settings
        );
        $config = new Config(
            $settings
        );
        $clientFactory = new HttpClientFactory();

        $client = new Client(
            $config,
            $clientFactory,
            $logger
        );
        $storage = new Storage(
            $this->session
        );

        return new Checkout(
            $client,
            $storage
        );
    }
}
