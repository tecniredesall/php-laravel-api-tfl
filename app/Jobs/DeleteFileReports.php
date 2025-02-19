<?php

namespace App\Jobs;

use App\FileReports;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

class DeleteFileReports implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $file;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($file)
    {
        $this->file = $file;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $file = FileReports::find($this->file->id);
        if (Storage::disk('s3')->exists($file->route))
            Storage::disk('s3')->delete($file->route);
        $file->delete();
    }
}
