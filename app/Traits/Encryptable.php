<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;

trait Encryptable
{
    // protected static $encryptable = [];

    /**
     * Get the encryptable attributes.
     */
    public static function encryptableAttributes()
    {
        return static::$encryptable;
    }

    /**
     * Encrypt the attribute's value before saving it to the database.
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, static::encryptableAttributes())) {
            $value = $value ? Crypt::encryptString($value) : null;
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Decrypt the attribute's value when accessing it.
     */
    public function getAttribute($key)
    {
        // dd($key);
        $value = parent::getAttribute($key);
        // dd(in_array($key, static::encryptableAttributes()),$value);

        if (in_array($key, static::encryptableAttributes()) && $value) {
            try {
                // dd($value);
                $value = Crypt::decryptString($value);
            } catch (\Exception $e) {
                // Handle exception if decryption fails
                // dd($value,"catch");
            }
        }

        return $value;
    }
}
