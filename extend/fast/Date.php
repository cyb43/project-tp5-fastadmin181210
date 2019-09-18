<?php

namespace fast;

/**
 * 日期时间处理类
 * @author ^2_3^
 */
class Date
{

    const YEAR = 31536000; //365天;
    const MONTH = 2592000; //30天;
    const WEEK = 604800;
    const DAY = 86400;
    const HOUR = 3600;
    const MINUTE = 60;

    /**
     * 计算两个时区间相差的时长,单位为秒
     *
     * $seconds = self::offset('America/Chicago', 'GMT');
     *
     * [!!] A list of time zones that PHP supports can be found at <http://php.net/timezones>.
     *
     * @param   string  $remote timezone that to find the offset of
     * @param   string  $local  timezone used as the baseline
     * @param   mixed   $now    UNIX timestamp or date string
     * @return  integer
     * @author ^2_3^
     */
    public static function offset($remote, $local = NULL, $now = NULL)
    {
        //// 本地时区
        if ($local === NULL)
        {
            // Use the default timezone
            $local = date_default_timezone_get();
        }

        //// 日期字串
        if (is_int($now))
        {
            // Convert the timestamp into a string
            // DateTimeInterface::RFC2822 = "D, d M Y H:i:s O" ;
            // D，星期中的第几天，文本表示，3 个字母 Mon 到 Sun；
            // d，月份中的第几天，有前导零的 2 位数字	01 到 31；
            // O，与格林威治时间相差的小时数，例如：+0200；
            $now = date(DateTime::RFC2822, $now);
        }

        // Create timezone objects
        $zone_remote = new DateTimeZone($remote);
        $zone_local = new DateTimeZone($local);

        // Create date objects from timezones
        $time_remote = new DateTime($now, $zone_remote);
        $time_local = new DateTime($now, $zone_local);

        // Find the offset
        $offset = $zone_remote->getOffset($time_remote) - $zone_local->getOffset($time_local);

        return $offset;
    }

    /**
     * 计算两个时间戳之间相差的时间
     *
     * $span = self::span(60, 182, 'minutes,seconds'); // array('minutes' => 2, 'seconds' => 2)
     * $span = self::span(60, 182, 'minutes'); // 2
     *
     * @param   int $remote timestamp to find the span of
     * @param   int $local  timestamp to use as the baseline
     * @param   string  $output formatting string
     * @return  string   when only a single output is requested
     * @return  array    associative(组合的) list of all outputs requested
     * @from https://github.com/kohana/ohanzee-helpers/blob/master/src/Date.php
     *
     * @author ^2_3^
     */
    public static function span($remote, $local = NULL, $output = 'years,months,weeks,days,hours,minutes,seconds')
    {
        // Normalize(格式化) output
        $output = trim(strtolower((string) $output));
        if (!$output)
        {
            // Invalid output
            return FALSE;
        }

        // Array with the output formats
        // preg_split — 通过一个正则表达式分隔字符串;
        $output = preg_split('/[^a-z]+/', $output);

        // Convert the list of outputs to an associative array
        // array_fill — 用给定的值填充数组;
        // array_combine — 创建一个数组，用一个数组的值作为其键名，另一个数组的值作为其值;
        $output = array_combine($output, array_fill(0, count($output), 0));

        // Make the output values into keys
        // array_flip — 交换数组中的键和值;
        // extract — 从数组中将变量导入到当前的符号表；EXTR_SKIP 如果有冲突，不覆盖已有的变量；
        extract(array_flip($output), EXTR_SKIP);

        // 当前时间戳
        if ($local === NULL)
        {
            // Calculate the span from the current time
            $local = time();
        }

        // (时间戳差值)Calculate timespan (seconds)
        $timespan = abs($remote - $local);

        // 年(365天算)
        if (isset($output['years']))
        {
            $timespan -= self::YEAR * ($output['years'] = (int) floor($timespan / self::YEAR));
        }

        // 月(按30天算)
        if (isset($output['months']))
        {
            $timespan -= self::MONTH * ($output['months'] = (int) floor($timespan / self::MONTH));
        }

        // 周
        if (isset($output['weeks']))
        {
            $timespan -= self::WEEK * ($output['weeks'] = (int) floor($timespan / self::WEEK));
        }

        // 天
        if (isset($output['days']))
        {
            $timespan -= self::DAY * ($output['days'] = (int) floor($timespan / self::DAY));
        }

        // 小时
        if (isset($output['hours']))
        {
            $timespan -= self::HOUR * ($output['hours'] = (int) floor($timespan / self::HOUR));
        }

        // 分钟
        if (isset($output['minutes']))
        {
            $timespan -= self::MINUTE * ($output['minutes'] = (int) floor($timespan / self::MINUTE));
        }

        // Seconds ago, 1
        if (isset($output['seconds']))
        {
            $output['seconds'] = $timespan;
        }

        if (count($output) === 1)
        {
            // Only a single output was requested, return it
            return array_pop($output);
        }

        // Return array
        return $output;
    }

