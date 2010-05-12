<?php
header('Content-type: text/json');

//
// parseProcStat is shamelessly stolen from
// phpSysInfo 3.0 - http://phpsysinfo.sourceforge.net/
//
function parseProcStat($cpuline)
{
    $load = 0;
    $load2 = 0;
    $total = 0;
    $total2 = 0;
    if ($buf = shell_exec('cat /proc/stat')) {
        $lines = preg_split("/\n/", $buf, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($lines as $line) {
            if (preg_match('/^'.$cpuline.' (.*)/', $line, $matches)) {
                $ab = 0;
                $ac = 0;
                $ad = 0;
                $ae = 0;
                sscanf($buf, "%*s %Ld %Ld %Ld %Ld", $ab, $ac, $ad, $ae);
                $load = $ab + $ac + $ad; // cpu.user + cpu.sys
                $total = $ab + $ac + $ad + $ae; // cpu.total
                break;
            }
        }
    }
    // we need a second value, wait 1 second befor getting (< 1 second no good value will occour)
    sleep(1);
    if ($buf = shell_exec('cat /proc/stat')) {
        $lines = preg_split("/\n/", $buf, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($lines as $line) {
            if (preg_match('/^'.$cpuline.' (.*)/', $line, $matches)) {
                $ab = 0;
                $ac = 0;
                $ad = 0;
                $ae = 0;
                sscanf($buf, "%*s %Ld %Ld %Ld %Ld", $ab, $ac, $ad, $ae);
                $load2 = $ab + $ac + $ad;
                $total2 = $ab + $ac + $ad + $ae;
                break;
            }
        }
    }
    if ($total > 0 && $total2 > 0 && $load > 0 && $load2 > 0 && $total2 != $total && $load2 != $load) {
        return number_format((100 * ($load2 - $load)) / ($total2 - $total), 2, '.', '');
    }
    return 0;
}

$mem_info = preg_split('/\n/', shell_exec("free -m"));

$total = preg_split('/\s+/', $mem_info[1]);
$total = $total[1];

$used = preg_split('/\s+/', $mem_info[2]);
$used = $used[2];

print json_encode(array(
        'cpu' => parseProcStat("cpu"),
        'load_avg' => implode(", ", sys_getloadavg()),
        'mem_total' => $total,
        'mem_used' => $used,
    ));
?>