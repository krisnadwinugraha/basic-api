<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Article;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $admin = User::role('Admin')->count();    
        $member = User::role('Member')->count();
        $roles = Role::count();
        $article = Article::count();

        return response()->json([
            'Jumlah Admin Saat Ini' =>  $admin,
            'Jumlah Member Saat Ini' =>  $member,
            'Jumlah Role Saat Ini' =>  $roles,
            'Jumlah Article Saat Ini' =>  $article
        ], 200);
    }
}
