<?php
namespace UncleProject\UncleLaravel;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use UncleProject\UncleLaravel\Helpers\Utils;

class UncleLaravelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        Validator::extendImplicit('latitude',
            function ($attribute, $value, $parameters, $validator) {
                return preg_match('/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/', $value);
            });
        Validator::extendImplicit('in_or', function ($attribute, $value, $parameters, $validator) {
            $flag = true;
            if (!empty($value)) {
                $values = explode('|', $value);
                foreach ($values as $orValue) {
                    $flag &= in_array($orValue, $parameters);
                }
            }
            return $flag;
        });
        Validator::extendImplicit('range_numeric', function ($attribute, $value, $parameters, $validator) {
            $values = explode('-', $value);
            if (count($values) == 1 || count($values) == 2) {
                $validator = Validator::make($values, [
                    '*' => 'numeric'
                ]);
                $result = !$validator->fails();
                if (count($values) == 2) {
                    $validator = Validator::make($values, [
                        '0' => 'lte:1'
                    ]);
                    $result &= !$validator->fails();
                }
                return $result;
            }
            return false;
        });
        Validator::extendImplicit('longitude',
            function ($attribute, $value, $parameters, $validator) {
                return preg_match('/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/', $value);
            });
        Validator::extend('phone', function ($attribute, $value, $parameters, $validator) {
            return preg_match('%^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$%i', $value) && strlen($value) >= 10;
        });
        Validator::replacer('phone', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, ':attribute is invalid phone number');
        });

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Utils', function ($app) {
            return new Utils($app);
        });

        $this->app->singleton(FakerGenerator::class, function ($app) {
            $faker = FakerFactory::create($app['config']->get('app.faker_locale', 'en_US'));
            ProviderCollectionHelper::addAllProvidersTo($faker);
            $faker->addProvider(new LoremFlickrFakerProvider($faker));
            return $faker;
        });

    }

}