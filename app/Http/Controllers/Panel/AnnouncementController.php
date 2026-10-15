<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Panel/Announcements/Index', [
            'announcements' => Announcement::query()->current()->with('creator:id,name,avatar_path')->paginate(15),
        ]);
    }
}
