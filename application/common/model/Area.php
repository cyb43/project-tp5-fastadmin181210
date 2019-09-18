<?php

namespace app\common\model;

use think\Cache;
use think\Model;

/**
 * 地区数据模型
 *
 * @author ^2_3^
 */
class Area extends Model
{

    /**
     * 根据经纬度获取当前地区信息
     *
     * @param string $lng 经度
     * @param string $lat 纬度
     * @return Area 城市信息
     *
     * @author ^2_3^
     */
    public static function getAreaFromLngLat($lng, $lat, $level = 3)
    {
        // 名称
        $namearr = [1 => 'geo:province', 2 => 'geo:city', 3 => 'geo:district'];
        // 范围
        $rangearr = [1 => 15000, 2 => 1000, 3 => 200];

        $geoname = isset($namearr[$level]) ? $namearr[$level] : $namearr[3];
        $georange = isset($rangearr[$level]) ? $rangearr[$level] : $rangearr[3];

        // 读取范围内的ID
        $redis = Cache::store('redis')->handler();
        $georadiuslist = [];
        if (method_exists($redis, 'georadius')) {
            $georadiuslist = $redis->georadius($geoname, $lng, $lat, $georange, 'km',
                ['WITHDIST', 'COUNT' => 5, 'ASC']);
        }

        if ($georadiuslist) {
            list($id, $distance) = $georadiuslist[0];
        }
        $id = isset($id) && $id ? $id : 3; //无值则选"中国,北京,北京市,东城区";
        return self::get($id);
    }

    /**
     * 根据经纬度获取省份
     *
     * @param string $lng 经度
     * @param string $lat 纬度
     * @return Area
     * @author ^2_3^
     */
    public static function getProvinceFromLngLat($lng, $lat)
    {
        $provincedata = null;
        $citydata = self::getCityFromLngLat($lng, $lat);
        if ($citydata) {
            $provincedata = self::get($citydata['pid']);
        }
        return $provincedata;
    }

    /**
     * 根据经纬度获取城市
     *
     * @param string $lng 经度
     * @param string $lat 纬度
     * @return Area
     *
     * @author ^2_3^
     */
    public static function getCityFromLngLat($lng, $lat)
    {
        $citydata = null;
        $districtdata = self::getDistrictFromLngLat($lng, $lat);
        if ($districtdata) {
            $citydata = self::get($districtdata['pid']);
        }
        return $citydata;
    }

    /**
     * 根据经纬度获取地区
     *
     * @param string $lng 经度
     * @param string $lat 纬度
     * @return Area
     *
     * @author ^2_3^
     */
    public static function getDistrictFromLngLat($lng, $lat)
    {
        $districtdata = self::getAreaFromLngLat($lng, $lat, 3);
        return $districtdata;
    }

}
