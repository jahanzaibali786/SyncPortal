<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\EmployeeLetterTemplates as Template;
use App\Models\User;

class EmployeeLetterTemplatesController extends AccountBaseController
{
    public function index()
    {
        // Generate tag list from users table
        $exclude = [
            'id', 'password', 'remember_token', 'created_at', 'updated_at', 'stripe_id',
            'pm_type', 'pm_last_four', 'trial_ends_at', 'inactive_date', 'is_client_contact',
        ];

        $columns = Schema::getColumnListing('users');
        $tags = [];

        $pageTitle = 'Joining Letter Template';

        foreach ($columns as $column) {
            if (!in_array($column, $exclude)) {
                $tags[] = '{user_' . $column . '}';
            }
        }

        // For now, we only load or create the Joining Letter template
        $template = Template::firstOrCreate(
            ['type' => 'joining_letter'],
            ['title' => 'Default Joining Letter', 'content' => '']
        );

        return view('employees.ajax.joining-letter', compact('tags', 'template', 'pageTitle'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
        ]);

        Template::updateOrCreate(
            ['type' => 'joining_letter'],
            ['title' => $request->title, 'content' => $request->content]
        );

        return redirect()->route('templates.joining')->with('success', 'Template saved successfully!');
    }
}
