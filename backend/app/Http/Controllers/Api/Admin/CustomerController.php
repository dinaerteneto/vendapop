<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        return Customer::withCount('orders')->latest()->get();
    }

    public function show(Customer $customer)
    {
        return $customer->load('orders');
    }
}

