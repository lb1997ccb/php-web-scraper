<?php

namespace App;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class SEOScraper
{
    /**
     * Scrapes SEO data for multiple URLs based on given keywords
     *
     * @param array $urlKeywords array where keys are URLs and values are arrays of keywords
     * @return array
     *
     * @throws ClientExceptionInterface client error during HTTP request
     * @throws RedirectionExceptionInterface redirection error during HTTP request
     * @throws ServerExceptionInterface server error during HTTP request
     * @throws TransportExceptionInterface transport level error occurs during HTTP request
     */
    public function getSEOData(array $urlKeywords): array
    {
        $client = HttpClient::create();
        $allData = [];

        foreach ($urlKeywords as $url => $keywords) {
            try {
                $response = $client->request('GET', $url);
                $crawler = new Crawler($response->getContent());

                $title = $this->getTitle($crawler);
                $metaTags = $this->getMetaTags($crawler);
                $keywordData = $this->analyzeKeywords($crawler, $keywords);
                $searchEnginePositions = $this->simulateSearchEnginePositions($keywords);

                $allData[] = [
                    'url' => $url,
                    'title' => $title,
                    'meta_tags' => $metaTags,
                    'keyword_analysis' => $keywordData,
                    'search_engine_positions' => $searchEnginePositions,
                ];
            } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
                error_log('HTTP Client error for URL ' . $url . ': ' . $e->getMessage());
                $allData[] = [
                    'url' => $url,
                    'title' => 'Error fetching data',
                    'meta_tags' => [],
                    'keyword_analysis' => [],
                    'search_engine_positions' => [],
                ];
            } catch (\Exception $e) {
                error_log('Unexpected error for URL ' . $url . ': ' . $e->getMessage());
                $allData[] = [
                    'url' => $url,
                    'title' => 'Unexpected error',
                    'meta_tags' => [],
                    'keyword_analysis' => [],
                    'search_engine_positions' => [],
                ];
            }
        }

        return $allData;
    }

    /**
     *
     * @param Crawler $crawler
     * @return string
     */
    private function getTitle(Crawler $crawler): string
    {
        try {
            return $crawler->filter('title')->text();
        } catch (\Exception $e) {
            error_log('Error fetching title: ' . $e->getMessage());
            return 'No title found';
        }
    }

    /**
     * Retrieves meta tags from the crawler
     *
     * @param Crawler $crawler
     * @return array
     */
    private function getMetaTags(Crawler $crawler): array
    {
        $metaTags = [];
        $crawler->filter('meta')->each(function ($node) use (&$metaTags) {
            $name = $node->attr('name');
            $content = $node->attr('content');
            if (!empty($name) || !empty($content)) {
                $metaTags[] = [
                    'name' => isset($name) ? $name : 'No name',
                    'content' => isset($content) ? $content : 'No content',
                ];
            }
        });
        return $metaTags;
    }

    /**
     * Analyzes keyword usage in the crawler content
     *
     * @param Crawler $crawler
     * @param array $keywords
     * @return array
     */
    private function analyzeKeywords(Crawler $crawler, array $keywords): array
    {
        $keywordData = [];
        foreach ($keywords as $keyword) {
            $count = substr_count(strtolower($crawler->text()), strtolower($keyword));
            $keywordData[] = [
                'keyword' => $keyword,
                'usage_count' => $count,
            ];
        }
        return $keywordData;
    }

    /**
     * Simulates search engine ranking positions for given keywords
     *
     * @param array $keywords
     * @return array
     */
    private function simulateSearchEnginePositions(array $keywords): array
    {
        $searchEnginePositions = [];
        foreach ($keywords as $keyword) {
            // Randomly simulating a ranking position between 1 and 10
            $position = rand(1, 10);
            $searchEnginePositions[] = [
                'keyword' => $keyword,
                'position' => $position,
            ];
        }
        return $searchEnginePositions;
    }
}


