<?php

final class TariffPolicyRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $tariffId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tariff_policies WHERE tariff_id = :tariff_id');
        $stmt->execute(['tariff_id' => $tariffId]);
        $policy = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($policy) {
            return $policy;
        }

        return $this->defaultPolicy($tariffId);
    }

    private function defaultPolicy(int $tariffId): array
    {
        return [
            'tariff_id' => $tariffId,
            'title' => 'Тариф ' . $tariffId,
            'system_prompt_report' => 'Сформируй нумерологический отчёт в JSON: text, pdf_blocks, disclaimer.',
            'user_prompt_template_report' => 'Анкета: {{profile}}',
            'system_prompt_followup' => 'Отвечай строго по содержанию отчёта. Если вопрос уводит в сторону — мягко возвращайся к отчёту.',
            'followup_limit' => 3,
            'followup_window_hours' => null,
            'followup_rules' => 'Отвечай строго по содержанию отчёта. Если вопрос уводит в сторону — мягко возвращайся к отчёту.',
            'output_format' => 'text+json',
        ];
    }
}
