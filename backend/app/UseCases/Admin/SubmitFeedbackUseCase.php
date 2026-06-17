<?php

namespace App\UseCases\Admin;

use App\Models\Feedback;
use Illuminate\Database\Eloquent\Model;

class SubmitFeedbackUseCase
{
    public function execute(int $tenantId, string $subject, string $message): Model
    {
        return Feedback::create([
            'tenant_id' => $tenantId,
            'subject' => $subject,
            'message' => $message,
        ]);
    }
}
