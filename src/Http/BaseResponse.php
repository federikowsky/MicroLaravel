<?php

namespace App\HTTP;

use App\Facades\Encrypt;
use App\HTTP\ResponseData;

class BaseResponse
{
    protected ResponseData $response_data;

    public function __construct(ResponseData $response_data)
    {
        $this->response_data = $response_data;
    }

    public function send(): void
    {
        // Imposta gli header
        foreach ($this->response_data->get_headers() as $key => $value) {
            header("$key: $value");
        }

        // Imposta i cookie
        foreach ($this->response_data->get_cookies() as $cookie) {
            if ($cookie->expire < time()) {
                $cookie->expire = time() - 3600;
                $cookie->value = '';
            }
            setcookie(
                $cookie->name,
                Encrypt::encrypt($cookie->value),
                $cookie->expire,
                $cookie->path,
                $cookie->domain,
                $cookie->secure,
                $cookie->httpOnly
            );
        }

        // Imposta lo status HTTP
        http_response_code($this->response_data->get_status());

        // Mostra il contenuto
        echo $this->response_data->get_content();
    }
}
