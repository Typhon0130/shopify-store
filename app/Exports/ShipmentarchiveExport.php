<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;

// class ShipmentarchiveExport implements FromCollection
// {
//     /**
//     * @return \Illuminate\Support\Collection
//     */
//     public function collection()
//     {
//         // return Order::all();
//         return Order::where('calledDHL_at', '>=', '2022-02-22')->get();
//     }


// }


use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;

class ShipmentarchiveExport implements FromQuery, WithHeadings, WithEvents
{
    use Exportable;

    public function __construct($search, $from,  $to)
    {
        $this->search = $search;
        $this->from = $from;
        $this->to = $to;
    }

    public function query()
    {
     
        $param = ['search' => $this->search, 'from' => $this->from, 'to' => $this->to];
        
        $orders = isset($this->from) 
                ? Order::select('shipment_order', 'name','first_name', 'last_name', 'calledDHL_at')->where('calledDHL_at', '>=', $param['from'])
                : Order::select('shipment_order', 'name','first_name', 'last_name', 'calledDHL_at')->where('calledDHL_at', '>=', '2022-01-01');

        $orders = $orders->where(function($query) use($param) {
            if(isset($param['to']))
                $query->whereDate('calledDHL_at', '<=', $param['to']);

            if(isset($param['search']))
                $query->where('shipment_order', 'like', '%'.$param['search'].'%')
                      ->orWhere('first_name',   'like', '%'.$param['search'].'%')
                      ->orWhere('last_name',    'like', '%'.$param['search'].'%')
                      ->orWhere('name',         'like', '%'.$param['search'].'%');
        });

        return $orders;
    }

    public function headings(): array
    {
        return ["Shipment order", "Order name", "Firstname", "Last name", "Shipping date"];
    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
   
                $event->sheet->getDelegate()->getColumnDimension('A')->setAutoSize(true);
                $event->sheet->getDelegate()->getColumnDimension('B')->setAutoSize(true);
                $event->sheet->getDelegate()->getColumnDimension('C')->setAutoSize(true);
                $event->sheet->getDelegate()->getColumnDimension('D')->setAutoSize(true);
                $event->sheet->getDelegate()->getColumnDimension('E')->setAutoSize(true);
     
            },
        ];
    }
}