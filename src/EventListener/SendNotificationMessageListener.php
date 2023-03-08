<?php

namespace Terminal42\NewsNewsletterBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\FilesModel;
use Contao\Validator;
use NotificationCenter\Model\Gateway;
use NotificationCenter\Model\Message;

/**
 * @Hook("sendNotificationMessage")
 */
class SendNotificationMessageListener
{
    /**
     * @param Message $objMessage
     * @param array $arrTokens
     * @param string $language
     * @param Gateway $objGatewayModel
     * @return bool
     */
    public function __invoke($objMessage, &$arrTokens, $language, $objGatewayModel): bool
    {
        if (($objMessage->getRelated('pid')->type !== 'news_newsletter_default')
            || 'queue' !== $objMessage->gateway_type
            || (false !== json_encode($arrTokens))) {
            return true;
        }

        foreach ($arrTokens as $key => $token) {
            if (false !== json_encode($token)) {
                continue;
            }

            if (Validator::isBinaryUuid($token)) {
                $fileModel = FilesModel::findByUuid($token);
                $arrTokens[$key] = $fileModel->path;
                continue;
            }

            if (false === json_encode($token)) {
                unset($arrTokens[$key]);
            }
        }

        return true;
    }

}
