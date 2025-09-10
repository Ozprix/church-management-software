<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class EncryptionService
{
    /**
     * Encrypt a value
     *
     * @param mixed $value
     * @return string|null
     */
    public static function encrypt($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        
        return Crypt::encryptString($value);
    }
    
    /**
     * Decrypt a value
     *
     * @param string|null $encrypted
     * @return mixed
     */
    public static function decrypt(?string $encrypted)
    {
        if (empty($encrypted)) {
            return null;
        }
        
        try {
            return Crypt::decryptString($encrypted);
        } catch (DecryptException $e) {
            // Log the error
            \Log::error('Failed to decrypt value: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Encrypt an array of values
     *
     * @param array $data
     * @param array $fieldsToEncrypt
     * @return array
     */
    public static function encryptFields(array $data, array $fieldsToEncrypt): array
    {
        foreach ($fieldsToEncrypt as $field) {
            if (isset($data[$field])) {
                $data[$field] = self::encrypt($data[$field]);
            }
        }
        
        return $data;
    }
    
    /**
     * Decrypt an array of values
     *
     * @param array $data
     * @param array $fieldsToDecrypt
     * @return array
     */
    public static function decryptFields(array $data, array $fieldsToDecrypt): array
    {
        foreach ($fieldsToDecrypt as $field) {
            if (isset($data[$field])) {
                $data[$field] = self::decrypt($data[$field]);
            }
        }
        
        return $data;
    }
}
