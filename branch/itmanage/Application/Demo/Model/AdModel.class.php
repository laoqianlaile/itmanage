<?php
namespace Demo\Model;
use Think\Model;
class AdModel extends Model {

    /**
     * ��ȡ��֯�ṹ������
     * @param array $data
     * @return array
     */
    public function getTreeArray($data = array()){
        if(empty($data)) return $data;
        foreach($data as &$value){
            $times = substr_count($value['ConvertPath'], '\\'); //\�ڲ㼶��ϵ�г��ֵĴ���
            if($times == 1){
                $value['pId'] = '';
                $value['id'] = md5($value['ConvertPath']);
            }else{
                $value['pId']  = md5(substr($value['ConvertPath'], 0, strripos( $value['ConvertPath'], '\\')));
                $value['id'] = md5($value['ConvertPath']);
            }
            $value['name'] = $value['Name'];
            unset($value['Name']);
            unset($value['Description']);
            unset($value['ChildOrganizationalUnit']);
        }
        S('deptTree', $data, 3600 * 24);
        return $data;
    }
}