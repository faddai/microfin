<?php

namespace App\Jobs;

use App\Exceptions\NoDataAvailableForExportException;
use Illuminate\Support\Collection;
use League\Csv\Writer;
use Ramsey\Uuid\Uuid;

class ExportDataToCsvJob
{
    /**
     * @var Collection
     */
    private $data;
    /**
     * @var null
     */
    private $filename;

    /**
     * Create a new job instance.
     *
     * @param Collection $data
     * @param null $filename
     */
    public function __construct(Collection $data, $filename = null)
    {
        $this->data = $data;
        $this->filename = $filename ? sprintf('%s.csv', $filename) : sprintf('%s.csv', str_replace('-', '', Uuid::uuid4()->toString()));
    }

    /**
     * Execute the job.
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        if (! $this->data->count()) {
            throw new NoDataAvailableForExportException;
        }

        $writer = Writer::createFromFileObject(new \SplTempFileObject());

        $meta = $this->data->meta ?? collect();

        // write meta information
        if ($meta->count()) {

            $writer->insertAll(
                $meta->map(function ($value, $key) {
                    return sprintf('%s, %s', $key, $value);
                })
                ->prepend(' ')
                ->prepend('"'. config('app.address') .'"')
                ->prepend(config('app.company'))
                ->push(' ') // insert a blank line
                ->toArray()
            );

        }

        $this->data->prepend(array_keys($this->data->first()));

        $writer->insertAll($this->data)->output($this->filename);

        // Without immediately exiting, the output of $writer->output, an integer representing the number of characters
        // written, is appended as the last row of the CSV
        die();
    }
}
