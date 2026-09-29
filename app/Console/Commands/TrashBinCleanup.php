<?php
namespace Pterodactyl\Console\Commands;
use Illuminate\Console\Command;
use Pterodactyl\BlueprintFramework\Extensions\sagatrashbin\TrashBinRepository;
class TrashBinCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'p:trash:cleanup';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes files from the trash bin that have been there for more than 24 hours.';
    /**
     * @var \Pterodactyl\BlueprintFramework\Extensions\sagatrashbin\TrashBinRepository
     */
    protected $repository;
    /**
     * Create a new command instance.
     *
     * @param \Pterodactyl\BlueprintFramework\Extensions\sagatrashbin\TrashBinRepository $repository
     */
    public function __construct(TrashBinRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $count = $this->repository->deleteExpiredItems();
        $this->info("Deleted {$count} expired items from the trash bin.");
        return 0;
    }
}
