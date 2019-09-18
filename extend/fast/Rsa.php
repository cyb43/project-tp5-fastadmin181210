<?php

namespace fast;

/**
 * RSA签名类
 * (消息发送方私钥加密，消息接收方公钥解密)
 * @author ^2_3^
 */
class Rsa
{

    // 公钥
    public $publicKey = '';
    // 私钥
    public $privateKey = '';

    // 私钥资源
    private $_privKey;
    // 公钥资源
    private $_pubKey;

    /**
     * construtor
     * @author ^2_3^
     */
    function __construct($publicKey = null, $privateKey = null)
    {
        $this->setKey($publicKey, $privateKey);
    }

    /**
     * 设置公钥和私钥
     * @param string $publicKey 公钥
     * @param string $privateKey 私钥
     * @author ^2_3^
     */
    public function setKey($publicKey = null, $privateKey = null)
    {
        if (!is_null($publicKey))
        {
            $this->publicKey = $publicKey;
        }

        if (!is_null($privateKey))
        {
            $this->privateKey = $privateKey;
        }
    }

    /**
     * 设置私钥资源
     * setup the private key
     * @author ^2_3^
     */
    private function setupPrivKey()
    {
        // is_resource — 检测变量是否为资源类型;
        if (is_resource($this->_privKey))
        {
            return true;
        }

        // chunk_split — 将字符串分割成小块;
        $pem = chunk_split($this->privateKey, 64, "\n");
        $pem = "-----BEGIN PRIVATE KEY-----\n" . $pem . "-----END PRIVATE KEY-----\n";
        // openssl_pkey_get_private — 获取私钥;
        $this->_privKey = openssl_pkey_get_private($pem);

        return true;
    }

    /**
     * 设施公钥资源
     * setup the public key
     * @author ^2_3^
     */
    private function setupPubKey()
    {
        if (is_resource($this->_pubKey))
        {
            return true;
        }

        $pem = chunk_split($this->publicKey, 64, "\n");
        $pem = "-----BEGIN PUBLIC KEY-----\n" . $pem . "-----END PUBLIC KEY-----\n";

        // openssl_pkey_get_public — 从证书中解析公钥，以供使用。
        $this->_pubKey = openssl_pkey_get_public($pem);

        return true;
    }

    /**
     * 使用私钥加密数据
     * encrypt with the private key
     * @author ^2_3^
     */
    public function privEncrypt($data)
    {
        if (!is_string($data))
        {
            return null;
        }

        $this->setupPrivKey();

        // openssl_private_encrypt — 使用私钥加密数据;
        // openssl_private_encrypt ( string $data , string &$crypted ,
        //  mixed $key [, int $padding = OPENSSL_PKCS1_PADDING ] ) : bool
        // openssl_private_encrypt() 使用私钥 key 加密数据 data 并且将结果保存至变量 crypted中。
        //  加密后的数据可以通过openssl_public_decrypt()函数来解密。该函数用来签名数据（或者哈希）让别人相信数据并不是其他人写的。
        $r = openssl_private_encrypt($data, $encrypted, $this->_privKey);
        if ($r)
        {
            return base64_encode($encrypted);
        }

        return null;
    }

    /**
     * 使用私钥解密数据
     * decrypt with the private key
     * @author ^2_3^
     */
    public function privDecrypt($encrypted)
    {
        if (!is_string($encrypted))
        {
            return null;
        }

        $this->setupPrivKey();

        // 解码数据
        $encrypted = base64_decode($encrypted);

        // openssl_private_decrypt — 使用私钥解密数据;
        $r = openssl_private_decrypt($encrypted, $decrypted, $this->_privKey);
        if ($r)
        {
            return $decrypted;
        }

        return null;
    }

    /**
     * 使用公钥加密数据
     * encrypt with public key
     * @author ^2_3^
     */
    public function pubEncrypt($data)
    {
        if (!is_string($data))
        {
            return null;
        }

        $this->setupPubKey();

        //
        $r = openssl_public_encrypt($data, $encrypted, $this->_pubKey);
        if ($r)
        {
            // 编码数据
            return base64_encode($encrypted);
        }
        return null;
    }

    /**
     * 使用公钥解密数据
     * decrypt with the public key
     * @author ^2_3^
     */
    public function pubDecrypt($crypted)
    {
        if (!is_string($crypted))
        {
            return null;
        }

        $this->setupPubKey();

        $crypted = base64_decode($crypted);

        $r = openssl_public_decrypt($crypted, $decrypted, $this->_pubKey);
        if ($r)
        {
            return $decrypted;
        }
        return null;
    }

    /**
     * 构造签名
     * @param string $dataString 被签名数据
     * @return string
     * @author ^2_3^
     */
    public function sign($dataString)
    {
        $this->setupPrivKey();

        // 签名数据
        $signature = false;

        // 签名
        openssl_sign($dataString, $signature, $this->_privKey);

        return base64_encode($signature);
    }

    /**
     * 验证签名
     * @param string $dataString 被签名数据
     * @param string $signString 已经签名的字符串
     * @return number 1签名正确 0签名错误
     * @author ^2_3^
     */
    public function verify($dataString, $signString)
    {
        $this->setupPubKey();

        $signature = base64_decode($signString);

        $flg = openssl_verify($dataString, $signature, $this->_pubKey);

        return $flg;
    }

    /**
     * 析构函数
     * @author ^2_3^
     */
    public function __destruct()
    {
        // openssl_free_key — 释放密钥资源;
        is_resource($this->_privKey) && @openssl_free_key($this->_privKey);
        is_resource($this->_pubKey) && @openssl_free_key($this->_pubKey);
    }

}
