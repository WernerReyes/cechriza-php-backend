<?php
class UuidUtil
{
    public static function v4()
    {
        // Generate 16 random bytes
        $data = random_bytes(16);

        // Set the version to 0100
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        // Set the variant to 10xx
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        // Convert to hexadecimal format and insert hyphens
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}