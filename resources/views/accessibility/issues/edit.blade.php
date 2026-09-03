<x-app-layout>
    @livewire('accessibility-issue-form', ['project' => $project, 'issue' => $issue], key('edit-' . $issue->id))
</x-app-layout>
