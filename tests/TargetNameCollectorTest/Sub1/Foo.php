<?php

use App\AppController;

final class Foo extends BaseClass implements BaseInterface
{
    use BaseTrait;

    public BaseClass $var;
    public AppController $controller;

    public function something(BaseClass $var1, BaseInterface $var2)
    {
        var_dump(BASE_CONST);
        SplFileInfo::class;
        base_function();
        BaseClass();

        var_dump(DEFINED_VAR);
        Hoge::class;

        new Bar();
    }
}
