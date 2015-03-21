<?php

function fetch_defra($site_id = 'ACTH', $view = 'last_hour') {

    $site_id = preg_replace('![^A-Z]!','',$site_id);
    $view = preg_replace('![^a-z_]!','',$view);
    $url = "http://uk-air.defra.gov.uk/data/site-data?f_site_id=${site_id}&view=${view}";

    $html = file_get_contents($url);
    $html_without_attributes = preg_replace('!<([a-z0-9]+) [^/>]+>!','<$1>',$html);
    preg_match('!<table>.+?</table>!s',$html_without_attributes,$m);
    $html_table_clean = preg_replace('!</td>\r\n!','</td>',$m[0]);
    $html_table_clean = preg_replace('!<tr>\r\n!','<tr>',$html_table_clean);
    $html_table_clean = preg_replace('!\t!','',$html_table_clean);
    $html_table_clean = preg_replace('!\r!','',$html_table_clean);
    $html_table_clean = preg_replace('!\n\n!',"\n",$html_table_clean);

    preg_match_all('!<th>(.+?)</th>!',$html_table_clean,$m);
    $headings = $m[1];

    preg_match_all('!<tr>(.+?)</tr>!',$html_table_clean,$m);
    $data = array();
    foreach ($m[1] as $v) {
        preg_match_all('!<td>(.+?)</td>!',$v,$n);
	$d = array();
        foreach ($headings as $k=>$h) {
        	$d[strtolower($h)] = $n[1][$k];
	}
        $data[] = $d;
    }

    return array(
        'headings'=> $headings,
        'data'=> $data
    );
}
