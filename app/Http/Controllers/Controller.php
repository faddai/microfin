<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use App\Jobs\ExportDataToCsvJob;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param Request $request
     * @param Collection $data
     * @param array $options
     * @return mixed
     */
    public function export(Request $request, Collection $data, array $options= [])
    {
        $format = $request->get('format');
        $filename = $options['filename'] ?? md5(str_random());
        $view = $options['view'] ?? '';
        $dataKey = $options['dataKey'] ?? 'data';

        if (in_array($format, ['pdf', 'print'], true)) {
            $data->prepend(array_keys($data->first()));

            $pdf = \PDF::loadView($view, [$dataKey => $data])->setPaper('a4', $options['orientation'] ?? 'portrait');
        }

        switch ($format) {
            case 'csv':
                return $this->dispatch(new ExportDataToCsvJob($data, $filename));
                break;

            case 'pdf':
                return $pdf->download($filename.'.pdf');
                break;

            case 'print':
                $data->prepend(array_keys($data->first()));
                return $pdf->stream($filename.'.pdf');
                break;

            default:
                break;

        }

    }
}
