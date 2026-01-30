<?php

final class GeminiProvider implements LlmProviderInterface
{
    private string $apiKey;
    private string $modelReport;
    private string $modelFollowup;
    private float $temperature;
    private int $maxOutputTokens;
    private int $timeoutSeconds;

    public function __construct(
        string $apiKey,
        string $modelReport,
        string $modelFollowup,
        float $temperature,
        int $maxOutputTokens,
        int $timeoutSeconds
    ) {
        $this->apiKey = $apiKey;
        $this->modelReport = $modelReport;
        $this->modelFollowup = $modelFollowup;
        $this->temperature = $temperature;
        $this->maxOutputTokens = $maxOutputTokens;
        $this->timeoutSeconds = $timeoutSeconds;
    }

    public function generateReport(array $tariffPolicy, array $profileData): LlmResult
    {
        $messages = $this->buildReportMessages($tariffPolicy, $profileData);
        return $this->callApi($this->modelReport, $messages);
    }

    public function answerFollowup(
        array $tariffPolicy,
        array $profileData,
        string $reportText,
        array $followupHistory,
        string $userQuestion
    ): LlmResult {
        $messages = $this->buildFollowupMessages(
            $tariffPolicy,
            $profileData,
            $reportText,
            $followupHistory,
            $userQuestion
        );

        return $this->callApi($this->modelFollowup, $messages);
    }

    private function callApi(string $model, array $messages): LlmResult
    {
        $payload = [
            'contents' => $messages,
            'generationConfig' => [
                'temperature' => $this->temperature,
                'maxOutputTokens' => $this->maxOutputTokens,
            ],
        ];

        $start = microtime(true);
        $response = $this->postJson(
            'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $this->apiKey,
            $payload
        );
        $latency = (int) ((microtime(true) - $start) * 1000);

        $rawText = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $parsedJson = $this->parseJson($rawText);

        return new LlmResult(
            'gemini',
            $model,
            $rawText,
            $parsedJson,
            $parsedJson['text'] ?? $rawText,
            $parsedJson['pdf_blocks'] ?? null,
            $parsedJson['disclaimer'] ?? null,
            $response['usageMetadata'] ?? null,
            $latency,
            $response['requestId'] ?? null
        );
    }

    private function postJson(string $url, array $payload): array
    {
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $headers = ['Content-Type: application/json'];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeoutSeconds);

        $result = curl_exec($ch);
        if ($result === false) {
            throw new RuntimeException('Gemini API error: ' . curl_error($ch));
        }

        $decoded = json_decode($result, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Gemini API invalid response');
        }

        return $decoded;
    }

    private function parseJson(string $text): ?array
    {
        $decoded = json_decode($text, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function buildReportMessages(array $tariffPolicy, array $profileData): array
    {
        $system = $tariffPolicy['system_prompt_report'] ?? 'Сформируй нумерологический отчёт в JSON-формате.';
        $template = $tariffPolicy['user_prompt_template_report'] ?? 'Анкета: {{profile}}';
        $profile = json_encode($profileData, JSON_UNESCAPED_UNICODE);

        return [
            ['role' => 'user', 'parts' => [['text' => $system]]],
            ['role' => 'user', 'parts' => [['text' => str_replace('{{profile}}', $profile, $template)]]],
        ];
    }

    private function buildFollowupMessages(
        array $tariffPolicy,
        array $profileData,
        string $reportText,
        array $followupHistory,
        string $userQuestion
    ): array {
        $system = $tariffPolicy['system_prompt_followup'] ?? 'Отвечай строго по отчёту.';
        $history = json_encode($followupHistory, JSON_UNESCAPED_UNICODE);
        $profile = json_encode($profileData, JSON_UNESCAPED_UNICODE);

        return [
            ['role' => 'user', 'parts' => [['text' => $system]]],
            ['role' => 'user', 'parts' => [['text' => 'Анкета: ' . $profile]]],
            ['role' => 'user', 'parts' => [['text' => 'Отчёт: ' . $reportText]]],
            ['role' => 'user', 'parts' => [['text' => 'История: ' . $history]]],
            ['role' => 'user', 'parts' => [['text' => 'Вопрос: ' . $userQuestion]]],
        ];
    }
}
