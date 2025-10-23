<?php

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray', 'die'])
    ->each->not->toBeUsed();
