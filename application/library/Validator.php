<?php

use Valitron\Validator as ValitronValidator;

class Validator
{
    // Validator __construct($data, $fields, $lang, $langDir)
    // todo
    public static $_lang = 'zh_cn';
    public static $_langDir = APPLICATION_PATH.'lang'.DS.'valitron';

    public static function validateId($id)
    {
        $values = ['id' => $id];
        $rules = [
            'id' => [['required'], ['integer']],
        ];
        $validator = new ValitronValidator($values);
        $validator->mapFieldsRules($rules);

        return $validator->validate();
    }

    public static function customerValidate($data, $rules, $stopOnFirstFail = true)
    {
        $validator = new ValitronValidator($data, [], self::$_lang, self::$_langDir);
        $validator->mapFieldsRules($rules);
        $validator->stopOnFirstFail((bool) $stopOnFirstFail);

        return $validator;
    }

    public static function uploadValidate($data, $rules, $stopOnFirstFail = true)
    {
        $validator = new ValitronValidator($data, [], self::$_lang, self::$_langDir);
        $validator->stopOnFirstFail(true);
        $validator->addRule('uploadError', function ($field, $value, $params, $fields) {
            $error = 0;
            if ($value) {
                foreach ($value as $k => $v) {
                    $error = $v['error'] ?? 0;
                    if (0 != $error) {
                        return false;
                    }
                }
            }

            return true;
        }, '文件上传错误');
        $validator->addRule('uploadType', function ($field, $value, $params, $fields) {
            if ($value) {
                foreach ($value as $k => $v) {
                    if (!in_array($v['type'], $params[0])) {
                        return false;
                    }
                }
            }

            return true;
        }, '文件MIME类型暂不支持');
        $validator->addRule('uploadExt', function ($field, $value, $params, $fields) {
            if ($value) {
                foreach ($value as $k => $v) {
                    if (isset($v['name'])) {
                        $ext = pathinfo($v['name'], PATHINFO_EXTENSION);
                        if (!in_array(strtolower($ext), $params[0])) {
                            return false;
                        }
                    }
                }
            }

            return true;
        }, '上传文件后缀不允许');
        $validator->addRule('uploadSize', function ($field, $value, $params) {
            if ($value) {
                foreach ($value as $k => $v) {
                    if (isset($v['size'])) {
                        if ($v['size'] > $params) {
                            return false;
                        }
                    }
                }
            }

            return true;
        }, '文件大小暂不支持');
        $validator->addRule('uploadNum', function ($field, $value, $params) {
            if ($value) {
                if (count($value) > (int) $params[0]) {
                    return false;
                }
            }

            return true;
        }, '文件上传最大数量暂不支持');
        $validator->mapFieldsRules($rules);
        $validator->stopOnFirstFail((bool) $stopOnFirstFail);

        return $validator;
    }
}
