<?php

namespace Majie\LaravelCustomRouter;

class Route
{
    use MatchTypeTrait;

    protected string $namespace;

    /** @var string|array 路由分割符号(将类似'/add-user' 或者 '/add_user' 解析成addUer) */
    protected string|array $separator;

    /** @var bool 请求的参数是否为蛇形 */
    protected bool $paramNameIsSnake;

    protected array $forbiddenMethods = ['middleware', 'getMiddleware', 'callAction','__construct'];


    public function __construct(string $namespace, string|array $separator = ['-','_'], bool $isSnake = true)
    {
        $this->namespace = $namespace;
        $this->separator = $separator;
        $this->paramNameIsSnake = $isSnake;
    }

    /**
     * 设置禁止访问的方法(laravel内部方法不可访问)
     * @param array $methods
     * @return $this
     */
    public function setForbiddenMethods(array $methods): static
    {
        $this->forbiddenMethods += $methods;
        return $this;
    }

    /**
     * @throws \ReflectionException
     * @throws CustomRouteException
     */
    public function dispatchRoute(string $controller, string $action, array $data): void
    {
        $controllerClass = $this->getControllerClass($controller);
        $method = $this->getMethod($action);
        if (in_array($method, $this->forbiddenMethods)) {
            throw new CustomRouteException("the $method method not accessible in $controllerClass");
        }

        if (!class_exists($controllerClass) || !method_exists($controllerClass, $method)) {
            throw new CustomRouteException("$controllerClass::{$method} does not exist");
        }

       $this->paramsDispatch(app($controllerClass), $method, $data);

    }


    /**
     * @param object $class
     * @param string $method
     * @param array $params
     * @return mixed
     * @throws CustomRouteException
     * @throws \ReflectionException
     */
    protected function paramsDispatch(object $class, string $method, array $params): mixed
    {
        $reflectionMethod = new \ReflectionMethod($class, $method);
        if (!$reflectionMethod->isPublic()) {
            throw new CustomRouteException("the method {$method} is not public");
        }

        $parameters = $reflectionMethod->getParameters();
        $args = $this->parseArgs($parameters, $params);

        return $class->{$method}(...$args);
    }

    /**
     * @param \ReflectionParameter[] $parameters
     * @param array $params
     * @return array
     * @throws CustomRouteException
     */
    protected function parseArgs(array $parameters, array $params): array
    {
        $args = [];
        foreach ($parameters as $parameter) {
            $name = $this->paramNameIsSnake ? self::snake($parameter->getName()) : $parameter->getName();
            $type = $parameter->getType();
            if($type instanceof \ReflectionNamedType){
                $typeName = $parameter->getType()->getName();
                if ($type->isBuiltin()) {
                    $args[] = isset($params[$name]) ? $this->matchBuiltinType($typeName, $name, $params[$name]) :$this->parameterEmpty($parameter);
                } else { //对象
                    $args[] = new $typeName($params);
                }
            }else{
                throw new CustomRouteException("Parameter type exception,the parameter not support");
            }
        }

        return $args;
    }

    /**
     * @param \ReflectionParameter $parameter
     * @return mixed|null
     * @throws CustomRouteException
     */
    private function parameterEmpty(\ReflectionParameter $parameter): mixed
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        } elseif ($parameter->allowsNull()) {
            return null;
        } else {
            throw new CustomRouteException("Parameter exception,the parameter can not be empty");
        }
    }

    /**
     * @param string $action
     * @return string
     */
    protected function getMethod(string $action): string
    {
        return lcfirst(self::camelize($action, $this->separator));
    }

    /**
     * @param string $controller
     * @return string
     */
    protected function getControllerClass(string $controller): string
    {
        $controllerArr = explode("/", $controller);
        foreach ($controllerArr as &$value) {
            $value = self::camelize($value, $this->separator);
        }

        //转成命名空间
        $controller = implode('\\', $controllerArr);
        return $this->namespace . '\\' . $controller . 'Controller';
    }


    /**
     * 下划线转驼峰
     * 1.原字符串转小写,原字符串中的分隔符用空格替换,在字符串开头加上分隔符
     * 2.将字符串中每个单词的首字母转换为大写,再去空格,去字符串首部附加的分隔符.
     *
     * @param string $words
     * @param string|array $separator
     * @return string
     */
    private static function camelize(string $words, string|array $separator): string
    {
        return str_replace(' ', '', ucwords(str_replace($separator, ' ', $words)));
    }

    /**
     * 驼峰命名转下划线命名
     * 小写和大写紧挨一起的地方,加上分隔符,然后全部转小写
     *
     * @param string $words
     * @param string $separator
     * @return string
     */
    private static function snake(string $words, string $separator = '_'): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $words));
    }


}