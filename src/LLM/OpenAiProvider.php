<?php

final class OpenAiProvider implements LlmProviderInterface
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
            'model' => $model,
            'messages' => $messages,
            'temperature' => $this->temperature,
            'max_output_tokens' => $this->maxOutputTokens,
        ];

        $start = microtime(true);
        $response = $this->postJson('https://api.openai.com/v1/responses', $payload);
        $latency = (int) ((microtime(true) - $start) * 1000);

        $rawText = $response['output_text'] ?? '';
        $parsedJson = $this->parseJson($rawText);

        return new LlmResult(
            'openai',
            $model,
            $rawText,
            $parsedJson,
            $parsedJson['text'] ?? $rawText,
            $parsedJson['pdf_blocks'] ?? null,
            $parsedJson['disclaimer'] ?? null,
            $response['usage'] ?? null,
            $latency,
            $response['id'] ?? null
        );
    }

    private function postJson(string $url, array $payload): array
    {
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeoutSeconds);

        $result = curl_exec($ch);
        if ($result === false) {
            throw new RuntimeException('OpenAI API error: ' . curl_error($ch));
        }

        $decoded = json_decode($result, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('OpenAI API invalid response');
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
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => str_replace('{{profile}}', $profile, $template)],
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
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => 'Анкета: ' . $profile],
            ['role' => 'user', 'content' => 'Отчёт: ' . $reportText],
            ['role' => 'user', 'content' => 'История: ' . $history],
            ['role' => 'user', 'content' => 'Вопрос: ' . $userQuestion],
        ];
    }
}
