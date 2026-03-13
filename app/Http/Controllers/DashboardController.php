<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\PreAssembledPackage;
use App\Models\SurgicalChecklist;
use App\Models\Invoice;
use App\Models\ProductUnit;


class DashboardController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->get();
        $availablePackages = PreAssembledPackage::available()->count();
        $activeChecklists = SurgicalChecklist::active()->count();
        $pendingInvoices = Invoice::where('status', 'draft')->count();
        $units = ProductUnit::with('product')->get();
        return view('dashboard', compact('products', 'availablePackages', 'activeChecklists', 'pendingInvoices', 'units'));
    }
}
