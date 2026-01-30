<?php

final class LlmService
{
    private Config $config;
    private LlmProviderInterface $provider;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->provider = $this->makeProvider();
    }

    public function generateReport(array $tariffPolicy, array $profileData): LlmResult
    {
        $result = $this->provider->generateReport($tariffPolicy, $profileData);
        return $this->repairJsonIfNeeded($tariffPolicy, $profileData, $result);
    }

    public function answerFollowup(
        array $tariffPolicy,
        array $profileData,
        string $reportText,
        array $followupHistory,
        string $userQuestion
    ): LlmResult {
        $result = $this->provider->answerFollowup(
            $tariffPolicy,
            $profileData,
            $reportText,
            $followupHistory,
            $userQuestion
        );

        return $this->repairJsonIfNeeded($tariffPolicy, $profileData, $result);
    }

    private function makeProvider(): LlmProviderInterface
    {
        $provider = $this->config->getString('LLM_PROVIDER', 'openai');

        if ($provider === 'gemini') {
            return new GeminiProvider(
                $this->config->getString('GEMINI_API_KEY'),
                $this->config->getString('GEMINI_MODEL_REPORT'),
                $this->config->getString('GEMINI_MODEL_FOLLOWUP'),
                $this->config->getFloat('LLM_TEMPERATURE', 0.7),
                $this->config->getInt('LLM_MAX_OUTPUT_TOKENS', 1200),
                $this->config->getInt('LLM_TIMEOUT_SECONDS', 30)
            );
        }

        return new OpenAiProvider(
            $this->config->getString('OPENAI_API_KEY'),
            $this->config->getString('OPENAI_MODEL_REPORT'),
            $this->config->getString('OPENAI_MODEL_FOLLOWUP'),
            $this->config->getFloat('LLM_TEMPERATURE', 0.7),
            $this->config->getInt('LLM_MAX_OUTPUT_TOKENS', 1200),
            $this->config->getInt('LLM_TIMEOUT_SECONDS', 30)
        );
    }

    private function repairJsonIfNeeded(array $tariffPolicy, array $profileData, LlmResult $result): LlmResult
    {
        if ($result->parsedJson !== null) {
            return $result;
        }

        $repairPolicy = $tariffPolicy;
        $repairPolicy['system_prompt_report'] = 'Исправь JSON, не меняя смысла. Верни валидный JSON.';
        $repairPolicy['user_prompt_template_report'] = '{{profile}}';

        $repairResult = $this->provider->generateReport($repairPolicy, [
            'original_response' => $result->rawText,
            'profile' => $profileData,
        ]);

        if ($repairResult->parsedJson === null) {
            return $result;
        }

        return $repairResult;
    }
}
