<?php

namespace App\Exports;

use DateTime;use Maatwebsite\Excel\Concerns\FromArray;

class BatchReportExport implements FromArray
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($batchTickets)
    {
        $this->batchTickets = $batchTickets;
    }

    public function array(): array
    {
        $btickets = $this->batchTickets;
        $fecha = '';
        if( isset($btickets['contract']['end_date']) ) {
            if($btickets['contract']['lang'] == "en"){
                $date = new DateTime($btickets['contract']['end_date']);
                $fecha = $date->format('m/d/Y');
            }else{
                $date = new DateTime($btickets['contract']['end_date']);
                $fecha = $date->format('d/m/Y');
            }
        }

        $total = 0; $k = 0;
        if(isset( $btickets['tickets'] )){
            foreach( $btickets['tickets'] as $k => $value ){
                $total += isset($value->net) ? $value->net : 0;
            }
        }

        $data = [
            ['Contract', $btickets['contract']['cmodity_contract_id'] ],
            ['REPORT' ],
            ['Details'],
            ['Batch No:', $btickets['contract']['no_batch'], $btickets['contract']['status']  ],
            ['Seller:', $btickets['contract']['seller'], 'Date:', $fecha ],
            ['Buyer:', $btickets['contract']['buyer'], 'Commodity:', $btickets['contract']['name'] ],
            ['Elevator:', $btickets['contract']['elevator'], 'Quantity:', number_format($total, $btickets['contract']['decimals_in_tickets']) . ' '. $btickets['contract']['metric_system'] ],
            ['', '', '', '', ''],
            ['Total Tickets:', $k+1],
            ['ID', 'FIELD TICKET', 'CREATED', 'WEIGHT', 'CHARACTERISTICS']
        ];

        foreach ($btickets['tickets'] as $ticket) {
            $fecha = '';$characteristics = [];
            if( isset( $ticket->date_start )){

                if($btickets['contract']['lang'] == "en"){
                    $date = new DateTime($ticket->date_start);
                    $fecha = $date->format('m/d/Y');
                }else{
                    $date = new DateTime($ticket->date_start);
                    $fecha = $date->format('d/m/Y');
                }
            }

            foreach( $ticket->characteristics as $row )
                $characteristics[] = $row->cmodity_characteristic_id. ': ' .''. $row->value;

            $feature = str_replace('"', '', json_encode($characteristics));
            $feature = str_replace( ["[","]"], "", $feature );

            $tickets[] = [
                [$ticket->ticket_id, ( $ticket->orgticket === '' ) ? 'N/A' : $ticket->orgticket, $fecha,
                    number_format(isset($ticket->net) ? $ticket->net : 0, $btickets['contract']['decimals_in_tickets']). ' '. $btickets['contract']['metric_system'], $feature]
            ];
        }

        return array_merge($data, $tickets);
    }
}
