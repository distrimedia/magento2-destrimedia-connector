<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Helper;

use DistriMedia\Connector\Model\ConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;

class ErrorHandlingHelper
{
    private $config;
    private $transportBuilder;
    private $inlineTranslation;

    public function __construct(
        ConfigInterface $config,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->config = $config;
    }

    /**
     * Send email to administrator if error
     *
     * @return $this
     */
    public function sendErrorEmail(array $errors, string $subject = '', string $title = '')
    {
        $template = $this->config->getErrorEmailTemplate();
        $identity = $this->config->getErrorEmailIdentity();
        $recipient = $this->config->getErrorEmailRecipient();

        if (!$recipient) {
            throw new \Exception('No Error email recipient defined');
        }

        if (count($errors)) {
            if (!$template) {
                return $this;
            }

            $this->inlineTranslation->suspend();

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($template)
                ->setTemplateOptions(
                [
                    'area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
                )->setTemplateVars(
                    [
                        'warnings' => implode('<br />', $errors),
                        'subject' => $subject,
                        'title' => $title,
                    ]
                )->setFrom($identity)
                ->addTo($recipient)
                ->getTransport();

            $transport->sendMessage();

            $this->inlineTranslation->resume();
        }
    }
}
