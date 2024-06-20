<?php


namespace App\Helpers;


use App\FileReports;
use App\Jobs\DeleteFileReports;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use mikehaertl\wkhtmlto\Pdf;

class ReportsHTMLtoPDF
{
    protected $html;
    protected $name;
    protected $folder;
    protected $code = 200;
    protected $msg = "Success";
    protected $disk;
    protected $pdfString;
    protected $daysExpired=7;
    protected $nameFielXLS;
    public function __construct($html, $name, $folder = "", $disk = "", $nameFielXLS, $format = "")
    {
        $this->html = $html;
        $this->setName($name);
        $this->disk = $disk;
        $this->nameFielXLS = $nameFielXLS;
        $this->format = $format;
        if (empty($folder)) {
            $this->folder = (env("INSTANCE_ID") . '/reports/pdf/');
        }
    }

    public function setName($name)
    {
        $this->name = $name . '_' . str_random(10);
    }

    public function savePDF()
    {
        try {
            $pdf = new Pdf($this->html);
            $pdf->setOptions([
                'footer-center' => 'Page [page] of [toPage]',
                'footer-font-size' => 8
            ]);

            $fileContent = $pdf->toString();
            if (empty($fileContent)) {
                $this->code = 500;
                $this->msg = $pdf->getError();
                return $this;
            }

            $this->html = "";
            if (!empty($this->disk))
                Storage::disk($this->disk)->put($this->getPathFilePDF(), $fileContent);
            else $this->pdfString=$fileContent;
        } catch (\Exception $e) {
            $this->fail($e);
        }
        return $this;

    }

    public function output()
    {
        $file=""; $s3 ="";
        if(!empty($this->disk)){
            $expired=now()->addDays($this->daysExpired);
            $fileReport=new FileReports();
            $fileReport->expired_at=$expired;

            if($this->format === 'pdf') {
                $fileReport->route=$this->getPathFilePDF();
                $fileReport->save();
                $file=URL::temporarySignedRoute('download-pdf', $expired, ['id'=>$fileReport->id, 'name'=>$this->name]);
            }else if($this->format === 'xlsx') {
                if (strstr($this->nameFielXLS, 'csv'))
                    $nameFielXLS=$this->nameFielXLS;
                else
                    $nameFielXLS=$this->nameFielXLS . '.xls';

                $fileReport->route=env("INSTANCE_ID") . '/reports/xlsx/' . $nameFielXLS;
                $fileReport->save();
                $file=URL::temporarySignedRoute('download-xlsx', $expired, ['id'=>$fileReport->id, 'name'=>$this->name]);
            }

            dispatch(new DeleteFileReports($fileReport))->delay($expired);
            $s3=$fileReport->route;
        }else{
            $file=base64_encode($this->pdfString);
        }

        $data = [
            'data' => [
                'msg' => $this->msg,
                'file' => $file,
                'format' => $this->format,
                's3' => $s3
            ],
            'code' => $this->code,

        ];
        return $data;
    }

    public function getHtml()
    {
        return $this->html;
    }

    public function getPathFilePDF()
    {
        return $this->folder . $this->getName() . '.pdf';

    }


    public function getName()
    {
        return $this->name;
    }

    public function fail($exception)
    {
        $this->code = 500;
        $this->msg = $exception->getMessage();
        return $this;

    }

}
