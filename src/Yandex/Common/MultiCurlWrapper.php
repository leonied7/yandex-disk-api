<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 26.04.2017
 * Time: 9:52
 */

namespace Yandex\Common;

class MultiCurlWrapper
{
    protected $recourse;
    /**
     *
     * @var CurlWrapper[]
     */
    protected $arCurl = array();

    /**
     * активен ли сейчас мультикурл
     * @var null
     */
    protected $active = null;
    /**
     * хз зачем, как понимаю это статус
     * @var
     */
    protected $mrc;

    function __construct(array $curlWrappers = array())
    {
        $this->recourse = curl_multi_init();

        $this->setCurls($curlWrappers);

        $this->addCurls();
    }

    /**
     * @param CurlWrapper[] ...$curlWrappers
     */
    protected function setCurls(array $curlWrappers = array())
    {
        foreach($curlWrappers as $curlWrapper)
        {
            $this->arCurl[] = $curlWrapper;
        }

    }

    function exec()
    {
        do {
            $this->mrc = curl_multi_exec($this->recourse, $this->active);
        } while ($this->mrc == CURLM_CALL_MULTI_PERFORM);

        while ($this->active && $this->mrc == CURLM_OK) {
            if (curl_multi_select($this->recourse) == -1) {
                continue;
            }

            do {
                $this->mrc = curl_multi_exec($this->recourse, $this->active);
            } while ($this->mrc == CURLM_CALL_MULTI_PERFORM);
        }

        $result = array();

        foreach ($this->arCurl as $key => $curlWrapper)
        {
            $curl = $curlWrapper->getCurl();

            $body = curl_multi_getcontent($curl);
            $response = new CurlResponse(
                $body,
                curl_getinfo($curl, CURLINFO_HTTP_CODE),
                curl_getinfo($curl, CURLINFO_HEADER_OUT),
                curl_getinfo($curl, CURLINFO_CONTENT_TYPE),
                $curlWrapper->getResponseHandler()
            );

            $result[] = $response;

            curl_multi_remove_handle($this->recourse, $curl);

            unset($this->arCurl[$key]);
        }

        $this->active = null;

        curl_multi_close($this->recourse);

        return $result;
    }

    protected function addCurls()
    {
        foreach($this->arCurl as $curl)
        {
            curl_multi_add_handle($this->recourse, $curl->getCurl());
        }
    }
}