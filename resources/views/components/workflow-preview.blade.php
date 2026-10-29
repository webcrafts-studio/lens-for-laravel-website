<div class="workflow-preview" data-workflow>
    <div class="workflow-toolbar"><span class="micro">The Lens workflow, inside Lens</span><span class="example-badge">Actual interface / example project</span></div>
    <div class="workflow-tabs" role="tablist" aria-label="Explore the Lens workflow">
        <button type="button" role="tab" id="workflow-tab-source" aria-controls="workflow-panel-source" aria-selected="true" tabindex="0">Locate the source</button>
        <button type="button" role="tab" id="workflow-tab-review" aria-controls="workflow-panel-review" aria-selected="false" tabindex="-1">Review the change</button>
        <button type="button" role="tab" id="workflow-tab-verify" aria-controls="workflow-panel-verify" aria-selected="false" tabindex="-1">Verify the result</button>
    </div>
    <div id="workflow-panel-source" class="workflow-panel" role="tabpanel" aria-labelledby="workflow-tab-source" tabindex="0">
        <div class="workflow-explanation"><span class="issue-tag">Finding / source location</span><h3>The failing element.<br>The file it came from.</h3><p>The report shows the failing node, its CSS selector, and the mapped source location. Open the file in your editor with the context already in front of you.</p><a class="text-link" href="{{ route('docs.show', 'frontend-support') }}">How source mapping works <x-icon name="arrow"/></a></div>
        <x-product-preview image="source" title="A finding and its source location" description="The actual Lens issue card shows the accessibility rule, failing HTML node, mapped Blade source file and line, CSS selector, View docs, and AI Fix actions."/>
    </div>
    <div id="workflow-panel-review" class="workflow-panel" role="tabpanel" aria-labelledby="workflow-tab-review" tabindex="0" hidden>
        <div class="workflow-explanation"><span class="issue-tag">AI-assisted / reviewed by you</span><h3>A proposed change.<br>You make the call.</h3><p>Review the original and suggested code in the actual AI Fix window. Edit the proposal before applying it. The screenshot uses a prepared example suggestion on the demo project.</p><a class="text-link" href="{{ route('docs.show', 'ai-fix-engine') }}">See the review workflow <x-icon name="arrow"/></a></div>
        <x-product-preview image="ai-review" title="Review fixes in the Fix All A queue" description="The actual Fix All A window, showing a prepared example proposal for image-alt. Two suggestions are ready. Review the diff, select Edit or Accept & Apply, or use Apply All Ready for the queue."/>
    </div>
    <div id="workflow-panel-verify" class="workflow-panel" role="tabpanel" aria-labelledby="workflow-tab-verify" tabindex="0" hidden>
        <div class="workflow-explanation"><span class="issue-tag">Applied / pending re-scan</span><h3>A saved file isn't proof.<br>Run a fresh scan.</h3><p>After applying a change, Lens marks the finding as pending re-scan. It stays in the violation count until you run a new audit and check the result.</p><a class="text-link" href="{{ route('docs.show', 'dashboard') }}">Explore the dashboard <x-icon name="arrow"/></a></div>
        <x-product-preview image="verification" title="An applied change, pending re-scan" description="The actual report after applying the image-alt fix. The finding is labelled AI Fix applied - pending re-scan and remains in the count of 3 violations. A new scan is needed to verify the change."/>
    </div>
</div>
