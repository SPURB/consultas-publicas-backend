<?php

interface APICallableModel{
    
    function get($id);
    function getList($filtro);
    function insert($input);
    function selfUpdate($input);
    function remove($id);
    //function execAction($action, $request);
    //function setTableName();
    //function setColumns();
    //function setOneMany();
    //function setManyOne();
    //function setManyOneId();

}
?>