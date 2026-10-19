let mermaidPromise;
let mermaidQueue = Promise.resolve();
let livewireHooksRegistered = false;
let activeMermaidTheme = null;

const isDarkMode = () => document.documentElement.classList.contains('dark');

const mermaidThemeVariables = (dark) => dark ? {
    background: '#18181b',
    primaryColor: '#1e3a8a',
    primaryTextColor: '#eff6ff',
    primaryBorderColor: '#60a5fa',
    lineColor: '#a1a1aa',
    secondaryColor: '#14532d',
    secondaryTextColor: '#f0fdf4',
    secondaryBorderColor: '#4ade80',
    tertiaryColor: '#78350f',
    tertiaryTextColor: '#fffbeb',
    tertiaryBorderColor: '#fbbf24',
    mainBkg: '#1e3a8a',
    nodeBorder: '#60a5fa',
    clusterBkg: '#27272a',
    clusterBorder: '#71717a',
    edgeLabelBackground: '#27272a',
    textColor: '#f4f4f5',
    fontFamily: 'Figtree, sans-serif',
} : {
    background: '#ffffff',
    primaryColor: '#dbeafe',
    primaryTextColor: '#172554',
    primaryBorderColor: '#2563eb',
    lineColor: '#475569',
    secondaryColor: '#dcfce7',
    secondaryTextColor: '#14532d',
    secondaryBorderColor: '#16a34a',
    tertiaryColor: '#fef3c7',
    tertiaryTextColor: '#78350f',
    tertiaryBorderColor: '#d97706',
    mainBkg: '#dbeafe',
    nodeBorder: '#2563eb',
    clusterBkg: '#f8fafc',
    clusterBorder: '#94a3b8',
    edgeLabelBackground: '#ffffff',
    textColor: '#18181b',
    fontFamily: 'Figtree, sans-serif',
};

const configureMermaidTheme = (mermaid, dark) => {
    const theme = dark ? 'dark' : 'light';

    if (activeMermaidTheme === theme) {
        return;
    }

    mermaid.initialize({
        securityLevel: 'strict',
        startOnLoad: false,
        theme: 'base',
        themeVariables: mermaidThemeVariables(dark),
    });

    activeMermaidTheme = theme;
};

const loadMermaid = () => {
    mermaidPromise ??= import('mermaid').then(({ default: mermaid }) => {
        configureMermaidTheme(mermaid, isDarkMode());
        return mermaid;
    });

    return mermaidPromise;
};

const withMermaidLock = (callback) => {
    const run = mermaidQueue.then(callback, callback);
    mermaidQueue = run.catch(() => {});

    return run;
};

const renderMermaidDiagrams = (root = document, force = false) => withMermaidLock(() => renderMermaidDiagramsNow(root, force));

const renderMermaidDiagramsNow = async (root, force) => {
    const selector = force
        ? '[data-mermaid-diagram]:not([data-rendering])'
        : '[data-mermaid-diagram]:not([data-rendered]):not([data-rendering])';
    const diagrams = [
        ...(root.matches?.(selector) ? [root] : []),
        ...(root.querySelectorAll?.(selector) ?? []),
    ];

    if (diagrams.length === 0) {
        return;
    }

    diagrams.forEach((diagram) => {
        diagram.dataset.rendering = 'true';
    });

    try {
        const mermaid = await loadMermaid();
        const dark = isDarkMode();
        configureMermaidTheme(mermaid, dark);

        await Promise.all(diagrams.map(async (diagram) => {
            try {
                const { svg } = await mermaid.render(
                    `${diagram.id}-${dark ? 'dark' : 'light'}-svg`,
                    diagram.dataset.mermaidSource,
                );
                diagram.innerHTML = svg;
                diagram.dataset.rendered = 'true';
                diagram.dataset.theme = dark ? 'dark' : 'light';
                diagram.classList.add('flex', 'justify-center', 'max-w-full');
            } catch (error) {
                diagram.classList.add('whitespace-pre-wrap');
                console.error(`Failed to render Mermaid diagram "${diagram.id}".`, error);
            } finally {
                delete diagram.dataset.rendering;
            }
        }));
    } catch (error) {
        diagrams.forEach((diagram) => {
            delete diagram.dataset.rendering;
            diagram.classList.add('whitespace-pre-wrap');
        });
        console.error('Failed to load Mermaid renderer.', error);
    }
};

