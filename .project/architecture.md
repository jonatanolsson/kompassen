  # IDRC WCAG Reporter - System Architecture

  ## High-Level Overview

  Kompassen is a **database-backed Laravel application** that manages accessibility audits and produces HTML/PDF reports. Authenticated users work in team-scoped projects through Livewire/Volt components and Flux UI, while thin controllers handle nested project resources and report endpoints.

  Current high-level flow:

  ```
  Authenticated user → Team/project access → Pages and issues → WCAG/methodology data → HTML/PDF report or share link
  ```

  The markdown-file pipeline described in later sections is historical.

  ## Core System Components

  ### 1. **Application and access layer**
  **Responsibility**: Authenticate users and enforce team/project permissions.

  - Laravel routes and policies protect authenticated resources.
  - Project membership controls `view`, `update` and `delete` access.
  - Nested resources must remain scoped to their parent project.

  ### 2. **Audit data layer**
  **Responsibility**: Store and organize audit findings and metadata

  - **Accessibility projects** - Team-scoped audit containers with target WCAG level, status and audit date.
  - **Pages** - Audited URLs/services with descriptions and in/out-of-scope classification.
  - **Issues** - Findings with severity, difficulty, status/resolution, assignment, attachments and WCAG criteria.
  - **Reference data** - WCAG success criteria, examples, related resources and testing methodologies.
  - **Reports and share links** - Stored report snapshots and controlled guest previews.

  ### 3. **Livewire/Flux application layer**
  **Responsibility**: Provide interactive project, issue, knowledge-base, settings and dashboard workflows.

  - Livewire components own reactive forms and validation.
  - Volt/Blade views compose the application shell and legacy resource pages.
  - Flux components provide form fields, tables, dialogs, badges and navigation.

  ### 4. **Report generation layer**
  **Responsibility**: Transform and organize raw content for rendering

  **Key Functions:**
  - **Issue aggregator** - Loads project issues and their WCAG criteria.
  - Groups issues by WCAG guideline
  - Sorts by SC number
  - Associates severity levels
  - **Report builder** - Calculates issue statistics and renders the stored HTML snapshot.
  - **PDF generator** - Converts a stored report snapshot to PDF for download.

  **HTML Report Generator:**
- Converts processed data to HTML structure
- Applies styling and layout
- Creates navigation and table of contents
- Embeds metadata for accessibility
- Generates index pages and overview sections

**PDF Report Generator:**
- Converts HTML to PDF/UA-1 (universally accessible) format
- Maintains semantic structure for screen readers
- Preserves document outline and navigation
- Applies print-specific styling
- Ensures compliance with PDF accessibility standards

## Data Flow Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    INPUT DOCUMENTS                           │
├─────────────────────────────────────────────────────────────┤
│  Report Index (index.md)                                     │
│  - Metadata: title, evaluators, date, scope, etc.           │
│  - Main report content                                       │
│                                                              │
│  Issue Files (issues/*.md)                                   │
│  - One file per finding                                      │
│  - Metadata: SC references, severity, difficulty            │
│  - Content: problem, solutions, references                  │
│                                                              │
│  Reference Data (JSON/JS)                                    │
│  - WCAG Success Criteria definitions                        │
│  - Severity/difficulty options                              │
├─────────────────────────────────────────────────────────────┤
│              PROCESSING PIPELINE                             │
├─────────────────────────────────────────────────────────────┤
│  1. Content Parsing                                          │
│     - Extract frontmatter from each markdown file            │
│     - Parse human-readable content                          │
│                                                              │
│  2. Validation                                               │
│     - Check required fields present                          │
│     - Validate SC references format                         │
│     - Verify severity values                                │
│                                                              │
│  3. Organization                                             │
│     - Group issues by WCAG guideline                        │
│     - Sort by SC number                                      │
│     - Calculate issue statistics                            │
│                                                              │
│  4. Enrichment                                               │
│     - Add SC definitions                                     │
│     - Resolve language-specific text                        │
│     - Prepare navigation structure                          │
├─────────────────────────────────────────────────────────────┤
│              OUTPUT GENERATION                               │
├─────────────────────────────────────────────────────────────┤
│  HTML Report:                                                │
│  - Structured HTML with semantic markup                     │
│  - Navigation and table of contents                         │
│  - Styled for readability                                    │
│  - Embedded metadata (title, author, etc.)                  │
│                                                              │
│  PDF Report:                                                 │
│  - Generated from HTML output                               │
│  - PDF/UA-1 compliant (accessible PDF standard)            │
│  - Optimized for printing and distribution                 │
└─────────────────────────────────────────────────────────────┘
```

## Key Workflows

### Workflow 1: Creating an Audit Report

1. **Auditor creates report index** with metadata (title, evaluators, dates, scope)
2. **Auditor creates individual issue files** for each finding discovered
3. **System validates** all inputs are correct
4. **System processes** and organizes all findings
5. **System generates** HTML and PDF reports
6. **Auditor reviews** and distributes reports

### Workflow 2: Report Generation

1. **System discovers** all markdown files in designated directories
2. **System reads and parses** each file's frontmatter and content
3. **System validates** data integrity
4. **System queries** reference data (WCAG definitions, localization)
5. **System calculates** statistics and summaries
6. **System renders** reports in target formats
7. **System outputs** final HTML and PDF files

### Workflow 3: Report Navigation and Viewing

1. **User receives** HTML or PDF report
2. **User navigates** using table of contents
3. **User reviews** audit summary and scope
4. **User examines** issues organized by WCAG guideline
5. **User understands** severity and remediation difficulty
6. **User accesses** linked resources and references

## Multi-Language Support

The system supports multiple languages through:
- Language-specific reference data files (WCAG SC definitions, labels)
- Template-based rendering that uses language-appropriate text
- Automatic selection based on configuration
- Currently supported: English, Swedish, and extensible for other languages

## Key Design Principles

1. **Separation of Concerns** - Clear division between content, processing, and output
2. **Markdown as Canonical Format** - Simple, versionable, maintainable format for audit data
3. **Reference-Based Organization** - WCAG Success Criteria as the organizing principle
4. **Metadata-Driven** - Structured metadata for filtering, sorting, and classification
5. **Technology-Agnostic** - Core logic independent of specific rendering technology
6. **Accessibility First** - Both the reports and the system itself are accessible
