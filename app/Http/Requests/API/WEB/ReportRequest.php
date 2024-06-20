<?php

namespace App\Http\Requests\API\WEB;

use App\Helpers\ReportPDFQuery;
use App\Helpers\ReportsHTMLtoPDF;
use App\Mail\ReportPDF;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class ReportRequest extends FormRequest
{
    protected $MANY_RECORDS = 5000;
    public $pdfReport;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
//            'types' => 'required',
//            'formato' => 'required',
//            'lang' => 'required'

        ];
    }

    public function initReport($report_id, $lang)
    {
        $this->pdfReport = new ReportPDFQuery($this->all(), $report_id, $lang);
        $this->pdfReport->InitReport();
        $this->xlsx_name = '';
        return $this;

    }

    public function getResponse($format, $report_id, $lang)
    {
        $data = $this->pdfReport->getData();
        $manyRecords = $data['total'] > $this->MANY_RECORDS;
        if ($this->input('jsonResponse') == true) {
            $this->pdfReport->getInfoDB();
            $info['data'] = $this->pdfReport->getData();
            $info['code'] = 200;
            return $info;
        }
        if ($manyRecords && $this->input('sendMail') != 1) {
            $info = [
                'data' => [
                    'msg' => 'Many records',
                    'total' => $data['total']
                ],
                'code' => 413
            ];
        } else if ($manyRecords && $this->input('sendMail') == 1) {
            $info = [
                'data' => [
                    'msg' => 'Success',
                    'file' => null,
                    'total' => $data['total']
                ],
                'code' => 200
            ];
            $pdfReport = new ReportPDFQuery($this->all(), $report_id, $lang);
            Mail::to($this->user())->queue(new ReportPDF($pdfReport, $this->user()->name . " " . $this->user()->lastname));

        } else {
            $this->pdfReport->getInfoDB();
            $data = $this->pdfReport->getData();
            $nameFielXLS = 'report'.date("dmYHis");
            $reportPDF = new ReportsHTMLtoPDF(view('partials.pdf.' . $data['report_name'], $data)->render(), strtoupper($data['report_name']), "", "s3", $nameFielXLS, $format);
            $info = $reportPDF->savePDF()->output();
        }
        return $info;
    }

    public function getXlsResponse($format)
    {
        $this->pdfReport->getInfoDB();
        $data = $this->pdfReport->getData();
        $manyRecords = $data['total'] > $this->MANY_RECORDS;
        if ($manyRecords && $this->input('sendMail') != 1) {
            return [
                'data' => [
                    'msg' => 'Many records',
                    'total' => $data['total']
                ],
                'code' => 413
            ];
        } else{
            $query = isset($data['infoFormato']) ? $data['infoFormato'] : '';
            $groupBy = isset($data["groupBy"]) ? $data['groupBy'] : '';
            $field_ticket = isset($data["fields"]) ? $data['fields'] : '';
            $fullDate = isset($data["date_from"]) ? $data['date_from'] : '';
            $toI = isset($data["date_to"]) ? $data['date_to'] : '';
            $nombre = isset($data["coname"]) ? $data['coname'] : '';
            $groupBy_commodity = isset($data["groupBy_commodity"]) ? $data['groupBy_commodity'] : '';
            $report_name = isset($data["report_name"]) ? $data["report_name"] : '';
            $decimals_in_tickets = isset($data["decimals_in_tickets"]) ? $data["decimals_in_tickets"] : '';
            $display_show_id = isset($data["display_show_id"]) ? $data["display_show_id"] : '';
            $commodities = isset($data['commodities']) ? $data['commodities'] : null;
            $bushel = isset($data['bushel']) ? $data['bushel'] : 0;
            $cwt = isset($data['cwt']) ? $data['cwt'] : 0;
            $lang = isset($data["lang"]) ? $data["lang"] : '';
            $nameFielXLS = 'report'.date("dmYHis");

            $file = view('partials.excel.' . $report_name, [
                'infoFormato' => $query,
                'groupBy' => $groupBy,
                'fields' => $field_ticket,
                'date_from' => $fullDate,
                'date_to' => $toI,
                'groupBy_commodity' => $groupBy_commodity !== '' ? $groupBy_commodity : '',
                'coname' => $nombre,
                'lang' => $lang,
                'decimals_in_tickets' => $decimals_in_tickets,
                'display_show_id' => $display_show_id,
                'commodities' => $commodities,
                'bushel' => $bushel,
                'cwt' => $cwt
            ]);

            Storage::disk('s3')->put(env("INSTANCE_ID") . '/reports/xlsx/'.$nameFielXLS.'.xls', $file);
            $reportPDF = new ReportsHTMLtoPDF(view('partials.excel.' . $data['report_name'], $data)->render(), strtoupper($data['report_name']), "", "s3", $nameFielXLS, $format);
            return $reportPDF->output();
        }
    }

}