renderMermaidDiagrams();

const appearanceObserver = new MutationObserver(() => {
    const theme = isDarkMode() ? 'dark' : 'light';

    if (activeMermaidTheme && activeMermaidTheme !== theme) {
        renderMermaidDiagrams(document, true);
    }
});

appearanceObserver.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class'],
});

const registerLivewireHooks = () => {
    if (livewireHooksRegistered || !window.Livewire) {
        return;
    }

    livewireHooksRegistered = true;
    window.Livewire.hook('morph.updated', ({ el }) => renderMermaidDiagrams(el));
    window.Livewire.hook('morphed', ({ el }) => renderMermaidDiagrams(el));
    document.addEventListener('livewire:navigated', () => renderMermaidDiagrams());

    window.Livewire.on('toast', ({ message, variant = 'success' }) => {
        window.Flux.toast(message, {
            variant: variant === 'error' ? 'danger' : variant,
        });
    });
};

document.addEventListener('livewire:init', registerLivewireHooks);
registerLivewireHooks();
document.addEventListener('DOMContentLoaded', registerLivewireHooks);

const SVG_NAMESPACE = 'http://www.w3.org/2000/svg';
const DIAGRAM_NUMBER = '[-+]?(?:\\d+\\.?\\d*|\\.\\d+)(?:[eE][-+]?\\d+)?';
const DIAGRAM_LENGTH = `${DIAGRAM_NUMBER}(?:px|%|em|ex|pt)?`;
const DIAGRAM_ID_PATTERN = /^[A-Za-z][A-Za-z0-9_\-:.]{0,127}$/;
const DIAGRAM_TEXT_ELEMENTS = ['text', 'tspan', 'title', 'desc'];
// Styles are flattened into presentation attributes. Filters are decorative (e.g. shadows) and symbols are
// unreferenced because <use> is rejected, so neither can be referenced after sanitizing.
const DIAGRAM_DROPPED_ELEMENTS = ['style', 'filter', 'symbol'];
const DIAGRAM_PAINTED_ELEMENTS = ['path', 'rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon', 'text', 'tspan'];
const DIAGRAM_REFERENCE_ATTRIBUTES = ['fill', 'stroke', 'clip-path', 'marker-start', 'marker-mid', 'marker-end'];
const DIAGRAM_SHARED_EXCLUSIONS = ['svg', 'defs', 'title', 'desc'];
const DIAGRAM_TEXT_PROPERTIES = ['text-anchor', 'dominant-baseline', 'font-family', 'font-size', 'font-weight', 'font-style'];
const DIAGRAM_PAINT_PROPERTIES = [
    'fill', 'fill-opacity', 'fill-rule', 'stroke', 'stroke-width', 'stroke-opacity', 'stroke-dasharray',
    'stroke-dashoffset', 'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit', 'opacity', 'clip-rule',
];
const DIAGRAM_SHARED_ATTRIBUTES = [...DIAGRAM_PAINT_PROPERTIES, 'transform', 'clip-path', 'marker-start', 'marker-mid', 'marker-end'];

// Mirrors App\Services\IssueDiagramSvg; the server remains the authority and rejects anything else.
const DIAGRAM_ELEMENT_ATTRIBUTES = {
    svg: ['width', 'height', 'viewBox', 'preserveAspectRatio', 'role', 'aria-roledescription'],
    g: [],
    defs: [],
    title: [],
    desc: [],
    marker: ['viewBox', 'refX', 'refY', 'markerWidth', 'markerHeight', 'markerUnits', 'orient', 'preserveAspectRatio'],
    clipPath: ['clipPathUnits'],
    path: ['d'],
    rect: ['x', 'y', 'width', 'height', 'rx', 'ry'],
    circle: ['cx', 'cy', 'r'],
    ellipse: ['cx', 'cy', 'rx', 'ry'],
    line: ['x1', 'y1', 'x2', 'y2'],
    polyline: ['points'],
    polygon: ['points'],
    text: ['x', 'y', 'dx', 'dy', ...DIAGRAM_TEXT_PROPERTIES],
    tspan: ['x', 'y', 'dx', 'dy', ...DIAGRAM_TEXT_PROPERTIES],
};

