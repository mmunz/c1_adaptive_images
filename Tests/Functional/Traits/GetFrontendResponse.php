<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\Tests\Functional\Traits;

use Nimut\TestingFramework\Http\Response;
use PHPUnit\Util\PHP\DefaultPhpProcess;

/**
 *
 *
 */
trait GetFrontendResponse
{

    /**
     * @param int $pageId
     * @param int $languageId
     * @param int $backendUserId
     * @param int $workspaceId
     * @param bool $failOnFailure
     * @param int $frontendUserId
     * @param string $arguments
     * @return Response
     */
    protected function getFrontendResponse($pageId, $languageId = 0, $backendUserId = 0, $workspaceId = 0, $failOnFailure = true, $frontendUserId = 0, $arguments = null)
    {
        $pageId = (int)$pageId;
        $languageId = (int)$languageId;

        $additionalParameter = '';

        if (!empty($frontendUserId)) {
            $additionalParameter .= '&frontendUserId=' . (int)$frontendUserId;
        }
        if (!empty($backendUserId)) {
            $additionalParameter .= '&backendUserId=' . (int)$backendUserId;
        }
        if (!empty($workspaceId)) {
            $additionalParameter .= '&workspaceId=' . (int)$workspaceId;
        }
        if (!empty($arguments)) {
            $additionalParameter .= $arguments;
        }

        $arguments = [
            'documentRoot' => $this->getInstancePath(),
            'requestUrl' => 'http://localhost/?id=' . $pageId . '&L=' . $languageId . $additionalParameter,
        ];

        $template = new \Text_Template('ntf://Frontend/Request.tpl');
        $template->setVar(
            [
                'arguments' => var_export($arguments, true),
                'originalRoot' => ORIGINAL_ROOT,
                'ntfRoot' => __DIR__ . '/../../../',
            ]
        );

        /** @var DefaultPhpProcess $php */
        $php = DefaultPhpProcess::factory();
        $response = $php->runJob($template->render());
        $result = json_decode($response['stdout'], true);

        if ($result === null) {
            $this->fail('Frontend Response is empty.' . LF . 'Error: ' . LF . $response['stderr']);
        }

        if ($failOnFailure && $result['status'] === Response::STATUS_Failure) {
            $this->fail('Frontend Response has failure:' . LF . $result['error']);
        }

        $response = new Response($result['status'], $result['content'], $result['error']);

        return $response;
    }
}
