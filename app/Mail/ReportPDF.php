<?php

namespace App\Mail;

use App\Helpers\ReportPDFQuery;
use App\Helpers\ReportsHTMLtoPDF;
use App\Http\Requests\API\WEB\ReportRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class ReportPDF extends Mailable
{
    use Queueable, SerializesModels;
    protected  $request;
    protected  $data;
    protected $user;



    public function __construct(ReportPDFQuery $request,$user)
    {
        $this->request=$request;
        $this->user=$user;
    }

    public function build()
    {
        ini_set('max_execution_time', 3000); //3000 seconds = 50 minutes

        $data=$this->request->initReport()->getInfoDB()->getData();
        App::setLocale($data['lang']);
        $reportPDF= new ReportsHTMLtoPDF(view('partials.pdf.'.$data['report_name'],$data)->render(),strtoupper($data['report_name']),"","s3", "");
        $file=$reportPDF->savePDF()->output();
        $info=[
            'file'=>$file['data']['file'],
            'user'=>$this->user
        ];

        return $this->bcc('ycedeno@grainchain.io')->markdown('mail.reports.pdf',$info)->subject(__('messages.reports.subject'));

    }
    public function failed(\Exception $exception)
    {
        app('sentry')->captureException($exception);
    }
}
