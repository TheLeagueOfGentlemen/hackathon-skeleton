<?php

function array_pluck($arr, $keys) {
    return array_reduce(
        $keys,
        function ($reduced, $key) use ($arr) {
            $reduced[$key] = isset($arr[$key]) ? $arr[$key] : null;
            return $reduced;
        },
        array()
    );
}
