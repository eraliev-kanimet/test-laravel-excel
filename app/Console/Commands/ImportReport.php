<?php

namespace App\Console\Commands;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\BudgetItemCategory;
use App\Models\BudgetItemName;
use Illuminate\Console\Command;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\UnavailableStream;

class ImportReport extends Command
{
    protected $signature = 'import:report';

    protected array $months = ['January',];
    protected array $no_product = [
        'HAGERSTOWN TOTAL',
        'Line 109',
        'Total',
        'Internet Total',
        'PVR on Internet',
        'CO-OP',
        'GRAND TOTAL (WITHOUT CO-OP)',
        'PVR',
        'Allocation',
        'New',
        'Used'
    ];

    /**
     * @throws UnavailableStream
     * @throws Exception
     */
    public function handle(): void
    {
        $name = $this->ask('Write the name of the file located in the excel folder to be imported!', 'report.csv');

        $path = storage_path('app/excel/' . $name);

        if (file_exists($path)) {
            $array = explode('.', $path);

            $file_extension = $array[count($array) - 1];

            if ($file_extension == 'csv') {
                $data = $this->parseCSV($path);

                $this->syncData($data);

                file_put_contents('products.json', json_encode($data));

                $this->info('Import successfully completed!');
            } else {
                $this->error('This file extension is not supported by this parser yet, supported file extension (.csv)');
            }
        } else {
            $this->error('File not found, try again!');
        }
    }

    /**
     * @throws UnavailableStream
     * @throws Exception
     */
    protected function parseCSV(string $path): array
    {
        $data = [];

        $csv = Reader::createFromPath($path);

        $name = '';
        $category = '';

        foreach ($csv->getRecords() as $row) {
            $row = array_values(array_map('trim', $row));

            $count_row = count(array_filter($row, fn($item) => $item));

            if ($count_row) {
                if ($name == '' && $count_row == 1) {
                    $name = $row[0];
                    continue;
                }

                if (!empty(array_intersect($row, $this->months))) {
                    continue;
                }

                if ($name) {
                    if (!$category && $count_row == 1) {
                        $category = $row[0];
                        continue;
                    }

                    if ($category && in_array('Total', $row)) {
                        $category = '';
                        continue;
                    }

                    if ($category) {
                        if (!empty(array_intersect($row, $this->no_product))) {
                            continue;
                        }
                        $product = $row[0];

                        unset($row[0]);

                        if ($product) {
                            $data[$name][$category][$product] = array_slice($row, 0, -3);
                        }
                    }
                }
            }
        }

        return $data;
    }

    protected function syncData(array $data): void
    {
        foreach ($data as $name => $categories) {
            $budget = Budget::firstOrCreate([
                'name' => $name,
            ], [
                'name' => $name,
            ]);

            foreach ($categories as $category => $items) {
                $budgetItemCategory = BudgetItemCategory::firstOrCreate([
                    'name' => $category,
                    'budget_id' => $budget->id,
                ], [
                    'name' => $category,
                    'budget_id' => $budget->id,
                ]);

                foreach ($items as $product => $amounts) {
                    $budgetItemName = BudgetItemName::firstOrCreate([
                        'name' => $product,
                        'budget_item_category_id' => $budgetItemCategory->id,
                    ], [
                        'name' => $product,
                        'budget_item_category_id' => $budgetItemCategory->id,
                    ]);

                    for ($i = 0; $i < count($amounts); $i++) {
                        $data = [
                            'budget_item_name_id' => $budgetItemName->id,
                            'month' => $i + 1,
                            'amount' => floatval(str_replace(['$', ','], '', $amounts[$i])),
                        ];

                        BudgetItem::updateOrCreate([
                            'budget_item_name_id' => $budgetItemName->id,
                            'month' => $i + 1,
                        ], $data);
                    }
                }
            }
        }
    }
}
