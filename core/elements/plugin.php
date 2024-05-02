<?php

/** @var \MODX\Revolution\modX $modx */

if ($modx->services->has('mmxFenom')) {
    $modx->services->get('mmxFenom')->handleEvent($modx->event);
}