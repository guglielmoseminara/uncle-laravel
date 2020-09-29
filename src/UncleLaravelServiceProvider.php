<?php
namespace UncleProject\UncleLaravel;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use UncleProject\UncleLaravel\Helpers\Utils;
use UncleProject\UncleLaravel\Helpers\XMLResource;

use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Bezhanov\Faker\ProviderCollectionHelper;
use RicLeP\Faker\LoremFlickrFakerProvider;



class UncleLaravelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/uncle.php' => config_path('uncle.php'),
        ]);

        Schema::defaultStringLength(191);
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
        Validator::replacer('in_or', function ($message, $attribute, $rule, $parameters) {
            if(Lang::has($message)) return str_replace(':attribute', $attribute, Lang::has($message));
            else return str_replace(':attribute', $attribute, 'One or more element in :attribute are not accepted');
        });

        Validator::extendImplicit('set_in', function ($attribute, $value, $parameters, $validator) {
            if (!empty($value)) {
                $values = explode(',', $value);
                $validator = Validator::make($values, [
                    '*' => [Rule::in($parameters)]
                ]);
                return !$validator->fails();
            }
            else return true;
        });
        Validator::replacer('set_in', function ($message, $attribute, $rule, $parameters) {
            if(Lang::has($message)) return str_replace(':attribute', $attribute, Lang::has($message));
            else return str_replace(':attribute', $attribute, 'One or more element in :attribute are not accepted');
        });

        Validator::extend('exists_or', function ($attribute, $value, $parameters, $validator) {
            if (!empty($value)) {
                $values = explode('|', $value);
                $validator = Validator::make($values, [
                    '*' => 'exists:'.$parameters[0].','.$parameters[1]
                ]);
                return !$validator->fails();
            }
            else return true;
        });
        Validator::replacer('exists_or', function ($message, $attribute, $rule, $parameters) {
            if(Lang::has($message)) return str_replace(':attribute', $attribute, Lang::has($message));
            else return str_replace(':attribute', $attribute, ':attribute not exist in tables');
        });

        Validator::extend('exists_with_columns', function ($attribute, $value, $parameters, $validator) {
            if (!empty($value)) {
                $values = explode('|', $value);
                $validator = Validator::make($values, [
                    '*' => [
                        Rule::exists($parameters[0], $parameters[1])
                            ->where(function ($query) use ($parameters) {
                                for($i = 2; $i < count($parameters); $i++)
                                    $query = $query->where($parameters[$i], $parameters[++$i]);
                            }),
                    ]
                ]);
                return !$validator->fails();
            }
            else return true;
        });
        Validator::replacer('exists_with_columns', function ($message, $attribute, $rule, $parameters) {
            if(Lang::has($message)) return str_replace(':attribute', $attribute, Lang::has($message));
            else return str_replace(':attribute', $attribute, ':attribute not exist with this parameter columns');
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
        Validator::replacer('range_numeric', function ($message, $attribute, $rule, $parameters) {
            if(Lang::has($message)) return str_replace(':attribute', $attribute, Lang::has($message));
            else return str_replace(':attribute', $attribute, ':attribute is an invalid range numeric');
        });

        Validator::extendImplicit('latitude',
            function ($attribute, $value, $parameters, $validator) {
                if (!empty($value)) return preg_match('/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/', $value);
                else return true;
            });
        Validator::replacer('latitude', function ($message, $attribute, $rule, $parameters) {
            if(Lang::has($message)) return str_replace(':attribute', $attribute, Lang::has($message));
            else return str_replace(':attribute', $attribute, ':attribute is an invalid latitude');
        });

        Validator::extendImplicit('longitude',
            function ($attribute, $value, $parameters, $validator) {
                if (!empty($value)) return preg_match('/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/', $value);
                else return true;
            });
        Validator::replacer('longitude', function ($message, $attribute, $rule, $parameters) {
            if(Lang::has($message)) return str_replace(':attribute', $attribute, Lang::has($message));
            else return str_replace(':attribute', $attribute, ':attribute is an invalid longitude');
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
        $this->app->singleton(FakerGenerator::class, function ($app) {
            $faker = FakerFactory::create($app['config']->get('app.faker_locale', 'en_US'));
            ProviderCollectionHelper::addAllProvidersTo($faker);
            $faker->addProvider(new LoremFlickrFakerProvider($faker));
            return $faker;
        });

        $this->app->singleton('Utils', function ($app) {
            return new Utils($app);
        });

        $this->app->singleton('XMLResource', function ($app) {
            return new XMLResource($app);
        });
    }

}