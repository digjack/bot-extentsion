<?php
/**
 * Created by PhpStorm.
 * User: banli
 * Date: 2018/6/29
 * Time: 1:33
 */

namespace Hanson\Vbot\Api;

class Search extends BaseApi
{
    public function needParams(): array
    {
        return [];
    }

    public function handle($params): array
    {
//        $class = '\\Hanson\\Vbot\\Contact\\'.ucfirst($params['type']);
//
//        if (!class_exists($class)) {
//            return $this->response('Class: \''.$class.'\' not exist.', 500);
//        }
//
//        if ($params['type'] === 'myself') {
//            return $this->response('Can not get myself from \'search\'.', 500);
//        }
//
//        $type = strtolower($params['type']);
//
//        if (isset($params['filter'])) {
//            //            $contacts = (new Contacts($this->vbot->$type->toArray()));
//            $contacts = $this->vbot->$type;
//            $result = call_user_func_array([$contacts, $params['method']], $params['filter']);
//        } else {
//            $result = $this->vbot->$type;
//        }

        return $this->response(['config' => $this->vbot->config], 200);
    }
}
