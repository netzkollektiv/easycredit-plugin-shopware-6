<?php declare(strict_types=1);
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Framework\Context;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

use Netzkollektiv\EasyCredit\Helper\Payment as PaymentHelper;
use Netzkollektiv\EasyCredit\Helper\Quote as QuoteHelper;
use Netzkollektiv\EasyCredit\EasyCreditRatenkauf;
use Netzkollektiv\EasyCredit\Payment\StateHandler;
use Netzkollektiv\EasyCredit\Api\IntegrationFactory;
use Netzkollektiv\EasyCredit\Webhook\OrderTransactionNotFoundException;

use Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionInformation;

/**
 * @RouteScope(scopes={"storefront"})
 */
class PaymentController extends StorefrontController
{
    private $integrationFactory;

    private $cartService;

    private $quoteHelper;

    private $stateHandler;

    public function __construct(
        IntegrationFactory $integrationFactory,
        CartService $cartService,
        QuoteHelper $quoteHelper,
        StateHandler $stateHandler,
        EntityRepositoryInterface $orderTransactionRepository
    ) {
        $this->integrationFactory = $integrationFactory;
        $this->cartService = $cartService;
        $this->quoteHelper = $quoteHelper;
        $this->stateHandler = $stateHandler;
        $this->orderTransactionRepository = $orderTransactionRepository;
    }

    /**
     * @Route("/easycredit/cancel", name="frontend.easycredit.cancel", options={"seo"="false"}, methods={"GET"})
     */
    public function cancel(SalesChannelContext $salesChannelContext): RedirectResponse
    {
        return $this->redirectToRoute('frontend.checkout.confirm.page');
    }

    /**
     * @Route("/easycredit/return", name="frontend.easycredit.return", options={"seo"="false"}, methods={"GET"})
     */
    public function return(SalesChannelContext $salesChannelContext): RedirectResponse
    {
        $checkout = $this->integrationFactory->createCheckout($salesChannelContext);

        if (!$checkout->isInitialized()) {
            throw new \Exception(
                'Payment was not initialized.'
            );
        }

        try {
            $checkout->loadTransaction();
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
        }

        return $this->redirectToRoute('frontend.checkout.confirm.page');
    }

    /**
     * @Route("/easycredit/reject", name="frontend.easycredit.reject", options={"seo"="false"}, methods={"GET"})
     */
    public function reject(SalesChannelContext $salesChannelContext): RedirectResponse
    {
        return $this->redirectToRoute('frontend.checkout.confirm.page');
    }

    /**
     * @Route("/easycredit/authorize/{secToken}/", name="frontend.easycredit.authorize", options={"seo"="false"}, methods={"GET"})
     */
    public function authorize(Request $request, SalesChannelContext $salesChannelContext): Response
    {
        $secToken = $request->attributes->get('secToken');
        $transactionId = $request->query->get('transactionId');

        try {
            if (!$transactionId) {
                throw new OrderTransactionNotFoundException([
                    'suffix' => 'no transaction ID provided'
                ]);
            }

            $orderTransaction = $this->getOrderTransaction(
                $transactionId,
                $secToken,
                $salesChannelContext->getContext()
            );

            $checkout = $this->integrationFactory->createCheckout($salesChannelContext);
            $tx = $checkout->loadTransaction($orderTransaction->getCustomFields()['easycredit_technical_transaction_id']);

            if ($tx->getStatus() !== TransactionInformation::STATUS_AUTHORIZED) {
                return new Response('payment status of transaction not updated as transaction status is not AUTHORIZED', Response::HTTP_CONFLICT);
            }

            $this->stateHandler->handleTransactionState(
                $orderTransaction,
                $salesChannelContext
            );
            $this->stateHandler->handleOrderState(
                $orderTransaction->getOrder(),
                $salesChannelContext
            );
            return new Response('payment status successfully set', Response::HTTP_OK);

        } catch (OrderTransactionNotFoundException $e) {
            return new Response($e->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (\Throwable $e) {
            return new Response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getOrderTransaction ($transactionId, $secToken, Context $context) {
        $criteria = new Criteria();
        $criteria->addAssociation('order');
        $criteria->addFilter(
            new EqualsFilter(
                \sprintf('customFields.%s', EasyCreditRatenkauf::ORDER_TRANSACTION_CUSTOM_FIELDS_EASYCREDIT_TRANSACTION_ID),
                $transactionId
            )
        );
        $criteria->addFilter(
            new EqualsFilter(
                \sprintf('customFields.%s', EasyCreditRatenkauf::ORDER_TRANSACTION_CUSTOM_FIELDS_EASYCREDIT_TRANSACTION_SEC_TOKEN),
                $secToken
            )
        );

        /** @var OrderTransactionEntity|null $orderTransaction */
        $orderTransaction = $this->orderTransactionRepository->search($criteria, $context)->first();

        if ($orderTransaction === null) {
            throw new OrderTransactionNotFoundException([
                'suffix' => \sprintf('with order transaction_id ID "%s" (order transaction ID)', $transactionId)
            ]);
        }

        return $orderTransaction;
    }
}
