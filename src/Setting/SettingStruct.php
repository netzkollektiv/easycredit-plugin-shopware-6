<?php declare(strict_types=1);

namespace Netzkollektiv\EasyCredit\Setting;

use Shopware\Core\Framework\Struct\Struct;

class SettingStruct extends Struct
{
    /**
     * @var string
     */
    protected $webshopId;

    /**
     * @var string
     */
    protected $apiPassword;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * @var bool
     */
    protected $widgetEnabled;

    /**
     * @var string|null
     */
    protected $widgetSelectorProductDetail;

    /**
     * @var string|null
     */
    protected $widgetSelectorCart;

    public function getWebshopId(): string
    {
        return $this->webshopId;
    }

    public function setWebshopId(string $webshopId): void
    {
        $this->webshopId = $webshopId;
    }

    public function getApiPassword(): string
    {
        return $this->apiPassword;
    }

    public function setApiPassword(string $apiPassword): void
    {
        $this->apiPassword = $apiPassword;
    }

    public function getDebug(): bool
    {
        return $this->debug;
    }

    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    public function getWidgetEnabled(): bool
    {
        return $this->widgetEnabled;
    }

    public function setWidgetEnabled(bool $widgetEnabled): void
    {
        $this->widgetEnabled = $widgetEnabled;
    }

    public function setWidgetSelectorProductDetail(string $widgetSelectorProductDetail): void
    {
        $this->widgetSelectorProductDetail = $widgetSelectorProductDetail;
    }

    public function getWidgetSelectorProductDetail(): ?string
    {
        return $this->widgetSelectorProductDetail;
    }

    public function getWidgetSelectorCart(): ?string
    {
        return $this->widgetSelectorCart;
    }

    public function setWidgetSelectorCart(string $widgetSelectorCart): void
    {
        $this->widgetSelectorCart = $widgetSelectorCart;
    }
}
