<?php

namespace App\Imports;

use App\Jobs\ImportExcelScrape;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class RoomeImport implements ToModel, WithStartRow
{
    public function startRow(): int
    {
        return 2;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {

            ImportExcelScrape::dispatch($row);
//        $data = $row;
//        $job = (new \App\Jobs\ImportExcelScrape($data))
//            ->delay(
//                now()
//                    ->addSeconds(2)
//            );
//        dispatch($job);

    }


}
