<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\VarDumper\Command\Descriptor;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use function var_dump;

/**
 * Describe collected data clones for html output.
 *
 * @author Maxime Steinhausser <maxime.steinhausser@gmail.com>
 *
 * @final
 */
class HtmlDescriptor implements DumpDescriptorInterface
{
    private $dumper;
    private bool $initialized = false;

    public function __construct(HtmlDumper $dumper)
    {
        $this->dumper = $dumper;
    }

    public function describe(OutputInterface $output, Data $data, array $context, int $clientId): void
    {
        if (!$this->initialized) {
            $styles = file_get_contents(__DIR__.'/../../Resources/css/htmlDescriptor.css');
            $scripts = file_get_contents(__DIR__.'/../../Resources/js/htmlDescriptor.js');
            $output->writeln("<html><head>");
            $output->writeln("<style>$styles</style><script>$scripts</script>");
            $output->writeln("</head><body style='visibility: hidden'>");

            $output->writeln('<div style="order: 1"><header class=""></header><section class="body"><p class="text-small">');
            $output->writeln("<a style=' background-color: #4CAF50;  border: none;  color: white;  padding: 15px 32px;  text-align: center;  text-decoration: none;  display: inline-block;  font-size: 16px;' id='clearBtn' href=''>Clear</a>&nbsp;");
            $output->writeln("<a style=' background-color: #4CAF50;  border: none;  color: white;  padding: 15px 32px;  text-align: center;  text-decoration: none;  display: inline-block;  font-size: 16px;' id='refreshBtn' href=''>Refresh</a>");
            $output->writeln("</p></section></div>");
            $this->initialized = true;
        }

        $title = '-';
        if (isset($context['request'])) {
            $request = $context['request'];
            $controller = "<span class='dumped-tag'>{$this->dumper->dump($request['controller'], true, ['maxDepth' => 0])}</span>";
            $title = sprintf('<code>%s</code> <a href="%s">%s</a>', $request['method'], $uri = $request['uri'], $uri);
            // aj
            $dedupIdentifier = $request['_stopwatch_token'];
            //$dedupIdentifier = $request['identifier'];
        } elseif (isset($context['cli'])) {
            $title = '<code>$ </code>'.$context['cli']['command_line'];
            $dedupIdentifier = $context['cli']['identifier'];
        } else {
            $dedupIdentifier = uniqid('', true);
        }

        $sourceDescription = '';
        if (isset($context['source'])) {
            $source = $context['source'];
            $projectDir = $source['project_dir'] ?? null;
            $sourceDescription = sprintf('%s on line %d', $source['name'], $source['line']);
            if (isset($source['file_link'])) {
                $sourceDescription = sprintf('<a href="%s">%s</a>', $source['file_link'], $sourceDescription);
            }
        }

        $isoDate = $this->extractDate($context, 'c');
        $tags = array_filter([
            'controller' => $controller ?? null,
            'project dir' => $projectDir ?? null,
        ]);

        // so can merge dumps from same req and dump call
        $outid = $dedupIdentifier . $source['name'] . $source['line'];



            $output->writeln(
                <<<HTML
<article data-dedup-id="$dedupIdentifier">
    <header>
        <div class="row">
            <h2 class="col">$title</h2>
            <time class="col text-small" title="$isoDate" datetime="$isoDate">
                {$this->extractDate($context)}
            </time>
        </div>
        {$this->renderTags($tags)}
    </header>
    <section class="body">
        <p class="text-small" data-dedup-id="$outid">
            $sourceDescription
        </p>
        {$this->dumper->dump($data, true)}
    </section>
</article>
HTML
            );

    }

    private function extractDate(array $context, string $format = 'r'): string
    {
        return date($format, (int) $context['timestamp']);
    }

    private function renderTags(array $tags): string
    {
        if (!$tags) {
            return '';
        }

        $renderedTags = '';
        foreach ($tags as $key => $value) {
            $renderedTags .= sprintf('<li><span class="badge">%s</span>%s</li>', $key, $value);
        }

        return <<<HTML
<div class="row">
    <ul class="tags">
        $renderedTags
    </ul>
</div>
HTML;
    }
}
