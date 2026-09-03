<x-app-layout>
    @livewire('accessibility-issue-form', ['project' => $project, 'issue' => null], key('create-' . $project->id))
</x-app-layout>
