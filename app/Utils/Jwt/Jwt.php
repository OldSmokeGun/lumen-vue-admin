<?php

namespace App\Utils\Jwt;

use oldSmokeGun\Jwt\Exception;

class Jwt
{
    /**
     * @var string
     */
    private $sec;

    /**
     * @var string
     */
    private $alg;

    /**
     * @var string
     */
    private $iss;

    /**
     * @var string
     */
    private $sub;

    /**
     * @var string
     */
    private $aud;

    /**
     * @var string
     */
    private $jti;

    /**
     * @var float|int
     */
    private $expire;

    public function __construct()
    {
        $this->setSec(config('internal.jwt.sec'));
        $this->setAlg(config('internal.jwt.alg'));
        $this->setIss(config('internal.jwt.iss'));
        $this->setSub(config('internal.jwt.sub'));
        $this->setAud(config('internal.jwt.aud'));
        $this->setJti(config('internal.jwt.jti'));
        $this->setExpire(config('internal.jwt.expire'));
    }

    /**
     * @param array $extraData
     *
     * @return string
     * @throws Exception
     */
    public function encode(array $extraData = []): string
    {
        $jwt = new \oldSmokeGun\Jwt\Jwt();

        $token = $jwt
            ->setSecret($this->getSec())
            ->setAlg($this->getAlg())
            ->setIss($this->getIss())
            ->setSub($this->getSub())
            ->setAud($this->getAud())
            ->setJti($this->getJti())
            ->setExp(time() + $this->getExpire())
            ->setNbf(time())
            ->setExtraData($extraData)
            ->build();

        return $token;
    }

    /**
     * @param string $token
     *
     * @return array|null
     */
    public function decode(string $token)
    {
        try {
            $jwt = new \oldSmokeGun\Jwt\Jwt();

            $result = $jwt
                ->setSecret($this->getSec())
                ->parse($token);

            return $result;

        } catch (\Exception $e) {

            return null;
        }
    }

    /**
     * @param string $token
     *
     * @return int
     * @throws Exception
     */
    public function validate(string $token): int
    {
        $jwt = new \oldSmokeGun\Jwt\Jwt();

        return $jwt->setSecret($this->getSec())->validate($token);
    }

    /**
     * @return string
     */
    private function getSec(): string
    {
        return $this->sec;
    }

    /**
     * @param string $sec
     *
     * @return Jwt
     */
    public function setSec(string $sec): Jwt
    {
        $this->sec = $sec;
        return $this;
    }

    /**
     * @return string
     */
    private function getAlg(): string
    {
        return $this->alg;
    }

    /**
     * @param string $alg
     *
     * @return Jwt
     */
    public function setAlg(string $alg): Jwt
    {
        $this->alg = $alg;
        return $this;
    }

    /**
     * @return string
     */
    private function getIss(): string
    {
        return $this->iss;
    }

    /**
     * @param string $iss
     *
     * @return Jwt
     */
    public function setIss(string $iss): Jwt
    {
        $this->iss = $iss;
        return $this;
    }

    /**
     * @return string
     */
    private function getSub(): string
    {
        return $this->sub;
    }

    /**
     * @param string $sub
     *
     * @return Jwt
     */
    public function setSub(string $sub): Jwt
    {
        $this->sub = $sub;
        return $this;
    }

    /**
     * @return string
     */
    private function getAud(): string
    {
        return $this->aud;
    }

    /**
     * @param string $aud
     *
     * @return Jwt
     */
    public function setAud(string $aud): Jwt
    {
        $this->aud = $aud;
        return $this;
    }

    /**
     * @return string
     */
    private function getJti(): string
    {
        return $this->jti;
    }

    /**
     * @param string $jti
     *
     * @return Jwt
     */
    public function setJti(string $jti): Jwt
    {
        $this->jti = $jti;
        return $this;
    }

    /**
     * @return float|int
     */
    private function getExpire()
    {
        return $this->expire;
    }

    /**
     * @param $expire
     *
     * @return Jwt
     */
    public function setExpire($expire): Jwt
    {
        $this->expire = $expire;
        return $this;
    }

}
