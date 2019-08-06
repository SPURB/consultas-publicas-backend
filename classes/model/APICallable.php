<?php

interface APICallable{
    
    function get($id);
    function getList($filtro);
    function execAction($action, $request);
    /*
    function post($input);
    function put($input, $id);
    function delete($id);

    function getPrimaryKeyName($banco);
    function setOneMany($array);
    function setManyOne($array, $id);
*/
}
?>