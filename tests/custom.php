<?php

    require('algorithm.php');

    echo "get species count";

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

    echo "pielou index";
    echo "\n";

    $array = array(0);
    print_r(0);
    echo "\n";
    print_r($s->calculate_pielou($array));
    echo "\n";

    $array2 = array(2, 3, 4, 2);
    $lns = log(4);
    $plnplist = array((2/11) * log(2/11),
                      (3/11) * log(3/11),
                      (4/11) * log(4/11),
                      (2/11) * log(2/11));
    $r = -array_sum($plnplist);
    $res = $r / $lns;
    print_r($res);
    echo "\n";
    print_r($s->calculate_pielou($array2));
    echo "\n";

    $array3 = array(2, 2, 2, 2);
    $lns2 = log(4);
    $plnplist2 = array((2/8) * log(2/8),
                       (2/8) * log(2/8),
                       (2/8) * log(2/8),
                       (2/8) * log(2/8));
    $r2 = -array_sum($plnplist2);
    $res2 = $r2 / $lns2;
    print_r($res2);
    echo "\n";
    print_r($s->calculate_pielou($array3));
    echo "\n";

    echo "calculate num animals index";
    echo "\n";

    $array = array();
    $array2 = array(0);
    $array3 = array(5, 2, 1, 4, 3);

    $res = array(null, null, null);
    $res2 = array(0, 0, 0);
    $res3 = array(1, 3, 5);

    print_r($s->calculate_num_animals($array));
    print_r($res);
    print_r($s->calculate_num_animals($array2));
    print_r($res2);
    print_r($s->calculate_num_animals($array3));
    print_r($res3);
?>