<?php

declare(strict_types=1);

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SkeletonDancer\Cli\Handler;

use SkeletonDancer\Configuration\DanceSelector;
use SkeletonDancer\Dances;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Console\Api\Args\Args;

final class DancesCommandHandler
{
    private $style;
    private $dances;
    private $bufferedOut;
    private $danceSelector;

    public function __construct(SymfonyStyle $style, DanceSelector $danceSelector, Dances $dances)
    {
        $this->style = $style;
        $this->dances = $dances;
        $this->danceSelector = $danceSelector;

        $this->bufferedOut = new BufferedOutput(null, $this->style->isDecorated(), $this->style->getFormatter());
    }

    public function handleList()
    {
        $this->style->title('List dances');

        $dances = $this->dances->all();
        $this->style->listing(array_keys($dances));

        return 0;
    }

    public function handleShow(Args $args)
    {
        $this->style->title('Show dance information');

        $dance = $this->danceSelector->resolve($args->getArgument('dance'));
        $this->style->section($dance->name);

        $row = [
            ['Title', $dance->title],
            ['Description', $dance->description."\n"],
            ['Configurators', ($dance->questioners ? '- '.implode("\n- ", $dance->questioners) : '[ ]')."\n"],
            ['Generators', $dance->generators ? '- '.implode("\n- ", $dance->generators) : '[ ]'],
        ];

        $this->style->write($this->detailsTable($row), false, SymfonyStyle::OUTPUT_RAW);

        return 0;
    }

    private function detailsTable(array $rows)
    {
        if (!$rows) {
            return '';
        }

        $rows = array_map(
            function ($row) {
                $row[0] = sprintf('<info>%s:</info>', $row[0]);

                return $row;
            },
            $rows
        );

        $table = new Table($this->bufferedOut);
        $table->getStyle()
            ->setPaddingChar(' ')
            ->setHorizontalBorderChar('')
            ->setVerticalBorderChar(' ')
            ->setCrossingChar('')
            ->setCellHeaderFormat('%s')
            ->setCellRowFormat('%s')
            ->setCellRowContentFormat('%s')
            ->setBorderFormat('%s')
            ->setPadType(STR_PAD_RIGHT)
        ;
        $table->setRows($rows);
        $table->render();

        return $this->bufferedOut->fetch();
    }
}
