<?php

// C:\Users\Asim\sekkeiShoLrvl\app\Contracts\UserExportInterface.php

namespace App\Contracts\User;

use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface UserExportInterface
{
    /**
     * @param array $filters
     * @return StreamedResponse
     */
    public function export(array $filters): StreamedResponse;
}
