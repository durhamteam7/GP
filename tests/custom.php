<?php

    require('algorithm.php');

    $array = array(array(array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "")));
    $res = array(0);

    $array2 = array(array(array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "animal")));
    $res2 = array(1);

    $array3 = array(array(array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "animal")),
                          array(array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "animal")));
    $res3 = array(1, 1);

    $array4 = array(array(array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "animal")),
                          array(array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "animal"),
                                array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "animal")));
    $res4 = array(1, 2);

    $s = new Swanson();

    print_r($s->get_species_counts($array));
    print_r($res);

    print_r($s->get_species_counts($array2));
    print_r($res2);

    print_r($s->get_species_counts($array3));
    print_r($res3);

    print_r($s->get_species_counts($array4));
    print_r($res4);
?>