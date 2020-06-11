<?php

namespace Sendportal\Base\Http\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Sendportal\Base\Facades\Helper;
use Sendportal\Base\Http\Requests\TemplateStoreRequest;
use Sendportal\Base\Http\Requests\TemplateUpdateRequest;
use Sendportal\Base\Repositories\TemplateTenantRepository;
use Sendportal\Base\Traits\NormalizeTags;

class TemplatesController extends Controller
{
    use NormalizeTags;

    /** @var TemplateTenantRepository */
    protected $templates;

    public function __construct(TemplateTenantRepository $templates)
    {
        $this->templates = $templates;
    }

    /**
     * Show a listing of the resource.
     *
     * @return View
     * @throws Exception
     */
    public function index(): View
    {
        $templates = $this->templates->paginate(Helper::getCurrentWorkspace()->id, 'name');

        return view('sendportal::templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        return view('sendportal::templates.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param TemplateStoreRequest $request
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function store(TemplateStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $data['content'] = $this->normalizeTags($data['content'], 'content');

        $this->templates->store(Helper::getCurrentWorkspace()->id, $data);

        return redirect()
            ->route('sendportal.templates.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return View
     * @throws Exception
     */
    public function edit(int $id): View
    {
        $template = $this->templates->find(Helper::getCurrentWorkspace()->id, $id);

        return view('sendportal::templates.edit', compact('template'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TemplateUpdateRequest $request
     * @param int $id
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function update(TemplateUpdateRequest $request, int $id): RedirectResponse
    {
        $data = $request->validated();

        $data['content'] = $this->normalizeTags($data['content'], 'content');

        $this->templates->update(Helper::getCurrentWorkspace()->id, $id, $data);

        return redirect()
            ->route('sendportal.templates.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function destroy(int $id): RedirectResponse
    {
        $template = $this->templates->find(Helper::getCurrentWorkspace()->id, $id);

        // TODO(david): I don't think `is_in_use` has been implemented.
        if ($template->is_in_use) {
            return redirect()
                ->back()
                ->withErrors(['template' => __('Cannot delete a template that has been used.')]);
        }

        $this->templates->destroy(Helper::getCurrentWorkspace()->id, $template->id);

        return redirect()
            ->route('sendportal.templates.index')
            ->with('success', __('Template successfully deleted.'));
    }
}
