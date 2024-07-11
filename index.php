<?php

require 'vendor/autoload.php';

use App\NewsScraper;
use App\SEOScraper;

$newsScraper = new NewsScraper();
$seoScraper = new SEOScraper();

$urlSelectors = [
    'https://symfony.com/blog/' => [],
    'https://blog.angular.dev/' => [
        'postSelector' => '.js-trackPostPresentation',
        'titleSelector' => 'a h3 div',
        'pathSelector' => 'a',
    ],
];

$allNews = $newsScraper->getNews($urlSelectors);

$urlKeywords = [
    'https://symfony.com/blog/' => ['Symfony', 'PHP', 'Framework'],
    'https://blog.angular.dev/' => ['Angular', 'TypeScript', 'JavaScript'],
];

$seoData = $seoScraper->getSEOData($urlKeywords);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scraping Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }

        .table-container {
            overflow-x: auto;
            width: 100%;
        }
    </style>
</head>
<body>

<h2>Scraping Results</h2>

<?php foreach ($urlSelectors as $url => $selectors): ?>
    <h3>News from <?php echo $url; ?></h3>

    <?php
    $newsForUrl = array_filter($allNews, function ($item) use ($url) {
        return strpos($item['url'], $url) !== false;
    });
    ?>

    <?php if (!empty($newsForUrl)): ?>
        <ul>
            <?php foreach ($newsForUrl as $item): ?>
                <li><a href="<?php echo $item['url']; ?>" target="_blank"><?php echo $item['title']; ?></a></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No news found for <?php echo $url; ?></p>
    <?php endif; ?>
<?php endforeach; ?>

<h3>SEO Data</h3>
<?php if (!empty($seoData)): ?>
<div class="table-container">
    <table>
        <thead>
        <tr>
            <?php if (isset($seoData[0]['url'])): ?><th>URL</th><?php endif; ?>
            <?php if (isset($seoData[0]['title'])): ?><th>Title</th><?php endif; ?>
            <?php if (isset($seoData[0]['meta_tags'])): ?><th>Meta Tags</th><?php endif; ?>
            <?php if (isset($seoData[0]['keyword_analysis'])): ?><th>Keyword Analysis</th><?php endif; ?>
            <?php if (isset($seoData[0]['search_engine_positions'])): ?><th>Search Engine Positions</th><?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($seoData as $data): ?>
            <tr>
                <?php if (isset($data['url'])): ?><td><?php echo $data['url']; ?></td><?php endif; ?>
                <?php if (isset($data['title'])): ?><td><?php echo $data['title']; ?></td><?php endif; ?>
                <?php if (isset($data['meta_tags'])): ?>
                    <td>
                        <ul>
                            <?php foreach ($data['meta_tags'] as $tag): ?>
                                <li><?php echo (!empty($tag['name']) ? $tag['name'] : 'Name') . ': ' . (!empty($tag['content']) ? $tag['content'] : 'Content'); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                <?php endif; ?>
                <?php if (isset($data['keyword_analysis'])): ?>
                    <td>
                        <ul>
                            <?php foreach ($data['keyword_analysis'] as $keyword): ?>
                                <li><?php echo $keyword['keyword'] . ': ' . $keyword['usage_count']; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                <?php endif; ?>
                <?php if (isset($data['search_engine_positions'])): ?>
                    <td>
                        <ul>
                            <?php foreach ($data['search_engine_positions'] as $position): ?>
                                <li><?php echo $position['keyword'] . ': ' . $position['position']; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
    <p>No SEO data found.</p>
<?php endif; ?>
</body>
</html>
