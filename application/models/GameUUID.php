<?php

class GameUUIDModel extends GameBaseModel
{
    protected $_table = 'uuids';
    protected $_primary_key = 'id';
    protected $_filed = ['id', 'uuid', 'status'];
    protected $_order = [];
    protected $_default_order = ['id' => 'DESC'];

    public function getOne($status = 0, $limit = 1)
    {
        $where = [
            'status' => $status,
        ];
        $cloumn = ['id', 'uuid'];

        return $this->fetchRow($where, $cloumn);
    }

    public function setOne($id, $status = 1)
    {
        $array['status'] = $status;

        return $this->update($array, [
            'id' => $id,
        ]);
    }

    public function getAll($where = [], $column = '', $order = [])
    {
        if (empty($column)) {
            $column = $this->_filed;
        }
        $where['ORDER'] = ['id' => 'ASC'];

        if ($order) {
            $where['ORDER'] = $order;
        }

        return $this->fetchAll($where, $column);
    }
}
