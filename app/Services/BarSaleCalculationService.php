<?php

namespace App\Services;

use App\Models\BarSale;
use Illuminate\Support\Collection;

class BarSaleCalculationService
{
    /**
     * @param  Collection<int, BarSale>  $sales
     * @param  array<int, array{mixer_id: int, ratio?: int}>  $mapping
     * @return array{
     *     allBarSales: Collection<int, BarSale>,
     *     liquorSales: Collection<int, BarSale>,
     *     mixerSales: Collection<int, BarSale>,
     *     totalTiendaUnits: int,
     *     totalLiquorCombos: int|float,
     *     subtotalLiquors: int|float,
     *     totalMixerConsumed: int|float,
     *     totalMixerExtras: int|float,
     *     subtotalMixers: int|float,
     *     grandTotalBar: float
     * }
     */
    public function summarize(Collection $sales, bool $isStore, array $mapping, float $storeSalesTotal = 0): array
    {
        $liquorSales = $sales->filter(fn (BarSale $sale): bool => $sale->product->category !== 'Mixers')->values();
        $mixerSales = $sales->filter(fn (BarSale $sale): bool => $sale->product->category === 'Mixers')->values();

        if ($isStore) {
            $sales = $sales->sort(function (BarSale $a, BarSale $b): int {
                $isMixerA = $a->product->category === 'Mixers' ? 1 : 0;
                $isMixerB = $b->product->category === 'Mixers' ? 1 : 0;

                return $isMixerA !== $isMixerB
                    ? $isMixerA <=> $isMixerB
                    : $a->product->id <=> $b->product->id;
            })->values();

            $combosPerMixer = $this->calculateIncludedMixers($liquorSales, $mapping);
            foreach ($sales as $sale) {
                if ($sale->product->category === 'Mixers') {
                    $included = $combosPerMixer[$sale->product_id] ?? 0;
                    $sale->included_in_combos = $included;
                    $sale->vendido = max((int) $sale->vendido, $included);
                    $sale->extras = max(0, (int) $sale->vendido - $included);
                    $sale->subtotal = $sale->extras * (float) $sale->unit_price;
                } else {
                    $sale->subtotal = (int) $sale->vendido * (float) $sale->unit_price;
                }
            }

            return [
                'allBarSales' => $sales,
                'liquorSales' => $liquorSales,
                'mixerSales' => $mixerSales,
                'totalTiendaUnits' => (int) $sales->sum('vendido'),
                'totalLiquorCombos' => 0,
                'subtotalLiquors' => 0,
                'totalMixerConsumed' => 0,
                'totalMixerExtras' => 0,
                'subtotalMixers' => 0,
                'grandTotalBar' => (float) $sales->sum('subtotal') + $storeSalesTotal,
            ];
        }

        $combosPerMixer = $this->calculateIncludedMixers($liquorSales, $mapping);
        foreach ($mixerSales as $mixerSale) {
            $mixerSale->included_in_combos = $combosPerMixer[$mixerSale->product_id] ?? 0;
            $mixerSale->extras = max(0, (int) $mixerSale->vendido - $mixerSale->included_in_combos);
            $mixerSale->subtotal = $mixerSale->extras * (float) $mixerSale->unit_price;
        }

        $subtotalLiquors = $liquorSales->sum('subtotal');
        $subtotalMixers = $mixerSales->sum('subtotal');

        return [
            'allBarSales' => $sales,
            'liquorSales' => $liquorSales,
            'mixerSales' => $mixerSales,
            'totalTiendaUnits' => 0,
            'totalLiquorCombos' => $liquorSales->sum('vendido'),
            'subtotalLiquors' => $subtotalLiquors,
            'totalMixerConsumed' => $mixerSales->sum('vendido'),
            'totalMixerExtras' => $mixerSales->sum('extras'),
            'subtotalMixers' => $subtotalMixers,
            'grandTotalBar' => (float) $subtotalLiquors + (float) $subtotalMixers,
        ];
    }

    /**
     * @param  iterable<BarSale>  $liquorSales
     * @param  array<int, array{mixer_id: int, ratio?: int}>  $mapping
     * @return array<int, int>
     */
    public function calculateIncludedMixers(iterable $liquorSales, array $mapping): array
    {
        $combosPerMixer = [];

        foreach ($liquorSales as $liquorSale) {
            $specialMixers = $this->decodeSpecialMixers($liquorSale->selected_special_mixer);
            $specialCount = 0;

            foreach ($specialMixers as $mixerId => $quantity) {
                $quantity = (int) $quantity;
                if ($quantity <= 0 || ! $mixerId) {
                    continue;
                }

                $mixerId = (int) $mixerId;
                $combosPerMixer[$mixerId] = ($combosPerMixer[$mixerId] ?? 0) + $quantity;
                $specialCount += $quantity;
            }

            $mixerInfo = $mapping[$liquorSale->product_id] ?? null;
            if ($mixerInfo) {
                $mixerId = (int) $mixerInfo['mixer_id'];
                $ratio = (int) ($mixerInfo['ratio'] ?? 1);
                $remainingCombos = max(0, (int) $liquorSale->vendido - $specialCount);
                $combosPerMixer[$mixerId] = ($combosPerMixer[$mixerId] ?? 0) + ($remainingCombos * $ratio);
            }
        }

        return $combosPerMixer;
    }

    /**
     * @return array<int|string, mixed>
     */
    private function decodeSpecialMixers(mixed $selectedMixers): array
    {
        if (is_string($selectedMixers)) {
            $selectedMixers = json_decode($selectedMixers, true);
        }

        return is_array($selectedMixers) ? $selectedMixers : [];
    }
}
