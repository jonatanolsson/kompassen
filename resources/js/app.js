let mermaidPromise;

const loadMermaid = () => {
    mermaidPromise ??= import('mermaid').then(({ default: mermaid }) => {
        mermaid.initialize({
            securityLevel: 'strict',
            startOnLoad: false,
            theme: 'neutral',
        });

        return mermaid;
    });

    return mermaidPromise;
};

const renderMermaidDiagrams = async (root = document) => {
    const diagrams = root.querySelectorAll?.('[data-mermaid-diagram]:not([data-rendered])') ?? [];
    const mermaid = diagrams.length > 0 ? await loadMermaid() : null;

    diagrams.forEach(async (diagram) => {
        try {
            const { svg } = await mermaid.render(diagram.id, diagram.textContent.trim());
            diagram.innerHTML = svg;
            diagram.dataset.rendered = 'true';
            diagram.classList.add('flex', 'justify-center', 'max-w-full');
        } catch {
            diagram.classList.add('whitespace-pre-wrap');
        }
    });
};

document.addEventListener('DOMContentLoaded', () => renderMermaidDiagrams());

document.addEventListener('livewire:init', () => {
    Livewire.hook('morph.updated', ({ el }) => renderMermaidDiagrams(el));
});
