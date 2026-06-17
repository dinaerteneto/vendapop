<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\UseCases\Admin\SubmitFeedbackUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackWidgetController extends Controller
{
    public function __construct(
        private SubmitFeedbackUseCase $submitFeedbackUseCase,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $feedback = $this->submitFeedbackUseCase->execute(
            tenantId: $request->user()->tenant->id,
            subject: $validated['subject'],
            message: $validated['message'],
        );

        return response()->json($feedback, 201);
    }
}
