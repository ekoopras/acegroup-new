<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;

class PrintService
{
    public static function send($view, $data = [], $filename = 'service.print')
    {
        $pdf = Pdf::loadView($view, $data);

        $path = storage_path('app/' . $filename);

        $pdf->save($path);

        return Http::attach(
            'file',
            file_get_contents($path),
            $filename
        )->post('http://10.10.10.2:5000/print');
    }

    // public static function send($view, $data = [], $filename = 'print.pdf')
    // {
    //     $pdf = Pdf::loadView($view, $data);

    //     return $pdf->stream($filename);
    // }
}