const diagramValuePattern = (name) => {
    switch (name) {
        case 'x': case 'y': case 'dx': case 'dy': case 'width': case 'height': case 'rx': case 'ry':
        case 'cx': case 'cy': case 'r': case 'x1': case 'y1': case 'x2': case 'y2': case 'refX': case 'refY':
        case 'markerWidth': case 'markerHeight': case 'stroke-width': case 'stroke-dashoffset': case 'font-size':
            return new RegExp(`^(?:${DIAGRAM_LENGTH}(?:[\\s,]+${DIAGRAM_LENGTH})*)?$`);
        case 'opacity': case 'fill-opacity': case 'stroke-opacity': case 'stroke-miterlimit':
            return new RegExp(`^${DIAGRAM_NUMBER}%?$`);
        case 'viewBox': case 'points':
            return /^[0-9eE.,+\-\s]*$/;
        case 'stroke-dasharray':
            return new RegExp(`^(?:none|${DIAGRAM_LENGTH}(?:[\\s,]+${DIAGRAM_LENGTH})*)$`);
        case 'd':
            return /^[MmZzLlHhVvCcSsQqTtAa0-9eE.,+\-\s]*$/;
        case 'transform':
            return /^(?:\s*(?:translate|scale|rotate|matrix|skewX|skewY)\s*\([0-9eE.,+\-\s]*\)\s*,?)*$/;
        case 'fill': case 'stroke':
            return /^(?:none|transparent|currentColor|[a-zA-Z]{3,20}|#[0-9a-fA-F]{3,8}|rgba?\(\s*[0-9.]+%?\s*,\s*[0-9.]+%?\s*,\s*[0-9.]+%?\s*(?:,\s*[0-9.]+%?\s*)?\))$/;
        case 'fill-rule': case 'clip-rule':
            return /^(?:nonzero|evenodd|inherit)$/;
        case 'stroke-linecap':
            return /^(?:butt|round|square|inherit)$/;
        case 'stroke-linejoin':
            return /^(?:miter|round|bevel|arcs|miter-clip|inherit)$/;
        case 'clip-path': case 'marker-start': case 'marker-mid': case 'marker-end':
            return /^none$/;
        case 'markerUnits':
            return /^(?:strokeWidth|userSpaceOnUse)$/;
        case 'clipPathUnits':
            return /^(?:userSpaceOnUse|objectBoundingBox)$/;
        case 'orient':
            return new RegExp(`^(?:auto|auto-start-reverse|${DIAGRAM_NUMBER}(?:deg)?)$`);
        case 'preserveAspectRatio':
            return /^(?:none|x(?:Min|Mid|Max)Y(?:Min|Mid|Max))(?:\s+(?:meet|slice))?$/;
        case 'text-anchor':
            return /^(?:start|middle|end)$/;
        case 'dominant-baseline':
            return /^(?:auto|middle|central|hanging|alphabetic|ideographic|mathematical|text-top|text-bottom|text-after-edge|text-before-edge)$/;
        case 'font-family':
            return /^[A-Za-z0-9\s,"'\-]{1,200}$/;
        case 'font-weight':
            return /^(?:normal|bold|bolder|lighter|[1-9]00)$/;
        case 'font-style':
            return /^(?:normal|italic|oblique)$/;
        case 'role': case 'aria-roledescription':
            return /^[A-Za-z0-9\s\-_]{1,100}$/;
        default:
            return /(?!)/;
    }
};

class UnsupportedDiagramError extends Error {}

const normalizeMermaidSource = (source) => String(source ?? '')
    .replace(/\r\n?/g, '\n')
    .replace(/^[ \t\n\r\0\x0B]+|[ \t\n\r\0\x0B]+$/g, '');

const hashMermaidSource = async (source) => {
    if (!window.crypto?.subtle) {
        throw new Error('crypto-unavailable');
    }

    const digest = await window.crypto.subtle.digest('SHA-256', new TextEncoder().encode(normalizeMermaidSource(source)));

    return [...new Uint8Array(digest)].map((byte) => byte.toString(16).padStart(2, '0')).join('');
};

const diagramAttributeAllowed = (element, attribute) => attribute === 'id'
    || DIAGRAM_ELEMENT_ATTRIBUTES[element].includes(attribute)
    || (!DIAGRAM_SHARED_EXCLUSIONS.includes(element) && DIAGRAM_SHARED_ATTRIBUTES.includes(attribute));

const normalizeDiagramValue = (attribute, rawValue) => {
    const value = String(rawValue ?? '').trim();

    if (value === '' || /[<>\\@;{}]|javascript:|data:|expression\s*\(|\/\*/i.test(value.replace(/url\([^)]*\)/gi, ''))) {
        return null;
    }

    if (/url\(/i.test(value)) {
        const fragment = value.match(/^url\(\s*["']?[^"'#)]*#([A-Za-z][A-Za-z0-9_\-:.]{0,127})["']?\s*\)$/);

        return DIAGRAM_REFERENCE_ATTRIBUTES.includes(attribute) && fragment ? `url(#${fragment[1]})` : null;
    }

    if (attribute === 'id') {
        return DIAGRAM_ID_PATTERN.test(value) ? value : null;
    }

    return diagramValuePattern(attribute).test(value) ? value : null;
};

const copyDiagramElement = (source, ids) => {
    const name = source.localName;

    if (source.namespaceURI !== SVG_NAMESPACE || !Object.hasOwn(DIAGRAM_ELEMENT_ATTRIBUTES, name)) {
        throw new UnsupportedDiagramError(name);
    }

    const target = document.createElementNS(SVG_NAMESPACE, name);

    [...source.attributes].forEach((attribute) => {
        if (attribute.namespaceURI || !diagramAttributeAllowed(name, attribute.name)) {
            return;
        }

        const value = normalizeDiagramValue(attribute.name, attribute.value);

        if (value === null || (attribute.name === 'id' && ids.has(value))) {
            return;
        }

        if (attribute.name === 'id') {
            ids.add(value);
        }

        target.setAttribute(attribute.name, value);
    });

    const computed = getComputedStyle(source);
    const computedProperties = [
        ...(DIAGRAM_PAINTED_ELEMENTS.includes(name) ? DIAGRAM_PAINT_PROPERTIES : name === 'g' ? ['opacity'] : []),
        ...(name === 'text' || name === 'tspan' ? DIAGRAM_TEXT_PROPERTIES : []),
    ];

    computedProperties.forEach((property) => {
        const value = normalizeDiagramValue(property, computed.getPropertyValue(property));

        if (value !== null) {
            target.setAttribute(property, value);
        }
    });

    source.childNodes.forEach((child) => {
        if (child.nodeType === Node.ELEMENT_NODE) {
            if (!DIAGRAM_DROPPED_ELEMENTS.includes(child.localName)) {
                target.appendChild(copyDiagramElement(child, ids));
            }
        } else if (child.nodeType === Node.TEXT_NODE) {
            if (DIAGRAM_TEXT_ELEMENTS.includes(name)) {
                target.appendChild(document.createTextNode(child.nodeValue));
            } else if (child.nodeValue.trim() !== '') {
                throw new UnsupportedDiagramError('text');
            }
        }
    });

    return target;
};

const removeDanglingDiagramReferences = (svg, ids) => {
    svg.querySelectorAll('*').forEach((element) => {
        DIAGRAM_REFERENCE_ATTRIBUTES.forEach((attribute) => {
            const reference = element.getAttribute(attribute)?.match(/^url\(#(.+)\)$/);

            if (reference && !ids.has(reference[1])) {
                element.removeAttribute(attribute);
            }
        });
    });
};

const flattenMermaidSvg = (markup) => {
    const parsed = new DOMParser().parseFromString(markup, 'image/svg+xml');

    if (parsed.querySelector('parsererror') || parsed.documentElement.localName !== 'svg') {
        throw new UnsupportedDiagramError('parsererror');
    }

    const unsupported = parsed.querySelector('script, foreignObject, image, use, a, iframe, object, embed, switch, animate, set');

    if (unsupported) {
        throw new UnsupportedDiagramError(unsupported.localName);
    }

    const host = document.createElement('div');
    host.setAttribute('aria-hidden', 'true');
    host.style.cssText = 'position:fixed;left:-100000px;top:0;width:1600px;visibility:hidden;pointer-events:none;';

    const rendered = document.importNode(parsed.documentElement, true);
    host.appendChild(rendered);
    document.body.appendChild(host);

    try {
        const ids = new Set();
        const svg = copyDiagramElement(rendered, ids);
        const viewBox = svg.getAttribute('viewBox')?.trim().split(/[\s,]+/).map(Number);

        if (viewBox?.length === 4 && viewBox.every(Number.isFinite)) {
            svg.setAttribute('width', String(Math.ceil(viewBox[2])));
            svg.setAttribute('height', String(Math.ceil(viewBox[3])));
        } else {
            svg.removeAttribute('width');
            svg.removeAttribute('height');
        }

        removeDanglingDiagramReferences(svg, ids);

        return new XMLSerializer().serializeToString(svg);
    } finally {
        host.remove();
    }
};

let captureSequence = 0;

const captureMermaidSvg = (source) => withMermaidLock(async () => {
    const mermaid = await loadMermaid();

    mermaid.initialize({
        securityLevel: 'strict',
        startOnLoad: false,
        theme: 'base',
        htmlLabels: false,
        themeVariables: mermaidThemeVariables(false),
    });
    activeMermaidTheme = null;

    try {
        const { svg } = await mermaid.render(`issue-diagram-capture-${++captureSequence}`, normalizeMermaidSource(source));

        return {
            svg: flattenMermaidSvg(svg),
            hash: await hashMermaidSource(source),
        };
    } finally {
        configureMermaidTheme(mermaid, isDarkMode());
    }
});

const issueDiagramForm = (action, messages = {}, fields = {}) => ({
    rendering: false,
    diagramError: '',

    init() {
        const clear = () => {
            this.diagramError = '';
            this.$wire[fields.svg ?? 'mermaid_svg'] = null;
            this.$wire[fields.hash ?? 'mermaid_source_hash'] = null;
        };
        this.$wire.$watch(fields.source ?? 'mermaid_diagram', clear);
        if (fields.language) {
            this.$wire.$watch(fields.language, clear);
        }
    },

    async save() {
        if (this.rendering) {
            return;
        }

        this.rendering = true;
        this.diagramError = '';

        try {
            const source = fields.language && this.$wire[fields.language] !== 'mermaid'
                ? ''
                : this.$wire[fields.source ?? 'mermaid_diagram'] ?? '';

            if (normalizeMermaidSource(source) === '') {
                this.$wire[fields.svg ?? 'mermaid_svg'] = null;
                this.$wire[fields.hash ?? 'mermaid_source_hash'] = null;
            } else {
                const { svg, hash } = await captureMermaidSvg(source);

                if (source !== (this.$wire[fields.source ?? 'mermaid_diagram'] ?? '')
                    || (fields.language && this.$wire[fields.language] !== 'mermaid')) {
                    throw new Error('source-changed');
                }

                this.$wire[fields.svg ?? 'mermaid_svg'] = svg;
                this.$wire[fields.hash ?? 'mermaid_source_hash'] = hash;
            }
        } catch (error) {
            this.$wire[fields.svg ?? 'mermaid_svg'] = null;
            this.$wire[fields.hash ?? 'mermaid_source_hash'] = null;
            this.diagramError = this.messageFor(error);
            this.rendering = false;
            console.error('Failed to prepare Mermaid diagram for saving.', error);
            this.$nextTick(() => this.$refs.diagramError?.focus());

            return;
        }

        try {
            await this.$wire.$call(action);
        } finally {
            this.rendering = false;
        }
    },

    messageFor(error) {
        if (error instanceof UnsupportedDiagramError) {
            return `${messages.unsupported ?? ''} (${error.message})`.trim();
        }

        if (error?.message === 'crypto-unavailable') {
            return messages.cryptoUnavailable ?? error.message;
        }

        if (error?.message === 'source-changed') {
            return messages.sourceChanged ?? error.message;
        }

        const detail = String(error?.message ?? error ?? '').split('\n')[0].slice(0, 300);

        return [messages.renderFailed, detail].filter(Boolean).join(' ');
    },
});

const registerAlpineData = () => window.Alpine?.data('issueDiagramForm', issueDiagramForm);

document.addEventListener('alpine:init', registerAlpineData);
registerAlpineData();
