<?php

interface APICallableModel{
    
    function get($id);
    function getList($filtro);
    function insert($input);
    function selfUpdate($input);
    function remove($id);


}
?>
