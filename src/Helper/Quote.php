<?php declare(strict_types=1);
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Helper;

use Netzkollektiv\EasyCredit\Api;
use Netzkollektiv\EasyCreditApi\Rest\QuoteInterface;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RequestStack;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

use Netzkollektiv\EasyCredit\Helper\MetaDataProvider;

class Quote
{
    private $requestStack;

    private $cartService;

    public function __construct(
        RequestStack $requestStack,
        CartService $cartService,
        MetaDataProvider $metaDataProvider
    ) {
        $this->requestStack = $requestStack;
        $this->cartService = $cartService;
        $this->metaDataProvider = $metaDataProvider;
    }

    /**
     * @param Cart|\Shopware\Core\Checkout\Order\OrderEntity|null $cart
     */
    public function getQuote(SalesChannelContext $salesChannelContext, $cart = null): QuoteInterface
    {
        if ($cart === null) {
            $cart = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);
        }
        if ($cart instanceof Cart) {
            return new Api\Quote(
                $cart,
                $this->metaDataProvider,
                $salesChannelContext
            );
        }

        return new Api\Order(
            $cart,
            $this->metaDataProvider,
            $salesChannelContext
        );
    }
}
