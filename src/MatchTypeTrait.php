<?php

namespace Majie\LaravelCustomRouter;

trait MatchTypeTrait
{

    /**
     * @param string $typeName
     * @param string $paramName
     * @param $value
     * @return array|bool|float|int|string|null
     * @throws CustomRouteException
     */
    public function matchBuiltinType(string $typeName, string $paramName, $value)
    {
      return  match ($typeName) {
            'string' => $this->matchString($paramName, $value),
            'array' => $this->matchArray($paramName, $value),
            'int' => $this->matchInt($paramName, $value),
            'bool' => $this->matchBool($paramName, $value),
            'float' => $this->matchFloat($paramName, $value),
            'false' => $this->matchFalse($paramName, $value),
            'true' => $this->matchTrue($paramName, $value),
            'null' => $this->matchNull($paramName, $value),
            default=> throw new CustomRouteException("The type of parameter {$paramName} is wrong, parameter type not supported")
        };
    }

    /**
     * @param string $paramName
     * @param mixed $value
     * @return string
     * @throws CustomRouteException
     */
    private function matchString(string $paramName, mixed $value): string
    {
        if (is_string($value) || is_int($value) || is_float($value)) {
            return (string)$value;
        }

        throw new CustomRouteException("The type of parameter {$paramName} is wrong, parameter type must be a string type");
    }

    /**
     * @param string $paramName
     * @param mixed $value
     * @return array
     * @throws CustomRouteException
     */
    private function matchArray(string $paramName, mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }
        throw new CustomRouteException("The type parameter of {$paramName} is wrong, parameter type must be a array type");

    }

    /**
     * @param string $paramName
     * @param mixed $value
     * @return int
     * @throws CustomRouteException
     */
    private function matchInt(string $paramName, mixed $value): int
    {
        if (is_numeric($value) && preg_match('/^-?\d+$/', (string)$value)) {
            return (int)$value;
        }
        throw new CustomRouteException("The type parameter of {$paramName} is wrong, parameter type must be a integer type");
    }

    /**
     * @param string $paramName
     * @param mixed $value
     * @return bool
     * @throws CustomRouteException
     */
    private function matchBool(string $paramName, mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        throw new CustomRouteException("The type parameter of {$paramName} is wrong, parameter type must be a boolean type");
    }

    /**
     * @param string $paramName
     * @param mixed $value
     * @return float
     * @throws CustomRouteException
     */
    private function matchFloat(string $paramName, mixed $value): float
    {
        if (is_int($value) || is_float($value) || (is_string($value) && preg_match('/^[+-]?(\d*\.\d+([eE]?[+-]?\d+)?|\d+[eE][+-]?\d+)$/', $value))) {
            return (float)$value;
        }

        throw new CustomRouteException("The type parameter of {$paramName} is wrong, parameter type must be a float type");
    }


    private function matchFalse(string $paramName, mixed $value): float
    {
        if (is_float($value) || (is_string($value) && preg_match('/^[+-]?(\d*\.\d+([eE]?[+-]?\d+)?|\d+[eE][+-]?\d+)$/', $value))) {
            return (float)$value;
        }

        throw new CustomRouteException("The type parameter of {$paramName} is wrong, parameter type must be a float type");
    }

    private function matchTrue(string $paramName, mixed $value): bool
    {
        if ($value === true) {
            return false;
        }

        throw new CustomRouteException("The type parameter of {$paramName} is wrong, parameter type always false");
    }

    /**
     * @param string $paramName
     * @param mixed $value
     * @return null
     * @throws CustomRouteException
     */
    private function matchNull(string $paramName, mixed $value)
    {
        if (null === $value) {
            return null;
        }

        throw new CustomRouteException("The type parameter of {$paramName} is wrong, parameter type must be a float type");
    }


}