<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * List all pages (admin) - like pages/term-condition list.
     */
    public function index()
    {
        $pages = Page::ordered()->get();
        $pageTitle = __('messages.content_pages');
        return view('setting.pages.index', compact('pages', 'pageTitle'));
    }

    /**
     * Edit form for a page - like pages/term-condition.
     */
    public function edit($id)
    {
        $page = Page::findOrFail($id);
        $pageTitle = $page->title;
        return view('setting.page_form', compact('page', 'pageTitle'));
    }

    /**
     * Save page (admin).
     */
    public function update(Request $request)
    {
        if (demoUserPermission()) {
            return redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }

        $request->validate([
            'id'    => 'required|exists:pages,id',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $page = Page::findOrFail($request->id);
        $page->title = $request->title;
        $page->content = $request->content ?? '';
        $page->is_active = $request->boolean('is_active');
        $page->sort_order = (int) ($request->sort_order ?? 0);
        $page->save();

        $message = __('messages.update_form', ['form' => $page->title]);
        return redirect()->route('page.index')->with('success', $message);
    }
}
