<?php
/**
 * 来源：https://segmentfault.com/a/1190000002424023
 * 程序的依赖注入demo
 */
//传统的思路是应用程序用到一个Foo类，就会创建Foo类并调用Foo类的方法，假如这个方法内需要一个Bar类，就会创建Bar类并调用Bar类的方法，而这个方法内需要一个Bim类，就会创建Bim类，接着做些其它工作。
class Bim
{
    public function doSomething()
    {
        echo __METHOD__, '|';
    }
}

class Bar
{
    public function doSomething()
    {
        $bim = new Bim();
        $bim->doSomething();
        echo __METHOD__, '|';
    }
}

class Foo
{
    public function doSomething()
    {
        $bar = new Bar();
        $bar->doSomething();
        echo __METHOD__;
    }
}

$foo = new Foo();
$foo->doSomething(); //Bim::doSomething|Bar::doSomething|Foo::doSomething
//使用依赖注入的思路是应用程序用到Foo类，Foo类需要Bar类，Bar类需要Bim类，那么先创建Bim类，
//再创建Bar类并把Bim注入，再创建Foo类，并把Bar类注入，再调用Foo方法，Foo调用Bar方法，接着做些其它工作。
// 代码【2】
class Bim1
{
    public function doSomething()
    {
        echo __METHOD__, '|';
    }
}

class Bar1
{
    private $bim;

    public function __construct(Bim1 $bim)
    {
        $this->bim = $bim;
    }

    public function doSomething()
    {
        $this->bim->doSomething();
        echo __METHOD__, '|';
    }
}

class Foo1
{
    private $bar;

    public function __construct(Bar1 $bar)
    {
        $this->bar = $bar;
    }

    public function doSomething()
    {
        $this->bar->doSomething();
        echo __METHOD__;
    }
}

$foo = new Foo(new Bar(new Bim()));
$foo->doSomething(); // Bim::doSomething|Bar::doSomething|Foo::doSomething

//这就是控制反转模式。依赖关系的控制反转到调用链的起点。这样你可以完全控制依赖关系，通过调整不同的注入对象，来控制程序的行为。
//例如Foo类用到了memcache，可以在不修改Foo类代码的情况下，改用redis。使用依赖注入容器后的思路是应用程序需要到Foo类，
//就从容器内取得Foo类，容器创建Bim类，再创建Bar类并把Bim注入，再创建Foo类，并把Bar注入，应用程序调用Foo方法，Foo调用Bar方法，
//接着做些其它工作.总之容器负责实例化，注入依赖，处理依赖关系等工作。

class Container
{
    private $s = array();

    function __get($k)
    {
        return $this->s[$k]($this);
    }

    function __set($k, $c)
    {
        $this->s[$k] = $c;
    }
}

$c = new Container();


$c->bim = function () {
    return new Bim();
};
$c->bar = function ($c) {
    return new Bar($c->bim);
};
$c->foo = function ($c) {
    return new Foo($c->bar);
};

// 从容器中取得Foo
$foo = $c->foo;

//容器
class IoC
{
    protected static $registry = [];

    public static function bind($name, Callable $resolver)
    {
        static::$registry[$name] = $resolver;
    }

    public static function make($name)
    {
        if (isset(static::$registry[$name])) {
            $resolver = static::$registry[$name];
            return $resolver();
        }
        throw new Exception('Alias does not exist in the IoC registry.');
    }
}

IoC::bind('bim', function () {
    return new Bim();
});
IoC::bind('bar', function () {
    return new Bar(IoC::make('bim'));
});
IoC::bind('foo', function () {
    return new Foo(IoC::make('bar'));
});


// 从容器中取得Foo
$foo = IoC::make('foo');
$foo->doSomething(); // Bim::doSomething|Bar::doSomething|Foo::doSomething

//自定加载
class Bim2
{
    public function doSomething()
    {
        echo __METHOD__, '|';
    }
}

class Bar2
{
    private $bim;

    public function __construct(Bim $bim)
    {
        $this->bim = $bim;
    }

    public function doSomething()
    {
        $this->bim->doSomething();
        echo __METHOD__, '|';
    }
}