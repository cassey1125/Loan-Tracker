<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvestorProfitController extends Controller
{
    public function downloadPdf()
    {
        $loans5 = Loan::where('interest_rate', 5)->get();
        $loans7 = Loan::where('interest_rate', 7)->get();

        $totalProfit5_Investor1 = 0;
        $totalProfit5_Investor2 = 0;
        
        foreach ($loans5 as $loan) {
            $totalProfit5_Investor1 += $loan->investor1_interest;
            $totalProfit5_Investor2 += $loan->investor2_interest;
        }

        $totalProfit7_Investor1 = 0;
        $totalProfit7_Investor2 = 0;

        foreach ($loans7 as $loan) {
            $totalProfit7_Investor1 += $loan->investor1_interest;
            $totalProfit7_Investor2 += $loan->investor2_interest;
        }

        $summary = [
            'rate_5' => [
                'investor1' => $totalProfit5_Investor1,
                'investor2' => $totalProfit5_Investor2,
                'total_interest' => $loans5->sum('interest_amount'),
            ],
            'rate_7' => [
                'investor1' => $totalProfit7_Investor1,
                'investor2' => $totalProfit7_Investor2,
                'total_interest' => $loans7->sum('interest_amount'),
            ],
        ];

        $pdf = Pdf::loadView('pdf.investor-profit', compact('loans5', 'loans7', 'summary'));

        return $pdf->download('investor-profit-analysis.pdf');
    }
}
