<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RestockTemplate;

class GenerateRestockTemplate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-restock-template';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the restock template Excel file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Excel::store(new RestockTemplate(), 'templates/restock_template.xlsx');

        $this->info('Restock template generated successfully at storage/app/templates/restock_template.xlsx');
    }
}
