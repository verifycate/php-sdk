<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Resources;

use Verifycate\Sdk\Dto\Recipient;
use Verifycate\Sdk\Dto\RecipientPage;
use Verifycate\Sdk\Operations;

final class Recipients extends Resource
{
    public function list(
        ?string $search = null,
        ?string $status = null,
        ?string $certificate = null,
        int $limit = 50,
        int $offset = 0
    ): RecipientPage {
        if ($limit < 1 || $limit > 200) {
            throw new \InvalidArgumentException('The page limit must be between 1 and 200.');
        }

        if ($offset < 0) {
            throw new \InvalidArgumentException('The page offset cannot be negative.');
        }

        return RecipientPage::fromArray(
            $this->client->request(
                Operations::LIST_RECIPIENTS,
                ['slug' => $this->workspace()],
                [
                    'search' => $search,
                    'status' => $status,
                    'certificate' => $certificate,
                    'limit' => $limit,
                    'offset' => $offset,
                ]
            )
        );
    }

    /**
     * Fetch one recipient by email (matched case-insensitively by the API).
     */
    public function get(string $email): Recipient
    {
        $email = trim($email);
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException('A valid recipient email address is required.');
        }

        return Recipient::fromArray(
            $this->client->request(
                Operations::GET_RECIPIENT,
                ['slug' => $this->workspace(), 'email' => $email]
            )
        );
    }
}
