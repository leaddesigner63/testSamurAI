<?php

final class LlmResult
{
    public string $provider;
    public string $model;
    public ?string $rawText;
    public ?array $parsedJson;
    public string $text;
    public ?array $pdfBlocks;
    public ?string $disclaimer;
    public ?array $usage;
    public int $latencyMs;
    public ?string $requestId;

    public function __construct(
        string $provider,
        string $model,
        ?string $rawText,
        ?array $parsedJson,
        string $text,
        ?array $pdfBlocks,
        ?string $disclaimer,
        ?array $usage,
        int $latencyMs,
        ?string $requestId
    ) {
        $this->provider = $provider;
        $this->model = $model;
        $this->rawText = $rawText;
        $this->parsedJson = $parsedJson;
        $this->text = $text;
        $this->pdfBlocks = $pdfBlocks;
        $this->disclaimer = $disclaimer;
        $this->usage = $usage;
        $this->latencyMs = $latencyMs;
        $this->requestId = $requestId;
    }
}
