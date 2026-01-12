<?php
namespace App\Services;

use App\Models\ScheduledSurgery;
use App\Models\PreAssembledPackage;
use App\Models\SurgeryPreparation;
use App\Models\SurgeryPreparationItem;
use Illuminate\Support\Facades\DB;

class PreparationService
{
    public function createPreparation($surgeryId, $packageId, $userId)
    {
        return DB::transaction(function () use ($surgeryId, $packageId, $userId) {
        $surgery = ScheduledSurgery::findOrFail($surgeryId);
        $preparation = SurgeryPreparation::create([
            'scheduled_surgery_id' => $surgery->id,
            'pre_assembled_package_id' => $packageId,
            'status'                   => 'picking',
            'prepared_by'              => $userId,
            'started_at'               => now(),
        ]);
        $surgery->update(['status' => 'in_preparation']);
        
        $package = PreAssembledPackage::with('contents')->findOrFail($packageId);
        $package->update(['status' => 'in_preparation']);

        $neededItems = $surgery->getChecklistItemsWithConditionals();
        $packageContents = $package->contents->pluck('quantity', 'product_id');

        foreach ($neededItems as $data){
            $checklistItem = $data['item'];
            $productId = $checklistItem->product_id;
            $requiredQty = $data['adjusted_quantity'] ?? $checklistItem->quantity;

            $inPackageQty = $packageContents->get($productId, 0);   
            $missingQty = max(0, $requiredQty - $inPackageQty);

            $preparation->items()->create([
                'product_id'          => $productId,
                'quantity_required'   => $requiredQty,
                'quantity_in_package' => $inPackageQty,
                'quantity_picked'     => 0,
                'quantity_missing'    => $missingQty,
                'is_mandatory'        => $data['is_mandatory'] ?? true,
                'status'              => $missingQty <= 0 ? 'in_package' : 'pending',
            ]);

        }
        return $preparation;

        

        
        });
    }
}


