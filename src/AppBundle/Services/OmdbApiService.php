<?php

namespace AppBundle\Services;

class OmdbApiService
{
    const KEY = "db51a90b";

    public function getFilm($imdbId)
    {
        $response = $this->file_get_contents_curl("http://www.omdbapi.com/?i=".$imdbId."&apikey=".self::KEY);
        $response = json_decode($response, true);
        if ($response["Response"] === "True") {
            return $response;
        }
        return false;
    }

    private function file_get_contents_curl($url) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }
}