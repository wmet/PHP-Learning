<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/19
 * Time: 17:34
 */

interface IMessage{
    public function send();
}

class Message implements IMessage{
    public function send(){
        echo 'send';
    }
}

class ControllerA
{
    public function __construct()
    {
        echo '实例化';
    }
    public function index(IMessage $mes)
    {
        $mes->send();
    }
}

class Container{
    private $arr=[];
    //\Closure
    public function bind($name, $fun)
    {
        $this->arr[$name]=$fun;
    }

    public function make($name)
    {
        $fun=$this->arr[$name];
        return call_user_func($fun);
    }
}

class Route{
    private static $arr=[];
    public static function get($name,$controller)
    {
        self::$arr[$name]=$controller;
        self::invoke();
    }

    public static function  invoke()
    {
        global $container;
        $controller=self::$arr['callindex'];
        list($controllerClass,$method)=explode('@',$controller);
        //反射
        $reclass=new ReflectionClass($controllerClass);// 获取反射类
        $method=$reclass->getMethod($method);//index 方法
        $param=$method->getParameters(); //index 方法的所有参数
        $methodparamClassName=$param[0]->getClass()->getName(); //Imessage 第一个参数的 类名
        $paramInstance=$container->make($methodparamClassName);// 从容器中取这个类名绑定的实例

        $controllerInstance=$reclass->newInstance();//$a=new ControllerA

        $method->invoke($controllerInstance,$paramInstance);//执行$a->index
    }
}
$container=new Container();
$container->bind('IMessage',function(){
    return new Message();
});

Route::get('callindex','ControllerA@index'); //main start



/*
$reclass=new ReflectionClass('ControllerA');// 获取反射类
$method=$reclass->getMethod('index');//index 方法
$param=$method->getParameters(); //index 方法的所有参数
$methodparamClassName=$param[0]->getClass()->getName(); //Imessage 第一个参数的 类名
$paramInstance=$container->make('IMessage');// 从容器中取这个类名绑定的实例

$controllerInstance=$reclass->newInstance();//$a=new ControllerA
$method->invoke($controllerInstance,$paramInstance);//执行$a->index
*/