<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

    'type' => [1 => 'CRM', 2 => 'CMS', 3 => 'Wlanshop'],
    // company user siteaccess
    'siteaccess' => ['sso' => 0, 'cms' => 1, 'shop' => 2, 'crm' => 3],

    'assets_version' => '?1.0.0',

    'uniconta_static_data' => [
        "currency" => [
            "USD",
            "EUR",
            "GBP",
            "CHF",
            "JPY",
            "CAD",
            "AUD",
            "NZD",
            "DKK",
            "SEK",
            "NOK",
            "SGD",
            "RUB",
            "TRY",
            "HKD",
            "ZAR",
            "MXN",
            "CZK",
            "HUF",
            "PLN",
            "ISK",
            "AED",
            "CNY",
            "INR",
            "BRL",
            "ILS",
            "SAR",
            "AFA",
            "ALL",
            "AMD",
            "ANG",
            "AOK",
            "AON",
            "AOR",
            "ARS",
            "AWF",
            "AWG",
            "AZN",
            "BAD",
            "BBD",
            "BDT",
            "BGN",
            "BHD",
            "BIF",
            "BMD",
            "BND",
            "BOB",
            "BPS",
            "BSD",
            "BTN",
            "BTR",
            "BWP",
            "BYR",
            "BZD",
            "CDF",
            "CFP",
            "CLP",
            "COP",
            "CRC",
            "CVE",
            "DOP",
            "DZD",
            "ECS",
            "ERN",
            "ETB",
            "FJD",
            "FKP",
            "GEL",
            "GHC",
            "GMD",
            "GNF",
            "GTQ",
            "GWP",
            "GYD",
            "HNL",
            "HRK",
            "HTG",
            "IDR",
            "IQD",
            "IRR",
            "JMD",
            "JOD",
            "KES",
            "KGS",
            "KHR",
            "KMF",
            "KPW",
            "KRW",
            "KTS",
            "KWD",
            "KYD",
            "KYS",
            "KZT",
            "LAK",
            "LBP",
            "LKR",
            "LRD",
            "LSL",
            "LYD",
            "MAD",
            "MDL",
            "MGF",
            "MKD",
            "MMK",
            "MNT",
            "MOP",
            "MRO",
            "MUR",
            "MVR",
            "MWK",
            "MXV",
            "MYR",
            "MZM",
            "NAD",
            "NGN",
            "NIO",
            "NPR",
            "OMR",
            "PEN",
            "PGK",
            "PHP",
            "PKR",
            "PYG",
            "QAR",
            "RON",
            "RSD",
            "RWF",
            "SBD",
            "SCR",
            "SDD",
            "SHP",
            "SIT",
            "SLL",
            "SOS",
            "SRD",
            "STD",
            "SVC",
            "SYP",
            "SZL",
            "THB",
            "TJR",
            "TMM",
            "TND",
            "TOP",
            "TPE",
            "TTD",
            "TWD",
            "TZS",
            "UAH",
            "UGS",
            "UYU",
            "UZS",
            "VEB",
            "VND",
            "VUV",
            "WST",
            "XAF",
            "XCD",
            "XOF",
            "XPF",
            "YER",
            "ZMK",
            "ZRN",
            "ZWD",
            "BAM",
            "EGP",
            "BTC",
            "BCH",
            "BTG",
            "ETH",
            "BSV",
            "LTC",
            "CR1",
            "CR2",
            "CR3",
            "CR4",
            "CR5",
            "CR6",
            "CR7",
            "CR8",
            "CR9"
        ],
        "VatZone" => [
            "Domestic",
            "EU Member States",
            "Abroad",
            "No VAT Registration",
            "Exempt",
        ],
        "country"=> [
            "Unknown",
            "Antarctica",
            "Afghanistan",
            "Aland",
            "Albania",
            "Algeria",
            "AmericanSamoa",
            "Andorra",
            "Angola",
            "Anguilla",
            "AntiguaBarbuda",
            "Argentina",
            "Armenia",
            "Aruba",
            "AscensionIsland",
            "Australia",
            "Austria",
            "Azerbaijan",
            "Bahamas",
            "Bahrain",
            "Bangladesh",
            "Barbados",
            "Belarus",
            "Belgium",
            "Belize",
            "Benin",
            "Bermuda",
            "Bhutan",
            "Bolivia",
            "BosniaHerzegovina",
            "Botswana",
            "Brazil",
            "Brunei",
            "Bulgaria",
            "BurkinaFaso",
            "Burundi",
            "Cambodia",
            "Cameroon",
            "Canada",
            "CapeVerde",
            "CaymanIslands",
            "CentralAfricaRepublic",
            "Chad",
            "Chile",
            "China",
            "ChristmasIsland",
            "CocosKeelingIslands",
            "Colombia",
            "Comoros",
            "RepublicOfTheCongo",
            "CookIslands",
            "CostaRica",
            "Cotedlvoire",
            "Croatia",
            "Cuba",
            "Cyprus",
            "CzechRepublic",
            "Denmark",
            "Djibouti",
            "Dominica",
            "DominicanRepublic",
            "EastTimor",
            "Ecuador",
            "Egypt",
            "ElSalvador",
            "EquatorialGuinea",
            "Eritrea",
            "Estonia",
            "Ethiopia",
            "FalklandIslands",
            "FaroeIslands",
            "Fiji",
            "Finland",
            "France",
            "FrenchPolynesia",
            "Gabon",
            "Gambia",
            "Georgia",
            "Germany",
            "Ghana",
            "Gibraltar",
            "Greece",
            "Greenland",
            "Grenada",
            "Guam",
            "Guatemala",
            "Guernsey",
            "Guinea",
            "GuineaBissau",
            "Guyana",
            "Haiti",
            "Honduras",
            "HongKong",
            "Hungary",
            "Iceland",
            "India",
            "Indonesia",
            "Iran",
            "Iraq",
            "Ireland",
            "IsleofMan",
            "Israel",
            "Italy",
            "Jamaica",
            "Japan",
            "Jersey",
            "Jordan",
            "Kazakhstan",
            "Kenya",
            "Kiribati",
            "KoreaNorth",
            "KoreaSouth",
            "BouvetIsland",
            "Kuwait",
            "Kyrgyzstan",
            "Laos",
            "Latvia",
            "Lebanon",
            "Lesotho",
            "Liberia",
            "Libya",
            "Liechtenstein",
            "Lithuania",
            "Luxembourg",
            "Macao",
            "Macedonia",
            "Madagascar",
            "Malawi",
            "Malaysia",
            "Maldives",
            "Mali",
            "Malta",
            "MarshallIslands",
            "Mauritania",
            "Mauritius",
            "Mayotte",
            "Mexico",
            "Micronesia",
            "Moldova",
            "Monaco",
            "Mongolia",
            "Montenegro",
            "Montserrat",
            "Morocco",
            "Mozambique",
            "Myanmar",
            "Curaçao",
            "Namibia",
            "Nauru",
            "Nepal",
            "Netherlands",
            "NewCaledonia",
            "NewZealand",
            "Nicaragua",
            "Niger",
            "Nigeria",
            "Niue",
            "NorfolkIsland",
            "NeutralZone",
            "NorthernMarianaIslands",
            "Norway",
            "Oman",
            "Pakistan",
            "Palau",
            "Palestine",
            "Panama",
            "PapuaNewGuinea",
            "Paraguay",
            "Peru",
            "Philippines",
            "PitcaimIslands",
            "Poland",
            "Portugal",
            "PuertoRico",
            "Qatar",
            "Romania",
            "Russia",
            "Rwanda",
            "SouthGeorgiaIslands",
            "BritishIndianOceanTerritory",
            "SaintHelena",
            "SaintKittsAndNevis",
            "SaintLucia",
            "FrenchGuiana",
            "SaintPierreAndMiquelon",
            "SaintVincentAndGrenadines",
            "Samoa",
            "SanMarino",
            "SaoTomeAndPrincipe",
            "SaudiArabia",
            "Senegal",
            "Serbia",
            "Seychelles",
            "SierraLeone",
            "Singapore",
            "Slovakia",
            "Slovenia",
            "SolomonIslands",
            "Somalia",
            "FrenchSouthernTerritories",
            "SouthAfrica",
            "HeardAndMcDonaldIslands",
            "Spain",
            "SriLanka",
            "Sudan",
            "Suriname",
            "Svalbard",
            "Eswatini",
            "Sweden",
            "Switzerland",
            "Syria",
            "Tajikistan",
            "Tanzania",
            "Thailand",
            "Togo",
            "Tokelau",
            "Tonga",
            "WallisFutunaIslands",
            "TrinidadAndTobago",
            "WesternSahara",
            "Tunisia",
            "Türkiye",
            "Turkmenistan",
            "TurksAndCaicosIslands",
            "Tuvalu",
            "Uganda",
            "Ukraine",
            "UnitedArabEmirates",
            "UnitedKingdom",
            "UnitedStates",
            "Uruguay",
            "Uzbekistan",
            "Vanuatu",
            "VaticanCity",
            "Venezuela",
            "Vietnam",
            "VirginIslands",
            "Yemen",
            "Zambia",
            "Zimbabwe",
            "Taiwan",
            "CaribbeanNetherlands",
            "Kosovo",
            "SouthSudan",
            "DemocraticRepublicOfTheCongo",
            "Réunion",
            "Martinique",
            "NorthernIreland",
            "Guadeloupe"
          ],
    ],

];
