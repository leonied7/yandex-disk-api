<?php
/**
 * Created by PhpStorm.
 * User: Денис
 * Date: 15.03.2019
 * Time: 9:41
 * @author Denis Kolosov <kdnn@mail.ru>
 */

namespace Leonied7\Yandex\Disk\Http;


use Leonied7\Yandex\Disk\Entity\Result;

class Transport
{
    /**
     * @param Request $request
     * @return Result
     */
    public function send(Request $request)
    {
        $request->beforeSend();

        $curlOptions = $this->prepare($request);
        $curlResource = $this->initCurl($curlOptions);

        $headers = [];
        $this->setHeaderOutput($curlResource, $headers);

        $content = curl_exec($curlResource);
        $code = curl_getinfo($curlResource, CURLINFO_HTTP_CODE);
        $type = curl_getinfo($curlResource, CURLINFO_CONTENT_TYPE);

        curl_close($curlResource);

        $response = $request->getBuilder()->createResponse($content, $headers, $code, $type);
        $result = $request->getBuilder()->createResult($response);
        $request->afterSend($result);

        return $result;
    }

    /**
     * @param Request[] $requests requests to perform.
     * @return Result[] responses list.
     */
    public function batchSend(array $requests)
    {
        $curlBatchResource = curl_multi_init();

        $curlResources = [];
        $headers = [];
        /* @var $request Request */
        foreach ($requests as $key => $request) {
            $request->beforeSend();

            $curlOptions = $this->prepare($request);
            $curlResource = $this->initCurl($curlOptions);

            $headers[$key] = [];
            $this->setHeaderOutput($curlResource, $headers[$key]);
            $curlResources[$key] = $curlResource;
            curl_multi_add_handle($curlBatchResource, $curlResource);
        }

        $active = null;
        do {
            if (curl_multi_select($curlBatchResource) === -1) {
                usleep(100);
            }
            do {
                $mrc = curl_multi_exec($curlBatchResource, $active);
            } while ($mrc === CURLM_CALL_MULTI_PERFORM);
        } while ($active > 0 && $mrc === CURLM_OK);


        $contents = [];
        foreach ($curlResources as $key => $curlResource) {
            $contents[$key] = curl_multi_getcontent($curlResource);
            curl_multi_remove_handle($curlBatchResource, $curlResource);
        }

        $results = [];
        foreach ($requests as $key => $request) {
            $curlResource = $curlResources[$key];
            $code = curl_getinfo($curlResource, CURLINFO_HTTP_CODE);
            $type = curl_getinfo($curlResource, CURLINFO_CONTENT_TYPE);
            curl_close($curlResource);

            $response = $request->getBuilder()->createResponse($contents[$key], $headers[$key], $code, $type);
            $result = $request->getBuilder()->createResult($response);
            $request->afterSend($result);
            $results[$key] = $result;
        }
        curl_multi_close($curlBatchResource);

        return $results;
    }

    private function prepare(Request $request)
    {
        $curlOptions = [
            CURLOPT_URL => $request->getFullUrl(),
            CURLOPT_TIMEOUT => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $request->buildHeaders(),
            /** TODO: придумать как это убрать */
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ];

        $method = strtoupper($request->getMethod());
        if ($method === 'POST') {
            $curlOptions[CURLOPT_POST] = true;
        } else {
            $curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
        }

        $content = $request->getContent();
        if ($content !== null) {
            $curlOptions[CURLOPT_POSTFIELDS] = $content;
        }

        $curlOptions = array_replace($curlOptions, $request->getOptions());
        return $curlOptions;
    }

    private function initCurl(array $curlOptions)
    {
        $curlResource = curl_init();

        foreach ($curlOptions as $option => $value) {
            curl_setopt($curlResource, $option, $value);
        }

        return $curlResource;
    }

    private function setHeaderOutput($curlResource, array &$output)
    {
        curl_setopt($curlResource, CURLOPT_HEADERFUNCTION, function ($resource, $headerString) use (&$output) {
            $header = trim($headerString, "\n\r");
            if ($header !== '') {
                $output[] = $header;
            }
            return mb_strlen($headerString, '8bit');
        });
    }
}