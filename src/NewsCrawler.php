<?php

namespace App;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class NewsCrawler
{
    /**
     * Retrieves news articles from multiple URLs based on provided CSS selectors
     *
     * @param array $urlSelectors array where keys are URLs and values are arrays of CSS selectors
     * @return array
     *
     * @throws ClientExceptionInterface client error during HTTP request
     * @throws RedirectionExceptionInterface redirection error during HTTP request
     * @throws ServerExceptionInterface server error during HTTP request
     * @throws TransportExceptionInterface transport level error occurs during HTTP request
     */
    public function getNews(array $urlSelectors): array
    {
        $client = HttpClient::create();
        $news = [];

        foreach ($urlSelectors as $url => $selectors) {
            try {
                $postSelector = $selectors['postSelector'] ?? '.blog-post';
                $titleSelector = $selectors['titleSelector'] ?? 'h2 a';
                $pathSelector = $selectors['pathSelector'] ?? 'h2 a';

                $response = $client->request('GET', $url);
                $crawler = new Crawler($response->getContent());
                $filteredNodes = $crawler->filter($postSelector);

                if ($filteredNodes->count() > 0) {
                    $filteredNodes->each(function ($node) use (&$news, $url, $titleSelector, $pathSelector) {
                        $this->processNode($node, $news, $url, $titleSelector, $pathSelector);
                    });
                } else {
                    $this->addNoNewsFound($news);
                }
            } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
                error_log('HTTP Client error: ' . $e->getMessage());
                $this->addNoNewsFound($news);
            } catch (\Exception $e) {
                error_log('Unexpected error: ' . $e->getMessage());
                $this->addNoNewsFound($news);
            }
        }

        return $news;
    }

    /**
     * Processes a single DOM node to extract news title and URL
     *
     * @param \Symfony\Component\DomCrawler\Crawler $node The DOM node to process
     * @param array $news Reference to the news array to append results
     * @param string $url The base URL of the news page
     * @param string $titleSelector CSS selector for the title of the news article
     * @param string $pathSelector CSS selector for the path or URL of the news article
     */
    private function processNode(
        Crawler $node,
        array &$news,
        string $url,
        string $titleSelector,
        string $pathSelector
    )
    {
        try {
            $title = $node->filter($titleSelector)->text();
            $path = $node->filter($pathSelector)->attr('href');
            $fullUrl = $this->constructFullUrl($url, $path);

            $news[] = [
                'title' => $title,
                'url' => $fullUrl,
            ];
        } catch (\Exception $e) {
            error_log('Error fetching title or path: ' . $e->getMessage());
        }
    }

    /**
     * Constructs a full URL from a base URL and a path
     *
     * @param string $url base URL
     * @param string $path relative or absolute path
     * @return string constructed full URL
     */
    private function constructFullUrl(string $url, string $path): string
    {
        $baseUrl = parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST);

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return strpos($path, '/') === 0
            ? rtrim($baseUrl, '/') . $path
            : rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Adds a placeholder entry to indicate no news articles were found
     *
     * @param array $news Reference to the news array to append the placeholder entry
     */
    private function addNoNewsFound(array &$news)
    {
        $news[] = [
            'title' => 'No news found',
            'url' => '#',
        ];
    }
}
