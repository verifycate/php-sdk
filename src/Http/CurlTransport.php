<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Http;

use Verifycate\Sdk\Exception\TransportException;

/**
 * Default transport. Requires ext-curl but no Composer runtime dependencies.
 */
final class CurlTransport implements TransportInterface
{
    /** @var array<int, mixed> */
    private array $options;

    /**
     * @param array<int, mixed> $options Extra curl_setopt options applied last.
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function send(Request $request): Response
    {
        if (!function_exists('curl_init')) {
            throw new TransportException(
                'The curl extension is required by CurlTransport. Install ext-curl or inject a PSR-18 transport.'
            );
        }

        /** @var array<string, array<int, string>> $responseHeaders */
        $responseHeaders = [];

        $handle = curl_init($request->getUrl());
        if ($handle === false) {
            throw new TransportException('Unable to initialise a curl handle.');
        }

        $timeoutMs = max(1, (int) round($request->getTimeout() * 1000));

        $headers = [];
        foreach ($request->getHeaders() as $name => $value) {
            $headers[] = $name . ': ' . $value;
        }

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CUSTOMREQUEST => $request->getMethod(),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT_MS => $timeoutMs,
            CURLOPT_TIMEOUT_MS => $timeoutMs,
            CURLOPT_HEADERFUNCTION => static function ($curl, string $line) use (&$responseHeaders): int {
                $length = strlen($line);
                $trimmed = trim($line);

                if ($trimmed === '') {
                    return $length;
                }

                if (strncasecmp($trimmed, 'HTTP/', 5) === 0) {
                    $responseHeaders = [];

                    return $length;
                }

                $parts = explode(':', $trimmed, 2);
                if (count($parts) === 2) {
                    $name = strtolower(trim($parts[0]));
                    $responseHeaders[$name][] = trim($parts[1]);
                }

                return $length;
            },
        ];

        if ($request->getBody() !== null) {
            $options[CURLOPT_POSTFIELDS] = $request->getBody();
        }

        foreach ($this->options as $option => $value) {
            $options[$option] = $value;
        }

        curl_setopt_array($handle, $options);

        $body = curl_exec($handle);
        $errorNumber = curl_errno($handle);
        $errorMessage = curl_error($handle);
        $statusCode = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);

        if ($errorNumber !== 0 || !is_string($body)) {
            throw new TransportException(
                sprintf(
                    'Verifycate API request failed: %s',
                    $errorMessage !== '' ? $errorMessage : 'unknown curl error'
                ),
                $errorNumber
            );
        }

        return new Response($statusCode, $responseHeaders, $body);
    }
}