    /**
     * 格式化 UNIX 时间戳为人易读的字符串
     *
     * @param	int	Unix 时间戳
     * @param	mixed	$local 本地时间
     *
     * @return	string	格式化的日期字符串
     * @author ^2_3^
     */
    public static function human($remote, $local = null)
    {
        $timediff = ((is_null($local) || !$local) ? time() : $local) - $remote;

        $chunks = array(
            array(60 * 60 * 24 * 365, 'year'),
            array(60 * 60 * 24 * 30, 'month'),
            array(60 * 60 * 24 * 7, 'week'),
            array(60 * 60 * 24, 'day'),
            array(60 * 60, 'hour'),
            array(60, 'minute'),
            array(1, 'second')
        );

        for ($i = 0, $j = count($chunks); $i < $j; $i++)
        {
            $seconds = $chunks[$i][0];
            $name = $chunks[$i][1];
            if (($count = floor($timediff / $seconds)) != 0)
            {
                break;
            }
        }

        return __("%d {$name}%s ago", $count, ($count > 1 ? 's' : ''));
    }

    /**
     * 获取一个基于时间偏移的Unix时间戳
     *
     * @param string $type 时间类型，默认为day，可选minute,hour,day,week,month,quarter,year
     * @param int $offset 时间偏移量 默认为0，正数表示当前type之后，负数表示当前type之前
     * @param string $position 时间的开始或结束，默认为begin，可选(begin,start,first,front)，end
     * @param int $year 基准年，默认为null，即以当前年为基准
     * @param int $month 基准月，默认为null，即以当前月为基准
     * @param int $day 基准天，默认为null，即以当前天为基准
     * @param int $hour 基准小时，默认为null，即以当前年小时基准
     * @param int $minute 基准分钟，默认为null，即以当前分钟为基准
     *
     * @return int 处理后的Unix时间戳
     *
     * @author ^2_3^
     */
    public static function unixtime($type = 'day', $offset = 0, $position = 'begin', $year = null, $month = null,
                                    $day = null, $hour = null, $minute = null)
    {
        $year = is_null($year) ? date('Y') : $year;
        $month = is_null($month) ? date('m') : $month;
        // d，月份中的第几天，有前导零的 2 位数字	01 到 31；
        $day = is_null($day) ? date('d') : $day;
        $hour = is_null($hour) ? date('H') : $hour;
        $minute = is_null($minute) ? date('i') : $minute;
        $position = in_array($position, array('begin', 'start', 'first', 'front'));

        switch ($type)
        {
            case 'minute':
                // $position是否为开始
                $time = $position ? mktime($hour, $minute + $offset, 0, $month, $day, $year) :
                    mktime($hour, $minute + $offset, 59, $month, $day, $year);
                break;

            case 'hour':
                $time = $position ? mktime($hour + $offset, 0, 0, $month, $day, $year) :
                    mktime($hour + $offset, 59, 59, $month, $day, $year);
                break;

            case 'day':
                $time = $position ? mktime(0, 0, 0, $month, $day + $offset, $year) :
                    mktime(23, 59, 59, $month, $day + $offset, $year);
                break;

            case 'week':
                // w，星期中的第几天，数字表示 0（表示星期天）到 6（表示星期六）；
                $time = $position ?
                        mktime(0, 0, 0, $month, $day - date("w",
                                mktime(0, 0, 0, $month, $day, $year)) + 1 - 7 * (-$offset), $year) :
                        mktime(23, 59, 59, $month, $day - date("w",
                                mktime(0, 0, 0, $month, $day, $year)) + 7 - 7 * (-$offset), $year);
                break;

            case 'month':
                $time = $position ? mktime(0, 0, 0, $month + $offset, 1, $year) :
                    mktime(23, 59, 59, $month + $offset,
                        cal_days_in_month(CAL_GREGORIAN, $month + $offset, $year), $year);
                break;

            case 'quarter':
                // n, 数字表示的月份，没有前导零 1 到 12；
                $time = $position ?
                        mktime(0, 0, 0, 1 + (
                            (ceil(date('n', mktime(0, 0, 0, $month, $day, $year)) / 3)
                                + $offset) - 1
                            ) * 3, 1, $year) :
                        mktime(23, 59, 59,
                            (
                            ceil(date('n', mktime(0, 0, 0, $month, $day, $year)) / 3)
                                + $offset
                            ) * 3,
                            cal_days_in_month(CAL_GREGORIAN, (
                                    ceil(date('n', mktime(0, 0, 0, $month, $day, $year)
                                        ) / 3) + $offset) * 3, $year),
                            $year);
                break;

            case 'year':
                $time = $position ? mktime(0, 0, 0, 1, 1, $year + $offset) :
                    mktime(23, 59, 59, 12, 31, $year + $offset);
                break;

            default:
                $time = mktime($hour, $minute, 0, $month, $day, $year);
                break;
        }
        return $time;
    }

}
