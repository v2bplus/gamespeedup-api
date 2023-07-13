<?php
namespace Services\Api;

use Exception;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Http;
use Service;

class MaxmindIP extends Service
{
    protected static $countryFile = APPLICATION_PATH.'data'.DS.'GeoLite2-Country.mmdb';
    protected static $cityFile = APPLICATION_PATH.'data'.DS.'GeoLite2-City.mmdb';

    public static function getCountryCode($ip = null)
    {
        try {
            if (!is_file(self::$countryFile)) {
                throw new Exception('Country数据文件不存在');
            }
            if (!$ip) {
                $ipAddress = Http::clientIp();
            } else {
                $ipAddress = $ip;
            }
            $reader = new Reader(self::$countryFile);
            $rs = $reader->country($ipAddress);
            $data = [];
            $data['code'] = $rs->country->isoCode;
            $data['name'] = $rs->country->name;
            $data['cn'] = $rs->country->names['zh-CN'];
            return [
                'status' => 1,
                'data' => $data,
                'msg' => ''
            ];
        } catch (AddressNotFoundException $e) {
            $data = [
                'code' => 'CN',
                'name' => 'China',
                'cn' => '中国',
            ];
            return [
                'status' => 1,
                'data' => $data,
                'msg' => ''
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }

    public static function getInfo($ip = null)
    {
        try {
            if (!is_file(self::$cityFile)) {
                throw new Exception('City数据文件不存在');
            }
            if (!$ip) {
                $ipAddress = Http::clientIp();
            } else {
                $ipAddress = $ip;
            }
            $ipAddress = '202.96.134.133';
            $ipAddress = '119.123.72.250';
            $ipAddress = '127.0.0.1';
            $reader = new Reader(self::$cityFile);
            $info = $reader->city($ipAddress);

            $country = $city = $location = [];
            $country['code'] = $info->country->isoCode;
            $country['name'] = $info->country->name;
            $country['cn'] = $info->country->names['zh-CN'];

            $location['latitude'] = $info->location->latitude;
            $location['longitude'] = $info->location->longitude;

            $city['confidence'] = $info->city->confidence;
            $city['geonameId'] = $info->city->geonameId;
            $city['cn'] = $info->city->names['zh-CN'];
            $data = [
                'country' => $country,
                'location' => $location
            ];
            return [
                'status' => 1,
                'data' => $data,
                'msg' => ''
            ];
        } catch (AddressNotFoundException $e) {
            $country = [
                'code' => 'CN',
                'name' => 'China',
                'cn' => '中国',
            ];
            $location = [];
            $data = [
                'country' => $country,
                'location' => $location
            ];
            return [
                'status' => 1,
                'data' => $data,
                'msg' => ''
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }
}
