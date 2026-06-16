<?php

namespace App\Services;

class ModerationService
{
    // Эти слова автоматически ОТКЛОНЯЮТ событие
    private array $banned = [
        // Мат
        'блять','блядь','блядский','блядина',
        'пизда','пиздец','пиздёж','пиздить',
        'ебать','ебёт','ебал','ебись','ёбаный','ёбля','еблан','ебло','ебанутый',
        'хуй','хуйня','хуёво','хули','нахуй','похуй','хуйло','хуесос',
        'мудак','мудила','мудозвон',
        'сука','сучка','сучий',
        'залупа','ублюдок','шлюха','шлюшка',
        'педик','пидор','пидарас','пидрила',
        'дрочить','дрочит','дрочер',
        'ёбнутый','ёбнуть',
        'пиздюк','мразь',
        // Насилие и преступления против детей
        'насиловать','насилует','изнасилование','насильник',
        'педофил','педофилия','педофильский',
        'убей','убивай',
        'детское порно','секс с детьми',
        // Явный сексуальный контент
        'порно','порнуха','порнография',
        'проститутка','проституция',
        // Наркоторговля
        'закладка наркотик','продам наркотик','купить наркотик','наркоторговля',
    ];

    // Эти слова отправляют на ПРОВЕРКУ
    private array $suspicious = [
        'взорвать','взрыв','бомба','оружие',
        'убийство','убийца','убить','расстрел','казнить',
        'терроризм','террорист','теракт','экстремизм','геноцид',
        'насилие','наркотик','кокаин','героин','мефедрон','метамфетамин','амфетамин','спайс',
        'эскорт','интим','секс','знакомства','свингер',
        'казино','ставки','букмекер',
        'обнал','мошенник','скам',
        'магия','гадание','приворот','экстрасенс',
        'курить','кальян','вейп','алкоголь',
    ];

    // Замена похожих латинских букв на кириллицу
    private array $latinToCyrillic = [
        'a'=>'а','e'=>'е','o'=>'о','p'=>'р','c'=>'с','x'=>'х',
        'k'=>'к','h'=>'н','b'=>'в','m'=>'м','y'=>'у','t'=>'т',
        'A'=>'а','E'=>'е','O'=>'о','P'=>'р','C'=>'с','X'=>'х',
        'K'=>'к','B'=>'в','M'=>'м','Y'=>'у','T'=>'т',
    ];

    private function normalize(string $text): string
    {
        $text = mb_strtolower($text);
        $text = strtr($text, $this->latinToCyrillic);
        // Убираем спецсимволы между буквами (п.и.з.д.а → пизда)
        $text = preg_replace('/[\.\-_\*@#\$%\^&!,\s]+/', ' ', $text);
        return $text;
    }

    private function containsWord(string $text, string $word): bool
    {
        // Ищем слово как отдельное слово (не внутри другого)
        // Используем границы слова с учётом кириллицы
        $pattern = '/(^|[\s\p{P}])' . preg_quote($word, '/') . '([\s\p{P}]|$)/ui';
        return (bool) preg_match($pattern, $text);
    }

    private function containsPhrase(string $text, string $phrase): bool
    {
        // Для фраз из нескольких слов — простой поиск вхождения
        return mb_strpos($text, $phrase) !== false;
    }

    public function check(string ...$texts): array
    {
        $combined = implode(' ', $texts);
        $normalized = $this->normalize($combined);
        $original = mb_strtolower($combined);

        foreach ($this->banned as $word) {
            $normWord = $this->normalize($word);
            $isPhrase = str_contains($word, ' ');

            if ($isPhrase) {
                // Фразу ищем как вхождение
                if ($this->containsPhrase($normalized, $normWord)
                    || $this->containsPhrase($original, mb_strtolower($word))) {
                    return [
                        'status' => 'rejected',
                        'reason' => 'Автоматически отклонено: содержит запрещённое выражение.',
                    ];
                }
            } else {
                // Одно слово — ищем как целое слово
                if ($this->containsWord($normalized, $normWord)
                    || $this->containsWord($original, mb_strtolower($word))) {
                    return [
                        'status' => 'rejected',
                        'reason' => "Автоматически отклонено: содержит запрещённое слово «{$word}».",
                    ];
                }
            }
        }

        foreach ($this->suspicious as $word) {
            $normWord = $this->normalize($word);
            if ($this->containsWord($normalized, $normWord)
                || $this->containsWord($original, mb_strtolower($word))) {
                return ['status' => 'pending', 'reason' => null];
            }
        }

        return ['status' => 'approved', 'reason' => null];
    }
}
