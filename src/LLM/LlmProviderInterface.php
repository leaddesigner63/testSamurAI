<?php

interface LlmProviderInterface
{
    public function generateReport(array $tariffPolicy, array $profileData): LlmResult;

    public function answerFollowup(
        array $tariffPolicy,
        array $profileData,
        string $reportText,
        array $followupHistory,
        string $userQuestion
    ): LlmResult;
}
