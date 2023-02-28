# LaravelCustomRouter
Route::middleware([])->group(function (){
    Route::any('{controller}/{action}',function ($controller,$action){
        return (new \Majie\LaravelCustomRouter\Route('App\Http\Controllers'))->dispatchRoute($controller,$action,\request()->all());
    })->where('controller',".*");
});
