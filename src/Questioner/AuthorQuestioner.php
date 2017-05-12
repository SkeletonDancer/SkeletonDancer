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

namespace SkeletonDancer\Questioner;

use SkeletonDancer\Question;
use SkeletonDancer\Questioner;
use SkeletonDancer\QuestionsSet;
use SkeletonDancer\Service\Git;

final class AuthorQuestioner implements Questioner
{
    private $git;

    public function __construct(Git $git)
    {
        $this->git = $git;
    }

    public function interact(QuestionsSet $questions)
    {
        $questions->communicate('author_name', Question::ask('Author name', $this->git->getGitConfig('user.name', 'global')));
        $questions->communicate('author_email', Question::ask('Author e-mail', $this->git->getGitConfig('user.email', 'global')));
    }
}
