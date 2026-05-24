<?php

namespace App\Services;

class ModerationService
{
    private array $banned = [
        'хлеб',
        'блять','блядь','пизда','пиздец','пиздёж','пиздить',
        'ебать','ебёт','ебал','ебись','ёбаный','ёбля',
        'хуй','хуйня','хуёво','хули','нахуй','похуй',
        'мудак','мудила','мудозвон',
        'сука','сучка','сучий',
        'залупа','ублюдок','шлюха','педик','пидор','пидарас',
        'порно','порнуха',
        'наркотик','кокаин','мефедрон',
    ];

    private array $suspicious = [
        'эскорт','интим','казино','ставки','букмекер','оружие',
    ];

    public function check(string ...$texts): array
    {
        $combined = mb_strtolower(implode(' ', $texts));

        foreach ($this->banned as $word) {
            if (mb_strpos($combined, mb_strtolower($word)) !== false) {
                return [
                    'status' => 'rejected',
                    'reason' => "Автоматически отклонено: содержит запрещённое слово «{$word}».",
                ];
            }
        }

        foreach ($this->suspicious as $word) {
            if (mb_strpos($combined, mb_strtolower($word)) !== false) {
                return ['status' => 'pending', 'reason' => null];
            }
        }

        return ['status' => 'approved', 'reason' => null];
    }
}
